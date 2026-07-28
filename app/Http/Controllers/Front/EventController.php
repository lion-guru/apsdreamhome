<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class EventController extends BaseController
{
    use TenantAwareTrait;
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM events WHERE status = 'active' AND event_date >= CURDATE() ORDER BY event_date ASC");
            $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $events = [];
        }
        $this->render('pages/event_list', [
            'page_title' => 'Upcoming Events',
            'events' => $events
        ]);
    }

    public function show($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ? AND status = 'active'");
            $stmt->execute([$id]);
            $event = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $event = null;
        }
        if (!$event) {
            $this->render('pages/event_list', ['page_title' => 'Event Not Found', 'error' => 'Event not found']);
            return;
        }
        $this->render('pages/event_detail', [
            'page_title' => $event['title'] ?? 'Event Details',
            'event' => $event
        ]);
    }
}
