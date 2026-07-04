<?php
/**
 * Global Error Handler & Logging
 */

class ErrorHandler {
    public static function init() {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        $error_types = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED'
        ];

        $type = $error_types[$errno] ?? 'UNKNOWN';
        $message = "[$type] $errstr in $errfile:$errline";
        
        self::log($message);
        
        if ($errno === E_ERROR || $errno === E_PARSE || $errno === E_CORE_ERROR) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
            exit;
        }
        
        return true;
    }

    public static function handleException($exception) {
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();
        
        self::log("Exception: $message in $file:$line\nTrace: $trace");
        
        http_response_code(500);
        echo json_encode(['error' => 'An unexpected error occurred']);
        exit;
    }

    public static function handleShutdown() {
        $error = error_get_last();
        if ($error !== null) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    public static function log($message) {
        $log_file = 'logs/errors.log';
        
        if (!is_dir('logs')) {
            mkdir('logs', 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message\n";
        
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}

// Initialize error handling
ErrorHandler::init();
?>