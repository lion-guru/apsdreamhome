<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class BookingController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function list()
    {
        // List bookings
    }

    public function detail($id)
    {
        // Booking detail
    }

    public function create()
    {
        // Create booking request
    }

    public function update($id)
    {
        // Update booking
    }

    public function cancel($id)
    {
        // Cancel booking
    }

    public function recordPayment($id)
    {
        // Record payment
    }

    public function emiSchedule($id)
    {
        // EMI schedule
    }

    public function makePayment($id)
    {
        // Make EMI payment
    }

    public function siteVisits()
    {
        // List site visits
    }

    public function bookSiteVisit()
    {
        // Book site visit
    }

    public function startSiteVisit()
    {
        // Start site visit
    }

    public function updateSiteVisitLocation()
    {
        // Update location
    }

    public function completeSiteVisit()
    {
        // Complete site visit
    }

    public function getSiteVisitStatus($id)
    {
        // Get status
    }

    public function availableSlots($date)
    {
        // Available slots
    }

    public function rescheduleSiteVisit($id)
    {
        // Reschedule
    }

    public function cancelSiteVisit($id)
    {
        // Cancel
    }
}