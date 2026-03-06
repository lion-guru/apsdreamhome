<?php

namespace App\Services\Security\Legacy;
/**
 * Enhanced Security File Upload Handler
 * Comprehensive secure file upload functionality for APS Dream Homes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Secure File Upload Class
 */
class SecureFileUpload {

    private $allowed_types = [];
    private $max_file_size = 0;
    private $upload_dir = '';
    private $security_log_file = '';

    /**
     * Constructor - Initialize secure upload settings
     */
    public function __construct($config = []) {
        // Set default secure configurations
        $this->upload_dir = $config['upload_dir'] ?? __DIR__ . '/../uploads/';
        $this->security_log_file = $config['log_file'] ?? __DIR__ . '/../logs/security.log';
        $this->max_file_size = $config['max_size'] ?? 10485760; // 10MB default

        // Set allowed file types based on context
        $this->setAllowedTypes($config['allowed_types'] ?? 'documents');

        // Ensure upload directory exists and is secure
        $this->secureUploadDirectory();
    }

    /**
     * Set allowed file types based on context
     */
    private function setAllowedTypes($context) {
        $type_configs = [
            'images' => [
                'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'
            ],
            'documents' => [
                'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
                'txt', 'csv', 'rtf', 'odt', 'ods', 'odp'
            ],
            'media' => [
                'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm',
                'mp3', 'wav', 'ogg', 'm4a'
            ],
            'archives' => [
                'zip', 'rar', '7z', 'tar', 'gz'
            ]
        ];

        $this->allowed_types = $type_configs[$context] ?? $type_configs['documents'];
    }

    /**
     * Ensure upload directory is secure
     */
    private function secureUploadDirectory() {
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }

        // Set secure permissions
        chmod($this->upload_dir, 0755);

        // Create .htaccess if it doesn't exist
        $htaccess_file = $this->upload_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "# Secure uploads directory\n";
            $htaccess_content .= "Order deny,allow\n";
            $htaccess_content .= "Deny from all\n";
            $htaccess_content .= "<FilesMatch \.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|ppt|pptx|txt|csv|zip|rar)$>\n";
            $htaccess_content .= "    Allow from all\n";
            $htaccess_content .= "</FilesMatch>\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
    }

    /**
     * Handle secure file upload
     */
    public function uploadFile($file_input_name, $options = []) {
        $response = [
            'success' => false,
            'message' => '',
            'file_path' => '',
            'file_name' => '',
            'file_size' => 0,
            'file_type' => ''
        ];

        try {
            // Check if file was uploaded
            if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error occurred');
            }

            $file = $_FILES[$file_input_name];

            // Validate file size
            if ($file['size'] > $this->max_file_size) {
                throw new Exception('File size exceeds maximum allowed size');
            }

            // Validate file type
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, $this->allowed_types)) {
                $this->logSecurityEvent('Invalid file type uploaded', [
                    'file_name' => $file['name'],
                    'file_type' => $file['type'],
                    'file_size' => $file['size'],
                    'allowed_types' => $this->allowed_types
                ]);
                throw new Exception('File type not allowed');
            }

            // Security checks
            $this->performSecurityChecks($file);

            // Generate secure filename
            $secure_filename = $this->generateSecureFilename($file['name']);

            // Create subdirectory structure for better organization
            $upload_path = $this->createSecureUploadPath($secure_filename);

            // Move uploaded file to secure location
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Failed to move uploaded file');
            }

            // Set secure permissions
            chmod($upload_path, 0644);

            // Log successful upload
            $this->logSecurityEvent('File uploaded successfully', [
                'original_name' => $file['name'],
                'secure_name' => $secure_filename,
                'file_size' => $file['size'],
                'file_type' => $file_extension,
                'upload_path' => $upload_path
            ]);

            $response = [
                'success' => true,
                'message' => 'File uploaded successfully',
                'file_path' => $upload_path,
                'file_name' => $secure_filename,
                'file_size' => $file['size'],
                'file_type' => $file_extension
            ];

        } catch (Exception $e) {
            $this->logSecurityEvent('File upload failed', [
                'error' => $e->getMessage(),
                'file_name' => $file['name'] ?? 'unknown',
                'file_size' => $file['size'] ?? 0
            ]);

            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Perform comprehensive security checks on uploaded file
     */
    private function performSecurityChecks($file) {
        // Check file size (additional validation)
        if ($file['size'] === 0) {
            throw new Exception('Uploaded file is empty');
        }

        // Check for malicious file patterns
        $file_name = $file['name'];
        $suspicious_patterns = [
            '/\.(php|php3|php4|php5|phtml|pl|cgi|py|jsp|asp|htm|shtml|sh|csh)\./i',
            '/(eval|base64|cmd|exec|system|shell|phpinfo|passthru|proc_open|popen)/i',
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload/i',
            '/onerror/i'
        ];

        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $file_name)) {
                throw new Exception('Suspicious file pattern detected');
            }
        }

        // Check file content for PHP code (basic check)
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            $content = fread($handle, 1024); // Read first 1KB
            fclose($handle);

            // Check for PHP opening tags
            if (preg_match('/<\?php/i', $content) || preg_match('/<\?/i', $content)) {
                throw new Exception('PHP code detected in uploaded file');
            }
        }

        // Check MIME type
        $allowed_mimes = $this->getAllowedMimeTypes();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        if (is_resource($finfo)) { finfo_close($finfo); }

        if (!in_array($mime_type, $allowed_mimes)) {
            throw new Exception('Invalid file MIME type');
        }
    }

    /**
     * Get allowed MIME types for current file types
     */
    private function getAllowedMimeTypes() {
        $mime_mappings = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'bmp' => ['image/bmp'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt' => ['text/plain'],
            'csv' => ['text/csv', 'application/csv'],
            'rtf' => ['application/rtf'],
            'odt' => ['application/vnd.oasis.opendocument.text'],
            'ods' => ['application/vnd.oasis.opendocument.spreadsheet'],
            'odp' => ['application/vnd.oasis.opendocument.presentation'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'rar' => ['application/x-rar-compressed'],
            '7z' => ['application/x-7z-compressed'],
            'tar' => ['application/x-tar'],
            'gz' => ['application/gzip']
        ];

        $allowed_mimes = [];
        foreach ($this->allowed_types as $ext) {
            if (isset($mime_mappings[$ext])) {
                $allowed_mimes = array_merge($allowed_mimes, $mime_mappings[$ext]);
            }
        }

        return array_unique($allowed_mimes);
    }

    /**
     * Generate secure filename
     */
    private function generateSecureFilename($original_name) {
        $extension = \pathinfo($original_name, PATHINFO_EXTENSION);
        $basename = \pathinfo($original_name, PATHINFO_FILENAME);

        // Sanitize filename
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);

        // Generate unique filename
        $timestamp = \time();
        $random = \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(4));

        return "{$timestamp}_{$random}_{$basename}.{$extension}";
    }

    /**
     * Create secure upload path with organized directory structure
     */
    private function createSecureUploadPath($filename) {
        // Create organized directory structure (year/month/day)
        $date = date('Y/m/d');
        $full_path = $this->upload_dir . '/' . $date;

        if (!is_dir($full_path)) {
            mkdir($full_path, 0755, true);
            chmod($full_path, 0755);
        }

        return $full_path . '/' . $filename;
    }

    /**
     * Log security events
     */
    private function logSecurityEvent($event, $data = []) {
        $log_file = $this->security_log_file;

        if (!file_exists($log_file)) {
            // Create log directory if it doesn't exist
            $log_dir = dirname($log_file);
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
        }

        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'event' => $event,
            'data' => $data,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        $log_message = json_encode($log_entry) . PHP_EOL;
        file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get file information for uploaded file
     */
    public function getFileInfo($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }

        return [
            'path' => $file_path,
            'name' => basename($file_path),
            'size' => filesize($file_path),
            'type' => mime_content_type($file_path),
            'modified' => filemtime($file_path),
            'url' => $this->getFileUrl($file_path)
        ];
    }

    /**
     * Get secure URL for uploaded file
     */
    private function getFileUrl($file_path) {
        $base_url = 'https://' . $_SERVER['HTTP_HOST'];
        $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file_path);
        return $base_url . $relative_path;
    }

    /**
     * Delete uploaded file securely
     */
    public function deleteFile($file_path) {
        if (!file_exists($file_path)) {
            return ['success' => false, 'message' => 'File not found'];
        }

        // Log deletion
        $this->logSecurityEvent('File deleted', [
            'file_path' => $file_path,
            'file_size' => filesize($file_path)
        ]);

        // Secure deletion
        if (unlink($file_path)) {
            return ['success' => true, 'message' => 'File deleted successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete file'];
        }
    }
}

/**
 * Helper function to create secure upload instance
 */
function createSecureUpload($config = []) {
    return new SecureFileUpload($config);
}

/**
 * Quick upload function for common use cases
 */
function uploadSecureFile($file_input_name, $type = 'documents', $max_size = 10485760) {
    $config = [
        'allowed_types' => $type,
        'max_size' => $max_size
    ];

    $uploader = new SecureFileUpload($config);
    return $uploader->uploadFile($file_input_name);
}
