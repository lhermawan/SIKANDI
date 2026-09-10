<?php
// Set storage path to /tmp for Vercel serverless environment
if (isset($_ENV['VERCEL'])) {
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
    $tmpDirectories = ['/tmp/framework/views', '/tmp/framework/cache', '/tmp/framework/sessions'];
    foreach ($tmpDirectories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}

require __DIR__ . '/../public/index.php';
