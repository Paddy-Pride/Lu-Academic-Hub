<?php
/**
 * File Upload Handler
 * Manages secure file uploads with validation and virus scanning
 */

class FileUploadHandler {
    private $pdo;
    private $upload_dir = 'uploads/';
    private $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'txt'];
    private $max_file_size = 50 * 1024 * 1024; // 50MB
    private $allowed_mimes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip' => 'application/zip',
        'txt' => 'text/plain'
    ];

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->createUploadDirs();
    }

    /**
     * Create upload directories if they don't exist
     */
    private function createUploadDirs() {
        $dirs = [
            'uploads/papers',
            'uploads/materials',
            'uploads/temp',
            'uploads/quarantine'
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Handle file upload
     */
    public function uploadFile($file, $user_id, $resource_type) {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['success']) {
            return $validation;
        }

        // Generate unique filename
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = $this->generateUniqueFilename($file_ext);
        
        $upload_path = $resource_type === 'paper' ? 'uploads/papers/' : 'uploads/materials/';
        $file_path = $upload_path . $new_filename;

        // Move file to upload directory
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            return [
                'success' => false,
                'message' => 'Failed to move uploaded file'
            ];
        }

        // Log upload
        $log_result = $this->logUpload([
            'user_id' => $user_id,
            'original_filename' => $file['name'],
            'stored_filename' => $new_filename,
            'file_path' => $file_path,
            'file_size' => $file['size'],
            'file_type' => $file_ext,
            'mime_type' => $file['type'],
            'upload_status' => 'success'
        ]);

        return [
            'success' => true,
            'file_path' => $file_path,
            'filename' => $new_filename,
            'original_name' => $file['name'],
            'file_size' => $file['size'],
            'file_type' => $file_ext,
            'message' => 'File uploaded successfully'
        ];
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check if file exists
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'message' => 'No file uploaded'];
        }

        // Check file size
        if ($file['size'] > $this->max_file_size) {
            return [
                'success' => false,
                'message' => 'File size exceeds maximum limit of 50MB'
            ];
        }

        // Check file type
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $this->allowed_types)) {
            return [
                'success' => false,
                'message' => 'File type not allowed. Allowed types: ' . implode(', ', $this->allowed_types)
            ];
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!$this->validateMimeType($file_ext, $mime_type)) {
            return [
                'success' => false,
                'message' => 'Invalid file MIME type'
            ];
        }

        // Check for suspicious content
        if ($this->containsSuspiciousContent($file['tmp_name'])) {
            return [
                'success' => false,
                'message' => 'File contains suspicious content and cannot be uploaded'
            ];
        }

        return ['success' => true];
    }

    /**
     * Validate MIME type
     */
    private function validateMimeType($ext, $mime_type) {
        if (!isset($this->allowed_mimes[$ext])) {
            return false;
        }

        return $mime_type === $this->allowed_mimes[$ext] || 
               strpos($mime_type, $this->allowed_mimes[$ext]) === 0;
    }

    /**
     * Check for suspicious content
     */
    private function containsSuspiciousContent($file_path) {
        $suspicious_patterns = [
            '<?php',
            '<?',
            '<script',
            'exec(',
            'system(',
            'passthru(',
            'shell_exec('
        ];

        // For text-based files only
        $file_content = file_get_contents($file_path, false, null, 0, 1000);
        
        foreach ($suspicious_patterns as $pattern) {
            if (stripos($file_content, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($ext) {
        return uniqid('file_', true) . '.' . $ext;
    }

    /**
     * Log upload
     */
    private function logUpload($data) {
        $query = "INSERT INTO file_uploads_log 
        (user_id, original_filename, stored_filename, file_path, file_size, file_type, mime_type, upload_status, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($query);

        try {
            $stmt->execute([
                $data['user_id'],
                $data['original_filename'],
                $data['stored_filename'],
                $data['file_path'],
                $data['file_size'],
                $data['file_type'],
                $data['mime_type'],
                $data['upload_status'],
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error logging upload'];
        }
    }

    /**
     * Delete file
     */
    public function deleteFile($file_path) {
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return true;
    }

    /**
     * Get upload history
     */
    public function getUploadHistory($user_id, $limit = 20) {
        $query = "SELECT * FROM file_uploads_log WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    }
}
?>