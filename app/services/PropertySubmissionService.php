<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

/**
 * PropertySubmissionService
 * Handles property posts from agents and public users with commission split logic.
 */
class PropertySubmissionService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Submit a new property for approval.
     */
    public function submitProperty($data)
    {
        $splitLogic = [
            'company_fee_percent' => ($data['submitter_type'] == 'agent') ? 20 : 100,
            'agent_share_percent' => ($data['submitter_type'] == 'agent') ? 80 : 0
        ];

        $sql = "INSERT INTO property_submissions (
                    submitter_id, submitter_type, title, description, price, 
                    property_type, location, images, commission_split_json
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['submitter_id'],
            $data['submitter_type'],
            $data['title'],
            $data['description'] ?? '',
            $data['price'],
            $data['property_type'] ?? 'Plot',
            $data['location'] ?? '',
            json_encode($data['images'] ?? []),
            json_encode($splitLogic)
        ];

        try {
            $this->db->query($sql, $params);
            return [
                'success' => true, 
                'message' => 'Property submitted successfully. It will be live after admin approval.',
                'submission_id' => $this->db->lastInsertId()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get submissions for a specific user.
     */
    public function getUserSubmissions($userId)
    {
        $sql = "SELECT * FROM property_submissions WHERE submitter_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$userId]) ?? [];
    }

    /**
     * Approve a submission and move it to the main properties table.
     */
    public function approveSubmission($submissionId)
    {
        $submission = $this->db->fetchOne("SELECT * FROM property_submissions WHERE id = ?", [$submissionId]);
        if (!$submission) return ['success' => false, 'message' => 'Submission not found'];

        try {
            // 1. Insert into main properties table
            $sql = "INSERT INTO properties (title, description, price, city, status) 
                    VALUES (?, ?, ?, ?, 'available')";
            $this->db->query($sql, [
                $submission['title'], 
                $submission['description'], 
                $submission['price'], 
                $submission['location']
            ]);
            $propertyId = $this->db->lastInsertId();

            // 2. Mark submission as approved
            $this->db->query("UPDATE property_submissions SET status = 'approved' WHERE id = ?", [$submissionId]);

            return ['success' => true, 'message' => 'Property approved and live!', 'property_id' => $propertyId];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
