<?php

namespace App\Services;

use App\Core\Database;
use App\Services\Security\SecurityService;
use Exception;
use PDO;

/**
 * Agent Assignment Service
 * Handles automatic and manual assignment of agents/offices to direct customers
 */

class AgentAssignmentService
{
    private PDO $db;
    private SecurityService $securityService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->securityService = new SecurityService();
    }

    /**
     * Assign agent to customer based on various criteria
     */
    public function assignAgentToCustomer(int $customerId, array $preferences = []): array
    {
        try {
            // Check if customer already has an assigned agent
            if ($this->hasAssignedAgent($customerId)) {
                return ['success' => false, 'message' => 'Customer already has an assigned agent'];
            }

            // Get customer details
            $customer = $this->getCustomer($customerId);
            if (!$customer) {
                return ['success' => false, 'message' => 'Customer not found'];
            }

            // Find best agent based on criteria
            $agentId = $this->findBestAgent($customer, $preferences);
            
            if (!$agentId) {
                // If no agent found, assign to default office
                return $this->assignToOffice($customerId, $preferences);
            }

            // Perform assignment
            $result = $this->createAssignment($customerId, $agentId, 'agent');

            if ($result['success']) {
                // Send notifications
                $this->sendAssignmentNotifications($customerId, $agentId, 'agent');
                
                // Update agent workload
                $this->updateAgentWorkload($agentId);
            }

            return $result;

        } catch (Exception $e) {
            error_log("Agent assignment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Assignment failed'];
        }
    }

    /**
     * Assign office to customer
     */
    public function assignOfficeToCustomer(int $customerId, array $preferences = []): array
    {
        try {
            // Check if customer already has an assigned office
            if ($this->hasAssignedOffice($customerId)) {
                return ['success' => false, 'message' => 'Customer already has an assigned office'];
            }

            // Find best office based on criteria
            $officeId = $this->findBestOffice($customerId, $preferences);
            
            if (!$officeId) {
                return ['success' => false, 'message' => 'No suitable office found'];
            }

            // Perform assignment
            $result = $this->createAssignment($customerId, $officeId, 'office');

            if ($result['success']) {
                // Send notifications
                $this->sendAssignmentNotifications($customerId, $officeId, 'office');
                
                // Update office workload
                $this->updateOfficeWorkload($officeId);
            }

            return $result;

        } catch (Exception $e) {
            error_log("Office assignment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Office assignment failed'];
        }
    }

    /**
     * Automatic assignment for new customer registration
     */
    public function autoAssignNewCustomer(int $customerId): array
    {
        try {
            $customer = $this->getCustomer($customerId);
            if (!$customer) {
                return ['success' => false, 'message' => 'Customer not found'];
            }

            // Try agent assignment first
            $agentResult = $this->assignAgentToCustomer($customerId, [
                'location' => $customer['city'] ?? '',
                'property_type' => $customer['preferred_property_type'] ?? '',
                'budget_range' => $customer['budget_range'] ?? ''
            ]);

            if ($agentResult['success']) {
                return $agentResult;
            }

            // Fallback to office assignment
            return $this->assignOfficeToCustomer($customerId, [
                'location' => $customer['city'] ?? ''
            ]);

        } catch (Exception $e) {
            error_log("Auto assignment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Auto assignment failed'];
        }
    }

    /**
     * Check if customer has assigned agent
     */
    private function hasAssignedAgent(int $customerId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM customer_assignments 
                                   WHERE customer_id = :customer_id AND assignment_type = 'agent' 
                                   AND status = 'active' LIMIT 1");
        $stmt->execute(['customer_id' => $customerId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Check if customer has assigned office
     */
    private function hasAssignedOffice(int $customerId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM customer_assignments 
                                   WHERE customer_id = :customer_id AND assignment_type = 'office' 
                                   AND status = 'active' LIMIT 1");
        $stmt->execute(['customer_id' => $customerId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Get customer details
     */
    private function getCustomer(int $customerId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :customer_id AND role = 'customer' LIMIT 1");
        $stmt->execute(['customer_id' => $customerId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find best agent for customer
     */
    private function findBestAgent(array $customer, array $preferences): ?int
    {
        $location = $preferences['location'] ?? $customer['city'] ?? '';
        $propertyType = $preferences['property_type'] ?? '';
        $budgetRange = $preferences['budget_range'] ?? '';

        // Build query to find suitable agents
        $sql = "SELECT a.*, 
                       COUNT(ca.id) as current_assignments,
                       AVG(ar.rating) as avg_rating,
                       COUNT(p.id) as properties_handled
                FROM agents a
                LEFT JOIN customer_assignments ca ON a.user_id = ca.agent_id AND ca.status = 'active'
                LEFT JOIN agent_reviews ar ON a.user_id = ar.agent_id
                LEFT JOIN properties p ON a.user_id = p.assigned_agent_id
                WHERE a.status = 'active' AND a.workload < a.max_workload";

        $params = [];
        
        // Add location filter
        if (!empty($location)) {
            $sql .= " AND (a.service_areas LIKE :location OR a.city = :city)";
            $params['location'] = '%' . $location . '%';
            $params['city'] = $location;
        }

        // Add property type filter
        if (!empty($propertyType)) {
            $sql .= " AND a.specializations LIKE :property_type";
            $params['property_type'] = '%' . $propertyType . '%';
        }

        $sql .= " GROUP BY a.user_id
                 ORDER BY (a.priority_score * 0.4 + 
                          (CASE WHEN a.current_assignments < a.max_workload * 0.7 THEN 1 ELSE 0.5 END) * 0.3 +
                          COALESCE(ar.avg_rating, 0) * 0.2 +
                          (CASE WHEN a.experience_years > 3 THEN 1 ELSE 0.5 END) * 0.1) DESC
                 LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result ? (int)$result['user_id'] : null;
    }

    /**
     * Find best office for customer
     */
    private function findBestOffice(int $customerId, array $preferences): ?int
    {
        $location = $preferences['location'] ?? '';

        $sql = "SELECT o.*, 
                       COUNT(ca.id) as current_assignments,
                       AVG(orating.rating) as avg_rating
                FROM offices o
                LEFT JOIN customer_assignments ca ON o.id = ca.office_id AND ca.status = 'active'
                LEFT JOIN office_reviews orating ON o.id = orating.office_id
                WHERE o.status = 'active'";

        $params = [];
        
        // Add location filter
        if (!empty($location)) {
            $sql .= " AND (o.service_cities LIKE :location OR o.city = :city)";
            $params['location'] = '%' . $location . '%';
            $params['city'] = $location;
        }

        $sql .= " GROUP BY o.id
                 ORDER BY (o.priority_score * 0.5 + 
                          (CASE WHEN o.current_assignments < o.max_customers * 0.8 THEN 1 ELSE 0.5 END) * 0.3 +
                          COALESCE(orating.avg_rating, 0) * 0.2) DESC
                 LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result ? (int)$result['id'] : null;
    }

    /**
     * Create assignment record
     */
    private function createAssignment(int $customerId, int $assigneeId, string $assignmentType): array
    {
        try {
            $sql = "INSERT INTO customer_assignments 
                    (customer_id, {$assignmentType}_id, assignment_type, status, assigned_by, assigned_at)
                    VALUES (:customer_id, :assignee_id, :assignment_type, 'active', 'system', NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'customer_id' => $customerId,
                'assignee_id' => $assigneeId,
                'assignment_type' => $assignmentType
            ]);

            if ($success) {
                $assignmentId = (int)$this->db->lastInsertId();
                
                return [
                    'success' => true,
                    'assignment_id' => $assignmentId,
                    'message' => ucfirst($assignmentType) . ' assigned successfully'
                ];
            }

            return ['success' => false, 'message' => 'Failed to create assignment'];

        } catch (Exception $e) {
            error_log("Assignment creation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Assignment creation failed'];
        }
    }

    /**
     * Send assignment notifications
     */
    private function sendAssignmentNotifications(int $customerId, int $assigneeId, string $assignmentType): void
    {
        try {
            // Get customer details
            $customerStmt = $this->db->prepare("SELECT name, email FROM users WHERE id = :customer_id LIMIT 1");
            $customerStmt->execute(['customer_id' => $customerId]);
            $customer = $customerStmt->fetch();

            // Get assignee details
            if ($assignmentType === 'agent') {
                $assigneeStmt = $this->db->prepare("SELECT u.name, u.email, u.phone FROM users u 
                                                   JOIN agents a ON u.id = a.user_id 
                                                   WHERE u.id = :assignee_id LIMIT 1");
            } else {
                $assigneeStmt = $this->db->prepare("SELECT o.name, o.email, o.phone FROM offices o 
                                                   WHERE o.id = :assignee_id LIMIT 1");
            }
            
            $assigneeStmt->execute(['assignee_id' => $assigneeId]);
            $assignee = $assigneeStmt->fetch();

            if ($customer && $assignee) {
                // Notify customer
                $this->createNotification($customerId, 'assignment', 
                    ucfirst($assignmentType) . ' Assigned', 
                    "You have been assigned to {$assignee['name']}. They will contact you soon.");

                // Notify assignee
                $assigneeUserId = $assignmentType === 'agent' ? $assigneeId : null;
                if ($assigneeUserId) {
                    $this->createNotification($assigneeUserId, 'new_customer', 
                        'New Customer Assignment', 
                        "New customer {$customer['name']} has been assigned to you.");
                }
            }

        } catch (Exception $e) {
            error_log("Notification sending error: " . $e->getMessage());
        }
    }

    /**
     * Create notification
     */
    private function createNotification(int $userId, string $type, string $title, string $message): void
    {
        $sql = "INSERT INTO notifications (user_id, type, title, message, created_at)
                VALUES (:user_id, :type, :title, :message, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message
        ]);
    }

    /**
     * Update agent workload
     */
    private function updateAgentWorkload(int $agentId): void
    {
        $sql = "UPDATE agents SET workload = workload + 1 WHERE user_id = :agent_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['agent_id' => $agentId]);
    }

    /**
     * Update office workload
     */
    private function updateOfficeWorkload(int $officeId): void
    {
        $sql = "UPDATE offices SET current_customers = current_customers + 1 WHERE id = :office_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['office_id' => $officeId]);
    }

    /**
     * Reassign customer to different agent
     */
    public function reassignCustomer(int $customerId, int $newAgentId, string $reason = ''): array
    {
        try {
            // Get current assignment
            $stmt = $this->db->prepare("SELECT * FROM customer_assignments 
                                       WHERE customer_id = :customer_id AND assignment_type = 'agent' 
                                       AND status = 'active' LIMIT 1");
            $stmt->execute(['customer_id' => $customerId]);
            $currentAssignment = $stmt->fetch();

            if (!$currentAssignment) {
                return ['success' => false, 'message' => 'No current agent assignment found'];
            }

            // Deactivate current assignment
            $this->db->prepare("UPDATE customer_assignments SET status = 'inactive', 
                               ended_at = NOW(), end_reason = :reason 
                               WHERE id = :assignment_id")
                     ->execute([
                         'reason' => $reason ?: 'Reassigned',
                         'assignment_id' => $currentAssignment['id']
                     ]);

            // Update old agent workload
            $this->db->prepare("UPDATE agents SET workload = workload - 1 WHERE user_id = :agent_id")
                     ->execute(['agent_id' => $currentAssignment['agent_id']]);

            // Create new assignment
            $result = $this->createAssignment($customerId, $newAgentId, 'agent');

            if ($result['success']) {
                // Update new agent workload
                $this->updateAgentWorkload($newAgentId);
                
                // Send notifications
                $this->sendReassignmentNotifications($customerId, $currentAssignment['agent_id'], $newAgentId, $reason);
            }

            return $result;

        } catch (Exception $e) {
            error_log("Reassignment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Reassignment failed'];
        }
    }

    /**
     * Send reassignment notifications
     */
    private function sendReassignmentNotifications(int $customerId, int $oldAgentId, int $newAgentId, string $reason): void
    {
        try {
            // Get details
            $customerStmt = $this->db->prepare("SELECT name FROM users WHERE id = :customer_id LIMIT 1");
            $customerStmt->execute(['customer_id' => $customerId]);
            $customer = $customerStmt->fetch();

            $oldAgentStmt = $this->db->prepare("SELECT name FROM users WHERE id = :agent_id LIMIT 1");
            $oldAgentStmt->execute(['agent_id' => $oldAgentId]);
            $oldAgent = $oldAgentStmt->fetch();

            $newAgentStmt = $this->db->prepare("SELECT name FROM users WHERE id = :agent_id LIMIT 1");
            $newAgentStmt->execute(['agent_id' => $newAgentId]);
            $newAgent = $newAgentStmt->fetch();

            if ($customer && $oldAgent && $newAgent) {
                // Notify customer
                $this->createNotification($customerId, 'reassignment', 
                    'Agent Reassigned', 
                    "You have been reassigned from {$oldAgent['name']} to {$newAgent['name']}.");

                // Notify old agent
                $this->createNotification($oldAgentId, 'reassignment', 
                    'Customer Reassigned', 
                    "Customer {$customer['name']} has been reassigned to another agent.");

                // Notify new agent
                $this->createNotification($newAgentId, 'new_customer', 
                    'Customer Reassigned', 
                    "Customer {$customer['name']} has been reassigned to you.");
            }

        } catch (Exception $e) {
            error_log("Reassignment notification error: " . $e->getMessage());
        }
    }

    /**
     * Get assignment history for customer
     */
    public function getAssignmentHistory(int $customerId): array
    {
        $sql = "SELECT ca.*, 
                       CASE 
                           WHEN ca.assignment_type = 'agent' THEN u.name
                           ELSE o.name
                       END as assignee_name,
                       ca.assignment_type
                FROM customer_assignments ca
                LEFT JOIN users u ON ca.agent_id = u.id AND ca.assignment_type = 'agent'
                LEFT JOIN offices o ON ca.office_id = o.id AND ca.assignment_type = 'office'
                WHERE ca.customer_id = :customer_id
                ORDER BY ca.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId]);
        return $stmt->fetchAll();
    }

    /**
     * Get agent's current assignments
     */
    public function getAgentAssignments(int $agentId): array
    {
        $sql = "SELECT ca.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM customer_assignments ca
                JOIN users u ON ca.customer_id = u.id
                WHERE ca.agent_id = :agent_id AND ca.status = 'active'
                ORDER BY ca.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['agent_id' => $agentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get office's current assignments
     */
    public function getOfficeAssignments(int $officeId): array
    {
        $sql = "SELECT ca.*, u.name as customer_name, u.email as customer_email
                FROM customer_assignments ca
                JOIN users u ON ca.customer_id = u.id
                WHERE ca.office_id = :office_id AND ca.status = 'active'
                ORDER BY ca.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['office_id' => $officeId]);
        return $stmt->fetchAll();
    }
}
