<?php
/**
 * Fetch Farmers AJAX handler
 */
require_once __DIR__ . '/core/init.php';

// Validate CSRF token
if (!SecurityUtility::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die('Invalid CSRF token.');
}

if (!empty($_POST['sid'])) {
    $gata_id = SecurityUtility::sanitizeInput($_POST['sid'], 'int');

    $db = \App\Core\App::database();
    $farmers = $db->fetchAll("SELECT kissan_id, k_name FROM kissan_master WHERE gata_a=? OR gata_b=? OR gata_c=? OR gata_d=?", [$gata_id, $gata_id, $gata_id, $gata_id]);
    
    if (count($farmers) > 0) {
        echo '<option value="">Select Farmer</option>';
        foreach ($farmers as $row2) {
            echo '<option value="' . h($row2['kissan_id']) . '">' . h($row2['k_name']) . '</option>';
        }
    } else {
        echo '<option value="">No Farmers found</option>';
    }
}

if (!empty($_POST['site_id'])) {
    $site_id = SecurityUtility::sanitizeInput($_POST['site_id'], 'int');

    $db = \App\Core\App::database();
    $farmers = $db->fetchAll("SELECT kissan_id, k_name FROM kissan_master WHERE site_id=?", [$site_id]);
    
    if (count($farmers) > 0) {
        echo '<option value="">Select Farmer</option>';
        foreach ($farmers as $row3) {
            echo '<option value="' . h($row3['kissan_id']) . '">' . h($row3['k_name']) . '</option>';
        }
    } else {
        echo '<option value="">No Farmers found</option>';
    }
}
?>

