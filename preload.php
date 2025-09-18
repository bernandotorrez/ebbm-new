<?php
/**
 * Laravel + Filament OPcache Preload Script
 * This script preloads essential Laravel and Filament classes into OPcache for better performance
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    die('Preload script should only be run from command line');
}

// Base application path
$basePath = __DIR__;

// Load Composer autoloader
require_once $basePath . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once $basePath . '/bootstrap/app.php';

// Preload core Laravel classes
$coreClasses = [
    \Illuminate\Foundation\Application::class,
    \Illuminate\Http\Request::class,
    \Illuminate\Http\Response::class,
    \Illuminate\Routing\Router::class,
    \Illuminate\Database\Eloquent\Model::class,
    \Illuminate\Database\Eloquent\Builder::class,
    \Illuminate\Database\Query\Builder::class,
    \Illuminate\Support\Collection::class,
    \Illuminate\Support\Facades\Route::class,
    \Illuminate\Support\Facades\DB::class,
    \Illuminate\Support\Facades\Cache::class,
    \Illuminate\Support\Facades\Log::class,
    \Illuminate\Support\Facades\Config::class,
    \Illuminate\View\Factory::class,
    \Illuminate\View\View::class,
];

// Preload Filament classes
$filamentClasses = [
    \Filament\FilamentServiceProvider::class,
    \Filament\Panel::class,
    \Filament\Resources\Resource::class,
    \Filament\Pages\Page::class,
    \Filament\Forms\Form::class,
    \Filament\Tables\Table::class,
];

// Combine all classes to preload
$allClasses = array_merge($coreClasses, $filamentClasses);

$preloadedCount = 0;
$errors = [];

foreach ($allClasses as $class) {
    try {
        if (class_exists($class) || interface_exists($class) || trait_exists($class)) {
            opcache_compile_file((new ReflectionClass($class))->getFileName());
            $preloadedCount++;
        }
    } catch (Throwable $e) {
        $errors[] = "Failed to preload {$class}: " . $e->getMessage();
    }
}

// Preload application files
$appFiles = [
    $basePath . '/app/Http/Kernel.php',
    $basePath . '/app/Providers/AppServiceProvider.php',
    $basePath . '/app/Providers/AuthServiceProvider.php',
    $basePath . '/app/Providers/EventServiceProvider.php',
    $basePath . '/app/Providers/RouteServiceProvider.php',
];

foreach ($appFiles as $file) {
    if (file_exists($file)) {
        try {
            opcache_compile_file($file);
            $preloadedCount++;
        } catch (Throwable $e) {
            $errors[] = "Failed to preload {$file}: " . $e->getMessage();
        }
    }
}

echo "OPcache preload completed!\n";
echo "Preloaded files: {$preloadedCount}\n";

if (!empty($errors)) {
    echo "Errors encountered:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
}

// Display current OPcache status
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    echo "\nOPcache Status:\n";
    echo "- Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
    echo "- Memory usage: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
    echo "- Hit rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
}