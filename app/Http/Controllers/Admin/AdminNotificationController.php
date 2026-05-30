<?php
namespace App\Http\Controllers\Admin;

class AdminNotificationController extends AdminController
{
    private $notificationService;

    public function __construct() {
        parent::__construct();
        $this->notificationService = new \App\Services\AdminNotificationService();
    }

    public function index() {
        $notifications = $this->notificationService->getUnread();
        $this->render('admin/notifications/index', [
            'page_title' => 'Notifications',
            'page_heading' => 'All Notifications',
            'notifications' => $notifications
        ]);
    }

    public function panel() {
        $notifications = $this->notificationService->getUnread();
        $this->render('admin/notifications/panel', [
            'page_title' => 'Notifications Panel',
            'page_heading' => 'Recent Notifications',
            'notifications' => $notifications
        ]);
    }

    public function markRead($id) {
        $this->notificationService->markRead($id);
        header('Location: ' . BASE_URL . '/admin/notifications');
        exit;
    }

    public function markAllRead() {
        $this->notificationService->markAllRead();
        header('Location: ' . BASE_URL . '/admin/notifications');
        exit;
    }
}
