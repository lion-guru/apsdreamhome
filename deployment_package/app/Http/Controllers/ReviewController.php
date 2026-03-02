<?php
namespace App\Http\Controllers;

class ReviewController extends Controller
{
    public function addPropertyReview()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "review_id" => uniqid(),
                "property_id" => $_POST["property_id"] ?? 1,
                "rating" => $_POST["rating"] ?? 5,
                "comment" => $_POST["comment"] ?? "Great property!",
                "user_id" => 1,
                "created_at" => date("Y-m-d H:i:s")
            ]
        ]);
    }
    
    public function getPropertyReviews($id)
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
}
?>