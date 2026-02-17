<?php

/**
 * Fetch Gata AJAX handler
 */
require_once __DIR__ . '/core/init.php';

// Validate CSRF token
if (!SecurityUtility::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die('Invalid CSRF token.');
}

if (!empty($_POST["id"])) {
    $site_id = SecurityUtility::sanitizeInput($_POST['id'], 'int');
    if ($site_id) {
        $db = \App\Core\App::database();
        $gata_list = $db->fetchAll("SELECT gata_id, gata_no FROM gata_master WHERE site_id = :site_id ORDER BY gata_no ASC", ['site_id' => $site_id]);

        if (count($gata_list) > 0) {
            echo '<option value="">Select Gata</option>';
            foreach ($gata_list as $row) {
                echo '<option value="' . h($row['gata_id']) . '">' . h($row['gata_no']) . '</option>';
            }
        } else {
            echo '<option value="">No Gata Available</option>';
        }
    }
}

if (!empty($_POST['sid'])) {
    $gata_id = SecurityUtility::sanitizeInput($_POST['sid'], 'int');
    if ($gata_id) {
        // Find plots associated with this gata
        $db = \App\Core\App::database();
        $plots = $db->fetchAll("SELECT plot_id, plot_no FROM plot_master WHERE gata_a = :gata_a OR gata_b = :gata_b OR gata_c = :gata_c OR gata_d = :gata_d ORDER BY plot_no ASC", [
            'gata_a' => $gata_id,
            'gata_b' => $gata_id,
            'gata_c' => $gata_id,
            'gata_d' => $gata_id
        ]);

        if (count($plots) > 0) {
            echo '<option value="">Select Plot</option>';
            foreach ($plots as $row) {
                echo '<option value="' . h($row['plot_id']) . '">' . h($row['plot_no']) . '</option>';
            }
        } else {
            echo '<option value="">No Plots Available</option>';
        }
    }
}
