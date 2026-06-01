<?php

/**
 * WhatsApp Integration Settings
 * Configure WhatsApp Business API for chatbot
 */

if (!defined('BASE_PATH')) {
    if (session_status() === PHP_SESSION_NONE) {
        // Session started by controller
    }

    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/admin/login');
        exit;
    }
}


?>