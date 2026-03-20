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
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
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
                    SUM(p.market_value) as total_value,
                    AVG(p.area_sqft) as avg_area
                 FROM properties p
                 WHERE p.manager_id = ?
                 GROUP BY p.status
                 ORDER BY count DESC";

        $statusBreakdown = $this->db->fetchAll($query, [$this->employeeId]);

        // Get property distribution by type
        $typeQuery = "SELECT 
                        p.type,
                        COUNT(*) as count,
                        SUM(p.market_value) as total_value
                      FROM properties p
                      WHERE p.manager_id = ?
                      GROUP BY p.type
                      ORDER BY count DESC";

        $typeBreakdown = $this->db->fetchAll($typeQuery, [$this->employeeId]);

        // Get property distribution by location
        $locationQuery = "SELECT 
                            p.location,
                            COUNT(*) as count,
                            SUM(p.market_value) as total_value
                          FROM properties p
                          WHERE p.manager_id = ?
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
                        p.title as property_title,
                        p.location as property_location,
                        p.type as property_type,
                        v.name as visitor_name,
                        v.company as visitor_company
                 FROM site_visits sv
                 JOIN properties p ON sv.property_id = p.id
                 LEFT JOIN visitors v ON sv.visitor_id = v.id
                 WHERE sv.manager_id = ?
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
                        p.title as property_title,
                        p.location as property_location,
                        p.area_sqft,
                        p.estimated_value,
                        l.name as land_owner_name,
                        l.phone as land_owner_phone
                 FROM land_acquisitions la
                 JOIN properties p ON la.property_id = p.id
                 LEFT JOIN land_owners l ON la.land_owner_id = l.id
                 WHERE la.assigned_manager = ?
                 AND la.status IN ('evaluation', 'due_diligence', 'negotiation', 'final_approval')
                 ORDER BY la.priority DESC, la.created_at ASC
                 LIMIT 15";

        return $this->db->fetchAll($query, [$this->employeeId]);
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
                                AVG(TIMESTAMPDIFF(DAY, listed_date, sold_date)) as avg_days_to_sell,
                                SUM(market_value) as total_portfolio_value
                             FROM properties 
                             WHERE manager_id = ?
                             AND YEAR(listed_date) = YEAR(CURDATE())";

        $performanceMetrics = $this->db->fetchOne($performanceQuery, [$this->employeeId]);

        // Site visit metrics
        $visitMetricsQuery = "SELECT 
                                 COUNT(*) as total_visits,
                                 SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_visits,
                                 SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_visits,
                                 AVG(rating) as avg_rating
                              FROM site_visits 
                              WHERE manager_id = ?
                              AND MONTH(visit_date) = MONTH(CURDATE())";

        $visitMetrics = $this->db->fetchOne($visitMetricsQuery, [$this->employeeId]);

        // Acquisition metrics
        $acquisitionMetricsQuery = "SELECT 
                                      COUNT(*) as total_acquisitions,
                                      SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_acquisitions,
                                      SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_acquisitions,
                                      AVG(TIMESTAMPDIFF(DAY, created_at, completed_at)) as avg_acquisition_time
                                   FROM land_acquisitions 
                                   WHERE assigned_manager = ?
                                   AND YEAR(created_at) = YEAR(CURDATE())";

        $acquisitionMetrics = $this->db->fetchOne($acquisitionMetricsQuery, [$this->employeeId]);

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
        $query = "SELECT * FROM land_management_activities 
                  WHERE manager_id = ?
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
                    COUNT(*) as total_documents,
                    SUM(CASE WHEN status = 'complete' THEN 1 ELSE 0 END) as complete,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired
                 FROM property_documents 
                 WHERE property_id IN (
                     SELECT id FROM properties WHERE manager_id = ?
                 )";

        return $this->db->fetchOne($query, [$this->employeeId]);
    }

    /**
     * Schedule site visit
     */
    public function scheduleSiteVisit($propertyId, $visitData)
    {
        try {
            // Validate property assignment
            $propertyQuery = "SELECT id, title, location FROM properties WHERE id = ? AND manager_id = ?";
            $property = $this->db->fetchOne($propertyQuery, [$propertyId, $this->employeeId]);

            if (!$property) {
                throw new Exception("Property not found or not assigned to you");
            }

            // Create visitor record if not exists
            $visitorId = $this->createOrUpdateVisitor($visitData['visitor']);

            // Schedule site visit
            $query = "INSERT INTO site_visits (
                        property_id, visitor_id, manager_id, visit_date, visit_time,
                        purpose, expected_duration, notes, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())";

            $this->db->execute($query, [
                $propertyId,
                $visitorId,
                $this->employeeId,
                $visitData['visit_date'],
                $visitData['visit_time'],
                $visitData['purpose'] ?? '',
                $visitData['expected_duration'] ?? 60,
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
            $this->notifySiteVisitScheduled($visitorId, $property, $visitData);

            return [
                'success' => true,
                'visit_id' => $visitId,
                'message' => "Site visit scheduled successfully"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create or update visitor
     */
    private function createOrUpdateVisitor($visitorData)
    {
        // Check if visitor exists
        $checkQuery = "SELECT id FROM visitors WHERE phone = ? OR email = ?";
        $existingVisitor = $this->db->fetchOne($checkQuery, [$visitorData['phone'], $visitorData['email'] ?? '']);

        if ($existingVisitor) {
            // Update existing visitor
            $updateQuery = "UPDATE visitors 
                           SET name = ?, email = ?, company = ?, updated_at = NOW()
                           WHERE id = ?";

            $this->db->execute($updateQuery, [
                $visitorData['name'],
                $visitorData['email'] ?? '',
                $visitorData['company'] ?? '',
                $existingVisitor['id']
            ]);

            return $existingVisitor['id'];
        } else {
            // Create new visitor
            $insertQuery = "INSERT INTO visitors (name, email, phone, company, created_at)
                            VALUES (?, ?, ?, ?, NOW())";

            $this->db->execute($insertQuery, [
                $visitorData['name'],
                $visitorData['email'] ?? '',
                $visitorData['phone'],
                $visitorData['company'] ?? ''
            ]);

            return $this->db->lastInsertId();
        }
    }

    /**
     * Update land acquisition
     */
    public function updateAcquisition($acquisitionId, $acquisitionData)
    {
        try {
            // Get acquisition details
            $acquisitionQuery = "SELECT * FROM land_acquisitions WHERE id = ? AND assigned_manager = ?";
            $acquisition = $this->db->fetchOne($acquisitionQuery, [$acquisitionId, $this->employeeId]);

            if (!$acquisition) {
                throw new Exception("Acquisition not found or not assigned to you");
            }

            // Update acquisition
            $query = "UPDATE land_acquisitions 
                      SET status = ?, progress_percentage = ?, notes = ?, 
                          updated_by = ?, updated_at = NOW()
                      WHERE id = ?";

            $this->db->execute($query, [
                $acquisitionData['status'],
                $acquisitionData['progress_percentage'] ?? 0,
                $acquisitionData['notes'] ?? '',
                $this->employeeId,
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
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Complete site visit
     */
    public function completeSiteVisit($visitId, $completionData)
    {
        try {
            // Get visit details
            $visitQuery = "SELECT sv.*, p.title as property_title
                           FROM site_visits sv
                           JOIN properties p ON sv.property_id = p.id
                           WHERE sv.id = ? AND sv.manager_id = ?";

            $visit = $this->db->fetchOne($visitQuery, [$visitId, $this->employeeId]);

            if (!$visit) {
                throw new Exception("Site visit not found or not assigned to you");
            }

            // Update visit status
            $query = "UPDATE site_visits 
                      SET status = 'completed', completed_at = NOW(), 
                          actual_duration = ?, rating = ?, feedback_notes = ?,
                          outcome = ?, next_steps = ?
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
            $this->notifySiteVisitCompleted($visit['visitor_id'], $visit, $completionData);

            return [
                'success' => true,
                'message' => "Site visit completed successfully"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update property documentation
     */
    public function updatePropertyDocumentation($propertyId, $documents)
    {
        try {
            // Validate property assignment
            $propertyQuery = "SELECT id, title FROM properties WHERE id = ? AND manager_id = ?";
            $property = $this->db->fetchOne($propertyQuery, [$propertyId, $this->employeeId]);

            if (!$property) {
                throw new Exception("Property not found or not assigned to you");
            }

            foreach ($documents as $document) {
                // Check if document exists
                $checkQuery = "SELECT id FROM property_documents 
                               WHERE property_id = ? AND document_type = ?";
                $existingDoc = $this->db->fetchOne($checkQuery, [$propertyId, $document['type']]);

                if ($existingDoc) {
                    // Update existing document
                    $updateQuery = "UPDATE property_documents 
                                   SET status = ?, expiry_date = ?, notes = ?, 
                                       updated_by = ?, updated_at = NOW()
                                   WHERE id = ?";

                    $this->db->execute($updateQuery, [
                        $document['status'],
                        $document['expiry_date'] ?? null,
                        $document['notes'] ?? '',
                        $this->employeeId,
                        $existingDoc['id']
                    ]);
                } else {
                    // Create new document record
                    $insertQuery = "INSERT INTO property_documents (
                                        property_id, document_type, status, expiry_date,
                                        notes, created_by, created_at
                                    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";

                    $this->db->execute($insertQuery, [
                        $propertyId,
                        $document['type'],
                        $document['status'],
                        $document['expiry_date'] ?? null,
                        $document['notes'] ?? '',
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
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate land management report
     */
    public function generateLandReport($reportType, $filters = [])
    {
        try {
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
        } catch (Exception $e) {
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
        $whereClause = "p.manager_id = ?";
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
                        COUNT(pd.id) as document_count,
                        SUM(CASE WHEN pd.status = 'complete' THEN 1 ELSE 0 END) as complete_documents
                 FROM properties p
                 LEFT JOIN property_documents pd ON p.id = pd.property_id
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
                'total_value' => array_sum(array_column($reportData, 'market_value')),
                'avg_area' => array_sum(array_column($reportData, 'area_sqft')) / count($reportData)
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate site visit report
     */
    private function generateSiteVisitReport($filters)
    {
        $whereClause = "sv.manager_id = ?";
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

        $query = "SELECT sv.*, p.title as property_title, p.address,
                        v.name as visitor_name, v.phone as visitor_phone
                 FROM site_visits sv
                 LEFT JOIN properties p ON sv.property_id = p.id
                 LEFT JOIN visitors v ON sv.visitor_id = v.id
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
        $whereClause = "la.manager_id = ?";
        $params = [$this->employeeId];

        if (!empty($filters['status'])) {
            $whereClause .= " AND la.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $whereClause .= " AND la.priority = ?";
            $params[] = $filters['priority'];
        }

        $query = "SELECT la.*, l.location_name, l.area_sqft, l.expected_price,
                        COUNT(las.id) as stakeholder_count
                 FROM land_acquisitions la
                 LEFT JOIN land l ON la.land_id = l.id
                 LEFT JOIN acquisition_stakeholders las ON la.id = las.acquisition_id
                 WHERE {$whereClause}
                 GROUP BY la.id
                 ORDER BY la.created_at DESC";

        $reportData = $this->db->fetchAll($query, $params);

        return [
            'success' => true,
            'report_type' => 'acquisition_pipeline',
            'data' => $reportData,
            'summary' => [
                'total_acquisitions' => count($reportData),
                'total_value' => array_sum(array_column($reportData, 'expected_price')),
                'high_priority' => count(array_filter($reportData, fn($a) => $a['priority'] === 'high'))
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate documentation report
     */
    private function generateDocumentationReport($filters)
    {
        $whereClause = "pd.manager_id = ?";
        $params = [$this->employeeId];

        if (!empty($filters['status'])) {
            $whereClause .= " AND pd.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $whereClause .= " AND pd.document_type = ?";
            $params[] = $filters['type'];
        }

        $query = "SELECT pd.*, p.title as property_title, l.location_name,
                        CASE WHEN pd.expiry_date < CURDATE() THEN 'expired'
                             WHEN pd.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'expiring_soon'
                             ELSE 'valid' END as expiry_status
                 FROM property_documents pd
                 LEFT JOIN properties p ON pd.property_id = p.id
                 LEFT JOIN land l ON pd.land_id = l.id
                 WHERE {$whereClause}
                 ORDER BY pd.expiry_date ASC";

        $reportData = $this->db->fetchAll($query, $params);

        return [
            'success' => true,
            'report_type' => 'documentation_status',
            'data' => $reportData,
            'summary' => [
                'total_documents' => count($reportData),
                'complete_documents' => count(array_filter($reportData, fn($d) => $d['status'] === 'complete')),
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
        $query = "INSERT INTO acquisition_timeline (
                    acquisition_id, status, notes, performed_by, created_at
                ) VALUES (?, ?, ?, ?, NOW())";

        $this->db->execute($query, [
            $acquisitionId,
            $acquisitionData['status'],
            $acquisitionData['notes'] ?? '',
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
        // Get stakeholders to notify
        $stakeholdersQuery = "SELECT DISTINCT employee_id FROM acquisition_stakeholders 
                              WHERE acquisition_id = ?";
        $stakeholders = $this->db->fetchAll($stakeholdersQuery, [$acquisitionId]);

        foreach ($stakeholders as $stakeholder) {
            $message = "Land acquisition updated to status: {$acquisitionData['status']}";
            $this->createNotification($stakeholder['employee_id'], 'acquisition_update', $message, $acquisitionId);
        }
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
     * Log land activity
     */
    private function logLandActivity($activityType, $description, $relatedId = null)
    {
        $query = "INSERT INTO land_management_activities (
                    activity_type, description, related_id, 
                    manager_id, created_at
                ) VALUES (?, ?, ?, ?, NOW())";

        $this->db->execute($query, [$activityType, $description, $relatedId, $this->employeeId]);
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
