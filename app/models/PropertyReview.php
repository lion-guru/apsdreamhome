<?php

// TODO: Add proper error handling with try-catch blocks

/**
 * Property Review Model
 */

namespace App\Models;

class PropertyReview extends Model {
    public static $table = 'property_reviews';
    protected static $tenantScoped = true;
    
    protected $fillable = [
        'customer_id',
        'property_id',
        'rating',
        'review_text',
        'anonymous',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * Get reviews for a property
     */
    public function getPropertyReviews($propertyId, $limit = 10, $offset = 0) {
        return static::query()
            ->select(['r.*', 'u.name as user_name'])
            ->from(static::$table . ' as r')
            ->join('users as u', 'r.customer_id', '=', 'u.id')
            ->where('r.property_id', $propertyId)
            ->where('r.status', 'approved')
            ->orderBy('r.created_at', 'DESC')
            ->limit($limit)
            ->skip($offset)
            ->get();
    }

    /**
     * Get review summary for a property
     */
    public function getPropertyReviewSummary($propertyId) {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge([$propertyId, 'approved'], $tParams);
        $sql = "SELECT COUNT(*) as total_reviews, AVG(rating) as average_rating
                FROM " . static::$table . "
                WHERE property_id = ? AND status = ?{$tSql}";
        $row = static::getDb()->fetch($sql, $params);
        return [
            'total_reviews' => (int)($row['total_reviews'] ?? 0),
            'average_rating' => (float)($row['average_rating'] ?? 0)
        ];
    }

    /**
     * Get rating distribution for a property
     */
    public function getPropertyRatingDistribution($propertyId) {
        [$tSql, $tParams] = static::tenantClause();
        $params = array_merge([$propertyId, 'approved'], $tParams);
        $sql = "SELECT rating, COUNT(*) as count
                FROM " . static::$table . "
                WHERE property_id = ? AND status = ?{$tSql}
                GROUP BY rating";
        return static::getDb()->fetchAll($sql, $params);
    }

    /**
     * Check if user has already reviewed a property
     */
    public function hasReviewed($customerId, $propertyId) {
        $existing = static::query()
            ->select(['id'])
            ->where('customer_id', $customerId)
            ->where('property_id', $propertyId)
            ->first();
        return !empty($existing);
    }
}
