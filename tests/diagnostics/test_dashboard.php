<?php
/**
 * Test script to verify the dashboard functionality
 */

// Start session
session_start();

// Use the simple config that's known to work
require_once 'config_simple.php';

echo "Testing dashboard functionality\n";

// Simulate a logged in user
$associate_id = 1; // The ID of our test user

echo "Getting data for associate ID: " . $associate_id . "\n";

// Get comprehensive associate data (same logic as in dashboard)
$stmt = $conn->prepare("SELECT * FROM mlm_agents WHERE id = ?");
$stmt->bind_param("i", $associate_id);
$stmt->execute();
$associate_data = $stmt->get_result()->fetch_assoc();

if ($associate_data) {
    echo "Associate data retrieved successfully:\n";
    echo "Name: " . $associate_data['full_name'] . "\n";
    echo "Level: " . $associate_data['current_level'] . "\n";
    echo "Status: " . $associate_data['status'] . "\n";
    echo "Referral Code: " . $associate_data['referral_code'] . "\n";
    
    // Get dashboard statistics (same logic as in dashboard)
    echo "\nGetting dashboard statistics...\n";
    
    // Total Business
    $business_query = "SELECT COALESCE(SUM(amount), 0) as total_business FROM bookings WHERE associate_id = ? AND status IN ('booked', 'completed')";
    $business_stmt = $conn->prepare($business_query);
    $business_stmt->bind_param("i", $associate_id);
    $business_stmt->execute();
    $total_business = $business_stmt->get_result()->fetch_assoc()['total_business'];
    echo "Total Business: ₹" . number_format($total_business) . "\n";
    
    // Total Commissions
    $commission_query = "SELECT COALESCE(SUM(commission_amount), 0) as total_commission FROM mlm_commissions WHERE associate_id = ? AND status = 'paid'";
    $commission_stmt = $conn->prepare($commission_query);
    $commission_stmt->bind_param("i", $associate_id);
    $commission_stmt->execute();
    $total_commission = $commission_stmt->get_result()->fetch_assoc()['total_commission'];
    echo "Total Commissions: ₹" . number_format($total_commission) . "\n";
    
    // Team Size
    $team_query = "SELECT COUNT(*) as team_size FROM mlm_agents WHERE sponsor_id = ? AND status = 'active'";
    $team_stmt = $conn->prepare($team_query);
    $team_stmt->bind_param("i", $associate_id);
    $team_stmt->execute();
    $team_size = $team_stmt->get_result()->fetch_assoc()['team_size'];
    echo "Direct Team Size: " . $team_size . "\n";
    
    echo "\nDashboard functionality test completed successfully!\n";
} else {
    echo "Error: Associate data not found!\n";
}
?>