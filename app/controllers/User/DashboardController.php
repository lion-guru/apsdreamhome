<?php

namespace App\Controllers\User;

use App\Http\Controllers\Controller;

class DashboardController extends Controller {

    public function index() {
        // Get user dashboard data
        $data = [
            'title' => 'Dashboard - APS Dream Home',
            'user' => $this->getCurrentUser(),
            'stats' => $this->getUserStats(),
            'recent_properties' => $this->getRecentProperties(),
            'favorites' => $this->getFavorites(),
            'bookings' => $this->getBookings()
        ];

        $this->view('user/dashboard', $data);
    }

    private function getCurrentUser() {
        // Get current user data
        return [
            'name' => $_SESSION['user_name'] ?? 'User',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'user'
        ];
    }

    private function getUserStats() {
        // Get user statistics
        return [
            'total_bookings' => 0,
            'total_favorites' => 0,
            'total_inquiries' => 0
        ];
    }

    private function getRecentProperties() {
        // Get recently viewed properties
        return [];
    }

    private function getFavorites() {
        // Get user's favorite properties
        return [];
    }

    private function getBookings() {
        // Get user's bookings
        return [];
    }
}
