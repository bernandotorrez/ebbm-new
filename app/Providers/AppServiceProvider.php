<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;

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
        // Handle dynamic APP_URL for Docker/Production
        if (isset($_SERVER['HTTP_HOST'])) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
                        ? 'https' : 'http';
            
            $host = $_SERVER['HTTP_HOST'];
            $dynamicUrl = $protocol . '://' . $host;
            
            // Update APP_URL and Asset URL
            config(['app.url' => $dynamicUrl]);
            URL::forceRootUrl($dynamicUrl);
            
            // Force asset URL
            if (empty(config('app.asset_url'))) {
                config(['app.asset_url' => $dynamicUrl]);
            }
        }
        
        // Register custom JavaScript for Filament
        FilamentAsset::register([
            Js::make('fix-livewire-redirect', public_path('js/fix-livewire-redirect.js')),
        ]);
        
        // Force HTTPS in production if behind proxy (optional)
        // if ($this->app->environment('production')) {
        //     URL::forceScheme('https');
        // }
    }
}
