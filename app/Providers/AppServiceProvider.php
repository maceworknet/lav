<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settings = \App\Models\Setting::whereIn('key', ['google_client_id', 'google_client_secret'])->pluck('value', 'key')->toArray();
                config([
                    'services.google' => [
                        'client_id' => $settings['google_client_id'] ?? null,
                        'client_secret' => $settings['google_client_secret'] ?? null,
                        'redirect' => url('/auth/google/callback'),
                    ]
                ]);
            }
        } catch (\Exception $e) {
            // Avoid breaking during migrations/seeding/CLI when DB is not ready
        }
    }
}
