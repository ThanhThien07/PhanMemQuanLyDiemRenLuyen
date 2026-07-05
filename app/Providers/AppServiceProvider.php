<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Run migrations automatically on non-local environments (like Railway)
        if (env('APP_ENV') !== 'local' && env('RUN_MIGRATIONS_ON_BOOT', true)) {
            try {
                $migrationFiles = glob(database_path('migrations/*.php'));
                $hash = md5(implode('', $migrationFiles));

                if (Cache::get('migrations_hash') !== $hash) {
                    Artisan::call('migrate', ['--force' => true]);
                    Cache::forever('migrations_hash', $hash);
                }
            } catch (\Throwable $e) {
                // If cache fails (e.g. database not migrated yet), we run migrations anyway
                try {
                    Artisan::call('migrate', ['--force' => true]);
                    try {
                        Cache::forever('migrations_hash', md5(implode('', glob(database_path('migrations/*.php')))));
                    } catch (\Throwable $ex) {}
                } catch (\Throwable $ex) {
                    // Ignore if database is not reachable/configured yet during build
                }
            }
        }
    }
}
