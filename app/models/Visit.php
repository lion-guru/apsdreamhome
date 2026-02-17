<?php

namespace App\Models;

use App\Core\Model;

/**
 * Visit Model
 * Handles property site visits, virtual tours and follow-ups
 */
class Visit extends Model
{
    protected static $table = 'property_visits';
    protected static $primaryKey = 'id';

    protected array $fillable = [
        'customer_id',
        'property_id',
        'associate_id',
        'visit_date',
        'visit_type',
        'status',
        'notes',
        'feedback_rating',
        'feedback_comments',
        'interest_level',
        'follow_up_required',
        'follow_up_date',
        'created_by'
    ];

    /**
     * Get upcoming visits
     */
    public function getUpcoming($limit = 10)
    {
        $sql = "SELECT v.*, c.name as customer_name, p.title as property_title 
                FROM property_visits v
                JOIN customers c ON v.customer_id = c.id
                JOIN properties p ON v.property_id = p.id
                WHERE v.visit_date >= NOW() AND v.status != 'cancelled'
                ORDER BY v.visit_date ASC LIMIT ?";

        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get visit history for a customer
     */
    public function getHistoryByCustomer($customerId)
    {
        return $this->where('customer_id', $customerId)
            ->orderBy('visit_date', 'DESC')
            ->get();
    }

    /**
     * Update visit status and feedback
     */
    public function completeVisit($id, $feedback)
    {
        $sql = "UPDATE property_visits SET 
                status = 'completed', 
                feedback_rating = ?, 
                feedback_comments = ?, 
                interest_level = ?,
                updated_at = NOW() 
                WHERE id = ?";

        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute([
            $feedback['rating'],
            $feedback['comments'],
            $feedback['interest_level'],
            $id
        ]);
    }

    /**
     * Get visit statistics
     */
    public function getStats()
    {
        $stats = [];
        $stats['total'] = self::getConnection()->query("SELECT COUNT(*) FROM property_visits")->fetchColumn();
        $stats['upcoming'] = self::getConnection()->query("SELECT COUNT(*) FROM property_visits WHERE visit_date >= NOW() AND status = 'scheduled'")->fetchColumn();
        $stats['completed'] = self::getConnection()->query("SELECT COUNT(*) FROM property_visits WHERE status = 'completed'")->fetchColumn();

        return $stats;
    }
}
