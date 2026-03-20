<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Exception;

class NotificationController extends BaseController
{
    private $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->notificationService = new NotificationService();
    }

    /**
     * Get notifications for current user
     */
    public function getNotifications()
    {
        $userId = $this->getCurrentUserId();
        $filters = $_GET;

        $notifications = $this->notificationService->getUserNotifications($userId, $filters);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        $this->jsonResponse([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $notificationId = $_POST['notification_id'] ?? null;
        $userId = $this->getCurrentUserId();

        if (!$notificationId) {
            $this->jsonResponse(['success' => false, 'message' => 'Notification ID required'], 400);
        }

        $result = $this->notificationService->markAsRead($notificationId, $userId);

        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Get active popups for current page
     */
    public function getPopups()
    {
        $page = $_GET['page'] ?? 'home';
        $userRole = $this->getCurrentUserRole();

        $popups = $this->notificationService->getActivePopups($page, $userRole);

        // Filter out dismissed popups
        $activePopups = [];
        $userId = $this->getCurrentUserId();
        $sessionId = session_id();

        foreach ($popups as $popup) {
            if (!$this->notificationService->isPopupDismissed($popup['id'], $userId, $sessionId)) {
                $activePopups[] = $popup;
            }
        }

        $this->jsonResponse([
            'success' => true,
            'data' => $activePopups
        ]);
    }

    /**
     * Dismiss a popup
     */
    public function dismissPopup()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $popupId = $_POST['popup_id'] ?? null;
        $userId = $this->getCurrentUserId();
        $sessionId = session_id();

        if (!$popupId) {
            $this->jsonResponse(['success' => false, 'message' => 'Popup ID required'], 400);
        }

        $result = $this->notificationService->dismissPopup($popupId, $userId, $sessionId);

        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Popup dismissed'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to dismiss popup'
            ], 500);
        }
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount()
    {
        $userId = $this->getCurrentUserId();
        $count = $this->notificationService->getUnreadCount($userId);

        $this->jsonResponse([
            'success' => true,
            'data' => ['unread_count' => $count]
        ]);
    }

    /**
     * Create notification (admin only)
     */
    public function createNotification()
    {
        $this->middleware('admin.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $notificationData = [
            'title' => $_POST['title'] ?? '',
            'message' => $_POST['message'] ?? '',
            'type' => $_POST['type'] ?? 'info',
            'target_audience' => $_POST['target_audience'] ?? 'all',
            'user_id' => $_POST['user_id'] ?? null,
            'campaign_id' => $_POST['campaign_id'] ?? null
        ];

        if (empty($notificationData['title'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Title is required'], 400);
        }

        $result = $this->notificationService->sendToTargetAudience($notificationData);

        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Notification created successfully'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create notification'
            ], 500);
        }
    }

    /**
     * Create popup (admin only)
     */
    public function createPopup()
    {
        $this->middleware('admin.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $popupData = [
            'title' => $_POST['title'] ?? '',
            'content' => $_POST['content'] ?? '',
            'type' => $_POST['type'] ?? 'info',
            'target_audience' => $_POST['target_audience'] ?? 'all',
            'pages' => $_POST['pages'] ?? 'all',
            'position' => $_POST['position'] ?? 'center',
            'show_delay' => $_POST['show_delay'] ?? 0,
            'auto_close' => $_POST['auto_close'] ?? 0,
            'campaign_id' => $_POST['campaign_id'] ?? null
        ];

        if (empty($popupData['title'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Title is required'], 400);
        }

        $popupId = $this->notificationService->createPopup($popupData);

        if ($popupId) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Popup created successfully',
                'data' => ['popup_id' => $popupId]
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create popup'
            ], 500);
        }
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId()
    {
        // Check different session variables based on user type
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        } elseif (isset($_SESSION['employee_id'])) {
            return $_SESSION['employee_id'];
        } elseif (isset($_SESSION['admin_id'])) {
            return $_SESSION['admin_id'];
        }
        
        return null;
    }

    /**
     * Get current user role
     */
    private function getCurrentUserRole()
    {
        // Check different session variables based on user type
        if (isset($_SESSION['user_role'])) {
            return $_SESSION['user_role'];
        } elseif (isset($_SESSION['employee_role'])) {
            return 'employees';
        } elseif (isset($_SESSION['admin_role'])) {
            return 'admin';
        }
        
        return 'customer'; // Default role
    }
}