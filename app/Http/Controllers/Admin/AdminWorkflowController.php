<?php

namespace App\Http\Controllers\Admin;

use App\Services\WorkflowEngineService;
use App\Services\ReportBuilderService;
use App\Services\AuditTrailService;
use App\Services\ImportExportService;
use App\Services\BackupRestoreService;
use App\Services\EmailQueueService;
use App\Services\APIDocumentationService;
use \App\Traits\TenantAwareTrait;

/**
 * Admin Workflow Controller
 * Manages workflows, reports, audit trail, import/export, backups
 */
class AdminWorkflowController extends AdminController
{
    use TenantAwareTrait;

    private $workflowService;
    private $reportService;
    private $auditService;
    private $importExportService;
    private $backupService;
    private $emailQueueService;
    private $apiDocService;
    
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        try {
            $this->workflowService = new WorkflowEngineService();
        } catch (\Exception $e) { error_log("WorkflowEngineService error: " . $e->getMessage()); }
        try {
            $this->reportService = new ReportBuilderService();
        } catch (\Exception $e) { error_log("ReportBuilderService error: " . $e->getMessage()); }
        try {
            $this->auditService = new AuditTrailService();
        } catch (\Exception $e) { error_log("AuditTrailService error: " . $e->getMessage()); }
        try {
            $this->importExportService = new ImportExportService();
        } catch (\Exception $e) { error_log("ImportExportService error: " . $e->getMessage()); }
        try {
            $this->backupService = new BackupRestoreService();
        } catch (\Exception $e) { error_log("BackupRestoreService error: " . $e->getMessage()); }
        try {
            $this->emailQueueService = new EmailQueueService();
        } catch (\Exception $e) { error_log("EmailQueueService error: " . $e->getMessage()); }
        try {
            $this->apiDocService = new APIDocumentationService();
        } catch (\Exception $e) { error_log("APIDocumentationService error: " . $e->getMessage()); }
    }
    
    /**
     * Main dashboard for workflows & tools
     */
    public function dashboard()
    {
        $this->checkAdminAuth();
        
        $stats = [
            'workflows' => $this->workflowService ? $this->workflowService->getAllWorkflows() : [],
            'pending_approvals' => $this->workflowService ? $this->workflowService->getPendingForUser(
                $_SESSION['admin_id'] ?? 0, 
                $_SESSION['admin_role'] ?? 'admin'
            ) : [],
            'audit_stats' => $this->auditService ? $this->auditService->getStats('today') : [],
            'email_stats' => $this->emailQueueService ? $this->emailQueueService->getStats() : [],
            'backups' => $this->backupService ? $this->backupService->listBackups() : []
        ];
        
        $this->render('admin/workflows/dashboard', [
            'title' => 'Workflows & Tools Dashboard',
            'stats' => $stats
        ]);
    }
    
    // ==================== WORKFLOW MANAGEMENT ====================
    
    /**
     * List all workflows
     */
    public function workflows()
    {
        $this->checkAdminAuth();
        
        $workflows = $this->workflowService->getAllWorkflows();
        
        $this->render('admin/workflows/list', [
            'title' => 'Workflow Management',
            'workflows' => $workflows
        ]);
    }
    
    /**
     * Create new workflow
     */
    public function createWorkflow()
    {
        $this->checkAdminAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $id = $this->workflowService->createWorkflow(
                $_POST['code'],
                $_POST['name'],
                $_POST['entity_type'],
                $_POST['description'] ?? ''
            );
            
            if ($id) {
                $this->flashMessage('Workflow created successfully', 'success');
                redirect('/admin/workflows/' . $id . '/steps');
                exit;
            } else {
                $this->flashMessage('Failed to create workflow', 'error');
            }
        }
        
        $this->render('admin/workflows/create', [
            'title' => 'Create Workflow'
        ]);
    }
    
    /**
     * Manage workflow steps
     */
    public function workflowSteps(int $workflowId)
    {
        $this->checkAdminAuth();
        
        $workflow = $this->workflowService->getWorkflowByCode(''); // Need to fetch by ID
        $steps = $this->workflowService->getWorkflowSteps($workflowId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $this->workflowService->addWorkflowStep(
                $workflowId,
                (int)$_POST['step_order'],
                $_POST['step_name'],
                $_POST['approver_type'],
                $_POST['approver_id'] ?? null,
                $_POST['approver_role'] ?? null
            );
            
            $this->flashMessage('Step added successfully', 'success');
            redirect("/admin/workflows/{$workflowId}/steps");
            exit;
        }
        
        $this->render('admin/workflows/steps', [
            'title' => 'Workflow Steps',
            'workflow' => $workflow,
            'steps' => $steps
        ]);
    }
    
    /**
     * Pending approvals list
     */
    public function pendingApprovals()
    {
        $this->checkAdminAuth();
        
        $pending = $this->workflowService->getPendingForUser(
            $_SESSION['admin_id'] ?? 0,
            $_SESSION['admin_role'] ?? 'admin'
        );
        
        $this->render('admin/workflows/pending', [
            'title' => 'Pending Approvals',
            'pending' => $pending
        ]);
    }
    
    /**
     * Process workflow action
     */
    public function processWorkflowAction(int $instanceId)
    {
        $this->checkAdminAuth();
        
        $result = $this->workflowService->processAction(
            $instanceId,
            $_POST['action'],
            $_SESSION['admin_id'] ?? 0,
            'admin',
            $_POST['comments'] ?? ''
        );
        
        echo json_encode($result);
        exit;
    }
    
    // ==================== REPORT BUILDER ====================
    
    /**
     * Report builder dashboard
     */
    public function reports()
    {
        $this->checkAdminAuth();
        
        $savedReports = [];
        try {
            if ($this->reportService) {
                $savedReports = $this->reportService->getSavedReports($_SESSION['admin_id'] ?? 0);
            }
        } catch (\Exception $e) {
            error_log("reports() getSavedReports error: " . $e->getMessage());
        }
        
        $this->render('admin/reports/dashboard', [
            'title' => 'Report Builder',
            'saved_reports' => $savedReports
        ]);
    }
    
    /**
     * Generate sales report
     */
    public function salesReport()
    {
        $this->checkAdminAuth();
        
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d'),
            'group_by' => $_GET['group_by'] ?? 'daily'
        ];
        
        $data = [];
        try {
            if ($this->reportService) {
                $data = $this->reportService->generateSalesReport($filters);
            }
        } catch (\Exception $e) {
            error_log("salesReport error: " . $e->getMessage());
        }
        
        if ($_GET['export'] ?? false) {
            try {
                $export = $this->reportService->exportReport('sales', $filters, $_GET['format'] ?? 'csv');
                
                if ($export['success']) {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $export['filename'] . '"');
                    readfile($export['filepath']);
                    exit;
                }
            } catch (\Exception $e) {
                error_log("salesReport export error: " . $e->getMessage());
            }
        }
        
        $this->render('admin/reports/sales', [
            'title' => 'Sales Report',
            'data' => $data,
            'filters' => $filters
        ]);
    }
    
    /**
     * Generate leads report
     */
    public function leadsReport()
    {
        $this->checkAdminAuth();
        
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d')
        ];
        
        $data = [];
        try {
            if ($this->reportService) {
                $data = $this->reportService->generateLeadsReport($filters);
            }
        } catch (\Exception $e) {
            error_log("leadsReport error: " . $e->getMessage());
        }
        
        $this->render('admin/reports/leads', [
            'title' => 'Leads Report',
            'data' => $data,
            'filters' => $filters
        ]);
    }
    
    /**
     * Generate commission report
     */
    public function commissionReport()
    {
        $this->checkAdminAuth();
        
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d'),
            'associate_id' => $_GET['associate_id'] ?? null
        ];
        
        $data = [];
        try {
            if ($this->reportService) {
                $data = $this->reportService->generateCommissionReport($filters);
            }
        } catch (\Exception $e) {
            error_log("commissionReport error: " . $e->getMessage());
        }
        
        $this->render('admin/reports/commission', [
            'title' => 'Commission Report',
            'data' => $data,
            'filters' => $filters
        ]);
    }
    
    /**
     * Save custom report
     */
    public function saveReport()
    {
        $this->checkAdminAuth();
        
        $id = $this->reportService->saveReport(
            $_POST['name'],
            $_POST['type'],
            [
                'data_source' => $_POST['data_source'] ?? 'database',
                'filters' => $_POST['filters'] ?? [],
                'columns' => $_POST['columns'] ?? [],
                'chart_type' => $_POST['chart_type'] ?? null
            ],
            $_SESSION['admin_id'] ?? 0
        );
        
        echo json_encode(['success' => true, 'report_id' => $id]);
        exit;
    }
    
    // ==================== AUDIT TRAIL ====================
    
    /**
     * Audit trail viewer
     */
    public function auditTrail()
    {
        $this->checkAdminAuth();
        
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'entity_type' => $_GET['entity_type'] ?? null,
            'severity' => $_GET['severity'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 50);
        
        $logs = $this->auditService->query(array_filter($filters), $page, $limit);
        
        $this->render('admin/audit/trail', [
            'title' => 'Audit Trail',
            'logs' => $logs,
            'filters' => $filters
        ]);
    }
    
    /**
     * Entity history
     */
    public function entityHistory(string $entityType, int $entityId)
    {
        $this->checkAdminAuth();
        
        $history = $this->auditService->getEntityHistory($entityType, $entityId);
        
        echo json_encode($history);
        exit;
    }
    
    /**
     * User activity
     */
    public function userActivity(int $userId)
    {
        $this->checkAdminAuth();
        
        $activity = $this->auditService->getUserActivity(
            $userId, 
            $_GET['user_type'] ?? 'admin',
            (int)($_GET['days'] ?? 30)
        );
        
        echo json_encode($activity);
        exit;
    }
    
    // ==================== IMPORT/EXPORT ====================
    
    /**
     * Import/Export dashboard
     */
    public function importExport()
    {
        $this->checkAdminAuth();
        
        $this->render('admin/import-export/dashboard', [
            'title' => 'Import & Export'
        ]);
    }
    
    /**
     * Import data
     */
    public function importData()
    {
        $this->checkAdminAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) { $this->validateCsrfOrFail();
            $type = $_POST['import_type'] ?? 'properties';
            $file = $_FILES['import_file']['tmp_name'];
            
            $result = match($type) {
                'properties' => $this->importExportService->importProperties($file, $_POST),
                'leads' => $this->importExportService->importLeads($file, $_POST),
                'khatabook_sales' => $this->importExportService->importSales($file, $_POST),
                default => ['success' => false, 'error' => 'Invalid import type']
            };
            
            $this->render('admin/import-export/results', [
                'title' => 'Import Results',
                'result' => $result,
                'type' => $type
            ]);
            return;
        }
        
        $this->render('admin/import-export/import', [
            'title' => 'Import Data'
        ]);
    }
    
    /**
     * Export data
     */
    public function exportData()
    {
        $this->checkAdminAuth();
        
        $type = $_GET['type'] ?? 'properties';
        $filters = $_GET['filters'] ?? [];
        
        $result = match($type) {
            'properties' => $this->importExportService->exportProperties($filters),
            'leads' => $this->importExportService->exportLeads($filters),
            default => ['success' => false, 'error' => 'Invalid export type']
        };
        
        if ($result['success']) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            readfile($result['filepath']);
            exit;
        }
        
        $this->flashMessage($result['error'] ?? 'Export failed', 'error');
        redirect('/admin/import-export');
        exit;
    }
    
    /**
     * Download import template
     */
    public function downloadTemplate(string $type)
    {
        $this->checkAdminAuth();
        
        $result = $this->importExportService->downloadTemplate($type);
        
        if ($result['success']) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            readfile($result['filepath']);
            exit;
        }
        
        $this->flashMessage('Template not found', 'error');
        redirect('/admin/import-export');
        exit;
    }
    
    // ==================== BACKUP & RESTORE ====================
    
    /**
     * Backup management
     */
    public function backups()
    {
        $this->checkAdminAuth();
        
        $backups = $this->backupService->listBackups();
        
        $this->render('admin/backups/list', [
            'title' => 'Backup Management',
            'backups' => $backups
        ]);
    }
    
    /**
     * Create backup
     */
    public function createBackup()
    {
        $this->checkAdminAuth();
        
        $result = $this->backupService->createFullBackup($_SESSION['admin_id'] ?? null);
        
        if ($result['success']) {
            $this->flashMessage('Backup created successfully: ' . $result['size'], 'success');
        } else {
            $this->flashMessage('Backup failed: ' . $result['error'], 'error');
        }
        
        redirect('/admin/backups');
        exit;
    }
    
    /**
     * Download backup
     */
    public function downloadBackup(string $filename)
    {
        $this->checkAdminAuth();
        
        $filepath = STORAGE_PATH . '/backups/' . $filename;
        
        if (file_exists($filepath)) {
            header('Content-Type: application/gzip');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        }
        
        $this->flashMessage('Backup file not found', 'error');
        redirect('/admin/backups');
        exit;
    }
    
    // ==================== EMAIL QUEUE ====================
    
    /**
     * Email queue management
     */
    public function emailQueue()
    {
        $this->checkAdminAuth();
        
        $stats = $this->emailQueueService->getStats();
        
        $this->render('admin/emails/queue', [
            'title' => 'Email Queue',
            'stats' => $stats
        ]);
    }
    
    /**
     * Process email queue
     */
    public function processEmailQueue()
    {
        $this->checkAdminAuth();
        
        $result = $this->emailQueueService->processQueue($_POST['limit'] ?? 50);
        
        echo json_encode($result);
        exit;
    }
    
    /**
     * Retry failed emails
     */
    public function retryFailedEmails()
    {
        $this->checkAdminAuth();
        
        $count = $this->emailQueueService->retryFailed();
        
        echo json_encode(['success' => true, 'retried' => $count]);
        exit;
    }

    public function deleteBackup()
    {
        $this->checkAdminAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Missing backup ID']);
            exit;
        }
        try {
            [$where, $params] = $this->tenantWhere();
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM backups WHERE id = ? $where");
            $stmt->execute(array_merge([$id], $params));
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function restoreBackup()
    {
        $this->checkAdminAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Missing backup ID']);
            exit;
        }
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM backups WHERE id = ?");
            $stmt->execute([$id]);
            $backup = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$backup) {
                echo json_encode(['success' => false, 'error' => 'Backup not found']);
                exit;
            }
            $filePath = $backup['file_path'] ?? null;
            if ($filePath && file_exists($filePath)) {
                $sql = file_get_contents($filePath);
                $db->exec($sql);
                echo json_encode(['success' => true, 'message' => 'Backup restored successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Backup file not found on disk']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function cancelEmail()
    {
        $this->checkAdminAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Missing email ID']);
            exit;
        }
        try {
            [$where, $params] = $this->tenantWhere();
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE email_queue SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND status IN ('pending', 'queued') $where");
            $stmt->execute(array_merge([$id], $params));
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    // ==================== API DOCUMENTATION ====================
    
    /**
     * API Documentation
     */
    public function apiDocs()
    {
        $this->checkAdminAuth();
        
        $spec = $this->apiDocService->generateSpec();
        
        // Generate HTML docs
        $html = $this->apiDocService->generateHtmlDocs();
        
        $this->render('admin/api/docs', [
            'title' => 'API Documentation',
            'spec' => $spec,
            'html_docs' => $html
        ]);
    }
    
    /**
     * Export API spec
     */
    public function exportApiSpec(string $format)
    {
        $this->checkAdminAuth();
        
        if ($format === 'json') {
            $spec = $this->apiDocService->generateSpec();
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="api-spec.json"');
            echo json_encode($spec, JSON_PRETTY_PRINT);
            exit;
        }
        
        if ($format === 'html') {
            $html = $this->apiDocService->generateHtmlDocs();
            
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="api-docs.html"');
            echo $html;
            exit;
        }
        
        redirect('/admin/api-docs');
        exit;
    }
    
    /**
     * Check admin authentication
     */
    private function checkAdminAuth()
    {
        if (empty($_SESSION['admin_id'])) {
            redirect('/admin/login');
            exit;
        }
    }
}

