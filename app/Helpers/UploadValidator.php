<?php
/**
 * Lightweight file upload validator
 * Usage: $result = UploadValidator::validate($_FILES['file'], ['types' => 'images', 'max_size' => 5]);
 *        if (!$result['valid']) { echo $result['error']; return; }
 *        $path = UploadValidator::store($_FILES['file'], 'assets/images/uploads');
 */
class UploadValidator
{
    private static $typePresets = [
        'images'    => ['mime' => ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml'], 'ext' => ['jpg','jpeg','png','gif','webp','svg']],
        'documents' => ['mime' => ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','text/csv','text/plain'], 'ext' => ['pdf','doc','docx','csv','txt']],
        'media'     => ['mime' => ['image/jpeg','image/png','image/gif','image/webp','video/mp4','video/webm'], 'ext' => ['jpg','jpeg','png','gif','webp','mp4','webm']],
        'csv'       => ['mime' => ['text/csv','text/plain','application/vnd.ms-excel'], 'ext' => ['csv','txt']],
        'backups'   => ['mime' => ['application/sql','application/gzip','application/x-gzip','application/octet-stream'], 'ext' => ['sql','gz','zip']],
    ];

    /**
     * Validate a single $_FILES entry.
     * @param array $file  Single $_FILES['name'] entry
     * @param array $opts  Options: types (string preset or array), max_size (MB), required (bool)
     * @return array ['valid' => bool, 'error' => string, 'sanitized_name' => string]
     */
    public static function validate(array $file, array $opts = []): array
    {
        $types    = $opts['types']    ?? 'images';
        $maxSize  = ($opts['max_size'] ?? 10) * 1024 * 1024; // MB → bytes
        $required = $opts['required'] ?? true;

        // Check upload error
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required
                ? ['valid' => false, 'error' => 'No file uploaded', 'sanitized_name' => '']
                : ['valid' => true,  'error' => '', 'sanitized_name' => ''];
        }
        if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Upload error code: ' . $file['error'], 'sanitized_name' => ''];
        }

        // Size check
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File too large (max ' . ($maxSize / 1048576) . 'MB)', 'sanitized_name' => ''];
        }
        if ($file['size'] === 0) {
            return ['valid' => false, 'error' => 'Empty file', 'sanitized_name' => ''];
        }

        // Resolve allowed types
        if (is_string($types) && isset(self::$typePresets[$types])) {
            $allowed = self::$typePresets[$types];
        } elseif (is_array($types)) {
            $allowed = $types;
        } else {
            $allowed = self::$typePresets['images'];
        }

        // Extension check
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed['ext'], true)) {
            return ['valid' => false, 'error' => 'File type .' . $ext . ' not allowed', 'sanitized_name' => ''];
        }

        // MIME type check (finfo-based)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowed['mime'], true)) {
            // Fallback: some servers report application/octet-stream for valid files
            if ($mime !== 'application/octet-stream') {
                return ['valid' => false, 'error' => 'File content type ' . $mime . ' not allowed', 'sanitized_name' => ''];
            }
        }

        // Path traversal check on original name
        if (strpos($file['name'], '..') !== false || strpos($file['name'], '/') !== false || strpos($file['name'], '\\') !== false) {
            return ['valid' => false, 'error' => 'Invalid filename', 'sanitized_name' => ''];
        }

        // Generate safe filename
        $safeName = self::safeFilename($file['name']);

        return ['valid' => true, 'error' => '', 'sanitized_name' => $safeName];
    }

    /**
     * Store a validated file to the target directory.
     * @param array $file      Single $_FILES entry
     * @param string $dir      Relative directory from project root (e.g. 'assets/images/uploads')
     * @param string $safeName Sanitized filename (from validate())
     * @return array ['success' => bool, 'path' => string, 'error' => string]
     */
    public static function store(array $file, string $dir, string $safeName = ''): array
    {
        $root = dirname(__DIR__, 2);
        $fullDir = $root . '/' . ltrim($dir, '/');

        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        if (empty($safeName)) {
            $safeName = self::safeFilename($file['name']);
        }

        $dest = $fullDir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['success' => false, 'path' => '', 'error' => 'Failed to move uploaded file'];
        }

        // Return web-relative path
        $webPath = str_replace($root, '', $dest);
        $webPath = str_replace('\\', '/', $webPath);

        return ['success' => true, 'path' => $webPath, 'error' => ''];
    }

    /**
     * Generate a safe filename: sanitize + prefix with timestamp.
     */
    public static function safeFilename(string $original): string
    {
        $ext  = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $base = pathinfo($original, PATHINFO_FILENAME);

        // Strip non-alphanumeric chars (keep hyphens, underscores)
        $base = preg_replace('/[^a-zA-Z0-9_-]/', '_', $base);
        $base = substr($base, 0, 80); // Limit length

        return $base . '_' . time() . '.' . $ext;
    }
}
