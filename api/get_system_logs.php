<?php
/**
 * APS Dream Home - System Logs API
 * API endpoint for retrieving system logs
 */

require_once '../core/functions.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    send_json_response(['success' => false, 'message' => 'Unauthorized'], 401);
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $logs = [];

    // Read WhatsApp logs
    $whatsapp_log_file = '../logs/whatsapp.log';
    if (file_exists($whatsapp_log_file)) {
        $whatsapp_logs = file($whatsapp_log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_slice($whatsapp_logs, -10) as $log) {
            $data = json_decode($log, true);
            if ($data) {
                $logs[] = [
                    'timestamp' => $data['timestamp'],
                    'type' => 'WhatsApp',
                    'message' => "WhatsApp message to {$data['recipient']}: " . ($data['status'] === 'SENT' ? 'Sent' : 'Failed')
                ];
            }
        }
    }

    // Read Email logs
    $email_log_file = '../logs/email.log';
    if (file_exists($email_log_file)) {
        $email_logs = file($email_log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_slice($email_logs, -10) as $log) {
            $data = json_decode($log, true);
            if ($data) {
                $logs[] = [
                    'timestamp' => $data['timestamp'],
                    'type' => 'Email',
                    'message' => "Email to {$data['recipient']}: " . ($data['status'] === 'SENT' ? 'Sent' : 'Failed')
                ];
            }
        }
    }

    // Read AI logs
    $ai_log_file = '../logs/ai_interactions.log';
    if (file_exists($ai_log_file)) {
        $ai_logs = file($ai_log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_slice($ai_logs, -10) as $log) {
            $data = json_decode($log, true);
            if ($data) {
                $logs[] = [
                    'timestamp' => $data['timestamp'],
                    'type' => 'AI',
                    'message' => "AI interaction: {$data['interaction_type']}"
                ];
            }
        }
    }

    // Read admin action logs
    $admin_log_file = '../admin/logs/admin_actions.log';
    if (file_exists($admin_log_file)) {
        $admin_logs = file($admin_log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach (array_slice($admin_logs, -5) as $log) {
            $data = json_decode($log, true);
            if ($data && isset($data['data']['action'])) {
                $logs[] = [
                    'timestamp' => $data['timestamp'],
                    'type' => 'Admin',
                    'message' => "Admin action: {$data['data']['action']}"
                ];
            }
        }
    }

    // Sort logs by timestamp (most recent first)
    usort($logs, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    send_json_response([
        'success' => true,
        'logs' => array_slice($logs, 0, 20) // Return only last 20 logs
    ]);

} catch (Exception $e) {
    send_json_response(['success' => false, 'message' => 'Failed to retrieve logs: ' . $e->getMessage()], 500);
}
