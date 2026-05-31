<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminController;

class NotificationController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    public function index() 
    {
        $this->render('notification/index', ['page_title' => 'Notification Management']);
    }
    
    public function templates() 
    {
        $this->render('notification/templates', ['page_title' => 'Notification Templates']);
    }
    
    public function createTemplate() 
    {
        $this->render('notification/create_template', ['page_title' => 'Create Template']);
    }
    
    public function editTemplate($id) 
    {
        $this->render('notification/edit_template', ['page_title' => 'Edit Template']);
    }
    
    public function emailLogs() 
    {
        $this->render('notification/notifications_unified', ['page_title' => 'Email Logs']);
    }
    
    public function smsLogs() 
    {
        $this->render('notification/notifications_unified', ['page_title' => 'SMS Logs']);
    }
    
    public function settings() 
    {
        $this->render('notification/settings', ['page_title' => 'Notification Settings']);
    }
    
    public function sendTest() 
    {
        $this->render('notification/send_test', ['page_title' => 'Send Test Notification']);
    }
    
    public function preview() 
    {
        $this->render('notification/preview', ['page_title' => 'Preview Template']);
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