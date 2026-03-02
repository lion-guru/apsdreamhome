<?php
/**
 * Property Review Model
 */

namespace App\Models;

class PropertyReview extends Model {
    public static $table = 'property_reviews';
    
    protected array $fillable = [
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
            ->select(['r.*', 'u.uname as user_name'])
            ->from(static::$table . ' as r')
            ->join('user as u', 'r.customer_id', '=', 'u.uid')
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
        return static::query()
            ->select([
                'COUNT(*) as total_reviews',
                'AVG(rating) as average_rating'
            ])
            ->where('property_id', $propertyId)
            ->where('status', 'approved')
            ->first();
    }

    /**
     * Get rating distribution for a property
     */
    public function getPropertyRatingDistribution($propertyId) {
        return static::query()
            ->select(['rating', 'COUNT(*) as count'])
            ->where('property_id', $propertyId)
            ->where('status', 'approved')
            ->groupBy('rating')
            ->get();
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
