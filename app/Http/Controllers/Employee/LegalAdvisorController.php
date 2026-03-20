<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

/**
 * Legal Advisor Controller
 * Handles legal compliance, document management, and dispute resolution
 */
class LegalAdvisorController extends BaseController
{
    protected $db;
    protected $employeeId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->initializeEmployeeSession();
    }

    /**
     * Initialize employee session
     */
    private function initializeEmployeeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->employeeId = $_SESSION['employee_id'] ?? null;

        if (!$this->employeeId) {
            header('Location: ' . BASE_URL . '/employee/login');
            exit;
        }
    }

    /**
     * Legal Advisor dashboard
     */
    public function dashboard()
    {
        try {
            // Get pending document reviews
            $pendingDocuments = $this->getPendingDocuments();
            
            // Get upcoming compliance deadlines
            $upcomingCompliance = $this->getUpcomingCompliance();
            
            // Get active disputes
            $activeDisputes = $this->getActiveDisputes();
            
            // Get legal metrics
            $legalMetrics = $this->getLegalMetrics();
            
            // Get recent legal activities
            $recentActivities = $this->getRecentActivities();
            
            // Get contract status
            $contractStatus = $this->getContractStatus();

            $this->render('employee/legal_dashboard', [
                'page_title' => 'Legal Dashboard - APS Dream Home',
                'pending_documents' => $pendingDocuments,
                'upcoming_compliance' => $upcomingCompliance,
                'active_disputes' => $activeDisputes,
                'legal_metrics' => $legalMetrics,
                'recent_activities' => $recentActivities,
                'contract_status' => $contractStatus
            ]);

        } catch (Exception $e) {
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Get pending document reviews
     */
    private function getPendingDocuments()
    {
        $query = "SELECT ld.*, 
                        e.name as submitted_by_name,
                        p.title as related_property,
                        c.name as related_client
                 FROM legal_documents ld
                 LEFT JOIN employees e ON ld.submitted_by = e.id
                 LEFT JOIN properties p ON ld.related_property_id = p.id
                 LEFT JOIN clients c ON ld.related_client_id = c.id
                 WHERE ld.status = 'pending_review'
                 AND ld.assigned_to = ?
                 ORDER BY ld.priority DESC, ld.submitted_at ASC
                 LIMIT 15";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get upcoming compliance deadlines
     */
    private function getUpcomingCompliance()
    {
        $query = "SELECT ct.*, 
                        d.name as department_name,
                        e.name as responsible_person_name
                 FROM compliance_tasks ct
                 LEFT JOIN departments d ON ct.department_id = d.id
                 LEFT JOIN employees e ON ct.responsible_person = e.id
                 WHERE ct.due_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                 AND ct.status != 'completed'
                 AND (ct.assigned_to = ? OR ct.assigned_to IS NULL)
                 ORDER BY ct.due_date ASC, ct.priority DESC
                 LIMIT 20";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get active disputes
     */
    private function getActiveDisputes()
    {
        $query = "SELECT ld.*, 
                        c.name as client_name,
                        p.title as property_title,
                        e.name as assigned_lawyer_name
                 FROM legal_disputes ld
                 LEFT JOIN clients c ON ld.client_id = c.id
                 LEFT JOIN properties p ON ld.property_id = p.id
                 LEFT JOIN employees e ON ld.assigned_lawyer = e.id
                 WHERE ld.status IN ('active', 'investigation', 'negotiation')
                 AND (ld.assigned_lawyer = ? OR ld.assigned_lawyer IS NULL)
                 ORDER BY ld.created_at DESC
                 LIMIT 10";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get legal metrics
     */
    private function getLegalMetrics()
    {
        // Document processing metrics
        $docMetricsQuery = "SELECT 
                               COUNT(*) as total_documents,
                               SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                               SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) as pending,
                               AVG(TIMESTAMPDIFF(HOUR, submitted_at, reviewed_at)) as avg_review_time
                            FROM legal_documents 
                            WHERE assigned_to = ?
                            AND MONTH(submitted_at) = MONTH(CURDATE())";
        
        $docMetrics = $this->db->fetchOne($docMetricsQuery, [$this->employeeId]);
        
        // Compliance metrics
        $complianceMetricsQuery = "SELECT 
                                     COUNT(*) as total_tasks,
                                     SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                                     SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue
                                  FROM compliance_tasks 
                                  WHERE assigned_to = ?
                                  AND MONTH(due_date) = MONTH(CURDATE())";
        
        $complianceMetrics = $this->db->fetchOne($complianceMetricsQuery, [$this->employeeId]);
        
        // Dispute resolution metrics
        $disputeMetricsQuery = "SELECT 
                                  COUNT(*) as total_disputes,
                                  SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                                  SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                                  AVG(TIMESTAMPDIFF(DAY, created_at, resolved_at)) as avg_resolution_time
                               FROM legal_disputes 
                               WHERE assigned_lawyer = ?
                               AND YEAR(created_at) = YEAR(CURDATE())";
        
        $disputeMetrics = $this->db->fetchOne($disputeMetricsQuery, [$this->employeeId]);
        
        return [
            'document_metrics' => $docMetrics,
            'compliance_metrics' => $complianceMetrics,
            'dispute_metrics' => $disputeMetrics
        ];
    }

    /**
     * Get recent legal activities
     */
    private function getRecentActivities()
    {
        $query = "SELECT * FROM legal_activities 
                  WHERE performed_by = ?
                  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  ORDER BY created_at DESC
                  LIMIT 10";
        
        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get contract status
     */
    private function getContractStatus()
    {
        $query = "SELECT 
                    COUNT(*) as total_contracts,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'expiring_soon' THEN 1 ELSE 0 END) as expiring_soon,
                    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired
                 FROM contracts 
                 WHERE assigned_to = ? OR assigned_to IS NULL";
        
        return $this->db->fetchOne($query, [$this->employeeId]);
    }

    /**
     * Review legal document
     */
    public function reviewDocument($documentId, $reviewData)
    {
        try {
            // Get document details
            $docQuery = "SELECT * FROM legal_documents WHERE id = ? AND assigned_to = ?";
            $document = $this->db->fetchOne($docQuery, [$documentId, $this->employeeId]);
            
            if (!$document) {
                throw new Exception("Document not found or not assigned to you");
            }
            
            // Update document status
            $query = "UPDATE legal_documents 
                      SET status = ?, review_notes = ?, reviewed_by = ?, 
                          reviewed_at = NOW(), next_review_date = ?
                      WHERE id = ?";
            
            $this->db->execute($query, [
                $reviewData['status'],
                $reviewData['review_notes'] ?? '',
                $this->employeeId,
                $reviewData['next_review_date'] ?? null,
                $documentId
            ]);
            
            // Log activity
            $this->logLegalActivity('document_reviewed', 
                "Document '{$document['title']}' reviewed with status: {$reviewData['status']}", 
                $documentId);
            
            // Notify submitter
            $this->notifyDocumentReviewed($document['submitted_by'], $document, $reviewData);
            
            return [
                'success' => true,
                'message' => "Document reviewed successfully"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update compliance task
     */
    public function updateComplianceTask($taskId, $taskData)
    {
        try {
            // Get task details
            $taskQuery = "SELECT * FROM compliance_tasks WHERE id = ?";
            $task = $this->db->fetchOne($taskQuery, [$taskId]);
            
            if (!$task) {
                throw new Exception("Compliance task not found");
            }
            
            // Update task
            $query = "UPDATE compliance_tasks 
                      SET status = ?, completion_notes = ?, completed_at = NOW(),
                          completed_by = ?
                      WHERE id = ?";
            
            $this->db->execute($query, [
                $taskData['status'],
                $taskData['completion_notes'] ?? '',
                $this->employeeId,
                $taskId
            ]);
            
            // Log activity
            $this->logLegalActivity('compliance_updated', 
                "Compliance task '{$task['title']}' updated to status: {$taskData['status']}", 
                $taskId);
            
            // Notify responsible person
            if ($task['responsible_person']) {
                $this->notifyComplianceUpdated($task['responsible_person'], $task, $taskData);
            }
            
            return [
                'success' => true,
                'message' => "Compliance task updated successfully"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle legal dispute
     */
    public function handleDispute($disputeId, $actionData)
    {
        try {
            // Get dispute details
            $disputeQuery = "SELECT * FROM legal_disputes WHERE id = ?";
            $dispute = $this->db->fetchOne($disputeQuery, [$disputeId]);
            
            if (!$dispute) {
                throw new Exception("Dispute not found");
            }
            
            // Update dispute
            $query = "UPDATE legal_disputes 
                      SET status = ?, action_taken = ?, next_action_date = ?,
                          updated_by = ?, updated_at = NOW()
                      WHERE id = ?";
            
            $this->db->execute($query, [
                $actionData['status'],
                $actionData['action_taken'] ?? '',
                $actionData['next_action_date'] ?? null,
                $this->employeeId,
                $disputeId
            ]);
            
            // Add to dispute timeline
            $this->addToDisputeTimeline($disputeId, $actionData);
            
            // Log activity
            $this->logLegalActivity('dispute_handled', 
                "Dispute '{$dispute['title']}' action taken: {$actionData['status']}", 
                $disputeId);
            
            // Notify client
            $this->notifyDisputeUpdate($dispute['client_id'], $dispute, $actionData);
            
            return [
                'success' => true,
                'message' => "Dispute updated successfully"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create legal document template
     */
    public function createDocumentTemplate($templateData)
    {
        try {
            $query = "INSERT INTO legal_document_templates (
                        title, category, content, variables, 
                        created_by, created_at, status
                    ) VALUES (?, ?, ?, ?, ?, NOW(), 'active')";
            
            $this->db->execute($query, [
                $templateData['title'],
                $templateData['category'],
                $templateData['content'],
                json_encode($templateData['variables'] ?? []),
                $this->employeeId
            ]);
            
            $templateId = $this->db->lastInsertId();
            
            // Log activity
            $this->logLegalActivity('template_created', 
                "Legal document template '{$templateData['title']}' created", 
                $templateId);
            
            return [
                'success' => true,
                'template_id' => $templateId,
                'message' => "Document template created successfully"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate legal report
     */
    public function generateLegalReport($reportType, $filters = [])
    {
        try {
            switch ($reportType) {
                case 'compliance_status':
                    return $this->generateComplianceReport($filters);
                case 'dispute_summary':
                    return $this->generateDisputeReport($filters);
                case 'document_review':
                    return $this->generateDocumentReport($filters);
                case 'contract_expiration':
                    return $this->generateContractReport($filters);
                default:
                    throw new Exception("Invalid report type");
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate compliance report
     */
    private function generateComplianceReport($filters)
    {
        $whereClause = "1=1";
        $params = [];
        
        if (!empty($filters['department'])) {
            $whereClause .= " AND ct.department_id = ?";
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND ct.due_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND ct.due_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        $query = "SELECT 
                    ct.title,
                    ct.due_date,
                    ct.status,
                    d.name as department_name,
                    e.name as responsible_person,
                    TIMESTAMPDIFF(DAY, CURDATE(), ct.due_date) as days_until_due
                 FROM compliance_tasks ct
                 LEFT JOIN departments d ON ct.department_id = d.id
                 LEFT JOIN employees e ON ct.responsible_person = e.id
                 WHERE {$whereClause}
                 ORDER BY ct.due_date ASC";
        
        $reportData = $this->db->fetchAll($query, $params);
        
        // Calculate summary statistics
        $summary = [
            'total_tasks' => count($reportData),
            'completed' => count(array_filter($reportData, fn($r) => $r['status'] === 'completed')),
            'pending' => count(array_filter($reportData, fn($r) => $r['status'] === 'pending')),
            'overdue' => count(array_filter($reportData, fn($r) => $r['status'] === 'overdue')),
            'due_this_week' => count(array_filter($reportData, fn($r) => $r['days_until_due'] <= 7 && $r['days_until_due'] >= 0))
        ];
        
        return [
            'success' => true,
            'report_type' => 'compliance_status',
            'summary' => $summary,
            'data' => $reportData,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Add to dispute timeline
     */
    private function addToDisputeTimeline($disputeId, $actionData)
    {
        $query = "INSERT INTO dispute_timeline (
                    dispute_id, action, description, performed_by, created_at
                ) VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->execute($query, [
            $disputeId,
            $actionData['status'],
            $actionData['action_taken'] ?? '',
            $this->employeeId
        ]);
    }

    /**
     * Notify document reviewed
     */
    private function notifyDocumentReviewed($submittedBy, $document, $reviewData)
    {
        $message = "Document '{$document['title']}' has been reviewed. Status: {$reviewData['status']}";
        $this->createNotification($submittedBy, 'document_reviewed', $message, $document['id']);
    }

    /**
     * Notify compliance updated
     */
    private function notifyComplianceUpdated($responsiblePerson, $task, $taskData)
    {
        $message = "Compliance task '{$task['title']}' updated. Status: {$taskData['status']}";
        $this->createNotification($responsiblePerson, 'compliance_updated', $message, $task['id']);
    }

    /**
     * Notify dispute update
     */
    private function notifyDisputeUpdate($clientId, $dispute, $actionData)
    {
        $message = "Dispute '{$dispute['title']}' update. Status: {$actionData['status']}";
        $this->createNotification($clientId, 'dispute_update', $message, $dispute['id']);
    }

    /**
     * Create notification
     */
    private function createNotification($recipientId, $type, $message, $relatedId = null)
    {
        $query = "INSERT INTO notifications (
                    recipient_id, type, message, related_id, created_at, status
                ) VALUES (?, ?, ?, ?, NOW(), 'unread')";
        
        $this->db->execute($query, [$recipientId, $type, $message, $relatedId]);
    }

    /**
     * Log legal activity
     */
    private function logLegalActivity($activityType, $description, $relatedId = null)
    {
        $query = "INSERT INTO legal_activities (
                    activity_type, description, related_id, 
                    performed_by, created_at
                ) VALUES (?, ?, ?, ?, NOW())";
        
        $this->db->execute($query, [$activityType, $description, $relatedId, $this->employeeId]);
    }

    /**
     * Handle errors
     */
    private function handleError($message)
    {
        error_log("Legal Advisor Controller Error: " . $message);
        
        $_SESSION['error'] = "Unable to load legal dashboard. Please try again.";
        header('Location: ' . BASE_URL . '/employee/dashboard');
        exit;
    }
}
