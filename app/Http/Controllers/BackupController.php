<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class BackupController extends Controller
{
    /**
     * Display the backup dashboard.
     */
    public function index()
    {
        $settings = BackupService::getSettings();
        $history = BackupService::getHistory();

        // Scan files in storage/app/backups/
        $files = Storage::files('backups');
        $backups = [];
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext === 'sql' || $ext === 'zip') {
                $name = basename($file);
                $backups[] = [
                    'name' => $name,
                    'size' => Storage::size($file),
                    'created_at' => Carbon::createFromTimestamp(Storage::lastModified($file))->toDateTimeString(),
                    'ext' => $ext
                ];
            }
        }

        // Sort backups by creation date descending
        usort($backups, function ($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        // Fetch database stats for a premium dashboard feel
        $dbName = DB::getDatabaseName();
        $tables = [];
        $dbSize = 0;
        try {
            $tablesQuery = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $tables = array_map(function ($t) {
                return array_values((array)$t)[0];
            }, $tablesQuery);

            $sizeQuery = DB::select("
                SELECT SUM(data_length + index_length) AS size 
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$dbName]);
            $dbSize = $sizeQuery[0]->size ?? 0;
        } catch (Exception $e) {
            // Ignore database stat fetch error on non-mysql
        }

        return view('backup.index', compact('settings', 'history', 'backups', 'dbName', 'tables', 'dbSize'));
    }

    /**
     * Run manual backup.
     */
    public function runManual()
    {
        $result = BackupService::createBackup(true);

        if ($result['success']) {
            return redirect()->route('backup.index')->with('success', 'Đã tạo bản sao lưu dữ liệu thủ công thành công: ' . $result['file']);
        } else {
            return redirect()->route('backup.index')->with('warning', 'Sao lưu dữ liệu thất bại: ' . $result['error']);
        }
    }

    /**
     * Update backup configuration.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'interval_weeks' => 'required|integer|in:1,2',
        ]);

        $settings = BackupService::getSettings();
        
        $oldInterval = intval($settings['interval_weeks'] ?? 1);
        $newInterval = intval($request->interval_weeks);

        $settings['enabled'] = (bool)$request->enabled;
        $settings['interval_weeks'] = $newInterval;

        // Recompute next backup run time if interval changed or last backup exists
        if ($oldInterval !== $newInterval || empty($settings['next_backup_at'])) {
            $baseTime = $settings['last_backup_at'] ? Carbon::parse($settings['last_backup_at']) : Carbon::now();
            $settings['next_backup_at'] = $baseTime->addWeeks($newInterval)->toDateTimeString();
        }

        BackupService::saveSettings($settings);

        return redirect()->route('backup.index')->with('success', 'Cập nhật cấu hình sao lưu tự động thành công!');
    }

    /**
     * Download backup file.
     */
    public function download($file)
    {
        // Simple security validation against path traversal
        if (str_contains($file, '..') || str_contains($file, '/') || str_contains($file, '\\')) {
            abort(403, 'Đường dẫn tệp không hợp lệ.');
        }

        if (!Storage::exists('backups/' . $file)) {
            abort(404, 'Không tìm thấy tệp sao lưu.');
        }

        return Storage::download('backups/' . $file);
    }

    /**
     * Delete backup file.
     */
    public function delete($file)
    {
        if (str_contains($file, '..') || str_contains($file, '/') || str_contains($file, '\\')) {
            abort(403, 'Đường dẫn tệp không hợp lệ.');
        }

        if (Storage::exists('backups/' . $file)) {
            Storage::delete('backups/' . $file);
            return redirect()->route('backup.index')->with('success', 'Đã xóa bản sao lưu ' . $file);
        }

        return redirect()->route('backup.index')->with('warning', 'Không tìm thấy bản sao lưu cần xóa.');
    }

    /**
     * Restore database from file.
     */
    public function restore($file)
    {
        if (str_contains($file, '..') || str_contains($file, '/') || str_contains($file, '\\')) {
            abort(403, 'Đường dẫn tệp không hợp lệ.');
        }

        try {
            BackupService::restoreBackup($file);
            return redirect()->route('backup.index')->with('success', 'Đã khôi phục dữ liệu hệ thống thành công về trạng thái của bản sao lưu: ' . $file);
        } catch (Exception $e) {
            return redirect()->route('backup.index')->with('warning', 'Khôi phục dữ liệu thất bại: ' . $e->getMessage());
        }
    }

    /**
     * Restore database by uploading SQL/ZIP file.
     */
    public function restoreUpload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,zip|max:51200', // 50MB max
        ], [
            'backup_file.required' => 'Vui lòng chọn tệp tin sao lưu.',
            'backup_file.mimes' => 'Hệ thống chỉ chấp nhận tệp định dạng .sql hoặc .zip.',
            'backup_file.max' => 'Dung lượng tệp tải lên tối đa là 50MB.',
        ]);

        try {
            $uploadedFile = $request->file('backup_file');
            $originalName = $uploadedFile->getClientOriginalName();
            
            // Generate unique temp name
            $tempName = 'upload_' . uniqid() . '_' . $originalName;
            
            // Save to backups folder temporarily
            $uploadedFile->storeAs('backups', $tempName);

            // Restore from this file
            BackupService::restoreBackup($tempName);

            // Delete the temp upload file after successful restore
            if (Storage::exists('backups/' . $tempName)) {
                Storage::delete('backups/' . $tempName);
            }

            return redirect()->route('backup.index')->with('success', 'Đã tải lên và khôi phục dữ liệu hệ thống thành công từ tệp: ' . $originalName);

        } catch (Exception $e) {
            // Ensure temp file gets deleted if it exists
            if (isset($tempName) && Storage::exists('backups/' . $tempName)) {
                Storage::delete('backups/' . $tempName);
            }

            return redirect()->route('backup.index')->with('warning', 'Khôi phục dữ liệu thất bại: ' . $e->getMessage());
        }
    }
}
