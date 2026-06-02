<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class EventController extends AdminController
{
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
            $stmt = $this->db->prepare("INSERT INTO events (title, description, event_date, location, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $description, $event_date, $location, $status]);
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
            $stmt = $this->db->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, status = ? WHERE id = ?");
            $stmt->execute([$title, $description, $event_date, $location, $status, $id]);
            $_SESSION['success'] = 'Event updated successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/events/list');
    }

    public function destroy($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Event deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        $this->redirect('/admin/events/list');
    }
}
