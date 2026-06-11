<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Core\Database\Database;

class CustomerLeadExtrasController extends AdminController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    // Customer Behavior Analysis
    public function behavior()
    {
        $this->requireAdmin();
        
        // Get customer behavior analyses with customer info
        $query = "
            SELECT cba.*, u.name as customer_name, u.email as customer_email
            FROM customer_behavior_analysis cba
            LEFT JOIN users u ON cba.customer_id = u.id
            ORDER BY cba.analysis_date DESC
        ";
        $behaviors = $this->db->query($query)->fetchAll();
        
        // Get stats
        $statsQuery = "
            SELECT 
                COUNT(*) as total_analyzed,
                COUNT(CASE WHEN DATE(analysis_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as this_week,
                COUNT(CASE WHEN behavioral_data LIKE '%high risk%' OR behavioral_data LIKE '%urgent%' THEN 1 END) as high_risk
            FROM customer_behavior_analysis
        ";
        $stats = $this->db->query($statsQuery)->fetch();
        
        return $this->render('admin/customer-lead-extras/behavior', [
            'behaviors' => $behaviors,
            'stats' => $stats
        ]);
    }

    public function showBehavior($id)
    {
        $this->requireAdmin();
        
        $query = "
            SELECT cba.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
            FROM customer_behavior_analysis cba
            LEFT JOIN users u ON cba.customer_id = u.id
            WHERE cba.id = ?
        ";
        $behavior = $this->db->query($query, [$id])->fetch();
        
        if (!$behavior) {
            $_SESSION['error'] = 'Behavior analysis not found';
            header('Location: ' . BASE_URL . '/admin/customer-lead/behavior');
            return;
        }
        
        return $this->render('admin/customer-lead-extras/show-behavior', [
            'behavior' => $behavior
        ]);
    }

    // Customer Journeys
    public function journeys()
    {
        $this->requireAdmin();
        
        try {
            $query = "
                SELECT cj.*, u.name as customer_name, u.email as customer_email
                FROM customer_journeys cj
                LEFT JOIN users u ON cj.customer_id = u.id
                ORDER BY cj.started_at DESC
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $journeys = $this->db->query($query)->fetchAll();
        
        // Get stats
        $statsQuery = "
            SELECT 
                COUNT(*) as total_journeys,
                COUNT(CASE WHEN DATE(last_touch_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as active_journeys,
                COUNT(CASE WHEN journey LIKE '%completed%' OR journey LIKE '%converted%' THEN 1 END) as completed_journeys
            FROM customer_journeys
        ";
        $stats = $this->db->query($statsQuery)->fetch();
        
        return $this->render('admin/customer-lead-extras/journeys', [
            'journeys' => $journeys,
            'stats' => $stats
        ]);
    }

    public function showJourney($id)
    {
        $this->requireAdmin();
        
        try {
            $query = "
                SELECT cj.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM customer_journeys cj
                LEFT JOIN users u ON cj.customer_id = u.id
                WHERE cj.id = ?
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $journey = $this->db->query($query, [$id])->fetch();
        
        if (!$journey) {
            $_SESSION['error'] = 'Customer journey not found';
            header('Location: ' . BASE_URL . '/admin/customer-lead/journeys');
            return;
        }
        
        return $this->render('admin/customer-lead-extras/show-journey', [
            'journey' => $journey
        ]);
    }

    // Lead Scoring
    public function leadScores()
    {
        $this->requireAdmin();
        
        $query = "
            SELECT ls.*, l.name as lead_name, l.email as lead_email
            FROM lead_scoring ls
            LEFT JOIN leads l ON ls.lead_id = l.id
            ORDER BY ls.score DESC, ls.created_at DESC
        ";
        $leadScores = $this->db->query($query)->fetchAll();
        
        // Get stats
        $statsQuery = "
            SELECT 
                AVG(ls.score) as avg_score,
                COUNT(CASE WHEN ls.score >= 80 THEN 1 END) as high_risk_count,
                COUNT(CASE WHEN ls.score >= 60 AND ls.score < 80 THEN 1 END) as medium_score_count,
                COUNT(CASE WHEN ls.score < 60 THEN 1 END) as low_score_count
            FROM lead_scoring ls
        ";
        $stats = $this->db->query($statsQuery)->fetch();
        
        return $this->render('admin/customer-lead-extras/lead-scores', [
            'leadScores' => $leadScores,
            'stats' => $stats
        ]);
    }

    public function updateLeadScore($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $score = isset($_POST['score']) ? (int)$_POST['score'] : 0;
            $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
            
            // Validate score
            if ($score < 0 || $score > 100) {
                $_SESSION['error'] = 'Score must be between 0 and 100';
                header('Location: ' . BASE_URL . '/admin/customer-lead/lead-scores/edit/' . $id);
                return;
            }
            
            // Update lead score
            $this->db->query(
                "UPDATE lead_scoring SET score = ?, criteria = CONCAT(criteria, ' | Manual override: ', ?), updated_at = NOW() WHERE id = ?",
                [$score, $reason, $id]
            );
            
            // Also update the lead's score if needed
            $leadScore = $this->db->query(
                "SELECT lead_id FROM lead_scoring WHERE id = ?",
                [$id]
            )->fetch();
            
            if ($leadScore) {
                $this->db->query(
                    "UPDATE leads SET lead_score = ?, score_factors = CONCAT(score_factors, ' | Manual override: ', ?) WHERE id = ?",
                    [$score, $reason, $leadScore['lead_id']]
                );
            }
            
            $_SESSION['success'] = 'Lead score updated successfully';
            header('Location: ' . BASE_URL . '/admin/customer-lead/lead-scores');
            return;
        }
        
        // GET request - show edit form
        $query = "
            SELECT ls.*, l.name as lead_name, l.email as lead_email, l.phone as lead_phone
            FROM lead_scoring ls
            LEFT JOIN leads l ON ls.lead_id = l.id
            WHERE ls.id = ?
        ";
        $leadScore = $this->db->query($query, [$id])->fetch();
        
        if (!$leadScore) {
            $_SESSION['error'] = 'Lead score not found';
            header('Location: ' . BASE_URL . '/admin/customer-lead/lead-scores');
            return;
        }
        
        return $this->render('admin/customer-lead-extras/edit-lead-score', [
            'leadScore' => $leadScore
        ]);
    }

    // Lead Events
    public function events()
    {
        $this->requireAdmin();
        
        $query = "
            SELECT le.*, l.name as lead_name, l.email as lead_email
            FROM lead_events le
            LEFT JOIN leads l ON le.lead_id = l.id
            ORDER BY le.created_at DESC
        ";
        $events = $this->db->query($query)->fetchAll();
        
        // Get stats
        $statsQuery = "
            SELECT 
                COUNT(*) as total_events,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_events,
                COUNT(CASE WHEN event_type IN ('form_submit', 'booking', 'site_visit') THEN 1 END) as conversion_events
            FROM lead_events
        ";
        $stats = $this->db->query($statsQuery)->fetch();
        
        return $this->render('admin/customer-lead-extras/events', [
            'events' => $events,
            'stats' => $stats
        ]);
    }

    public function showEvent($id)
    {
        $this->requireAdmin();
        
        $query = "
            SELECT le.*, l.name as lead_name, l.email as lead_email, l.phone as lead_phone, l.company as lead_company
            FROM lead_events le
            LEFT JOIN leads l ON le.lead_id = l.id
            WHERE le.id = ?
        ";
        $event = $this->db->query($query, [$id])->fetch();
        
        if (!$event) {
            $_SESSION['error'] = 'Lead event not found';
            header('Location: ' . BASE_URL . '/admin/customer-lead/events');
            return;
        }
        
        return $this->render('admin/customer-lead-extras/show-event', [
            'event' => $event
        ]);
    }

    // Custom Fields
    public function customFields()
    {
        $this->requireAdmin();
        
        $query = "
            SELECT * FROM lead_custom_fields 
            WHERE is_active = 1 
            ORDER BY sort_order, field_name
        ";
        $customFields = $this->db->query($query)->fetchAll();
        
        return $this->render('admin/customer-lead-extras/custom-fields', [
            'customFields' => $customFields
        ]);
    }

    public function storeCustomField()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $fieldName = isset($_POST['field_name']) ? $_POST['field_name'] : '';
            $fieldLabel = isset($_POST['field_label']) ? $_POST['field_label'] : '';
            $fieldType = isset($_POST['field_type']) ? $_POST['field_type'] : 'text';
            $fieldGroup = isset($_POST['field_group']) ? $_POST['field_group'] : 'general';
            $defaultValue = isset($_POST['default_value']) ? $_POST['default_value'] : '';
            $isRequired = isset($_POST['is_required']) ? 1 : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $validationRules = isset($_POST['validation_rules']) ? $_POST['validation_rules'] : '';
            $sortOrder = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $createdBy = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 1;
            
            // Validate required fields
            if (empty($fieldName) || empty($fieldLabel)) {
                $_SESSION['error'] = 'Field name and label are required';
                header('Location: ' . BASE_URL . '/admin/customer-lead/custom-fields/add');
                return;
            }
            
            // Check if field name already exists
            $existing = $this->db->query(
                "SELECT id FROM lead_custom_fields WHERE field_name = ?",
                [$fieldName]
            )->fetch();
            
            if ($existing) {
                $_SESSION['error'] = 'Field name already exists';
                header('Location: ' . BASE_URL . '/admin/customer-lead/custom-fields/add');
                return;
            }
            
            // Insert custom field
            $this->db->query(
                "INSERT INTO lead_custom_fields (field_name, field_label, field_type, field_group, default_value, is_required, is_active, validation_rules, sort_order, created_by, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [$fieldName, $fieldLabel, $fieldType, $fieldGroup, $defaultValue, $isRequired, $isActive, $validationRules, $sortOrder, $createdBy]
            );
            
            $_SESSION['success'] = 'Custom field created successfully';
            header('Location: ' . BASE_URL . '/admin/customer-lead/custom-fields');
            return;
        }
        
        // GET request - show add form
        return $this->render('admin/customer-lead-extras/add-custom-field', []);
    }

    public function updateCustomField($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $fieldName = isset($_POST['field_name']) ? $_POST['field_name'] : '';
            $fieldLabel = isset($_POST['field_label']) ? $_POST['field_label'] : '';
            $fieldType = isset($_POST['field_type']) ? $_POST['field_type'] : 'text';
            $fieldGroup = isset($_POST['field_group']) ? $_POST['field_group'] : 'general';
            $defaultValue = isset($_POST['default_value']) ? $_POST['default_value'] : '';
            $isRequired = isset($_POST['is_required']) ? 1 : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $validationRules = isset($_POST['validation_rules']) ? $_POST['validation_rules'] : '';
            $sortOrder = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            
            // Validate required fields
            if (empty($fieldName) || empty($fieldLabel)) {
                $_SESSION['error'] = 'Field name and label are required';
                header('Location: ' . BASE_URL . '/admin/customer-lead/custom-fields/edit/' . $id);
                return;
            }
            
            // Check if field name already exists (excluding current record)
            $existing = $this->db->query(
                "SELECT id FROM lead_custom_fields WHERE field_name = ? AND id != ?",
                [$fieldName, $id]
            )->fetch();
            
            if ($existing) {
                $_SESSION['error'] = 'Field name already exists';
                header('Location: ' . BASE_URL . '/admin/customer-lead/custom-fields/edit/' . $id);
                return;
            }
            
            // Update custom field
            $this->db->query(
                "UPDATE lead_custom_fields SET field_name = ?, field_label = ?, field_type = ?, field_group = ?, default_value = ?, is_required = ?, is_active = ?, validation_rules = ?, sort_order = ? WHERE id = ?",
                [$fieldName, $fieldLabel, $fieldType, $fieldGroup, $defaultValue, $isRequired, $isActive, $validationRules, $sortOrder, $id]
            );
            
            $_SESSION['success'] = 'Custom field updated successfully';
            header('Location: ' . BASE_URL . '/admin/customer-lead/custom-fields');
            return;
        }
        
        // GET request - show edit form
        $query = "SELECT * FROM lead_custom_fields WHERE id = ?";
        $customField = $this->db->query($query, [$id])->fetch();
        
        if (!$customField) {
            $_SESSION['error'] = 'Custom field not found';
            header('Location: ' . BASE_URL . '/admin/customer-lead/custom-fields');
            return;
        }
        
        return $this->render('admin/customer-lead-extras/edit-custom-field', [
            'customField' => $customField
        ]);
    }

    public function deleteCustomField($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            // Soft delete - set is_active to 0
            $this->db->query(
                "UPDATE lead_custom_fields SET is_active = 0 WHERE id = ?",
                [$id]
            );
            
            $_SESSION['success'] = 'Custom field deleted successfully';
            header('Location: ' . BASE_URL . '/admin/customer-lead/custom-fields');
            return;
        }
        
        // If not POST, redirect back
        header('Location: ' . BASE_URL . '/admin/customer-lead/custom-fields');
        return;
    }

    // Lead Assignment Approvals
    public function approvals()
    {
        $this->requireAdmin();
        
        try {
            $query = "
                SELECT laa.*, 
                       l.name as lead_name,
                       u_requested.name as requested_by_name,
                       u_requested_to.name as requested_to_name
                FROM lead_assignment_approvals laa
                LEFT JOIN leads l ON laa.lead_id = l.id
                LEFT JOIN users u_requested ON laa.requested_by = u_requested.id
                LEFT JOIN users u_requested_to ON laa.requested_to = u_requested_to.id
                ORDER BY laa.created_at DESC
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $approvals = $this->db->query($query)->fetchAll();
        
        try {
            // Get stats
            $statsQuery = "
                SELECT 
                    COUNT(*) as total_approvals,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_approvals,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_approvals,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_approvals
                FROM lead_assignment_approvals
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stats = $this->db->query($statsQuery)->fetch();
        
        return $this->render('admin/customer-lead-extras/approvals', [
            'approvals' => $approvals,
            'stats' => $stats
        ]);
    }

    public function showApproval($id)
    {
        $this->requireAdmin();
        
        try {
            $query = "
                SELECT laa.*, 
                       l.name as lead_name, l.email as lead_email, l.phone as lead_phone,
                       u_requested.name as requested_by_name, u_requested.email as requested_by_email,
                       u_requested_to.name as requested_to_name, u_requested_to.email as requested_to_email
                FROM lead_assignment_approvals laa
                LEFT JOIN leads l ON laa.lead_id = l.id
                LEFT JOIN users u_requested ON laa.requested_by = u_requested.id
                LEFT JOIN users u_requested_to ON laa.requested_to = u_requested_to.id
                WHERE laa.id = ?
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $approval = $this->db->query($query, [$id])->fetch();
        
        if (!$approval) {
            $_SESSION['error'] = 'Assignment approval not found';
            header('Location: ' . BASE_URL . '/admin/customer-lead/approvals');
            return;
        }
        
        return $this->render('admin/customer-lead-extras/show-approval', [
            'approval' => $approval
        ]);
    }

    public function updateApprovalStatus($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $status = isset($_POST['status']) ? $_POST['status'] : '';
            $adminNotes = isset($_POST['admin_notes']) ? $_POST['admin_notes'] : '';
            $approvedBy = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 1;
            
            // Validate status
            if (!in_array($status, ['approved', 'rejected'])) {
                $_SESSION['error'] = 'Invalid status';
                header('Location: ' . BASE_URL . '/admin/customer-lead/approvals/show/' . $id);
                return;
            }
            
            try {
                // Update approval status
                $this->db->query(
                    "UPDATE lead_assignment_approvals SET status = ?, admin_notes = ?, approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?",
                    [$status, $adminNotes, $approvedBy, $id]
                );
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            
            $_SESSION['success'] = 'Approval status updated successfully';
            header('Location: ' . BASE_URL . '/admin/customer-lead/approvals');
            return;
        }
        
        // If not POST, redirect back
        header('Location: ' . BASE_URL . '/admin/customer-lead/approvals');
        return;
    }

    // File Extractions
    public function fileExtractions()
    {
        $this->requireAdmin();
        
        try {
            $query = "
                SELECT lfe.*, u.name as created_by_name
                FROM lead_file_extractions lfe
                LEFT JOIN users u ON lfe.created_by = u.id
                ORDER BY lfe.created_at DESC
            ";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $fileExtractions = $this->db->query($query)->fetchAll();
        
        return $this->render('admin/customer-lead-extras/file-extractions', [
            'fileExtractions' => $fileExtractions
        ]);
    }
}

?>