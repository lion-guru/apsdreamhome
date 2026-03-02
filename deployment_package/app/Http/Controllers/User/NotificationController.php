<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Exception;

class NotificationController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        
        // Register middlewares
        $this->middleware('auth');
        $this->middleware('csrf', ['only' => ['index']]); // Mark as read logic is in index POST
    }

    /**
     * Display the user's notifications
     */
    public function index()
    {
        try {
            $userId = $this->getCurrentUserId();
            
            // Mark all as read if requested
            if (isset($_POST['mark_all_read'])) {
                $this->db->query(
                    "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0",
                    [$userId]
                );
            }

            // Fetch Notifications
            $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50";
            $stmt = $this->db->query($sql, [$userId]);
            $notifications = $stmt->fetchAll();

            $this->data['page_title'] = 'Notifications - ' . APP_NAME;
            $this->data['notifications'] = $notifications;

            return $this->render('pages/notifications');

        } catch (Exception $e) {
            $this->data['error'] = $e->getMessage();
            return $this->render('pages/notifications');
        }
    }
}
