<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

/**
 * Land Manager Controller
 * Handles property management, land acquisition, and site coordination
 */
class LandManagerController extends BaseController
{
    protected $db;
    protected $employeeId;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/employee';
        $this->db = Database::getInstance();
        $this->initializeEmployeeSession();
    }

    /**
     * Initialize employee session
     */
    private function initializeEmployeeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $this->employeeId = $_SESSION['employee_id'] ?? $_SESSION['user_id'] ?? null;

        if (!$this->employeeId) {
            header('Location: ' . BASE_URL . '/employee/login');
            exit;
        }
    }

    /**
     * Land Manager Dashboard
     */
    public function dashboard()
    {
        try {
            // Get property portfolio status
            $propertyStatus = $this->getPropertyStatus();

            // Get pending site visits
            $pendingVisits = $this->getPendingSiteVisits();

            // Get land acquisition pipeline
            $acquisitionPipeline = $this->getAcquisitionPipeline();

            // Get land management metrics
            $managementMetrics = $this->getManagementMetrics();

            // Get recent activities
            $recentActivities = $this->getRecentActivities();

            // Get documentation status
            $documentationStatus = $this->getDocumentationStatus();

            $this->render('employee/land_manager_dashboard', [
                'page_title' => 'Land Manager Dashboard - APS Dream Home',
                'property_status' => $propertyStatus,
                'pending_visits' => $pendingVisits,
                'acquisition_pipeline' => $acquisitionPipeline,
                'management_metrics' => $managementMetrics,
                'recent_activities' => $recentActivities,
                'documentation_status' => $documentationStatus
            ]);
        } catch (\Exception $e) {
            error_log("Land Manager Controller Error: " . $e->getMessage());
            $this->render('employees/department', [
                'page_title' => 'Land Dashboard',
                'dept_title' => 'Land Dashboard',
                'dept_icon'  => 'fas fa-map',
                'dept_desc'  => 'Land acquisition: parcel tracking, survey status, and land bank overview.',
                'dept_color' => '#10b981',
                'dept_slug'  => 'land-dashboard',
                'employee_id' => $this->employeeId,
                'employee_name' => $_SESSION['employee_name'] ?? $_SESSION['user_name'] ?? 'Employee',
            ]);
        }
    }

    /**
     * Get property portfolio status
     */
    private function getPropertyStatus()
    {
        $query = "SELECT 
                    p.status,
                    COUNT(*) as count,
                    SUM(p.price) as total_value,
                    AVG(p.area_sqft) as avg_area
                 FROM properties p
                 WHERE p.created_by = ?
                 GROUP BY p.status
                 ORDER BY count DESC";

        $statusBreakdown = $this->db->fetchAll($query, [$this->employeeId]);

        // Get property distribution by type
        $typeQuery = "SELECT 
                        p.type,
                        COUNT(*) as count,
                        SUM(p.price) as total_value
                      FROM properties p
                      WHERE p.created_by = ?
                      GROUP BY p.type
                      ORDER BY count DESC";

        $typeBreakdown = $this->db->fetchAll($typeQuery, [$this->employeeId]);

        // Get property distribution by location
        $locationQuery = "SELECT 
                            p.location,
                            COUNT(*) as count,
                            SUM(p.price) as total_value
                          FROM properties p
                          WHERE p.created_by = ?
                          GROUP BY p.location
                          ORDER BY count DESC
                          LIMIT 10";

        $locationBreakdown = $this->db->fetchAll($locationQuery, [$this->employeeId]);

        return [
            'status_breakdown' => $statusBreakdown,
            'type_breakdown' => $typeBreakdown,
            'location_breakdown' => $locationBreakdown
        ];
    }

    /**
     * Get pending site visits
     */
    private function getPendingSiteVisits()
    {
        $query = "SELECT sv.*, 
                        COALESCE(up.name, p.plot_number) as property_title,
                        up.location as property_location,
                        sv.visitor_name,
                        sv.visitor_phone
                 FROM site_visits sv
                 LEFT JOIN user_properties up ON sv.property_id = up.id
                 LEFT JOIN plots p ON sv.plot_id = p.id
                 WHERE sv.assigned_to = ?
                 AND sv.visit_date >= CURDATE()
                 AND sv.status = 'scheduled'
                 ORDER BY sv.visit_date ASC, sv.visit_time ASC
                 LIMIT 20";

        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get land acquisition pipeline
     */
    private function getAcquisitionPipeline()
    {
        $query = "SELECT la.*, 
                        COALESCE(c.name, 'Land parcel') as property_title,
                        c.location as property_location,
                        NULL as area_sqft,
                        la.acquisition_cost as estimated_value,
                        '' as land_owner_name,
                        '' as land_owner_phone
                 FROM land_acquisitions la
                 LEFT JOIN colonies c ON la.colony_id = c.id
                 WHERE la.status IN ('evaluation', 'due_diligence', 'negotiation', 'final_approval')
                 ORDER BY la.created_at ASC
                 LIMIT 15";

        return $this->db->fetchAll($query);
    }

    /**
     * Get land management metrics
     */
    private function getManagementMetrics()
    {
        // Property performance metrics
        $performanceQuery = "SELECT 
                                COUNT(*) as total_properties,
                                SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold_properties,
                                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_properties,
                                AVG(CASE WHEN status = 'sold' THEN TIMESTAMPDIFF(DAY, created_at, updated_at) END) as avg_days_to_sell,
                                SUM(price) as total_portfolio_value
                             FROM properties 
                             WHERE created_by = ?
                             AND YEAR(created_at) = YEAR(CURDATE())";

        $performanceMetrics = $this->db->fetchOne($performanceQuery, [$this->employeeId]);

        // Site visit metrics
        $visitMetricsQuery = "SELECT 
                                 COUNT(*) as total_visits,
                                 SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_visits,
                                 SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_visits,
                                 AVG(rating) as avg_rating
                              FROM site_visits 
                              WHERE assigned_to = ?
                              AND MONTH(visit_date) = MONTH(CURDATE())";

        $visitMetrics = $this->db->fetchOne($visitMetricsQuery, [$this->employeeId]);

        // Acquisition metrics
        $acquisitionMetricsQuery = "SELECT 
                                      COUNT(*) as total_acquisitions,
                                      SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_acquisitions,
                                      AVG(TIMESTAMPDIFF(DAY, created_at, COALESCE(registration_date, updated_at))) as avg_acquisition_time
                                   FROM land_acquisitions 
                                   WHERE YEAR(created_at) = YEAR(CURDATE())";

        $acquisitionMetrics = $this->db->fetchOne($acquisitionMetricsQuery);

        return [
            'performance_metrics' => $performanceMetrics,
            'visit_metrics' => $visitMetrics,
            'acquisition_metrics' => $acquisitionMetrics
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $query = "SELECT * FROM employee_activities 
                  WHERE employee_id = ?
                  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  ORDER BY created_at DESC
                  LIMIT 10";

        return $this->db->fetchAll($query, [$this->employeeId]);
    }

    /**
     * Get documentation status
     */
    private function getDocumentationStatus()
    {
        $query = "SELECT 
                    COUNT(*) as total_documents
                 FROM documents 
                 WHERE entity_type = 'property' AND entity_id IN (
                     SELECT id FROM properties WHERE created_by = ?
                 )";

        return $this->db->fetchOne($query, [$this->employeeId]);
    }

    /**
     * Schedule site visit
     */
    public function scheduleSiteVisit()
    {
        try {
            $propertyId = (int)($_POST['property_id'] ?? 0);
            $visitData = [
                'visitor' => [
                    'name' => trim($_POST['visitor_name'] ?? ''),
                    'phone' => trim($_POST['visitor_phone'] ?? ''),
                ],
                'visit_date' => $_POST['visit_date'] ?? date('Y-m-d'),
                'visit_time' => $_POST['visit_time'] ?? '10:00:00',
                'purpose' => $_POST['purpose'] ?? 'site_visit',
                'expected_duration' => (int)($_POST['expected_duration'] ?? 60),
                'notes' => $_POST['notes'] ?? '',
            ];

            if ($propertyId <= 0 || $visitData['visit_date'] === '') {
                return ['success' => false, 'message' => 'Property and visit date are required'];
            }
            // Validate property assignment
            $propertyQuery = "SELECT id, title, location FROM properties WHERE id = ? AND created_by = ?";
            $property = $this->db->fetchOne($propertyQuery, [$propertyId, $this->employeeId]);

            if (!$property) {
                throw new Exception("Property not found or not assigned to you");
            }

            // Schedule site visit
            $query = "INSERT INTO site_visits (
                        property_id, visitor_name, visitor_phone, visit_date, visit_time,
                        visit_type, duration_minutes, assigned_to, notes, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())";

            $this->db->execute($query, [
                $propertyId,
                $visitData['visitor']['name'] ?? '',
                $visitData['visitor']['phone'] ?? '',
                $visitData['visit_date'],
                $visitData['visit_time'],
                $visitData['purpose'] ?? 'site_visit',
                $visitData['expected_duration'] ?? 60,
                $this->employeeId,
                $visitData['notes'] ?? ''
            ]);

            $visitId = $this->db->lastInsertId();

            // Log activity
            $this->logLandActivity(
                'site_visit_scheduled',
                "Site visit scheduled for property: {$property['title']}",
                $visitId
            );

            // Notify visitor
            $this->notifySiteVisitScheduled(null, $property, $visitData);

            return [
                'success' => true,
                'visit_id' => $visitId,
                'message' => "Site visit scheduled successfully"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create or update visitor (visitors merged into site_visits inline columns)
     */
    private function createOrUpdateVisitor($visitorData)
    {
        return null;
    }

    /**
     * Update land acquisition
     */
    public function updateAcquisition()
    {
        try {
            $acquisitionId = (int)($_POST['acquisition_id'] ?? 0);
            $acquisitionData = [
                'status' => $_POST['status'] ?? '',
                'notes' => $_POST['notes'] ?? '',
            ];

            if ($acquisitionId <= 0 || $acquisitionData['status'] === '') {
                return ['success' => false, 'message' => 'Acquisition ID and status are required'];
            }

            // Get acquisition details
            $acquisitionQuery = "SELECT * FROM land_acquisitions WHERE id = ?";
            $acquisition = $this->db->fetchOne($acquisitionQuery, [$acquisitionId]);

            if (!$acquisition) {
                throw new Exception("Acquisition not found or not assigned to you");
            }

            // Update acquisition
            $query = "UPDATE land_acquisitions 
                      SET status = ?, updated_at = NOW()
                      WHERE id = ?";

            $this->db->execute($query, [
                $acquisitionData['status'],
                $acquisitionId
            ]);

            // Add to acquisition timeline
            $this->addToAcquisitionTimeline($acquisitionId, $acquisitionData);

            // Log activity
            $this->logLandActivity(
                'acquisition_updated',
                "Land acquisition updated to status: {$acquisitionData['status']}",
                $acquisitionId
            );

            // Notify stakeholders
            $this->notifyAcquisitionUpdate($acquisitionId, $acquisitionData);

            return [
                'success' => true,
                'message' => "Land acquisition updated successfully"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Complete site visit
     */
    public function completeSiteVisit()
    {
        try {
            $visitId = (int)($_POST['visit_id'] ?? 0);
            $completionData = [
                'actual_duration' => (int)($_POST['actual_duration'] ?? 0),
                'rating' => (isset($_POST['rating']) && $_POST['rating'] !== '') ? (int)$_POST['rating'] : null,
                'feedback_notes' => $_POST['feedback_notes'] ?? '',
                'outcome' => $_POST['outcome'] ?? '',
                'next_steps' => $_POST['next_steps'] ?? '',
            ];

            if ($visitId <= 0) {
                return ['success' => false, 'message' => 'Visit ID is required'];
            }

            // Get visit details
            $visitQuery = "SELECT sv.*, p.title as property_title
                           FROM site_visits sv
                           LEFT JOIN properties p ON sv.property_id = p.id
                           WHERE sv.id = ? AND sv.assigned_to = ?";

            $visit = $this->db->fetchOne($visitQuery, [$visitId, $this->employeeId]);

            if (!$visit) {
                throw new Exception("Site visit not found or not assigned to you");
            }

            // Update visit status
            $query = "UPDATE site_visits 
                      SET status = 'completed', completed_at = NOW(), 
                          duration_minutes = ?, rating = ?,
                          feedback = CONCAT_WS(' | ', NULLIF(feedback, ''), ?, ?, ?)
                      WHERE id = ?";

            $this->db->execute($query, [
                $completionData['actual_duration'] ?? 0,
                $completionData['rating'] ?? null,
                $completionData['feedback_notes'] ?? '',
                $completionData['outcome'] ?? '',
                $completionData['next_steps'] ?? '',
                $visitId
            ]);

            // Log activity
            $this->logLandActivity(
                'site_visit_completed',
                "Site visit completed for property: {$visit['property_title']}",
                $visitId
            );

            // Notify visitor
            $this->notifySiteVisitCompleted(null, $visit, $completionData);

            return [
                'success' => true,
                'message' => "Site visit completed successfully"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update property documentation
     */
    public function updatePropertyDocumentation()
    {
        try {
            $propertyId = (int)($_POST['property_id'] ?? 0);
            if ($propertyId <= 0) {
                return ['success' => false, 'message' => 'Property ID is required'];
            }

            // Accept a JSON array of documents, or fall back to flat single-document fields
            $documents = [];
            if (!empty($_POST['documents'])) {
                $decoded = json_decode($_POST['documents'], true);
                if (is_array($decoded)) {
                    $documents = $decoded;
                }
            }
            if (empty($documents) && !empty($_POST['doc_type'])) {
                $documents[] = [
                    'type' => $_POST['doc_type'],
                    'status' => $_POST['doc_status'] ?? 'pending',
                    'expiry_date' => ($_POST['expiry_date'] ?? '') !== '' ? $_POST['expiry_date'] : null,
                ];
            }
            if (empty($documents)) {
                return ['success' => false, 'message' => 'No documents provided'];
            }

            // Validate property assignment
            $propertyQuery = "SELECT id, title FROM properties WHERE id = ? AND created_by = ?";
            $property = $this->db->fetchOne($propertyQuery, [$propertyId, $this->employeeId]);

            if (!$property) {
                throw new Exception("Property not found or not assigned to you");
            }

            foreach ($documents as $document) {
                // Check if document exists
                $checkQuery = "SELECT id FROM documents 
                               WHERE entity_type = 'property' AND entity_id = ? AND document_type = ?";
                $existingDoc = $this->db->fetchOne($checkQuery, [$propertyId, $document['type']]);

                if ($existingDoc) {
                    $updateQuery = "UPDATE documents 
                                   SET verification_status = ?, expiry_date = ?, 
                                       user_id = ?, uploaded_on = NOW()
                                   WHERE id = ?";

                    $this->db->execute($updateQuery, [
                        $document['status'],
                        $document['expiry_date'] ?? null,
                        $this->employeeId,
                        $existingDoc['id']
                    ]);
                } else {
                    $insertQuery = "INSERT INTO documents (
                                        entity_type, entity_id, document_type, verification_status, expiry_date,
                                        user_id, uploaded_on
                                    ) VALUES ('property', ?, ?, ?, ?, ?, NOW())";

                    $this->db->execute($insertQuery, [
                        $propertyId,
                        $document['type'],
                        $document['status'],
                        $document['expiry_date'] ?? null,
                        $this->employeeId
                    ]);
                }
            }

            // Log activity
            $this->logLandActivity(
                'documentation_updated',
                "Documentation updated for property: {$property['title']}",
                $propertyId
            );

            return [
                'success' => true,
                'message' => "Property documentation updated successfully"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate land management report
     */
    public function generateLandReport()
    {
        try {
            $reportType = $_POST['report_type'] ?? $_GET['type'] ?? '';
            $filters = array_merge($_GET, $_POST);
            unset($filters['csrf_token'], $filters['report_type']);

            switch ($reportType) {
                case 'property_portfolio':
                    return $this->generatePropertyPortfolioReport($filters);
                case 'site_visits':
                    return $this->generateSiteVisitReport($filters);
                case 'acquisition_pipeline':
                    return $this->generateAcquisitionReport($filters);
                case 'documentation_status':
                    return $this->generateDocumentationReport($filters);
                default:
                    throw new Exception("Invalid report type");
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate property portfolio report
     */
    private function generatePropertyPortfolioReport($filters)
    {
        $whereClause = "p.created_by = ?";
        $params = [$this->employeeId];

        if (!empty($filters['status'])) {
            $whereClause .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $whereClause .= " AND p.type = ?";
            $params[] = $filters['type'];
        }

        $query = "SELECT p.*, 
                         COUNT(pd.id) as document_count
                 FROM properties p
                 LEFT JOIN documents pd ON p.id = pd.entity_id AND pd.entity_type = 'property'
                 WHERE {$whereClause}
                 GROUP BY p.id
                 ORDER BY p.created_at DESC";

        $reportData = $this->db->fetchAll($query, $params);

        return [
            'success' => true,
            'report_type' => 'property_portfolio',
            'data' => $reportData,
            'summary' => [
                'total_properties' => count($reportData),
                'total_value' => array_sum(array_column($reportData, 'price')),
                'avg_area' => count($reportData) > 0 ? array_sum(array_column($reportData, 'area_sqft')) / count($reportData) : 0
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate site visit report
     */
    private function generateSiteVisitReport($filters)
    {
        $whereClause = "sv.assigned_to = ?";
        $params = [$this->employeeId];

        if (!empty($filters['status'])) {
            $whereClause .= " AND sv.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $whereClause .= " AND sv.visit_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereClause .= " AND sv.visit_date <= ?";
            $params[] = $filters['date_to'];
        }

        $query = "SELECT sv.*, p.title as property_title, p.location as address,
                        sv.visitor_name, sv.visitor_phone
                 FROM site_visits sv
                 LEFT JOIN properties p ON sv.property_id = p.id
                 WHERE {$whereClause}
                 ORDER BY sv.visit_date DESC";

        $reportData = $this->db->fetchAll($query, $params);

        return [
            'success' => true,
            'report_type' => 'site_visits',
            'data' => $reportData,
            'summary' => [
                'total_visits' => count($reportData),
                'completed_visits' => count(array_filter($reportData, fn($v) => $v['status'] === 'completed')),
                'pending_visits' => count(array_filter($reportData, fn($v) => $v['status'] === 'scheduled'))
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate acquisition report
     */
    private function generateAcquisitionReport($filters)
    {
        $whereClause = "1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $whereClause .= " AND la.status = ?";
            $params[] = $filters['status'];
        }

        $query = "SELECT la.*, c.name as location_name,
                        la.acquisition_cost as expected_price
                 FROM land_acquisitions la
                 LEFT JOIN colonies c ON la.colony_id = c.id
                 WHERE {$whereClause}
                 ORDER BY la.created_at DESC";

        $reportData = $this->db->fetchAll($query, $params);

        return [
            'success' => true,
            'report_type' => 'acquisition_pipeline',
            'data' => $reportData,
            'summary' => [
                'total_acquisitions' => count($reportData),
                'total_value' => array_sum(array_column($reportData, 'acquisition_cost'))
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate documentation report
     */
    private function generateDocumentationReport($filters)
    {
        $whereClause = "p.created_by = ?";
        $params = [$this->employeeId];

        if (!empty($filters['status'])) {
            $whereClause .= " AND pd.verification_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $whereClause .= " AND pd.document_type = ?";
            $params[] = $filters['type'];
        }

        $query = "SELECT pd.*, p.title as property_title,
                        CASE WHEN pd.expiry_date < CURDATE() THEN 'expired'
                             WHEN pd.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'expiring_soon'
                             ELSE 'valid' END as expiry_status
                 FROM documents pd
                 LEFT JOIN properties p ON pd.entity_id = p.id AND pd.entity_type = 'property'
                 WHERE {$whereClause}
                 ORDER BY pd.expiry_date ASC";

        $reportData = $this->db->fetchAll($query, $params);

        return [
            'success' => true,
            'report_type' => 'documentation_status',
            'data' => $reportData,
            'summary' => [
                'total_documents' => count($reportData),
                'complete_documents' => count(array_filter($reportData, fn($d) => $d['verification_status'] === 'verified')),
                'expired_documents' => count(array_filter($reportData, fn($d) => $d['expiry_status'] === 'expired')),
                'expiring_soon' => count(array_filter($reportData, fn($d) => $d['expiry_status'] === 'expiring_soon'))
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Add to acquisition timeline
     */
    private function addToAcquisitionTimeline($acquisitionId, $acquisitionData)
    {
        $query = "INSERT INTO daily_operations_log (
                    log_type, description, assigned_to, status, created_at
                ) VALUES ('acquisition_update', ?, ?, 'completed', NOW())";

        $this->db->execute($query, [
            "Acquisition #{$acquisitionId} updated to status: {$acquisitionData['status']}. " . ($acquisitionData['notes'] ?? ''),
            $this->employeeId
        ]);
    }

    /**
     * Notify site visit scheduled
     */
    private function notifySiteVisitScheduled($visitorId, $property, $visitData)
    {
        $message = "Site visit scheduled for property '{$property['title']}' on {$visitData['visit_date']} at {$visitData['visit_time']}";
        $this->createNotification($visitorId, 'site_visit_scheduled', $message);
    }

    /**
     * Notify site visit completed
     */
    private function notifySiteVisitCompleted($visitorId, $visit, $completionData)
    {
        $message = "Site visit completed for property '{$visit['property_title']}'";
        $this->createNotification($visitorId, 'site_visit_completed', $message);
    }

    /**
     * Notify acquisition update
     */
    private function notifyAcquisitionUpdate($acquisitionId, $acquisitionData)
    {
        $query = "INSERT INTO employee_activities (employee_id, activity_type, description, metadata, created_at)
                  VALUES (?, 'acquisition_update', ?, ?, NOW())";

        $this->db->execute($query, [
            $this->employeeId,
            "Land acquisition #{$acquisitionId} updated to status: {$acquisitionData['status']}",
            json_encode(['acquisition_id' => $acquisitionId])
        ]);
    }

    /**
     * Create notification
     */
    private function createNotification($recipientId, $type, $message, $relatedId = null)
    {
        $query = "INSERT INTO notifications (
                    user_id, type, title, message, related_id, is_read, created_at
                ) VALUES (?, ?, ?, ?, ?, 0, NOW())";

        $this->db->execute($query, [$recipientId, $type, ucfirst(str_replace('_', ' ', $type)), $message, $relatedId]);
    }

    /**
     * Log land activity
     */
    private function logLandActivity($activityType, $description, $relatedId = null)
    {
        $query = "INSERT INTO employee_activities (
                    employee_id, activity_type, description, metadata, created_at
                ) VALUES (?, ?, ?, ?, NOW())";

        $this->db->execute($query, [
            $this->employeeId,
            $activityType,
            $description,
            json_encode(['related_id' => $relatedId])
        ]);
    }

    /**
     * Handle errors
     */
    private function handleError($message)
    {
        error_log("Land Manager Controller Error: " . $message);

        $_SESSION['error'] = "Unable to load Land Manager dashboard. Please try again.";
        header('Location: ' . BASE_URL . '/employee/dashboard');
        exit;
    }
}
