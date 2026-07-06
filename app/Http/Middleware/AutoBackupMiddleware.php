<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\BackupService;
use App\Jobs\DatabaseBackupJob;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoBackupMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only trigger check on standard GET page loads, avoiding AJAX/POST requests
        if ($request->isMethod('GET') && !$request->ajax()) {
            try {
                $settings = BackupService::getSettings();
                
                if (!empty($settings['enabled']) && !empty($settings['next_backup_at'])) {
                    $nextBackup = Carbon::parse($settings['next_backup_at']);
                    
                    if (Carbon::now()->greaterThanOrEqualTo($nextBackup)) {
                        // Apply safety lock by setting next backup 1 hour in the future immediately
                        // to prevent duplicate jobs from concurrent requests
                        $settings['next_backup_at'] = Carbon::now()->addHour()->toDateTimeString();
                        BackupService::saveSettings($settings);

                        // Dispatch the backup job to the queue
                        DatabaseBackupJob::dispatch();
                        
                        Log::info("Đã kích hoạt sao lưu tự động ngầm qua Middleware.");
                    }
                }
            } catch (\Exception $e) {
                // Fail silently to avoid interrupting the end-user request
                Log::error("Lỗi khi tự động kích hoạt sao lưu trong Middleware: " . $e->getMessage());
            }
        }

        return $next($request);
    }
}
