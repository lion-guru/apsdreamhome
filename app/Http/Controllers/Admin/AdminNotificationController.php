<?php
namespace App\Http\Controllers\Admin;

class AdminNotificationController extends AdminController
{
    private $notificationService;

    public function __construct() {
        parent::__construct();
        $db = \App\Core\Database\Database::getInstance();
        $this->notificationService = new \App\Services\NotificationService($db);
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

    public function bookingLog() {
        $this->requireAdmin();

        $filters = [
            'type'      => $_GET['type'] ?? '',
            'channel'   => $_GET['channel'] ?? '',
            'status'    => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
            'search'    => $_GET['search'] ?? '',
            'limit'     => 50,
            'offset'    => 0,
        ];

        // Remove empty filters
        $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null);

        try {
            $notifier = new \App\Services\BookingNotificationService();
            $logs = $notifier->getBookingLog($filters);
            $stats = $notifier->getLogStats();
        } catch (\Throwable $e) {
            error_log("[AdminNotificationController] bookingLog error: " . $e->getMessage());
            $logs = [];
            $stats = ['total' => 0, 'email_sent' => 0, 'sms_sent' => 0, 'failed' => 0, 'today' => 0];
        }

        $this->render('admin/notifications/booking-log', [
            'page_title'   => 'Booking Notification Log - APS Dream Home',
            'page_heading' => 'Booking Notification Log',
            'logs'         => $logs,
            'stats'        => $stats,
            'filters'      => $filters,
        ]);
    }
}
