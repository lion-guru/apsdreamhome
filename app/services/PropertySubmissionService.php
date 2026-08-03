<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

use \App\Traits\ServiceTenantTrait;

/**
 * PropertySubmissionService
 * Handles property posts from users and public users with commission split logic.
 */
class PropertySubmissionService
{
    use \App\Traits\ServiceTenantTrait;

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

        try {
            $columns = array_merge(['submitter_id', 'submitter_type', 'title', 'description', 'price', 'property_type', 'location', 'images', 'commission_split_json'], array_keys($this->tenantInsertData()));
            $values = array_merge([$data['submitter_id'], $data['submitter_type'], $data['title'], $data['description'] ?? '', $data['price'], $data['property_type'] ?? 'Plot', $data['location'] ?? '', json_encode($data['images'] ?? []), json_encode($splitLogic)], array_values($this->tenantInsertData()));
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }

        try {
            $this->db->query("INSERT INTO property_submissions (" . implode(', ', $columns) . ") VALUES (" . $placeholders . ")", $values);
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
        $sql = "SELECT * FROM property_submissions WHERE submitter_id = ?" . $this->tenantSql();
        return $this->db->fetchAll($sql, array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : [])) ?? [];
    }

    /**
     * Approve a submission and move it to the main properties table.
     */
    public function approveSubmission($submissionId)
    {
        try {
            $submission = $this->db->fetchOne("SELECT * FROM property_submissions WHERE id = ?" . $this->tenantSql(), array_merge([$submissionId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        if (!$submission) return ['success' => false, 'message' => 'Submission not found'];

        try {
            // 1. Insert into main properties table
            $columns = array_merge(['title', 'description', 'price', 'city', 'status'], array_keys($this->tenantInsertData()));
            $values = array_merge([$submission['title'], $submission['description'], $submission['price'], $submission['location'], 'available'], array_values($this->tenantInsertData()));
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $this->db->query("INSERT INTO properties (" . implode(', ', $columns) . ") VALUES (" . $placeholders . ")", $values);
            $propertyId = $this->db->lastInsertId();

            // 2. Mark submission as approved
            $this->db->query("UPDATE property_submissions SET status = 'approved' WHERE id = ?" . $this->tenantSql(), array_merge([$submissionId], $this->tenantId() > 1 ? [$this->tenantId()] : []));

            return ['success' => true, 'message' => 'Property approved and live!', 'property_id' => $propertyId];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
