<?php

namespace App\Http\Controllers\Admin;

use App\Services\HR\GeoCheckinService;

class EmployeeCheckinController extends AdminController
{
    private GeoCheckinService $service;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
        $this->service = new GeoCheckinService();
    }

    public function index(string $date = null)
    {
        $this->requireAdmin();
        $date = $date ?: date('Y-m-d');

        $report = $this->service->teamStatusForDate($date);
        $rows = [];
        $counts = ['total' => 0, 'present' => 0, 'flagged' => 0, 'absent' => 0];
        if (!empty($report['success'])) {
            foreach ($report['rows'] as $r) {
                $counts['total']++;
                $status = $r['status'] ?? null;
                if (!$r['work_date']) {
                    $counts['absent']++;
                } elseif ($status === 'valid' || $status === 'approved') {
                    $counts['present']++;
                } else {
                    $counts['flagged']++;
                }
                $anoms = [];
                if (!empty($r['is_mock_gps'])) {
                    $anoms[] = ['code' => 'GPS_MOCK_FLAG', 'detail' => 'Mock GPS at check-in'];
                }
                if ($status === 'out_of_zone') {
                    $anoms[] = ['code' => 'OUT_OF_ZONE', 'detail' => 'Checked in ' . ($r['distance_from_zone_m'] ? round((float)$r['distance_from_zone_m']) . 'm from nearest zone' : 'outside zones')];
                }
                if ($status === 'review_required') {
                    $anoms[] = ['code' => 'SELFIE_MISSING', 'detail' => 'Selfie missing at field site'];
                }
                if ($status === 'mock_gps') {
                    $anoms[] = ['code' => 'GPS_MOCK_FLAG', 'detail' => 'Mock location reported by device'];
                }
                if (isset($r['check_in_accuracy_m']) && (float)$r['check_in_accuracy_m'] > 300) {
                    $anoms[] = ['code' => 'ACCURACY_SUSPICIOUS', 'detail' => 'GPS accuracy ' . (int)$r['check_in_accuracy_m'] . 'm at check-in'];
                }
                $r['anomalies'] = $anoms;
                $rows[] = $r;
            }
        }

        $this->render('admin/hrm/check_in_monitor', [
            'page_title' => 'GPS Check-In Monitor',
            'date' => $date,
            'rows' => $rows,
            'counts' => $counts,
            'fetchError' => empty($report['success']) ? ($report['error'] ?? 'query failed') : null,
        ]);
    }

    public function review(int $checkinId)
    {
        $this->requireAdmin();
        $action = $_POST['action'] ?? '';
        $note = trim((string)($_POST['note'] ?? ''));
        if (!in_array($action, ['approve', 'reject'], true)) {
            return $this->jsonResponse(['success' => false, 'error' => 'action must be approve or reject']);
        }
        $adminId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $result = $this->service->reviewCheckin($checkinId, $action, $note !== '' ? $note : null, $adminId ?: 1);
        return $this->jsonResponse($result);
    }

    public function dayReport(int $employeeId)
    {
        $this->requireAdmin();
        $date = $_GET['date'] ?? date('Y-m-d');
        $report = $this->service->dayReport($employeeId, $date);
        return $this->jsonResponse($report);
    }

    public function aiSummary(int $employeeId)
    {
        $this->requireAdmin();
        $date = $_GET['date'] ?? date('Y-m-d');
        $result = $this->service->aiDailySummary($employeeId, $date);
        return $this->jsonResponse($result);
    }
}
