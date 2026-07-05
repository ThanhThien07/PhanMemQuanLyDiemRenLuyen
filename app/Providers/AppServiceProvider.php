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

                if (Cache::get('migrations_hash_v2') !== $hash) {
                    Artisan::call('migrate', ['--force' => true]);

                    // Seed database if empty
                    try {
                        if (\App\Models\User::count() === 0) {
                            Artisan::call('db:seed', ['--force' => true]);
                        }
                    } catch (\Throwable $seederError) {
                        // Ignore seeder errors
                    }

                    Cache::forever('migrations_hash_v2', $hash);
                }
            } catch (\Throwable $e) {
                // If cache fails (e.g. database not migrated yet), we run migrations anyway
                try {
                    Artisan::call('migrate', ['--force' => true]);

                    // Seed database if empty
                    try {
                        if (\App\Models\User::count() === 0) {
                            Artisan::call('db:seed', ['--force' => true]);
                        }
                    } catch (\Throwable $seederError) {}

                    try {
                        Cache::forever('migrations_hash_v2', md5(implode('', glob(database_path('migrations/*.php')))));
                    } catch (\Throwable $ex) {}
                } catch (\Throwable $ex) {
                    // Ignore if database is not reachable/configured yet during build
                }
            }
        }
    }
}
