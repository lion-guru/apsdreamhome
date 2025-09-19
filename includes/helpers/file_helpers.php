<?php
/**
 * File Helper Functions
 */

/**
 * Ensure upload directory exists and is writable
 * @param string $subdir Subdirectory under uploads (e.g., 'properties')
 * @return string Full path to the upload directory
 * @throws Exception If directory cannot be created or is not writable
 */
function ensure_upload_dir($subdir = '') {
    $upload_dir = dirname(dirname(dirname(__DIR__))) . '/uploads';
    
    if (!empty($subdir)) {
        $upload_dir .= '/' . trim($subdir, '/');
    }
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception("Failed to create upload directory: " . $upload_dir);
        }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        throw new Exception("Upload directory is not writable: " . $upload_dir);
    }
    
    return $upload_dir;
}

/**
 * Handle file upload
 * @param array $file $_FILES array element
 * @param string $subdir Subdirectory under uploads
 * @return string Path to the uploaded file relative to the uploads directory
 * @throws Exception If upload fails
 */
function handle_file_upload($file, $subdir = '') {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error: " . $file['error']);
    }
    
    // Ensure upload directory exists
    $upload_dir = ensure_upload_dir($subdir);
    
    // Generate a unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . strtolower($file_extension);
    $destination = $upload_dir . '/' . $filename;
    
    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to move uploaded file");
    }
    
    // Return the relative path
    $relative_path = 'uploads/' . (!empty($subdir) ? $subdir . '/' : '') . $filename;
    return $relative_path;
}

/**
 * Delete a file
 * @param string $filepath Relative path to the file from the web root
 * @return bool True on success, false on failure
 */
function delete_uploaded_file($filepath) {
    $full_path = dirname(dirname(dirname(__DIR__))) . '/' . ltrim($filepath, '/');
    
    if (file_exists($full_path) && is_file($full_path)) {
        return unlink($full_path);
    }
    
    return false;
}
