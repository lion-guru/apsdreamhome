<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class NotificationController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function list()
    {
        // List notifications
    }

    public function markRead($id)
    {
        // Mark as read
    }

    public function markAllRead()
    {
        // Mark all read
    }

    public function delete($id)
    {
        // Delete notification
    }

    public function create()
    {
        // Create notification
    }
}