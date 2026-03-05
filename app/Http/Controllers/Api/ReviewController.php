<?php

namespace App\Http\Controllers\Api;

use \Exception;

class ReviewController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['only' => ['store', 'delete']]);
        $this->middleware('csrf', ['only' => ['store', 'delete']]);
    }

    /**
     * Get reviews for a property or agent
     */
    public function index()
    {
        try {
            $targetType = $this->request()->input('type', 'property'); // property, agent
            $targetId = (int)$this->request()->input('id', 0);

            if (!$targetId) {
                return $this->jsonError('Target ID is required', 400);
            }

            $page = \max(1, (int)$this->request()->input('page', 1));
            $limit = \min(50, \max(1, (int)$this->request()->input('limit', 10)));
            $offset = ($page - 1) * $limit;

            if ($targetType === 'property') {
                $reviewModel = $this->model('PropertyReview');
                $reviews = $reviewModel->getPropertyReviews($targetId, $limit, $offset);
                $summary = $reviewModel->getPropertyReviewSummary($targetId);
                $distribution = $reviewModel->getPropertyRatingDistribution($targetId);
            } else {
                $reviewModel = $this->model('AgentReview');
                $reviews = $reviewModel->getAgentReviews($targetId, $limit, $offset);
                $summary = $reviewModel->getAgentReviewSummary($targetId);
                $distribution = $reviewModel->getAgentRatingDistribution($targetId);
            }

            $distFormatted = \array_fill(1, 5, 0);
            foreach ($distribution as $row) {
                $distFormatted[(int)\round($row['rating'])] = (int)$row['count'];
            }

            return $this->jsonSuccess([
                'reviews' => $reviews,
                'summary' => [
                    'total_reviews' => (int)($summary['total_reviews'] ?? 0),
                    'average_rating' => \round((float)($summary['average_rating'] ?? 0), 1),
                    'rating_distribution' => $distFormatted
                ],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_count' => (int)($summary['total_reviews'] ?? 0),
                    'total_pages' => \ceil(($summary['total_reviews'] ?? 0) / $limit)
                ]
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Submit a new review
     */
    public function store()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();

            $targetType = $this->request()->input('target_type', '');
            $targetId = (int)$this->request()->input('target_id', 0);
            $rating = (int)$this->request()->input('rating', 0);
            $reviewText = strip_tags(\trim($this->request()->input('review_text', '')));

            if (empty($targetType) || !$targetId || !$rating || empty($reviewText)) {
                return $this->jsonError('Missing required fields', 400);
            }

            if ($rating < 1 || $rating > 5) {
                return $this->jsonError('Rating must be between 1 and 5', 400);
            }

            if ($targetType === 'property') {
                $reviewModel = $this->model('PropertyReview');
                if ($reviewModel->hasReviewed($user->uid, $targetId)) {
                    return $this->jsonError('You have already reviewed this property', 400);
                }

                $review = new \App\Models\PropertyReview([
                    'customer_id' => $user->uid,
                    'property_id' => $targetId,
                    'rating' => $rating,
                    'review_text' => $reviewText,
                    'status' => 'pending'
                ]);
            } else {
                $reviewModel = $this->model('AgentReview');
                if ($reviewModel->hasReviewed($user->uid, $targetId)) {
                    return $this->jsonError('You have already reviewed this agent', 400);
                }

                $review = new \App\Models\AgentReview([
                    'user_id' => $user->uid,
                    'agent_id' => $targetId,
                    'rating' => $rating,
                    'review_text' => $reviewText
                ]);
            }

            if ($review->save()) {
                return $this->jsonSuccess(null, 'Review submitted successfully and is awaiting approval', 201);
            }

            return $this->jsonError('Failed to submit review', 500);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function addPropertyReview()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "review_id" => uniqid(),
                "property_id" => Security::sanitize($_POST["property_id"]) ?? 1,
                "rating" => Security::sanitize($_POST["rating"]) ?? 5,
                "comment" => Security::sanitize($_POST["comment"]) ?? "Great property!",
                "user_id" => 1,
                "created_at" => date("Y-m-d H:i:s")
            ]
        ]);
    }
}


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Http\Controllers\ReviewController.php

function getPropertyReviews($id)
    {
        return $this->json([
            "success" => true,
            "data" => [
                "property_id" => $id,
                "reviews" => [
                    [
                        "id" => 1,
                        "rating" => 5,
                        "comment" => "Excellent property!",
                        "user_name" => "John Doe",
                        "created_at" => "2026-03-01"
                    ],
                    [
                        "id" => 2,
                        "rating" => 4,
                        "comment" => "Good location, nice amenities",
                        "user_name" => "Jane Smith",
                        "created_at" => "2026-02-28"
                    ]
                ],
                "average_rating" => 4.5,
                "total_reviews" => 2
            ]
        ]);
    }