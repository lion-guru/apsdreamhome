<?php

namespace App\Http\Controllers\Api;

use \Exception;

class BookingController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['except' => ['availability']]);
    }

    /**
     * Get available dates for a property
     */
    public function availability($propertyId)
    {
        try {
            if (!\is_numeric($propertyId)) {
                return $this->jsonError('Invalid property ID', 400);
            }

            $sql = "SELECT visit_date FROM visit_availability WHERE property_id = ? AND visit_date >= CURDATE()";
            $result = $this->db->fetchAll($sql, [$propertyId]);
            
            $availableDates = \array_column($result, 'visit_date');
            
            return $this->jsonSuccess($availableDates);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Book a property visit
     */
    public function book()
    {
        if ($this->request()->method() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();
            $propertyId = (int)($this->request()->input('property_id', 0));
            $visitDate = $this->request()->input('visit_date');
            $visitTime = $this->request()->input('visit_time');

            if (!$propertyId || empty($visitDate) || empty($visitTime)) {
                return $this->jsonError('Missing required fields', 400);
            }

            // Check availability
            $avail = $this->db->fetch(
                "SELECT id FROM visit_availability WHERE property_id = ? AND visit_date = ?",
                [$propertyId, $visitDate]
            );

            if (!$avail) {
                return $this->jsonError('Selected date is not available for visits', 400);
            }

            // Create booking
            $sql = "INSERT INTO bookings (customer_id, property_id, visit_date, visit_time, status, created_at) 
                    VALUES (?, ?, ?, ?, 'pending', NOW())";
            $this->db->execute($sql, [$user->id, $propertyId, $visitDate, $visitTime]);

            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }

            return $this->jsonSuccess(null, 'Visit booked successfully and is awaiting confirmation', 201);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get user bookings
     */
    public function myBookings()
    {
        try {
            $user = $this->auth->user();
            $sql = "SELECT b.*, p.title as property_title, p.location 
                    FROM bookings b
                    JOIN properties p ON b.property_id = p.id
                    WHERE b.customer_id = ?
                    ORDER BY b.visit_date DESC";
            $bookings = $this->db->fetchAll($sql, [$user->id]);

            return $this->jsonSuccess($bookings);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
