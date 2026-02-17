<?php
// Security functions
function sanitizeInput($data)
{
    return h(strip_tags(trim($data)));
}

function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateRandomString($length = 10)
{
    return \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes($length));
}

// Database helper functions
function getPropertyTypes()
{
    $db = \App\Core\App::database();
    $types = [];
    $rows = $db->fetchAll("SELECT id, type FROM property_types ORDER BY type");
    foreach ($rows as $row) {
        $types[$row['id']] = $row['type'];
    }
    return $types;
}

function getPropertyById($id)
{
    $db = \App\Core\App::database();
    return $db->fetch("SELECT p.*, pt.type as property_type
                         FROM properties p
                         LEFT JOIN property_types pt ON p.type_id = pt.id
                         WHERE p.id = :id", ['id' => $id]);
}

function getCustomerById($id)
{
    $db = \App\Core\App::database();
    return $db->fetch("SELECT * FROM customers WHERE id = :id", ['id' => $id]);
}

function getBookingById($id)
{
    $db = \App\Core\App::database();
    return $db->fetch("SELECT b.*, c.name as customer_name, p.title as property_title
                         FROM bookings b
                         LEFT JOIN customers c ON b.customer_id = c.id
                         LEFT JOIN properties p ON b.property_id = p.id
                         WHERE b.id = :id", ['id' => $id]);
}

// Format functions
function formatCurrency($amount)
{
    return '₹' . number_format($amount, 2);
}

function formatDate($date)
{
    return date('d M Y', strtotime($date));
}

function getStatusBadgeClass($status, $context = 'general')
{
    $classes = [
        'general' => [
            'active' => 'success',
            'inactive' => 'danger',
            'pending' => 'warning'
        ],
        'property' => [
            'available' => 'success',
            'sold' => 'danger',
            'rented' => 'info'
        ],
        'booking' => [
            'confirmed' => 'success',
            'pending' => 'warning',
            'cancelled' => 'danger'
        ],
        'payment' => [
            'completed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'refunded' => 'info'
        ]
    ];

    return isset($classes[$context][$status]) ? $classes[$context][$status] : 'secondary';
}

// Pagination helper
function getPaginationLinks($currentPage, $totalPages, $baseUrl)
{
    $links = [];

    // Previous link
    if ($currentPage > 1) {
        $links[] = "<li class=\"page-item\"><a class=\"page-link\" href=\"{$baseUrl}?page=" . ($currentPage - 1) . "\">Previous</a></li>";
    } else {
        $links[] = "<li class=\"page-item disabled\"><span class=\"page-link\">Previous</span></li>";
    }

    // Page numbers
    for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
        if ($i == $currentPage) {
            $links[] = "<li class=\"page-item active\"><span class=\"page-link\">$i</span></li>";
        } else {
            $links[] = "<li class=\"page-item\"><a class=\"page-link\" href=\"{$baseUrl}?page=$i\">$i</a></li>";
        }
    }

    // Next link
    if ($currentPage < $totalPages) {
        $links[] = "<li class=\"page-item\"><a class=\"page-link\" href=\"{$baseUrl}?page=" . ($currentPage + 1) . "\">Next</a></li>";
    } else {
        $links[] = "<li class=\"page-item disabled\"><span class=\"page-link\">Next</span></li>";
    }

    return implode("\n", $links);
}

// File upload helper
function handleFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png'], $maxSize = 5242880)
{
    try {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowedTypes));
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('File size too large. Maximum size: ' . ($maxSize / 1024 / 1024) . 'MB');
        }

        $newFilename = \App\Helpers\SecurityHelper::generateRandomString(16, false) . '.' . $extension;
        $uploadPath = SITE_ROOT_PATH . '/uploads/' . $newFilename;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to move uploaded file');
        }

        return [
            'success' => true,
            'filename' => $newFilename,
            'path' => $uploadPath
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Activity logging
/**
 * Log admin activity
 */
function logAdminActivity($action, $details = '')
{
    if (!isset($_SESSION['admin_session'])) {
        return false;
    }

    $db = \App\Core\App::database();
    $username = $_SESSION['admin_session']['username'];

    return $db->query(
        "INSERT INTO admin_activity_log (admin_username, action, details) VALUES (:username, :action, :details)",
        [
            'username' => $username,
            'action' => $action,
            'details' => $details
        ]
    );
}
