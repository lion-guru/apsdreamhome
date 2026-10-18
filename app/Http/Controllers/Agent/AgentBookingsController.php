<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;

class AgentBookingsController extends BaseController
{
    protected $db;
    protected $userId;
    protected $associateId;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
        $this->layout = 'layouts/agent';
        $this->userId = (int)($_SESSION['user_id'] ?? 0);
        $this->associateId = (int)($_SESSION['associate_id'] ?? 0);
    }

    public function index()
    {
        if ($this->userId <= 0) {
            $this->redirect('/agent/login');
            return;
        }
        $items = $this->db->fetchAll("SELECT pb.*, p.plot_number, c.name AS colony_name FROM plot_bookings pb LEFT JOIN plots p ON p.id = pb.plot_id LEFT JOIN colonies c ON c.id = pb.colony_id WHERE (pb.created_by = ? OR pb.associate_id = ?) AND pb.deleted_at IS NULL ORDER BY pb.created_at DESC", [$this->userId, $this->associateId]);
        return $this->render('agent/bookings', [
            'page_title' => 'My Bookings',
            'items' => $items ?: [],
        ]);
    }

    public function show($id)
    {
        if ($this->userId <= 0) {
            $this->redirect('/agent/login');
            return;
        }
        $item = $this->db->fetchOne("SELECT pb.*, p.plot_number, c.name AS colony_name FROM plot_bookings pb LEFT JOIN plots p ON p.id = pb.plot_id LEFT JOIN colonies c ON c.id = pb.colony_id WHERE pb.id = ? AND (pb.created_by = ? OR pb.associate_id = ?) AND pb.deleted_at IS NULL", [(int)$id, $this->userId, $this->associateId]);
        if (!$item) {
            $this->redirect('/agent/bookings');
            return;
        }
        return $this->render('agent/booking_detail', [
            'page_title' => 'Booking Detail',
            'item' => $item,
        ]);
    }
}
