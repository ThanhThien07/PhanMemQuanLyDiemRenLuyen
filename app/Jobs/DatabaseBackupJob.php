<?php

namespace App\Jobs;

use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DatabaseBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Bắt đầu tự động sao lưu cơ sở dữ liệu qua Job...");
        $result = BackupService::createBackup(false);
        if ($result['success']) {
            Log::info("Tự động sao lưu cơ sở dữ liệu hoàn tất thành công: " . $result['file']);
        } else {
            Log::error("Tự động sao lưu cơ sở dữ liệu thất bại: " . $result['error']);
        }
    }
}
