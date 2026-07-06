<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Exception;
use Carbon\Carbon;

class BackupService
{
    protected static $settingsFile = 'backups/settings.json';
    protected static $historyFile = 'backups/history.json';

    /**
     * Get backup settings.
     */
    public static function getSettings()
    {
        if (!Storage::exists(self::$settingsFile)) {
            $defaultSettings = [
                'enabled' => true,
                'interval_weeks' => 1, // 1 or 2
                'last_backup_at' => null,
                'next_backup_at' => Carbon::now()->addWeeks(1)->toDateTimeString(),
            ];
            // Ensure backups directory exists
            if (!Storage::exists('backups')) {
                Storage::makeDirectory('backups');
            }
            Storage::put(self::$settingsFile, json_encode($defaultSettings, JSON_PRETTY_PRINT));
            return $defaultSettings;
        }

        try {
            $settings = json_decode(Storage::get(self::$settingsFile), true);
            if (!$settings || !is_array($settings)) {
                throw new Exception("Invalid settings file format");
            }
            return $settings;
        } catch (Exception $e) {
            $defaultSettings = [
                'enabled' => true,
                'interval_weeks' => 1,
                'last_backup_at' => null,
                'next_backup_at' => Carbon::now()->addWeeks(1)->toDateTimeString(),
            ];
            Storage::put(self::$settingsFile, json_encode($defaultSettings, JSON_PRETTY_PRINT));
            return $defaultSettings;
        }
    }

    /**
     * Save backup settings.
     */
    public static function saveSettings($settings)
    {
        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }
        Storage::put(self::$settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    }

    /**
     * Get backup logs/history.
     */
    public static function getHistory()
    {
        if (!Storage::exists(self::$historyFile)) {
            return [];
        }
        try {
            $history = json_decode(Storage::get(self::$historyFile), true);
            return is_array($history) ? $history : [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Add a backup run history log entry.
     */
    public static function addHistoryEntry($entry)
    {
        $history = self::getHistory();
        array_unshift($history, $entry); // Show newest first
        if (count($history) > 50) {
            array_pop($history);
        }
        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }
        Storage::put(self::$historyFile, json_encode($history, JSON_PRETTY_PRINT));
    }

    /**
     * Perform database export (backup).
     */
    public static function createBackup($isManual = false)
    {
        $startTime = microtime(true);
        $fileName = 'backup_' . date('Y_m_d_H_i_s');
        $sqlFileName = $fileName . '.sql';

        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }

        $sqlPath = Storage::path('backups/' . $sqlFileName);
        $handle = null;

        try {
            $pdo = DB::connection()->getPdo();
            $handle = fopen($sqlPath, 'w');
            if (!$handle) {
                throw new Exception("Cannot create temporary SQL file: " . $sqlPath);
            }

            // Headers
            fwrite($handle, "-- M&S Database Backup\n");
            fwrite($handle, "-- Generated at: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Type: " . ($isManual ? 'Manual' : 'Automatic') . "\n");
            fwrite($handle, "-- Database: " . DB::getDatabaseName() . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET NAMES utf8mb4;\n\n");

            // Fetch all tables
            $tablesQuery = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $tables = $tablesQuery->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "-- Structure for table `" . $table . "`\n");
                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "DROP TABLE IF EXISTS `" . $table . "`;\n");

                $createTableQuery = $pdo->query("SHOW CREATE TABLE `" . $table . "`");
                $createTableRow = $createTableQuery->fetch(\PDO::FETCH_ASSOC);
                $createSql = $createTableRow['Create Table'];
                fwrite($handle, $createSql . ";\n\n");

                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "-- Data for table `" . $table . "`\n");
                fwrite($handle, "-- --------------------------------------------------------\n");

                // Select table rows
                $dataQuery = $pdo->query("SELECT * FROM `" . $table . "`");
                $count = 0;
                while ($row = $dataQuery->fetch(\PDO::FETCH_ASSOC)) {
                    $keys = array_keys($row);
                    $escapedKeys = array_map(function($key) {
                        return "`" . $key . "`";
                    }, $keys);
                    
                    $values = array_values($row);
                    $escapedValues = array_map(function($value) use ($pdo) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return $pdo->quote($value);
                    }, $values);

                    $insertSql = "INSERT INTO `" . $table . "` (" . implode(', ', $escapedKeys) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                    fwrite($handle, $insertSql);
                    $count++;
                }
                fwrite($handle, "-- Dumped " . $count . " rows\n\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);
            $handle = null;

            // Compress file if ZipArchive class exists
            $finalFileName = $sqlFileName;
            if (class_exists('ZipArchive')) {
                $zipFileName = $fileName . '.zip';
                $zipPath = Storage::path('backups/' . $zipFileName);

                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                    $zip->addFile($sqlPath, $sqlFileName);
                    $zip->close();
                    
                    // Remove raw SQL
                    if (file_exists($sqlPath)) {
                        unlink($sqlPath);
                    }
                    $finalFileName = $zipFileName;
                }
            }

            $fileSize = Storage::size('backups/' . $finalFileName);
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            // Update settings for auto run next schedule
            $settings = self::getSettings();
            $settings['last_backup_at'] = Carbon::now()->toDateTimeString();
            
            // Re-calculate next backup date
            $intervalWeeks = intval($settings['interval_weeks'] ?? 1);
            $settings['next_backup_at'] = Carbon::now()->addWeeks($intervalWeeks)->toDateTimeString();
            self::saveSettings($settings);

            // History log
            $entry = [
                'file_name' => $finalFileName,
                'size' => $fileSize,
                'created_at' => Carbon::now()->toDateTimeString(),
                'type' => $isManual ? 'Thủ công' : 'Tự động',
                'status' => 'Thành công',
                'duration' => $executionTime,
                'error' => null,
            ];
            self::addHistoryEntry($entry);

            return [
                'success' => true,
                'file' => $finalFileName,
                'size' => $fileSize,
            ];

        } catch (Exception $e) {
            if ($handle) {
                fclose($handle);
            }
            if (file_exists($sqlPath)) {
                unlink($sqlPath);
            }

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            // History log on failure
            $entry = [
                'file_name' => null,
                'size' => 0,
                'created_at' => Carbon::now()->toDateTimeString(),
                'type' => $isManual ? 'Thủ công' : 'Tự động',
                'status' => 'Thất bại',
                'duration' => $executionTime,
                'error' => $e->getMessage(),
            ];
            self::addHistoryEntry($entry);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restore database from a backup file.
     */
    public static function restoreBackup($fileName)
    {
        if (!Storage::exists('backups/' . $fileName)) {
            throw new Exception("File sao lưu không tồn tại: " . $fileName);
        }

        $filePath = Storage::path('backups/' . $fileName);
        $tempSqlFile = null;

        if (pathinfo($fileName, PATHINFO_EXTENSION) === 'zip') {
            if (!class_exists('ZipArchive')) {
                throw new Exception("Thư viện ZipArchive không được kích hoạt trên PHP, không thể giải nén bản sao lưu ZIP.");
            }
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                $sqlName = $zip->getNameIndex(0);
                if (!$sqlName) {
                    throw new Exception("Bản sao lưu ZIP bị rỗng.");
                }
                $tempSqlFile = Storage::path('backups/temp_restore.sql');
                copy('zip://' . $filePath . '#' . $sqlName, $tempSqlFile);
                $zip->close();
            } else {
                throw new Exception("Không thể mở tệp nén ZIP.");
            }
        } else {
            $tempSqlFile = $filePath;
        }

        try {
            $pdo = DB::connection()->getPdo();
            $sqlContent = file_get_contents($tempSqlFile);
            if ($sqlContent === false) {
                throw new Exception("Không thể đọc tệp SQL khôi phục.");
            }

            // Disable FK checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

            // Execute query chunks
            $queries = self::splitSqlStatements($sqlContent);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $pdo->exec($query);
                }
            }

            // Re-enable FK checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

            // Clean up temp extracted file if it was extracted
            if ($fileName !== 'temp_restore.sql' && file_exists(Storage::path('backups/temp_restore.sql'))) {
                unlink(Storage::path('backups/temp_restore.sql'));
            }

            return true;

        } catch (Exception $e) {
            if ($fileName !== 'temp_restore.sql' && file_exists(Storage::path('backups/temp_restore.sql'))) {
                unlink(Storage::path('backups/temp_restore.sql'));
            }
            throw $e;
        }
    }

    /**
     * Split sql contents into distinct query strings.
     */
    private static function splitSqlStatements($sql)
    {
        $lines = explode("\n", $sql);
        $queries = [];
        $currentQuery = '';

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || str_starts_with($line, '--') || str_starts_with($line, '#') || str_starts_with($line, '/*')) {
                continue;
            }

            $currentQuery .= ' ' . $line;

            if (str_ends_with($line, ';')) {
                $queries[] = $currentQuery;
                $currentQuery = '';
            }
        }

        if (!empty(trim($currentQuery))) {
            $queries[] = $currentQuery;
        }

        return $queries;
    }
}
