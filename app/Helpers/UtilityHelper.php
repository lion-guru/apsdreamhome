<?php

namespace App\Helpers;

/**
 * Utility Helper Class
 * Common utility functions for formatting, validation, and UI
 */
class UtilityHelper
{
    /**
     * Format amount as Indian currency
     * @param float $amount
     * @return string
     */
    public static function formatCurrency($amount)
    {
        return '₹' . number_format($amount, 2);
    }

    /**
     * Format date to Indian format
     * @param string $date
     * @return string
     */
    public static function formatDate($date)
    {
        return date('d M Y', strtotime($date));
    }

    /**
     * Format datetime to readable format
     * @param string $datetime
     * @return string
     */
    public static function formatDateTime($datetime)
    {
        return date('d M Y, h:i A', strtotime($datetime));
    }

    /**
     * Get Bootstrap badge class for status
     * @param string $status
     * @param string $context
     * @return string
     */
    public static function getStatusBadgeClass($status, $context = 'general')
    {
        $classes = [
            'general' => [
                'active' => 'success',
                'inactive' => 'danger',
                'pending' => 'warning',
                'approved' => 'success',
                'rejected' => 'danger',
                'verified' => 'info'
            ],
            'property' => [
                'available' => 'success',
                'sold' => 'danger',
                'rented' => 'info',
                'booked' => 'warning',
                'reserved' => 'primary'
            ],
            'booking' => [
                'confirmed' => 'success',
                'pending' => 'warning',
                'cancelled' => 'danger',
                'completed' => 'info'
            ],
            'payment' => [
                'completed' => 'success',
                'pending' => 'warning',
                'failed' => 'danger',
                'refunded' => 'info',
                'processing' => 'primary'
            ],
            'mlm' => [
                'paid' => 'success',
                'pending' => 'warning',
                'hold' => 'danger',
                'processing' => 'primary'
            ]
        ];

        return isset($classes[$context][$status]) ? $classes[$context][$status] : 'secondary';
    }

    /**
     * Validate email address
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitize input data
     * @param mixed $data
     * @return mixed
     */
    public static function sanitizeInput($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
            return $data;
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate pagination links
     * @param int $currentPage
     * @param int $totalPages
     * @param string $baseUrl
     * @return string
     */
    public static function getPaginationLinks($currentPage, $totalPages, $baseUrl)
    {
        if ($totalPages <= 1) {
            return '';
        }

        $links = [];

        // Previous link
        if ($currentPage > 1) {
            $links[] = '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">Previous</a></li>';
        } else {
            $links[] = '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }

        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);

        for ($i = $start; $i <= $end; $i++) {
            if ($i == $currentPage) {
                $links[] = '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $links[] = '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
            }
        }

        // Next link
        if ($currentPage < $totalPages) {
            $links[] = '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">Next</a></li>';
        } else {
            $links[] = '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }

        return '<ul class="pagination">' . implode("\n", $links) . '</ul>';
    }

    /**
     * Handle file upload with validation
     * @param array $file $_FILES array element
     * @param array $allowedTypes File extensions
     * @param int $maxSize Maximum file size in bytes
     * @return array
     */
    public static function handleFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880)
    {
        try {
            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('File upload error: ' . ($file['error'] ?? 'Unknown error'));
            }

            $fileInfo = pathinfo($file['name']);
            $extension = strtolower($fileInfo['extension'] ?? '');

            if (!in_array($extension, $allowedTypes)) {
                throw new \Exception('Invalid file type. Allowed types: ' . implode(', ', $allowedTypes));
            }

            if ($file['size'] > $maxSize) {
                throw new \Exception('File size too large. Maximum size: ' . ($maxSize / 1024 / 1024) . 'MB');
            }

            $newFilename = SecurityHelper::generateRandomString(16, false) . '.' . $extension;
            $uploadDir = SITE_ROOT_PATH . '/uploads/';
            
            // Create upload directory if not exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $uploadDir . $newFilename;

            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new \Exception('Failed to move uploaded file');
            }

            return [
                'success' => true,
                'filename' => $newFilename,
                'original_name' => $file['name'],
                'path' => $uploadPath,
                'url' => '/uploads/' . $newFilename,
                'size' => $file['size']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Truncate text to specified length
     * @param string $text
     * @param int $length
     * @param string $suffix
     * @return string
     */
    public static function truncateText($text, $length = 100, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Convert number to words (for amounts)
     * @param float $number
     * @return string
     */
    public static function numberToWords($number)
    {
        $formatter = new \NumberFormatter('en_IN', \NumberFormatter::SPELLOUT);
        return $formatter->format($number) . ' rupees';
    }

    /**
     * Format phone number to Indian format
     * @param string $phone
     * @return string
     */
    public static function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 10) {
            return '+91 ' . substr($phone, 0, 5) . ' ' . substr($phone, 5);
        }
        return $phone;
    }

    /**
     * Generate slug from string
     * @param string $string
     * @return string
     */
    public static function generateSlug($string)
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        $string = trim($string, '-');
        return $string;
    }

    /**
     * Get time ago string
     * @param string $datetime
     * @return string
     */
    public static function timeAgo($datetime)
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return self::formatDate($datetime);
        }
    }

    /**
     * Format file size
     * @param int $bytes
     * @return string
     */
    public static function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Mask sensitive data (like phone/email)
     * @param string $data
     * @param string $type
     * @return string
     */
    public static function maskData($data, $type = 'phone')
    {
        if ($type === 'phone') {
            $phone = preg_replace('/[^0-9]/', '', $data);
            if (strlen($phone) >= 10) {
                return substr($phone, 0, 2) . '******' . substr($phone, -2);
            }
        } elseif ($type === 'email') {
            $parts = explode('@', $data);
            if (count($parts) == 2) {
                $name = $parts[0];
                $domain = $parts[1];
                $maskedName = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
                return $maskedName . '@' . $domain;
            }
        }
        return $data;
    }
}
