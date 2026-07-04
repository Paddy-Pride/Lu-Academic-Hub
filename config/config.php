<?php
/**
 * Configuration File
 * Centralized configuration for the application
 */

return [
    // Database Configuration
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'lu_academic_hub',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4'
    ],

    // Application Settings
    'app' => [
        'name' => 'Lu-Academic-Hub',
        'version' => '1.0.0',
        'debug' => getenv('DEBUG') ?: false,
        'timezone' => 'UTC'
    ],

    // File Upload Settings
    'uploads' => [
        'max_size' => 50 * 1024 * 1024, // 50MB
        'allowed_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'txt'],
        'upload_dir' => 'uploads/',
        'temp_dir' => 'uploads/temp/',
        'quarantine_dir' => 'uploads/quarantine/'
    ],

    // Session Configuration
    'session' => [
        'timeout' => 3600, // 1 hour
        'name' => 'lu_academic_session',
        'secure' => false, // Set to true in production with HTTPS
        'httponly' => true
    ],

    // Pagination
    'pagination' => [
        'default_limit' => 12,
        'max_limit' => 100
    ],

    // Email Settings
    'email' => [
        'from_name' => 'Lu-Academic-Hub',
        'from_email' => getenv('FROM_EMAIL') ?: 'noreply@luacademic.edu',
        'smtp_host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'smtp_port' => getenv('SMTP_PORT') ?: 587,
        'smtp_user' => getenv('SMTP_USER') ?: '',
        'smtp_password' => getenv('SMTP_PASSWORD') ?: ''
    ],

    // API Settings
    'api' => [
        'rate_limit' => 100, // requests per hour
        'enable_cors' => true,
        'allowed_origins' => ['*'] // Restrict in production
    ],

    // Security
    'security' => [
        'password_min_length' => 8,
        'enable_2fa' => false,
        'csrf_token_length' => 32
    ]
];
?>