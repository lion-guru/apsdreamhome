<?php

namespace App\Services\Legacy;
/**
 * File Upload Validation and Security Class
 * Handles secure file uploads with validation, sanitization, and virus scanning
 */

class FileValidator {
    private $allowedTypes;
    private $maxSize;
    private $uploadPath;
    private $errors = [];
    
    public function __construct($uploadPath = 'uploads', $maxSize = 5242880) { // 5MB default
        $this->uploadPath = rtrim($uploadPath, '/');
        $this->maxSize = $maxSize;
        $this->allowedTypes = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx']
        ];
    }
    
    public function setAllowedTypes($types) {
        $this->allowedTypes = $types;
    }
    
    public function setMaxSize($size) {
        $this->maxSize = $size;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function validateFile($file) {
        // Basic error checking
        if (!isset($file['error']) || is_array($file['error'])) {
            $this->errors[] = 'Invalid file parameters';
            return false;
        }
        
        // Check file upload errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->errors[] = 'File size exceeds limit';
                return false;
            case UPLOAD_ERR_PARTIAL:
                $this->errors[] = 'File was only partially uploaded';
                return false;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No file was uploaded';
                return false;
            default:
                $this->errors[] = 'Unknown upload error';
                return false;
        }
        
        // Check file size
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = 'File size exceeds limit';
            return false;
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        if (is_resource($finfo)) { finfo_close($finfo); }
        
        if (!array_key_exists($mimeType, $this->allowedTypes)) {
            $this->errors[] = 'Invalid file type';
            return false;
        }
        
        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes[$mimeType])) {
            $this->errors[] = 'Invalid file extension';
            return false;
        }
        
        return true;
    }
    
    public function uploadFile($file, $customName = null) {
        if (!$this->validateFile($file)) {
            return false;
        }
        
        // Generate safe filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $customName ? $customName . '.' . $extension : 
                   'file_' . \App\Helpers\SecurityHelper::generateRandomString(16, false) . '.' . $extension;
        
        // Ensure upload directory exists and is writable
        if (!is_dir($this->uploadPath)) {
            if (!mkdir($this->uploadPath, 0755, true)) {
                $this->errors[] = 'Failed to create upload directory';
                return false;
            }
        }
        
        if (!is_writable($this->uploadPath)) {
            $this->errors[] = 'Upload directory is not writable';
            return false;
        }
        
        $filepath = $this->uploadPath . '/' . $filename;
        
        // Move file with additional checks
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->errors[] = 'Failed to move uploaded file';
            return false;
        }
        
        // Set proper permissions
        chmod($filepath, 0644);
        
        return $filename;
    }
    
    public function scanForViruses($filepath) {
        // Implement virus scanning here if required
        // This is a placeholder for future implementation
        return true;
    }
}

// Create global file validator instance
$fileValidator = new FileValidator();
