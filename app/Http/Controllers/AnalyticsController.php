<?php
namespace App\Http\Controllers;

class AnalyticsController extends Controller
{
    public function getRevenueAnalytics()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "total_revenue" => 1500000,
                "monthly_revenue" => 125000,
                "growth_rate" => 15.5,
                "revenue_by_property_type" => [
                    "apartments" => 600000,
                    "houses" => 500000,
                    "villas" => 400000
                ]
            ]
        ]);
    }
    
    public function getTrafficAnalytics()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "total_visitors" => 50000,
                "unique_visitors" => 35000,
                "page_views" => 150000,
                "bounce_rate" => 35.2,
                "avg_session_duration" => 245
            ]
        ]);
    }
    
    public function getConversionAnalytics()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "total_conversions" => 250,
                "conversion_rate" => 3.5,
                "conversions_by_source" => [
                    "organic" => 120,
                    "paid" => 80,
                    "social" => 30,
                    "referral" => 20
                ]
            ]
        ]);
    }
}
?>