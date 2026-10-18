<?php
/**
 * ScheduledReportController — Scheduled report email management (Gap 13).
 *
 * CRUD for scheduled_reports. Firing is handled by scripts/cron_scheduled_reports.php.
 * Routes: /admin/scheduled-reports (index/create/store/show/edit/update/delete/run)
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\ScheduledReportService;
use Exception;

class ScheduledReportController extends AdminController
{
    protected $srService;

    public function __construct()
    {
        parent::__construct();
        $this->srService = new ScheduledReportService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $reports = $this->srService->getAll($status ?: null);
            $stats = $this->srService->getStats();
            return $this->render('admin/scheduled_reports/index', [
                'page_title' => 'Scheduled Reports - APS Dream Home',
                'active_page' => 'scheduled_reports',
                'reports' => $reports,
                'stats' => $stats,
                'filters' => ['status' => $status],
                'statuses' => ['active', 'inactive'],
                'frequencies' => ['daily', 'weekly', 'monthly'],
                'formats' => ['csv', 'pdf', 'xlsx'],
                'reportTypes' => ['sales', 'property', 'associate', 'customer', 'financial'],
                'daysOfWeek' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load scheduled reports: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/scheduled_reports/create', [
            'page_title' => 'Create Scheduled Report',
            'active_page' => 'scheduled_reports',
            'frequencies' => ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'],
            'formats' => ['csv' => 'CSV', 'pdf' => 'PDF', 'xlsx' => 'Excel (.xlsx)'],
            'reportTypes' => ['sales' => 'Sales Report', 'property' => 'Property Report', 'associate' => 'Associate Report', 'customer' => 'Customer Report', 'financial' => 'Financial Report'],
            'daysOfWeek' => ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'],
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            if (empty($_POST['title'])) {
                return $this->jsonError('Title is required', 400);
            }
            if (empty($_POST['report_type'])) {
                return $this->jsonError('Report type is required', 400);
            }
            $id = $this->srService->create($_POST);
            $this->logActivity('scheduled_report_created', ['id' => $id, 'title' => $_POST['title']]);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Scheduled report created',
                'id' => $id,
                'redirect' => BASE_URL . '/admin/scheduled-reports/' . $id,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $report = $this->srService->getById((int)$id);
            if (!$report) {
                $this->setFlash('error', 'Scheduled report not found');
                return $this->redirect('/admin/scheduled-reports');
            }
            return $this->render('admin/scheduled_reports/show', [
                'page_title' => 'Scheduled Report: ' . $report['title'],
                'active_page' => 'scheduled_reports',
                'report' => $report,
                'parameters' => json_decode($report['parameters'] ?? '{}', true) ?: [],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/scheduled-reports');
        }
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $ok = $this->srService->update((int)$id, $_POST);
            if (!$ok) {
                return $this->jsonError('Scheduled report not found', 404);
            }
            $this->logActivity('scheduled_report_updated', ['id' => $id]);
            return $this->jsonResponse(['success' => true, 'message' => 'Scheduled report updated']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function runNow($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $ok = $this->srService->runNow((int)$id);
            if (!$ok) {
                return $this->jsonError('Scheduled report not found', 404);
            }
            $this->logActivity('scheduled_report_run_now', ['id' => $id]);
            return $this->jsonResponse(['success' => true, 'message' => 'Report queued for immediate run (cron will pick it up)']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }
        try {
            $ok = $this->srService->delete((int)$id);
            if (!$ok) {
                return $this->jsonError('Scheduled report not found', 404);
            }
            $this->logActivity('scheduled_report_deleted', ['id' => $id]);
            return $this->jsonResponse(['success' => true, 'message' => 'Scheduled report deleted']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }
}
