<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class SupportController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function getTickets()
    {
        // Get support tickets
    }

    public function createTicket()
    {
        // Create ticket
    }

    public function getTicketDetail($id)
    {
        // Ticket detail
    }

    public function addReply($id)
    {
        // Add reply
    }

    public function closeTicket($id)
    {
        // Close ticket
    }
}