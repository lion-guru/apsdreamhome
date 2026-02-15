<?php
/**
 * Agent Review Model
 */

namespace App\Models;

class AgentReview extends Model {
    public static $table = 'agent_reviews';
    
    protected array $fillable = [
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
            ->select(['r.*', 'u.uname as user_name'])
            ->from(static::$table . ' as r')
            ->join('user as u', 'r.user_id', '=', 'u.uid')
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
        return static::query()
            ->select([
                'COUNT(*) as total_reviews',
                'AVG(rating) as average_rating'
            ])
            ->where('agent_id', $agentId)
            ->first();
    }

    /**
     * Get rating distribution for an agent
     */
    public function getAgentRatingDistribution($agentId) {
        return static::query()
            ->select(['rating', 'COUNT(*) as count'])
            ->where('agent_id', $agentId)
            ->groupBy('rating')
            ->get();
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
