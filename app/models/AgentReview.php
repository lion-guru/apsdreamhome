<?php

// TODO: Add proper error handling with try-catch blocks

/**
 * Agent Review Model
 */

namespace App\Models;

class AgentReview extends Model {
    public static $table = 'agent_reviews';
    protected static $tenantScoped = true;
    
    protected $fillable = [
        'agent_id',
        'user_id',
        'rating',
        'review_text',
        'property_id',
        'verified',
        'helpful_count',
        'created_at',
        'updated_at'
    ];

    /**
     * Get reviews for an agent
     */
    public function getAgentReviews($agentId, $limit = 10, $offset = 0) {
        return static::query()
            ->select(['r.*', 'u.name as user_name'])
            ->from(static::$table . ' as r')
            ->join('users as u', 'r.user_id', '=', 'u.id')
            ->where('r.agent_id', $agentId)
            ->orderBy('r.created_at', 'DESC')
            ->limit($limit)
            ->skip($offset)
            ->get();
    }

    /**
     * Get review summary for an agent
     */
    public function getAgentReviewSummary($agentId) {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge([$agentId], $tParams);
        $sql = "SELECT COUNT(*) as total_reviews, AVG(rating) as average_rating
                FROM " . static::$table . "
                WHERE agent_id = ?{$tSql}";
        $row = static::getDb()->fetch($sql, $params);
        return [
            'total_reviews' => (int)($row['total_reviews'] ?? 0),
            'average_rating' => (float)($row['average_rating'] ?? 0)
        ];
    }

    /**
     * Get rating distribution for an agent
     */
    public function getAgentRatingDistribution($agentId) {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge([$agentId], $tParams);
        $sql = "SELECT rating, COUNT(*) as count
                FROM " . static::$table . "
                WHERE agent_id = ?{$tSql}
                GROUP BY rating";
        return static::getDb()->fetchAll($sql, $params);
    }

    /**
     * Check if user has already reviewed an agent
     */
    public function hasReviewed($userId, $agentId) {
        $existing = static::query()
            ->select(['id'])
            ->where('user_id', $userId)
            ->where('agent_id', $agentId)
            ->first();
        return !empty($existing);
    }
}
