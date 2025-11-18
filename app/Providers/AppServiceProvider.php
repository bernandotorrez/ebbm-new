<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Force HTTPS in production if behind proxy
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        // Handle dynamic APP_URL for Docker/Production
        if (isset($_SERVER['HTTP_HOST'])) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
                        ? 'https' : 'http';
            
            $host = $_SERVER['HTTP_HOST'];
            $dynamicUrl = $protocol . '://' . $host;
            
            // Only update if different from config
            if (config('app.url') !== $dynamicUrl) {
                config(['app.url' => $dynamicUrl]);
                URL::forceRootUrl($dynamicUrl);
            }
        }
    }
}
