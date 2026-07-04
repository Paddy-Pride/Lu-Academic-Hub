<?php
/**
 * Global Helper Functions
 * 
 * Reusable functions throughout the application
 * 
 * @category Helpers
 * @package LU Academic Hub
 * @author Paddy Pride
 */

// ============================================
// Session Functions
// ============================================

/**
 * Start secure session
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_TIMEOUT,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => SESSION_COOKIE_SECURE,
            'httponly' => SESSION_COOKIE_HTTPONLY,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * 
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * 
 * @return string|null
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check user role
 * 
 * @param string|array $role Role(s) to check
 * @return bool
 */
function hasRole($role) {
    $currentRole = getCurrentUserRole();
    if (is_array($role)) {
        return in_array($currentRole, $role);
    }
    return $currentRole === $role;
}

/**
 * Redirect user
 * 
 * @param string $location URL to redirect to
 * @param int $statusCode HTTP status code
 */
function redirect($location, $statusCode = 302) {
    header('Location: ' . $location, true, $statusCode);
    exit();
}

// ============================================
// Security Functions
// ============================================

/**
 * Generate CSRF token
 * 
 * @return string
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input
 * 
 * @param string $input Input to sanitize
 * @return string
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

/**
 * Escape output for HTML
 * 
 * @param mixed $data Data to escape
 * @return string
 */
function escape($data) {
    if (is_array($data)) {
        return array_map('escape', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 * 
 * @param string $email Email to validate
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password
 * 
 * @param string $password Password to hash
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
}

/**
 * Verify password
 * 
 * @param string $password Password to verify
 * @param string $hash Hash to compare
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ============================================
// String Functions
// ============================================

/**
 * Truncate string
 * 
 * @param string $string String to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to append
 * @return string
 */
function truncate($string, $length = 100, $suffix = '...') {
    if (strlen($string) <= $length) {
        return $string;
    }
    return substr($string, 0, $length) . $suffix;
}

/**
 * Convert string to slug
 * 
 * @param string $string String to slugify
 * @return string
 */
function slug($string) {
    $string = mb_strtolower($string, 'UTF-8');
    $string = preg_replace('/[^a-z0-9-]+/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Generate random string
 * 
 * @param int $length Length of string
 * @return string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// ============================================
// Date/Time Functions
// ============================================

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Get time ago string
 * 
 * @param string $date Date string
 * @return string
 */
function timeAgo($date) {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($diff / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

// ============================================
// Array Functions
// ============================================

/**
 * Get value from array with default
 * 
 * @param array $array Array to search
 * @param string $key Array key
 * @param mixed $default Default value
 * @return mixed
 */
function arrayGet($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Check if array has all keys
 * 
 * @param array $array Array to check
 * @param array $keys Keys to look for
 * @return bool
 */
function hasKeys($array, $keys) {
    return count(array_intersect_key(array_flip($keys), $array)) === count($keys);
}

// ============================================
// File Functions
// ============================================

/**
 * Format file size
 * 
 * @param int $bytes File size in bytes
 * @return string
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));

    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Get file extension
 * 
 * @param string $filename Filename
 * @return string
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file extension is allowed
 * 
 * @param string $filename Filename
 * @param array $allowed Allowed extensions
 * @return bool
 */
function isAllowedExtension($filename, $allowed = ALLOWED_EXTENSIONS) {
    $ext = getFileExtension($filename);
    return in_array($ext, $allowed);
}

/**
 * Generate unique filename
 * 
 * @param string $filename Original filename
 * @return string
 */
function generateUniqueFilename($filename) {
    $ext = getFileExtension($filename);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    return slug($name) . '_' . uniqid() . '.' . $ext;
}

// ============================================
// Logging Functions
// ============================================

/**
 * Log activity
 * 
 * @param string $action Action performed
 * @param string $details Action details
 * @param int $userId User ID
 * @param string $type Log type
 */
function logActivity($action, $details = '', $userId = null, $type = 'info') {
    if ($userId === null) {
        $userId = getCurrentUserId();
    }

    $logFile = LOGS_DIR . date('Y-m-d') . '.log';
    $timestamp = date(DATETIME_FORMAT);
    $logMessage = "[$timestamp] [$type] User:$userId | Action: $action | Details: $details\n";

    error_log($logMessage, 3, $logFile);
}

// ============================================
// JSON Response Functions
// ============================================

/**
 * Return JSON response
 * 
 * @param bool $success Success status
 * @param string $message Response message
 * @param array $data Response data
 * @param int $statusCode HTTP status code
 */
function jsonResponse($success, $message = '', $data = [], $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    exit();
}

// ============================================
// Validation Functions
// ============================================

/**
 * Validate form input
 * 
 * @param array $data Form data
 * @param array $rules Validation rules
 * @return array Errors
 */
function validateFormData($data, $rules) {
    $errors = [];

    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? '';
        $rulesList = explode('|', $fieldRules);

        foreach ($rulesList as $rule) {
            if ($rule === 'required' && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                break;
            } elseif ($rule === 'email' && !isValidEmail($value)) {
                $errors[$field] = 'Please enter a valid email address';
                break;
            } elseif (strpos($rule, 'min:') === 0) {
                $min = explode(':', $rule)[1];
                if (strlen($value) < $min) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . $min . ' characters';
                    break;
                }
            } elseif (strpos($rule, 'max:') === 0) {
                $max = explode(':', $rule)[1];
                if (strlen($value) > $max) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . $max . ' characters';
                    break;
                }
            }
        }
    }

    return $errors;
}

?>
