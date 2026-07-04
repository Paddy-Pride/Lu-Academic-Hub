<?php
/**
 * Application Bootstrap
 * Initialize all necessary components
 */

// Load configuration
$config = require_once __DIR__ . '/config.php';

// Set error reporting
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Load error handler
require_once __DIR__ . '/config/ErrorHandler.php';

// Load security headers
require_once __DIR__ . '/config/headers.php';

// Initialize timezone
date_default_timezone_set($config['app']['timezone']);

// Define constants
define('APP_NAME', $config['app']['name']);
define('APP_VERSION', $config['app']['version']);
define('MAX_UPLOAD_SIZE', $config['uploads']['max_size']);
define('ALLOWED_UPLOAD_TYPES', implode(',', $config['uploads']['allowed_types']));
define('UPLOAD_DIR', $config['uploads']['upload_dir']);
define('SESSION_TIMEOUT', $config['session']['timeout']);

// Create upload directories
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

foreach (['papers', 'materials', 'temp', 'quarantine'] as $dir) {
    if (!is_dir(UPLOAD_DIR . $dir)) {
        mkdir(UPLOAD_DIR . $dir, 0755, true);
    }
}

// Database initialization will happen in each endpoint
return $config;
?>