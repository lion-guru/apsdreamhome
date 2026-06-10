<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Admin\AdminController;

/**
 * SMS Controller
 * Handles SMS sending, bulk messages, and scheduling.
 */
class SmsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function send()
    {
        return $this->jsonResponse(['success' => true, 'message' => 'SMS send endpoint ready']);
    }

    public function sendBulk()
    {
        return $this->jsonResponse(['success' => true, 'message' => 'Bulk SMS endpoint ready']);
    }

    public function schedule()
    {
        return $this->jsonResponse(['success' => true, 'message' => 'SMS scheduled']);
    }

    public function getStatus($id)
    {
        return $this->jsonResponse(['success' => true, 'data' => ['status' => 'pending', 'id' => $id]]);
    }

    public function getStats()
    {
        return $this->jsonResponse(['success' => true, 'data' => ['total_sent' => 0, 'delivered' => 0]]);
    }
}
