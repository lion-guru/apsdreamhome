<?php
namespace App\Http\Controllers;

class NotificationController 
{
    public function index() 
    {
        // Notification Dashboard
        include __DIR__ . "/../../views/notification/index.php";
    }
    
    public function templates() 
    {
        // Notification Templates
        include __DIR__ . "/../../views/notification/templates.php";
    }
    
    public function createTemplate() 
    {
        // Create Notification Template
        include __DIR__ . "/../../views/notification/create_template.php";
    }
    
    public function editTemplate($id) 
    {
        // Edit Notification Template
        include __DIR__ . "/../../views/notification/edit_template.php";
    }
    
    public function emailLogs() 
    {
        // Email Logs
        include __DIR__ . "/../../views/notification/email_logs.php";
    }
    
    public function smsLogs() 
    {
        // SMS Logs
        include __DIR__ . "/../../views/notification/sms_logs.php";
    }
    
    public function settings() 
    {
        // Notification Settings
        include __DIR__ . "/../../views/notification/settings.php";
    }
    
    public function sendTest() 
    {
        // Send Test Notification
        include __DIR__ . "/../../views/notification/send_test.php";
    }
    
    public function preview() 
    {
        // Preview Template
        include __DIR__ . "/../../views/notification/preview.php";
    }

    public function getNotifications()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'notifications' => []]);
        exit;
    }

    public function markAsRead()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function getUnreadCount()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => 0]);
        exit;
    }

    public function getPopups()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'popups' => []]);
        exit;
    }

    public function dismissPopup()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function createNotification()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Notification created']);
        exit;
    }

    public function createPopup()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Popup created']);
        exit;
    }
}
?>