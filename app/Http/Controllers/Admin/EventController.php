<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class EventController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM events ORDER BY event_date DESC");
            $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $events = [];
        }
        $this->render('admin/events/index', [
            'page_title' => 'Events Management',
            'events' => $events
        ]);
    }

    public function create()
    {
        $this->render('admin/events/create', [
            'page_title' => 'Create Event'
        ]);
    }

    public function store()
    {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $event_date = $_POST['event_date'] ?? '';
        $location = $_POST['location'] ?? '';
        $status = $_POST['status'] ?? 'active';
        try {
            $stmt = $this->db->prepare("INSERT INTO events (title, description, event_date, location, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $description, $event_date, $location, $status, $this->tenantId()]);
            $_SESSION['success'] = 'Event created successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/events/list');
    }

    public function show($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$id]);
            $event = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $event = null;
        }
        if (!$event) {
            $_SESSION['error'] = 'Event not found';
            $this->redirect('/admin/events/list');
        }
        $this->render('admin/events/show', [
            'page_title' => 'Event Details',
            'event' => $event
        ]);
    }

    public function edit($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$id]);
            $event = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $event = null;
        }
        if (!$event) {
            $_SESSION['error'] = 'Event not found';
            $this->redirect('/admin/events/list');
        }
        $this->render('admin/events/edit', [
            'page_title' => 'Edit Event',
            'event' => $event
        ]);
    }

    public function update($id)
    {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $event_date = $_POST['event_date'] ?? '';
        $location = $_POST['location'] ?? '';
        $status = $_POST['status'] ?? 'active';
        try {
            [$tw, $tp] = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, status = ? WHERE id = ?" . $tw);
            $stmt->execute([$title, $description, $event_date, $location, $status, $id, ...$tp]);
            $_SESSION['success'] = 'Event updated successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/events/list');
    }

    public function destroy($id)
    {
        try {
            [$tw, $tp] = $this->tenantWhere();
            $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?" . $tw);
            $stmt->execute([$id, ...$tp]);
            $_SESSION['success'] = 'Event deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/events/list');
    }
}
