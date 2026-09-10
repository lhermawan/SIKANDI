<?php
// Set storage path to /tmp for Vercel serverless environment
if (isset($_ENV['VERCEL']) || getenv('VERCEL')) {
    putenv('APP_CONFIG_CACHE=/tmp/config.php');
    putenv('APP_EVENTS_CACHE=/tmp/events.php');
    putenv('APP_PACKAGES_CACHE=/tmp/packages.php');
    putenv('APP_ROUTES_CACHE=/tmp/routes.php');
    putenv('APP_SERVICES_CACHE=/tmp/services.php');
    putenv('VIEW_COMPILED_PATH=/tmp');
    putenv('CACHE_STORE=array');
    putenv('SESSION_DRIVER=cookie');
    putenv('LOG_CHANNEL=stderr');
    
    // Create necessary directories in /tmp
    $tmpDirectories = ['/tmp/framework/views', '/tmp/framework/cache', '/tmp/framework/sessions', '/tmp/logs'];
    foreach ($tmpDirectories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
    
    // Copy SQLite DB to /tmp so it's writable
    if (!file_exists('/tmp/database.sqlite')) {
        copy(__DIR__ . '/../database/database.sqlite', '/tmp/database.sqlite');
    }
    putenv('DB_DATABASE=/tmp/database.sqlite');
}

require __DIR__ . '/../public/index.php';
