<?php
// app/Services/FileUploadService.php

class FileUploadService {
    private $allowedTypes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'text/plain' => ['txt'],
        'application/zip' => ['zip'],
        'application/x-rar-compressed' => ['rar']
    ];

    private $maxFileSize = 5242880; // 5MB
    private $uploadPath;

    public function __construct($uploadPath = null) {
        $this->uploadPath = $uploadPath ?: __DIR__ . '/../storage/uploads/';
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    public function uploadFile(array $file, $allowedTypes = null): array {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed with error code: ' . $file['error']);
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File is too large. Maximum size: ' . ($this->maxFileSize / 1024 / 1024) . 'MB');
        }

        // Verify MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $allowedMimes = $allowedTypes ?: array_keys($this->allowedTypes);
        if (!in_array($mime, $allowedMimes)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowedMimes));
        }

        // Check file extension matches MIME type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = $this->allowedTypes[$mime] ?? [];

        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('File extension does not match MIME type');
        }

        // Generate secure filename
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $this->uploadPath . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file');
        }

        // Set secure permissions
        chmod($destination, 0644);

        // Scan for viruses (basic check)
        $this->virusScan($destination);

        return [
            'filename' => $filename,
            'path' => $destination,
            'mime_type' => $mime,
            'size' => $file['size'],
            'original_name' => $file['name'],
            'extension' => $extension
        ];
    }

    public function uploadImage(array $file): array {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        return $this->uploadFile($file, $imageTypes);
    }

    public function uploadDocument(array $file): array {
        $docTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
        return $this->uploadFile($file, $docTypes);
    }

    private function virusScan($filePath): void {
        // Basic virus scan using file command
        $output = [];
        $returnVar = 0;
        exec("file \"$filePath\" 2>&1", $output, $returnVar);

        if ($returnVar !== 0) {
            unlink($filePath);
            throw new Exception('File scan failed');
        }

        $fileInfo = implode(' ', $output);

        // Check for suspicious patterns
        $suspiciousPatterns = [
            'executable',
            'script',
            'archive',
            'ELF',
            'PE32',
            'MS-DOS executable'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($fileInfo, $pattern) !== false) {
                unlink($filePath);
                throw new Exception('Suspicious file detected');
            }
        }
    }

    public function deleteFile($filename): bool {
        $filePath = $this->uploadPath . $filename;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    public function getFilePath($filename): string {
        return $this->uploadPath . $filename;
    }

    public function getWebPath($filename): string {
        return '/storage/uploads/' . $filename;
    }

    public function setMaxFileSize($size): void {
        $this->maxFileSize = $size;
    }

    public function setAllowedTypes(array $types): void {
        $this->allowedTypes = $types;
    }
}
