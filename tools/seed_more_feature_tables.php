<?php
/**
 * seed_more_feature_tables.php — APS Dream Home
 * Seeds 80+ feature tables with realistic data.
 * For each table: checks existence, checks emptiness, inspects columns,
 * builds INSERT dynamically — only for columns that actually exist.
 * Skips if table already has data.
 *
 * Usage: php tools/seed_more_feature_tables.php
 */

$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function tableExists(PDO $pdo, string $name): bool {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$name]);
    return (bool) $stmt->fetch();
}

function tableRowCount(PDO $pdo, string $name): int {
    return (int) $pdo->query("SELECT COUNT(*) AS cnt FROM `$name`")->fetch()['cnt'];
}

function getColumns(PDO $pdo, string $name): array {
    $cols = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM `$name`");
    while ($row = $stmt->fetch()) {
        $cols[] = $row['Field'];
    }
    return $cols;
}

function insertDynamic(PDO $pdo, string $table, array $rows): int {
    if (empty($rows)) return 0;
    $cols = getColumns($pdo, $table);
    $inserted = 0;
    foreach ($rows as $row) {
        $filtered = array_intersect_key($row, array_flip($cols));
        if (empty($filtered)) continue;
        $names = array_keys($filtered);
        $placeholders = implode(',', array_fill(0, count($names), '?'));
        $sql = "INSERT IGNORE INTO `$table` (`" . implode('`,`', $names) . "`) VALUES ($placeholders)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($filtered));
            if ($stmt->rowCount() > 0) $inserted++;
        } catch (Exception $e) {
            echo "  SKIP $table: {$e->getMessage()}\n";
        }
    }
    return $inserted;
}

function seedOrSkip(PDO $pdo, string $label, string $table, array $rows): void {
    printf("  %-45s (%s): ", $label, $table);
    if (!tableExists($pdo, $table)) {
        echo "TABLE NOT FOUND\n";
        return;
    }
    $count = tableRowCount($pdo, $table);
    if ($count > 0) {
        echo "already has $count rows — SKIPPED\n";
        return;
    }
    $inserted = insertDynamic($pdo, $table, $rows);
    echo "seeded $inserted row(s)\n";
}

echo "=== APS Dream Home - Seed More Feature Tables ===\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

// ── Lookup reference IDs ──────────────────────────────────────────
try { $customerIds = $pdo->query("SELECT id FROM users WHERE role='customer' LIMIT 3")->fetchAll(PDO::FETCH_COLUMN); } catch (Exception $e) { $customerIds = []; }
$userId = $customerIds[0] ?? 0;
$userId2 = $customerIds[1] ?? 0;
$userId3 = $customerIds[2] ?? 0;

try { $empIds = $pdo->query("SELECT id FROM users WHERE role='employee' LIMIT 3")->fetchAll(PDO::FETCH_COLUMN); } catch (Exception $e) { $empIds = []; }
$empId = $empIds[0] ?? 0;
$empId2 = $empIds[1] ?? 0;

try { $assocIds = $pdo->query("SELECT id FROM users WHERE role='associate' LIMIT 3")->fetchAll(PDO::FETCH_COLUMN); } catch (Exception $e) { $assocIds = []; }
$assocId = $assocIds[0] ?? 0;

try { $adminIds = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 3")->fetchAll(PDO::FETCH_COLUMN); } catch (Exception $e) { $adminIds = []; }
$adminId = $adminIds[0] ?? 0;

try { $colonyId = $pdo->query("SELECT id FROM colonies LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $colonyId = 0; }
try { $plotId   = $pdo->query("SELECT id FROM plots LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $plotId = 0; }
try { $leadId   = $pdo->query("SELECT id FROM leads LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $leadId = 0; }
try { $bookingId = $pdo->query("SELECT id FROM bookings LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $bookingId = 0; }
try { $propId    = $pdo->query("SELECT id FROM properties LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $propId = 0; }
try { $upropId   = $pdo->query("SELECT id FROM user_properties LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $upropId = 0; }
try { $projectId = $pdo->query("SELECT id FROM projects LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $projectId = 0; }
try { $farmerId  = $pdo->query("SELECT id FROM farmers LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $farmerId = 0; }
try { $cityId    = $pdo->query("SELECT id FROM cities LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $cityId = 0; }
try { $districtId = $pdo->query("SELECT id FROM districts LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $districtId = 0; }
try { $stateId   = $pdo->query("SELECT id FROM states LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $stateId = 0; }
try { $deptId = $pdo->query("SELECT id FROM departments LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $deptId = 0; }
try { $designId = $pdo->query("SELECT id FROM designations LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $designId = 0; }
try { $camId = $pdo->query("SELECT campaign_id FROM campaigns LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $camId = 0; }
try { $farmerLandHoldingId = $pdo->query("SELECT id FROM farmer_land_holdings LIMIT 1")->fetchColumn() ?: 0; } catch (Exception $e) { $farmerLandHoldingId = 0; }

$now = date('Y-m-d H:i:s');
$today = date('Y-m-d');

//============================================================================
// 1. CUSTOMER / FEEDBACK
//============================================================================
echo "\n--- CUSTOMER/FEEDBACK ---\n";

seedOrSkip($pdo, 'Customer Inquiries', 'customer_inquiries', [
    [
        'customer_id' => $userId ?: 1,
        'inquiry_type' => 'property',
        'property_type' => 'plot',
        'property_id' => $propId ?: 1,
        'subject' => 'Want to know about payment plans',
        'message' => 'I am interested in purchasing a plot at Suryoday Heights. Please share the payment plan and EMI options.',
        'contact_name' => 'Amit Sharma',
        'contact_email' => 'amit.sharma@email.com',
        'contact_phone' => '9876543210',
        'status' => 'open',
        'priority' => 'medium',
        'created_at' => $now, 'updated_at' => $now,
    ],
    [
        'customer_id' => $userId2 ?: $userId ?: 1,
        'inquiry_type' => 'service',
        'property_type' => 'flat',
        'subject' => 'Home loan assistance required',
        'message' => 'I need assistance with home loan processing for a 2BHK flat.',
        'contact_name' => 'Priya Patel',
        'contact_email' => 'priya.patel@email.com',
        'contact_phone' => '9876543211',
        'status' => 'in_progress',
        'priority' => 'high',
        'created_at' => $now, 'updated_at' => $now,
    ],
    [
        'customer_id' => $userId3 ?: $userId ?: 1,
        'inquiry_type' => 'complaint',
        'subject' => 'Documentation delay',
        'message' => 'I have submitted all documents 2 weeks ago but have not received the sale deed yet.',
        'contact_name' => 'Rahul Verma',
        'contact_email' => 'rahul.verma@email.com',
        'contact_phone' => '9876543212',
        'status' => 'open',
        'priority' => 'high',
        'created_at' => $now, 'updated_at' => $now,
    ],
]);

seedOrSkip($pdo, 'Customer Summary', 'customer_summary', [
    [
        'customer_id' => $userId ?: 1,
        'customer_name' => 'Amit Sharma',
        'email' => 'amit.sharma@email.com',
        'mobile' => '9876543210',
        'customer_type' => 'individual',
        'kyc_status' => 'verified',
        'total_bookings' => 1,
        'total_investment' => 500000.00,
        'last_booking_date' => date('Y-m-d', strtotime('-30 days')),
        'days_since_last_booking' => 30,
        'customer_since' => date('Y-m-d', strtotime('-90 days')),
    ],
    [
        'customer_id' => $userId2 ?: $userId ?: 1,
        'customer_name' => 'Priya Patel',
        'email' => 'priya.patel@email.com',
        'mobile' => '9876543211',
        'customer_type' => 'individual',
        'kyc_status' => 'pending',
        'total_bookings' => 0,
        'total_investment' => 0.00,
        'customer_since' => date('Y-m-d', strtotime('-15 days')),
    ],
    [
        'customer_id' => $userId3 ?: $userId ?: 1,
        'customer_name' => 'Rahul Verma',
        'email' => 'rahul.verma@email.com',
        'mobile' => '9876543212',
        'customer_type' => 'nri',
        'kyc_status' => 'verified',
        'total_bookings' => 2,
        'total_investment' => 1200000.00,
        'last_booking_date' => date('Y-m-d', strtotime('-10 days')),
        'days_since_last_booking' => 10,
        'customer_since' => date('Y-m-d', strtotime('-180 days')),
    ],
]);

seedOrSkip($pdo, 'Customer Alerts', 'customer_alerts', [
    [
        'customer_id' => $userId ?: 1,
        'alert_type' => 'price_drop',
        'status' => 'active',
        'created_at' => $now, 'updated_at' => $now,
    ],
    [
        'customer_id' => $userId2 ?: $userId ?: 1,
        'property_type_id' => 1,
        'min_price' => 3000000,
        'max_price' => 5000000,
        'min_bedrooms' => 2,
        'max_bedrooms' => 3,
        'alert_type' => 'new_listing',
        'frequency' => 'daily',
        'status' => 'active',
        'created_at' => $now, 'updated_at' => $now,
    ],
    [
        'customer_id' => $userId3 ?: $userId ?: 1,
        'city' => 'Gorakhpur',
        'state' => 'Uttar Pradesh',
        'min_price' => 1500000,
        'max_price' => 2500000,
        'alert_type' => 'price_drop',
        'frequency' => 'weekly',
        'status' => 'active',
        'created_at' => $now, 'updated_at' => $now,
    ],
]);

seedOrSkip($pdo, 'Feedback Tickets', 'feedback_tickets', [
    ['user_id' => $userId ?: 1, 'message' => 'Excellent service at the Suryoday Heights site visit. The staff was very helpful.', 'status' => 'resolved', 'created_at' => $now],
    ['user_id' => $userId ?: 1, 'message' => 'The website could be more mobile-friendly. Forms are hard to fill on phone.', 'status' => 'open', 'created_at' => $now],
    ['user_id' => $empId ?: 1, 'message' => 'Need better integration between CRM and accounting modules.', 'status' => 'open', 'created_at' => $now],
]);

seedOrSkip($pdo, 'Deals', 'deals', [
    [
        'lead_id' => $leadId ?: 1,
        'deal_name' => 'Suryoday Heights Plot - 150 sq yd',
        'deal_value' => 2250000.00,
        'probability' => 75,
        'expected_close_date' => date('Y-m-d', strtotime('+30 days')),
        'status' => 'open',
        'created_by' => $empId ?: 1,
        'created_at' => $now, 'updated_at' => $now,
    ],
    [
        'lead_id' => max(1, $leadId ?: 1),
        'deal_name' => 'Braj Radha Enclave - 2BHK Flat',
        'deal_value' => 4500000.00,
        'probability' => 50,
        'expected_close_date' => date('Y-m-d', strtotime('+60 days')),
        'status' => 'negotiation',
        'created_by' => $empId ?: 1,
        'created_at' => $now, 'updated_at' => $now,
    ],
    [
        'lead_id' => max(1, $leadId + 1 ?: 1),
        'deal_name' => 'Commercial Shop - Raghunath City Center',
        'deal_value' => 8500000.00,
        'probability' => 90,
        'expected_close_date' => date('Y-m-d', strtotime('+15 days')),
        'status' => 'won',
        'created_by' => $empId2 ?: $empId ?: 1,
        'created_at' => $now, 'updated_at' => $now,
    ],
]);

seedOrSkip($pdo, 'Deal Activities', 'deal_activities', [
    ['deal_id' => 1, 'activity_type' => 'call', 'description' => 'Initial call with customer, discussed plot sizes and pricing', 'user_id' => $empId ?: 1, 'created_at' => $now],
    ['deal_id' => 1, 'activity_type' => 'meeting', 'description' => 'Site visit arranged and completed. Customer liked the location.', 'user_id' => $empId ?: 1, 'created_at' => $now],
    ['deal_id' => 2, 'activity_type' => 'email', 'description' => 'Sent brochure and payment plan via email.', 'user_id' => $empId ?: 1, 'created_at' => $now],
    ['deal_id' => 2, 'activity_type' => 'call', 'description' => 'Follow-up call. Customer comparing with other builders.', 'user_id' => $empId ?: 1, 'created_at' => $now],
    ['deal_id' => 3, 'activity_type' => 'meeting', 'description' => 'Final negotiation meeting. Deal closed successfully.', 'user_id' => $empId2 ?: $empId ?: 1, 'created_at' => $now],
]);

//============================================================================
// 2. PROPERTY
//============================================================================
echo "\n--- PROPERTY ---\n";

seedOrSkip($pdo, 'Property AI Tags', 'property_ai_tags', [
    ['property_id' => $propId ?: 1, 'tag' => 'garden-view', 'confidence' => 0.92, 'source' => 'vision_ai', 'created_at' => $now],
    ['property_id' => $propId ?: 1, 'tag' => 'corner-plot', 'confidence' => 0.88, 'source' => 'manual', 'created_at' => $now],
    ['property_id' => $upropId ?: 1, 'tag' => 'near-school', 'confidence' => 0.95, 'source' => 'location_ai', 'created_at' => $now],
]);

seedOrSkip($pdo, 'Property Analytics', 'property_analytics', [
    ['property_id' => $propId ?: 1, 'views' => 245, 'inquiries' => 12, 'favorites' => 18, 'shares' => 5, 'last_viewed' => $now],
    ['property_id' => $upropId ?: 1, 'views' => 189, 'inquiries' => 8, 'favorites' => 10, 'shares' => 3, 'last_viewed' => $now],
    ['property_id' => max(1, ($propId ?: 1) + 1), 'views' => 312, 'inquiries' => 15, 'favorites' => 22, 'shares' => 7, 'last_viewed' => $now],
]);

seedOrSkip($pdo, 'Property Development Costs', 'property_development_costs', [
    ['property_id' => $propId ?: 1, 'cost_type' => 'construction', 'description' => 'Foundation and structure work', 'amount' => 1500000.00, 'percentage_of_total' => 35.00, 'created_at' => $now],
    ['property_id' => $propId ?: 1, 'cost_type' => 'legal', 'description' => 'Registration and approval fees', 'amount' => 250000.00, 'percentage_of_total' => 5.83, 'created_at' => $now],
    ['property_id' => $propId ?: 1, 'cost_type' => 'amenities', 'description' => 'Park, roads and common facilities', 'amount' => 450000.00, 'percentage_of_total' => 10.50, 'created_at' => $now],
]);

seedOrSkip($pdo, 'Property Market Data', 'property_market_data', [
    [
        'location' => 'Gorakhpur', 'property_type' => 'plot',
        'data_date' => $today, 'avg_price_per_sqft' => 2500.00,
        'median_price' => 2400.00, 'price_trend_percentage' => 5.20,
        'days_on_market_avg' => 45, 'inventory_count' => 120,
        'sales_volume' => 18, 'rental_yield_avg' => 3.50,
        'market_sentiment' => 'positive', 'confidence_score' => 0.85,
        'data_source' => 'internal', 'created_at' => $now,
    ],
    [
        'location' => 'Lucknow', 'property_type' => 'flat',
        'data_date' => $today, 'avg_price_per_sqft' => 4500.00,
        'median_price' => 4300.00, 'price_trend_percentage' => 3.80,
        'days_on_market_avg' => 35, 'inventory_count' => 250,
        'sales_volume' => 42, 'rental_yield_avg' => 4.20,
        'market_sentiment' => 'stable', 'confidence_score' => 0.90,
        'data_source' => 'external', 'created_at' => $now,
    ],
    [
        'location' => 'Kushinagar', 'property_type' => 'plot',
        'data_date' => $today, 'avg_price_per_sqft' => 1200.00,
        'median_price' => 1100.00, 'price_trend_percentage' => 8.50,
        'days_on_market_avg' => 60, 'inventory_count' => 80,
        'sales_volume' => 10, 'rental_yield_avg' => 2.80,
        'market_sentiment' => 'positive', 'confidence_score' => 0.75,
        'data_source' => 'internal', 'created_at' => $now,
    ],
]);

seedOrSkip($pdo, 'Property Performance Metrics', 'property_performance_metrics', [
    ['property_id' => $propId ?: 1, 'date' => $today, 'total_views' => 245, 'unique_viewers' => 180, 'avg_view_duration' => 120, 'saves_count' => 18, 'inquiries_count' => 12, 'shares_count' => 5, 'conversion_rate' => 4.90, 'engagement_score' => 78.50, 'created_at' => $now],
    ['property_id' => $upropId ?: 1, 'date' => $today, 'total_views' => 189, 'unique_viewers' => 145, 'avg_view_duration' => 85, 'saves_count' => 10, 'inquiries_count' => 8, 'shares_count' => 3, 'conversion_rate' => 4.23, 'engagement_score' => 65.20, 'created_at' => $now],
    ['property_id' => max(1, ($propId ?: 1) + 1), 'date' => date('Y-m-d', strtotime('-1 day')), 'total_views' => 312, 'unique_viewers' => 230, 'avg_view_duration' => 150, 'saves_count' => 22, 'inquiries_count' => 15, 'shares_count' => 7, 'conversion_rate' => 4.81, 'engagement_score' => 82.10, 'created_at' => $now],
]);

seedOrSkip($pdo, 'Property Submissions', 'property_submissions', [
    ['submitter_id' => $userId ?: 1, 'submitter_type' => 'user', 'title' => '2BHK Flat in Gorakhpur', 'description' => 'Well-maintained 2BHK flat in a prime location with all amenities.', 'price' => 3500000.00, 'property_type' => 'flat', 'location' => 'Gorakhpur', 'status' => 'pending', 'created_at' => $now, 'updated_at' => $now],
    ['submitter_id' => $userId2 ?: $userId ?: 1, 'submitter_type' => 'user', 'title' => '150 sq yd Plot in Lucknow', 'description' => 'Corner plot suitable for residential construction.', 'price' => 3750000.00, 'property_type' => 'plot', 'location' => 'Lucknow', 'status' => 'approved', 'created_at' => $now, 'updated_at' => $now],
    ['submitter_id' => $assocId ?: 1, 'submitter_type' => 'associate', 'title' => 'Commercial Shop in City Center', 'description' => 'High-footfall commercial space in Raghunath City Center.', 'price' => 8500000.00, 'property_type' => 'commercial', 'location' => 'Gorakhpur', 'status' => 'approved', 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Property Comparisons', 'property_comparisons', [
    ['user_id' => $userId ?: 1, 'comparison_name' => 'Gorakhpur Plots', 'property_ids' => '[1,2,3]', 'comparison_type' => 'plot', 'created_at' => $now, 'updated_at' => $now],
    ['user_id' => $userId2 ?: $userId ?: 1, 'comparison_name' => '2BHK Flats under 40L', 'property_ids' => '[4,5]', 'comparison_type' => 'flat', 'created_at' => $now, 'updated_at' => $now],
    ['user_id' => $userId3 ?: $userId ?: 1, 'comparison_name' => 'Commercial Properties', 'property_ids' => '[6,7,8]', 'comparison_type' => 'commercial', 'shared' => 1, 'share_token' => 'share_' . bin2hex(random_bytes(8)), 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'User Property Preferences', 'user_property_preferences', [
    ['user_id' => $userId ?: 1, 'property_type' => 'plot', 'min_price' => 1000000, 'max_price' => 3000000, 'preferred_locations' => json_encode(['Gorakhpur']), 'min_area' => 100, 'max_area' => 300, 'created_at' => $now, 'updated_at' => $now],
    ['user_id' => $userId2 ?: $userId ?: 1, 'property_type' => 'flat', 'min_price' => 2500000, 'max_price' => 5000000, 'preferred_locations' => json_encode(['Lucknow']), 'bedrooms' => 2, 'bathrooms' => 2, 'created_at' => $now, 'updated_at' => $now],
    ['user_id' => $userId3 ?: $userId ?: 1, 'property_type' => 'plot', 'min_price' => 500000, 'max_price' => 1500000, 'preferred_locations' => json_encode(['Kushinagar']), 'budget_flexibility' => 10, 'created_at' => $now, 'updated_at' => $now],
]);

//============================================================================
// 3. FARMER
//============================================================================
echo "\n--- FARMER ---\n";

seedOrSkip($pdo, 'Farmer Activities', 'farmer_activities', [
    ['farmer_id' => $farmerId ?: 1, 'activity_type' => 'site_visit', 'title' => 'Land inspection for acquisition', 'description' => 'Inspected 5-acre plot for potential colony development.', 'performed_by' => $empId ?: 1, 'performed_at' => $now, 'follow_up_status' => 'completed', 'created_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'activity_type' => 'meeting', 'title' => 'Price negotiation meeting', 'description' => 'Discussed land price and payment terms with farmer cooperative.', 'performed_by' => $empId ?: 1, 'performed_at' => $now, 'follow_up_date' => date('Y-m-d', strtotime('+7 days')), 'follow_up_status' => 'pending', 'created_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'activity_type' => 'documentation', 'title' => 'Khasra document collection', 'description' => 'Collected land record documents for legal verification.', 'performed_by' => $empId2 ?: $empId ?: 1, 'performed_at' => $now, 'follow_up_status' => 'completed', 'outcome' => 'Documents verified successfully', 'created_at' => $now],
]);

seedOrSkip($pdo, 'Farmer Agreements', 'farmer_agreements', [
    ['farmer_id' => $farmerId ?: 1, 'agreement_number' => 'FARM-AGR-2026-001', 'agreement_type' => 'land_lease', 'start_date' => $today, 'end_date' => date('Y-m-d', strtotime('+5 years')), 'terms_conditions' => 'Annual lease with 5% yearly increment. Farmer responsible for land maintenance.', 'total_amount' => 2500000.00, 'advance_amount' => 500000.00, 'commission_rate' => 2.50, 'status' => 'active', 'signed_by_farmer' => 1, 'signed_by_company' => 1, 'signed_date' => $today, 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'agreement_number' => 'FARM-AGR-2026-002', 'agreement_type' => 'crop_share', 'start_date' => date('Y-m-d', strtotime('-1 month')), 'end_date' => date('Y-m-d', strtotime('+11 months')), 'terms_conditions' => 'Revenue sharing model: 60% farmer, 40% company.', 'total_amount' => 800000.00, 'advance_amount' => 200000.00, 'commission_rate' => 3.00, 'status' => 'active', 'signed_by_farmer' => 1, 'signed_by_company' => 1, 'signed_date' => date('Y-m-d', strtotime('-1 month')), 'created_by' => $empId2 ?: $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'agreement_number' => 'FARM-AGR-2025-003', 'agreement_type' => 'land_purchase', 'start_date' => date('Y-m-d', strtotime('-6 months')), 'end_date' => date('Y-m-d', strtotime('-1 month')), 'terms_conditions' => 'Outright purchase of 2-acre land for colony development.', 'total_amount' => 5000000.00, 'advance_amount' => 5000000.00, 'commission_rate' => 1.00, 'status' => 'completed', 'signed_by_farmer' => 1, 'signed_by_company' => 1, 'signed_date' => date('Y-m-d', strtotime('-6 months')), 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Farmer Loans', 'farmer_loans', [
    ['farmer_id' => $farmerId ?: 1, 'loan_number' => 'FARM-LN-2026-001', 'loan_amount' => 500000.00, 'interest_rate' => 9.50, 'loan_tenure' => 12, 'emi_amount' => 43800.00, 'purpose' => 'Crop development expenses', 'sanction_date' => date('Y-m-d', strtotime('-60 days')), 'disbursement_date' => date('Y-m-d', strtotime('-58 days')), 'maturity_date' => date('Y-m-d', strtotime('+304 days')), 'outstanding_amount' => 456200.00, 'status' => 'active', 'collateral_type' => 'land', 'collateral_value' => 1000000.00, 'guarantor_name' => 'Suresh Yadav', 'guarantor_phone' => '9876543201', 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'loan_number' => 'FARM-LN-2025-002', 'loan_amount' => 1000000.00, 'interest_rate' => 10.00, 'loan_tenure' => 24, 'emi_amount' => 46145.00, 'purpose' => 'Tractor and equipment purchase', 'sanction_date' => date('Y-m-d', strtotime('-180 days')), 'disbursement_date' => date('Y-m-d', strtotime('-178 days')), 'maturity_date' => date('Y-m-d', strtotime('+548 days')), 'outstanding_amount' => 850000.00, 'status' => 'active', 'collateral_type' => 'equipment', 'collateral_value' => 800000.00, 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'loan_number' => 'FARM-LN-2024-003', 'loan_amount' => 300000.00, 'interest_rate' => 8.50, 'loan_tenure' => 6, 'emi_amount' => 51250.00, 'purpose' => 'Short-term working capital', 'sanction_date' => date('Y-m-d', strtotime('-365 days')), 'disbursement_date' => date('Y-m-d', strtotime('-363 days')), 'maturity_date' => date('Y-m-d', strtotime('-183 days')), 'outstanding_amount' => 0.00, 'status' => 'closed', 'created_by' => $empId2 ?: $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Farmer Transactions', 'farmer_transactions', [
    ['farmer_id' => $farmerId ?: 1, 'transaction_type' => 'payment', 'amount' => 50000.00, 'payment_method' => 'bank_transfer', 'reference_number' => 'FT-2026-001', 'transaction_date' => $today, 'description' => 'Monthly land lease payment', 'status' => 'completed', 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'transaction_type' => 'advance', 'amount' => 200000.00, 'payment_method' => 'cheque', 'reference_number' => 'FT-2026-002', 'transaction_date' => date('Y-m-d', strtotime('-30 days')), 'description' => 'Advance payment against crop share agreement', 'status' => 'completed', 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'transaction_type' => 'withholding', 'amount' => 15000.00, 'payment_method' => 'adjustment', 'reference_number' => 'FT-2025-003', 'transaction_date' => date('Y-m-d', strtotime('-90 days')), 'description' => 'TDS deduction on lease payment', 'status' => 'completed', 'created_by' => $empId2 ?: $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Farmer Support Requests', 'farmer_support_requests', [
    ['farmer_id' => $farmerId ?: 1, 'request_number' => 'FSR-2026-001', 'request_type' => 'technical', 'priority' => 'high', 'subject' => 'Irrigation system repair needed', 'description' => 'The drip irrigation system installed last month has a leak near the main valve.', 'status' => 'open', 'assigned_to' => $empId ?: 1, 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'request_number' => 'FSR-2026-002', 'request_type' => 'financial', 'priority' => 'medium', 'subject' => 'Loan statement request', 'description' => 'Please provide the outstanding loan statement for my records.', 'status' => 'in_progress', 'assigned_to' => $empId2 ?: $empId ?: 1, 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'request_number' => 'FSR-2025-003', 'request_type' => 'legal', 'priority' => 'low', 'subject' => 'Land title verification update', 'description' => 'Need status update on the title verification for my 5-acre plot.', 'status' => 'resolved', 'assigned_to' => $empId ?: 1, 'resolution' => 'Title verified and registered at the sub-registrar office.', 'resolution_date' => date('Y-m-d', strtotime('-5 days')), 'satisfaction_rating' => 4, 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

//============================================================================
// 4. MARKET
//============================================================================
echo "\n--- MARKET ---\n";

seedOrSkip($pdo, 'Market Trends', 'market_trends', [
    ['city_id' => $cityId ?: 1, 'trend_date' => $today, 'avg_price' => 2500.00, 'median_price' => 2400.00, 'total_listings' => 120, 'total_sold' => 18, 'avg_days_on_market' => 45, 'price_change_percentage' => 5.20, 'created_at' => $now],
    ['city_id' => $cityId ?: 1, 'trend_date' => date('Y-m-d', strtotime('-30 days')), 'avg_price' => 2375.00, 'median_price' => 2300.00, 'total_listings' => 115, 'total_sold' => 15, 'avg_days_on_market' => 48, 'price_change_percentage' => 3.80, 'created_at' => $now],
    ['city_id' => $cityId ?: 1, 'trend_date' => date('Y-m-d', strtotime('-60 days')), 'avg_price' => 2280.00, 'median_price' => 2200.00, 'total_listings' => 108, 'total_sold' => 12, 'avg_days_on_market' => 52, 'price_change_percentage' => 2.50, 'created_at' => $now],
]);

seedOrSkip($pdo, 'Market Analytics Summary', 'market_analytics_summary', [
    ['summary_date' => $today, 'city_id' => $cityId ?: 1, 'total_properties' => 120, 'avg_price' => 2500.00, 'median_price' => 2400.00, 'total_views' => 5400, 'unique_viewers' => 1200, 'total_saves' => 340, 'total_inquiries' => 85, 'avg_days_on_market' => 45, 'market_health_score' => 78.50, 'price_trend' => 'up', 'demand_level' => 'high', 'created_at' => $now],
    ['summary_date' => date('Y-m-d', strtotime('-7 days')), 'city_id' => $cityId ?: 1, 'total_properties' => 118, 'avg_price' => 2475.00, 'median_price' => 2380.00, 'total_views' => 5100, 'unique_viewers' => 1150, 'total_saves' => 320, 'total_inquiries' => 78, 'avg_days_on_market' => 46, 'market_health_score' => 76.20, 'price_trend' => 'stable', 'demand_level' => 'high', 'created_at' => $now],
    ['summary_date' => date('Y-m-d', strtotime('-30 days')), 'city_id' => $cityId ?: 1, 'total_properties' => 115, 'avg_price' => 2375.00, 'median_price' => 2300.00, 'total_views' => 4800, 'unique_viewers' => 1080, 'total_saves' => 290, 'total_inquiries' => 70, 'avg_days_on_market' => 48, 'market_health_score' => 74.00, 'price_trend' => 'up', 'demand_level' => 'medium', 'created_at' => $now],
]);

seedOrSkip($pdo, 'Marketing Analytics', 'marketing_analytics', [
    ['campaign_id' => $camId ?: 1, 'lead_id' => $leadId ?: 1, 'action_type' => 'email_open', 'action_data' => json_encode(['campaign' => 'Summer Property Drive', 'email_id' => 'camp_001']), 'created_at' => $now],
    ['campaign_id' => $camId ?: 1, 'lead_id' => max(1, $leadId ?: 1), 'action_type' => 'link_click', 'action_data' => json_encode(['link' => 'Suryoday Heights brochure', 'url' => '/brochure/sh_2026.pdf']), 'created_at' => $now],
    ['campaign_id' => $camId ?: 1, 'action_type' => 'conversion', 'action_data' => json_encode(['type' => 'inquiry', 'source' => 'email']), 'created_at' => $now],
]);

seedOrSkip($pdo, 'Marketing Automations', 'marketing_automations', [
    ['name' => 'New Lead Welcome Email', 'trigger_type' => 'lead_created', 'trigger_conditions' => '{"status":"new"}', 'actions' => '{"send_email":1,"email_template":"welcome_lead","delay_hours":1}', 'is_active' => 1, 'created_at' => $now],
    ['name' => 'Inquiry Follow-up Reminder', 'trigger_type' => 'inquiry_created', 'trigger_conditions' => '{"priority":"high"}', 'actions' => '{"assign_agent":1,"send_notification":1,"create_task":1}', 'is_active' => 1, 'created_at' => $now],
    ['name' => 'Birthday Greeting', 'trigger_type' => 'customer_birthday', 'trigger_conditions' => '{}', 'actions' => '{"send_email":1,"send_whatsapp":1,"offer_discount":5}', 'is_active' => 0, 'created_at' => $now],
]);

seedOrSkip($pdo, 'Marketing Strategies', 'marketing_strategies', [
    ['title' => 'Summer Property Drive 2026', 'description' => 'Aggressive digital marketing campaign targeting NRI buyers with special discounts on premium plots. Focus on Facebook, Instagram and Google Ads.', 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
    ['title' => 'Referral Rewards Program', 'description' => 'Existing customers get 5% referral bonus for every successful referral. Quarterly lucky draw for top referrers.', 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
    ['title' => 'Local SEO Optimization', 'description' => 'Optimize website and Google Business Profile for local real estate searches. Target: "plots in Gorakhpur", "flats in Lucknow" keywords.', 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
]);

//============================================================================
// 5. COMMISSION (remaining)
//============================================================================
echo "\n--- COMMISSION ---\n";

seedOrSkip($pdo, 'Farmer Commissions', 'farmer_commissions', [
    ['farmer_id' => $farmerId ?: 1, 'commission_type' => 'land_acquisition', 'amount' => 50000.00, 'commission_percentage' => 2.00, 'reference_type' => 'agreement', 'reference_id' => 1, 'status' => 'paid', 'paid_at' => $now, 'remarks' => 'Commission on 5-acre land acquisition', 'created_at' => $now, 'updated_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'commission_type' => 'crop_share', 'amount' => 24000.00, 'commission_percentage' => 3.00, 'reference_type' => 'agreement', 'reference_id' => 2, 'status' => 'pending', 'remarks' => 'Q1 crop share commission', 'created_at' => $now, 'updated_at' => $now],
    ['farmer_id' => $farmerId ?: 1, 'commission_type' => 'referral', 'amount' => 10000.00, 'commission_percentage' => 1.00, 'reference_type' => 'farmer', 'reference_id' => 2, 'status' => 'approved', 'approved_by' => $adminId ?: 1, 'approved_at' => $now, 'remarks' => 'Referral commission for introducing new farmer', 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Farmer Commission Structures', 'farmer_commission_structures', [
    ['name' => 'Standard Land Acquisition', 'commission_type' => 'land_acquisition', 'min_amount' => 100000, 'max_amount' => 10000000, 'percentage' => 2.00, 'is_active' => 1, 'effective_from' => $today, 'description' => 'Standard 2% commission on land acquisition deals', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['name' => 'Premium Referral Bonus', 'commission_type' => 'referral', 'min_amount' => 50000, 'max_amount' => 500000, 'fixed_amount' => 10000.00, 'is_active' => 1, 'effective_from' => $today, 'description' => 'Fixed Rs. 10,000 referral bonus per new farmer onboarded', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['name' => 'Crop Share Commission', 'commission_type' => 'crop_share', 'min_amount' => 10000, 'max_amount' => 5000000, 'percentage' => 3.00, 'is_active' => 1, 'effective_from' => $today, 'effective_to' => date('Y-m-d', strtotime('+1 year')), 'description' => '3% commission on crop share revenue', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'MLM Advanced Analytics', 'mlm_advanced_analytics', [
    ['user_id' => $assocId ?: 1, 'mlm_level' => 1, 'commission_data' => json_encode(['direct' => 2, 'team' => 5, 'total_commission' => 45000]), 'performance_metrics' => json_encode(['rank' => 'silver', 'active_downline' => 3, 'monthly_volume' => 2500000]), 'created_at' => $now, 'updated_at' => $now],
    ['user_id' => $assocId ?: 1, 'mlm_level' => 2, 'commission_data' => json_encode(['direct' => 5, 'team' => 12, 'total_commission' => 125000]), 'performance_metrics' => json_encode(['rank' => 'gold', 'active_downline' => 8, 'monthly_volume' => 5800000]), 'created_at' => $now, 'updated_at' => $now],
    ['user_id' => $assocId ?: 1, 'mlm_level' => 3, 'commission_data' => json_encode(['direct' => 3, 'team' => 8, 'total_commission' => 78000]), 'performance_metrics' => json_encode(['rank' => 'silver', 'active_downline' => 5, 'monthly_volume' => 3200000]), 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'MLM Payout Batches', 'mlm_payout_batches', [
    ['batch_reference' => 'PAY-BATCH-2026-05-001', 'processed_by_user_id' => $adminId ?: 1, 'status' => 'completed', 'total_amount' => 125000.00, 'total_records' => 5, 'processed_at' => $now, 'created_at' => $now],
    ['batch_reference' => 'PAY-BATCH-2026-06-001', 'processed_by_user_id' => $adminId ?: 1, 'status' => 'processing', 'total_amount' => 89000.00, 'total_records' => 3, 'processed_at' => null, 'created_at' => $now],
]);

seedOrSkip($pdo, 'MLM Payout Batch Items', 'mlm_payout_batch_items', [
    ['batch_id' => 1, 'beneficiary_user_id' => $assocId ?: 1, 'amount' => 45000.00, 'status' => 'completed', 'notes' => 'May 2026 direct commission', 'created_at' => $now, 'updated_at' => $now],
    ['batch_id' => 1, 'beneficiary_user_id' => max(1, $assocId ?: 1), 'amount' => 35000.00, 'status' => 'completed', 'notes' => 'May 2026 team override', 'created_at' => $now, 'updated_at' => $now],
    ['batch_id' => 2, 'beneficiary_user_id' => $assocId ?: 1, 'amount' => 89000.00, 'status' => 'pending', 'notes' => 'June 2026 commission advance', 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'MLM Points Transactions', 'mlm_points_transactions', [
    ['user_id' => $assocId ?: 1, 'points' => 500, 'type' => 'earned', 'reference_type' => 'sale', 'reference_id' => 1, 'description' => 'Points earned for direct sale - Suryoday plot', 'created_at' => $now],
    ['user_id' => $assocId ?: 1, 'points' => 200, 'type' => 'earned', 'reference_type' => 'team_performance', 'description' => 'Team performance bonus points', 'created_at' => $now],
    ['user_id' => $assocId ?: 1, 'points' => 150, 'type' => 'redeemed', 'reference_type' => 'reward', 'reference_id' => 1, 'description' => 'Points redeemed for gift voucher', 'created_at' => $now],
]);

seedOrSkip($pdo, 'MLM Rank Upgrades', 'mlm_rank_upgrades', [
    ['associate_id' => $assocId ?: 1, 'old_rank' => 'bronze', 'new_rank' => 'silver', 'upgrade_date' => date('Y-m-d', strtotime('-90 days'))],
    ['associate_id' => $assocId ?: 1, 'old_rank' => 'silver', 'new_rank' => 'gold', 'upgrade_date' => date('Y-m-d', strtotime('-15 days'))],
    ['associate_id' => max(1, $assocId ?: 1), 'old_rank' => 'bronze', 'new_rank' => 'silver', 'upgrade_date' => date('Y-m-d', strtotime('-30 days'))],
]);

seedOrSkip($pdo, 'MLM Notification Log', 'mlm_notification_log', [
    ['user_id' => $assocId ?: 1, 'channel' => 'email', 'type' => 'rank_upgrade', 'subject' => 'Congratulations! You are now Gold', 'message' => 'You have been upgraded to Gold rank. Enjoy enhanced commission rates!', 'status' => 'sent', 'sent_at' => $now, 'created_at' => $now],
    ['user_id' => $assocId ?: 1, 'channel' => 'sms', 'type' => 'commission_credit', 'subject' => 'Commission credited', 'message' => 'Rs. 45,000 commission has been credited to your account.', 'status' => 'sent', 'sent_at' => $now, 'created_at' => $now],
    ['user_id' => $assocId ?: 1, 'channel' => 'in_app', 'type' => 'payout_available', 'subject' => 'Payout ready', 'message' => 'Your commission payout of Rs. 89,000 is ready for withdrawal.', 'status' => 'delivered', 'sent_at' => $now, 'created_at' => $now],
]);

seedOrSkip($pdo, 'MLM Import Audit', 'mlm_import_audit', [
    ['batch_reference' => 'IMP-2026-05-001', 'user_id' => $assocId ?: 1, 'sponsor_user_id' => $adminId ?: 1, 'referral_code' => 'APS-REF-001', 'status' => 'success', 'message' => 'Associate imported successfully', 'processed_at' => $now, 'created_at' => $now],
    ['batch_reference' => 'IMP-2026-05-001', 'status' => 'failed', 'message' => 'Duplicate referral code detected', 'payload' => '{"name":"Test User","email":"test@test.com"}', 'processed_at' => $now, 'created_at' => $now],
    ['batch_reference' => 'IMP-2026-05-002', 'user_id' => max(2, $assocId ?: 1), 'sponsor_user_id' => $assocId ?: 1, 'referral_code' => 'APS-REF-005', 'status' => 'success', 'message' => 'Bulk import row 5 of 50', 'processed_at' => $now, 'created_at' => $now],
]);

//============================================================================
// 6. INVENTORY
//============================================================================
echo "\n--- INVENTORY ---\n";

seedOrSkip($pdo, 'Inventory Log', 'inventory_log', [
    ['plot_id' => $plotId ?: 1, 'action' => 'created', 'user_id' => $adminId ?: 1, 'note' => 'Plot added to inventory with dimensions 30x50', 'action_date' => $today, 'created_at' => $now],
    ['plot_id' => $plotId ?: 1, 'action' => 'status_change', 'user_id' => $adminId ?: 1, 'note' => 'Plot status changed from available to reserved', 'action_date' => $today, 'created_at' => $now],
    ['plot_id' => $plotId ?: 1, 'action' => 'price_update', 'user_id' => $adminId ?: 1, 'note' => 'Price updated from Rs. 2,500/sqft to Rs. 2,750/sqft', 'action_date' => $today, 'created_at' => $now],
]);

seedOrSkip($pdo, 'Budgets', 'budgets', [
    ['budget_name' => 'Q3 2026 Marketing Budget', 'period_type' => 'quarterly', 'start_date' => '2026-07-01', 'end_date' => '2026-09-30', 'total_budget' => 500000.00, 'is_active' => 1, 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['budget_name' => 'Colony Development FY 2026-27', 'period_type' => 'yearly', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31', 'total_budget' => 5000000.00, 'is_active' => 1, 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['budget_name' => 'Employee Training Q2', 'period_type' => 'quarterly', 'start_date' => '2026-04-01', 'end_date' => '2026-06-30', 'total_budget' => 150000.00, 'is_active' => 0, 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Budget Expenses', 'budget_expenses', [
    ['budget_id' => 1, 'expense_type' => 'advertising', 'expense_name' => 'Facebook Ads Campaign', 'amount' => 50000.00, 'expense_date' => $today, 'vendor_name' => 'Meta Platforms', 'payment_mode' => 'online', 'notes' => 'Summer property drive campaign', 'created_by' => $adminId ?: 1, 'created_at' => $now],
    ['budget_id' => 1, 'expense_type' => 'printing', 'expense_name' => 'Brochure Printing', 'amount' => 15000.00, 'expense_date' => $today, 'vendor_name' => 'Print House Ltd', 'payment_mode' => 'cheque', 'notes' => '5000 brochures for Suryoday Heights', 'created_by' => $adminId ?: 1, 'created_at' => $now],
    ['budget_id' => 2, 'expense_type' => 'infrastructure', 'expense_name' => 'Road Construction - Phase 2', 'amount' => 500000.00, 'expense_date' => $today, 'vendor_name' => 'InfraBuild Solutions', 'payment_mode' => 'bank_transfer', 'reference_number' => 'PO-2026-045', 'notes' => 'Internal roads and drainage', 'created_by' => $adminId ?: 1, 'created_at' => $now],
]);

//============================================================================
// 7. FINANCE
//============================================================================
echo "\n--- FINANCE ---\n";

seedOrSkip($pdo, 'Cash Flow Projections', 'cash_flow_projections', [
    ['projection_date' => $today, 'projected_inflow' => 2500000.00, 'projected_outflow' => 1800000.00, 'net_cash_flow' => 700000.00, 'cumulative_balance' => 4500000.00, 'notes' => 'Expected inflows from plot bookings and outflows for construction', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['projection_date' => date('Y-m-d', strtotime('+7 days')), 'projected_inflow' => 1800000.00, 'projected_outflow' => 2200000.00, 'net_cash_flow' => -400000.00, 'cumulative_balance' => 4100000.00, 'notes' => 'Large construction payment due next week', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['projection_date' => date('Y-m-d', strtotime('+30 days')), 'projected_inflow' => 3500000.00, 'projected_outflow' => 1500000.00, 'net_cash_flow' => 2000000.00, 'cumulative_balance' => 6500000.00, 'notes' => 'Expected new bookings after summer campaign', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Financial Transactions', 'financial_transactions', [
    ['type' => 'income', 'category' => 'plot_booking', 'amount' => 500000.00, 'description' => 'Booking amount received for Plot 12 - Suryoday Heights', 'reference_id' => '1', 'reference_type' => 'booking', 'status' => 'completed', 'transaction_date' => $today, 'created_at' => $now],
    ['type' => 'expense', 'category' => 'construction', 'amount' => 250000.00, 'description' => 'Payment to contractor for foundation work Phase 2', 'reference_id' => 'PO-2026-050', 'reference_type' => 'purchase_order', 'status' => 'completed', 'transaction_date' => $today, 'created_at' => $now],
    ['type' => 'transfer', 'category' => 'internal', 'amount' => 100000.00, 'description' => 'Funds transferred to marketing budget account', 'status' => 'completed', 'transaction_date' => $today, 'created_at' => $now],
]);

seedOrSkip($pdo, 'Income Records', 'income_records', [
    ['income_number' => 'INC-2026-001', 'income_date' => $today, 'income_category' => 'plot_sale', 'income_subcategory' => 'booking_amount', 'amount' => 500000.00, 'description' => 'Booking amount - Plot 12, Suryoday Heights', 'customer_name' => 'Amit Sharma', 'payment_method' => 'bank_transfer', 'status' => 'received', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['income_number' => 'INC-2026-002', 'income_date' => date('Y-m-d', strtotime('-7 days')), 'income_category' => 'registration', 'income_subcategory' => 'stamp_duty', 'amount' => 125000.00, 'description' => 'Registration fee collected from buyer', 'customer_name' => 'Priya Patel', 'payment_method' => 'cheque', 'status' => 'received', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['income_number' => 'INC-2026-003', 'income_date' => $today, 'income_category' => 'commission', 'income_subcategory' => 'brokerage', 'amount' => 25000.00, 'description' => 'Brokerage income for referring Apna Ghar project', 'customer_name' => 'Verma Properties', 'payment_method' => 'bank_transfer', 'status' => 'pending', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Journal Entries', 'journal_entries', [
    ['journal_number' => 'JE-2026-001', 'entry_date' => $today, 'description' => 'Plot booking payment received', 'total_debit' => 500000.00, 'total_credit' => 500000.00, 'entry_type' => 'receipt', 'status' => 'posted', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['journal_number' => 'JE-2026-002', 'entry_date' => $today, 'description' => 'Construction material purchase', 'total_debit' => 150000.00, 'total_credit' => 150000.00, 'entry_type' => 'payment', 'status' => 'posted', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['journal_number' => 'JE-2026-003', 'entry_date' => $today, 'description' => 'Monthly salary accrual', 'total_debit' => 450000.00, 'total_credit' => 450000.00, 'entry_type' => 'accrual', 'status' => 'draft', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Recurring Transactions', 'recurring_transactions', [
    ['transaction_name' => 'Office Rent', 'transaction_type' => 'expense', 'amount' => 85000.00, 'frequency' => 'monthly', 'start_date' => $today, 'next_due_date' => date('Y-m-d', strtotime('+1 month')), 'category' => 'rent', 'description' => 'Monthly office rent for Gorakhpur branch', 'auto_create' => 1, 'status' => 'active', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['transaction_name' => 'Staff Salary', 'transaction_type' => 'expense', 'amount' => 350000.00, 'frequency' => 'monthly', 'start_date' => $today, 'next_due_date' => date('Y-m-d', strtotime('+25 days')), 'category' => 'salary', 'description' => 'Monthly staff salary disbursement', 'auto_create' => 1, 'status' => 'active', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['transaction_name' => 'Maintenance Income', 'transaction_type' => 'income', 'amount' => 25000.00, 'frequency' => 'monthly', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31', 'next_due_date' => date('Y-m-d', strtotime('+15 days')), 'category' => 'maintenance', 'description' => 'Monthly maintenance fee from colony residents', 'auto_create' => 1, 'status' => 'active', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Budget Planning', 'budget_planning', [
    ['budget_name' => 'Annual Marketing Plan 2026-27', 'budget_year' => '2026-27', 'budget_type' => 'marketing', 'budgeted_amount' => 2000000.00, 'actual_amount' => 450000.00, 'variance_amount' => -1550000.00, 'variance_percentage' => -77.50, 'period_start' => '2026-04-01', 'period_end' => '2027-03-31', 'status' => 'active', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['budget_name' => 'Infrastructure Q1', 'budget_year' => '2026-27', 'budget_type' => 'infrastructure', 'budgeted_amount' => 1500000.00, 'actual_amount' => 980000.00, 'variance_amount' => -520000.00, 'variance_percentage' => -34.67, 'period_start' => '2026-04-01', 'period_end' => '2026-06-30', 'status' => 'active', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['budget_name' => 'Technology Upgrade', 'budget_year' => '2026-27', 'budget_type' => 'it', 'budgeted_amount' => 500000.00, 'actual_amount' => 0.00, 'variance_amount' => -500000.00, 'variance_percentage' => -100.00, 'period_start' => '2026-04-01', 'period_end' => '2027-03-31', 'notes' => 'Pending approval from management', 'status' => 'draft', 'created_by' => $empId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

//============================================================================
// 8. PROJECT
//============================================================================
echo "\n--- PROJECT ---\n";

seedOrSkip($pdo, 'Company Projects', 'company_projects', [
    ['project_name' => 'Suryoday Heights Extension', 'description' => 'Phase 2 expansion of Suryoday Heights with 50 additional plots and modern amenities. Includes 30-ft wide roads, underground drainage, and a community center.', 'location' => 'Gorakhpur, UP', 'project_type' => 'colony_development', 'status' => 'in_progress', 'start_date' => '2026-01-15', 'end_date' => '2027-06-30', 'budget' => 25000000.00, 'created_at' => $now, 'updated_at' => $now],
    ['project_name' => 'APS Dream Heights - Lucknow', 'description' => 'Premium residential complex with 100 flats (2BHK and 3BHK) in Lucknow. Features include swimming pool, gym, and landscaped gardens.', 'location' => 'Lucknow, UP', 'project_type' => 'residential', 'status' => 'planning', 'start_date' => '2026-09-01', 'end_date' => '2028-12-31', 'budget' => 75000000.00, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Construction Projects', 'construction_projects', [
    ['project_name' => 'Community Center - Suryoday', 'site_id' => $colonyId ?: 5, 'project_type' => 'community', 'start_date' => '2026-03-01', 'estimated_completion' => '2026-08-30', 'budget_allocated' => 3500000.00, 'amount_spent' => 1850000.00, 'progress_percentage' => 52.00, 'status' => 'in_progress', 'description' => 'Community center with hall, library and indoor games room', 'contract_amount' => 3200000.00, 'advance_paid' => 1600000.00, 'quality_rating' => 4.20, 'created_at' => $now, 'updated_at' => $now, 'last_updated' => $now],
    ['project_name' => 'Internal Roads - Phase 2', 'site_id' => $colonyId ?: 5, 'project_type' => 'infrastructure', 'start_date' => '2026-05-01', 'estimated_completion' => '2026-07-15', 'budget_allocated' => 1800000.00, 'amount_spent' => 450000.00, 'progress_percentage' => 25.00, 'status' => 'in_progress', 'description' => '1.5 km internal roads with drainage system', 'contract_amount' => 1650000.00, 'advance_paid' => 500000.00, 'quality_rating' => 4.50, 'created_at' => $now, 'updated_at' => $now, 'last_updated' => $now],
]);

seedOrSkip($pdo, 'Land Projects', 'land_projects', [
    ['name' => 'Green Valley Township', 'location' => 'Kushinagar, UP', 'description' => 'Integrated township project on 25 acres with residential and commercial zones.', 'total_area' => 25.00, 'project_type' => 'township', 'developer_name' => 'APS Dream Home Pvt Ltd', 'rera_number' => 'UP-RERA-2026-045', 'status' => 'planning', 'start_date' => '2026-08-01', 'completion_date' => '2030-12-31', 'estimated_cost' => 120000000.00, 'created_at' => $now, 'updated_at' => $now],
    ['name' => 'Riverside Residency', 'location' => 'Varanasi, UP', 'description' => 'Premium river-view plots with clubhouse and gated community.', 'total_area' => 15.00, 'project_type' => 'plotting', 'developer_name' => 'APS Dream Home Pvt Ltd', 'approval_number' => 'GDA-2026-112', 'rera_number' => 'UP-RERA-2026-089', 'status' => 'approved', 'start_date' => '2026-10-01', 'completion_date' => '2028-06-30', 'estimated_cost' => 45000000.00, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Project Gallery', 'project_gallery', [
    ['project_id' => $projectId ?: 3, 'image_path' => '/assets/images/gallery/project_a_1.jpg', 'caption' => 'Master plan overview', 'drive_file_id' => 'drive_abc123'],
    ['project_id' => $projectId ?: 3, 'image_path' => '/assets/images/gallery/project_a_2.jpg', 'caption' => 'Site layout with plot markings', 'drive_file_id' => 'drive_def456'],
    ['project_id' => $projectId ?: 3, 'image_path' => '/assets/images/gallery/project_a_3.jpg', 'caption' => 'Amenities location map', 'drive_file_id' => 'drive_ghi789'],
]);

//============================================================================
// 9. SALES
//============================================================================
echo "\n--- SALES ---\n";

seedOrSkip($pdo, 'Sales', 'sales', [
    ['sale_number' => 'SALE-2026-001', 'property_id' => $propId ?: 1, 'customer_id' => $userId ?: 1, 'associate_id' => $assocId ?: 1, 'booking_id' => $bookingId ?: 1, 'sale_amount' => 2250000.00, 'commission_amount' => 112500.00, 'commission_percentage' => 5.00, 'sale_date' => $today, 'status' => 'completed', 'notes' => 'Plot 12 - Suryoday Heights', 'created_at' => $now, 'updated_at' => $now],
    ['sale_number' => 'SALE-2026-002', 'property_id' => $upropId ?: 1, 'customer_id' => $userId2 ?: $userId ?: 1, 'associate_id' => $assocId ?: 1, 'sale_amount' => 3500000.00, 'commission_amount' => 175000.00, 'commission_percentage' => 5.00, 'sale_date' => $today, 'status' => 'pending', 'notes' => '2BHK Flat - Payment pending', 'created_at' => $now, 'updated_at' => $now],
    ['sale_number' => 'SALE-2026-003', 'property_id' => max(1, ($propId ?: 1) + 1), 'customer_id' => $userId3 ?: $userId ?: 1, 'sale_amount' => 5000000.00, 'commission_amount' => 250000.00, 'commission_percentage' => 5.00, 'sale_date' => date('Y-m-d', strtotime('-15 days')), 'status' => 'completed', 'notes' => 'Commercial shop - Full payment received', 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Sales Funnel', 'sales_funnel', [
    ['date' => $today, 'stage' => 'awareness', 'count' => 120, 'value' => 45000000.00, 'source' => 'website', 'created_at' => $now],
    ['date' => $today, 'stage' => 'interest', 'count' => 65, 'value' => 28000000.00, 'source' => 'all', 'created_at' => $now],
    ['date' => $today, 'stage' => 'consideration', 'count' => 28, 'value' => 14000000.00, 'source' => 'all', 'created_at' => $now],
    ['date' => $today, 'stage' => 'evaluation', 'count' => 12, 'value' => 6500000.00, 'source' => 'all', 'created_at' => $now],
    ['date' => $today, 'stage' => 'purchase', 'count' => 3, 'value' => 2250000.00, 'source' => 'all', 'created_at' => $now],
]);

seedOrSkip($pdo, 'Sales Invoices', 'sales_invoices', [
    ['invoice_number' => 'SINV-2026-001', 'customer_id' => $userId ?: 1, 'invoice_date' => $today, 'due_date' => date('Y-m-d', strtotime('+15 days')), 'subtotal' => 2250000.00, 'tax_amount' => 0.00, 'total_amount' => 2250000.00, 'paid_amount' => 500000.00, 'balance_amount' => 1750000.00, 'payment_terms' => '50% booking, 50% on possession', 'status' => 'partial', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['invoice_number' => 'SINV-2026-002', 'customer_id' => $userId2 ?: $userId ?: 1, 'invoice_date' => $today, 'due_date' => date('Y-m-d', strtotime('+7 days')), 'subtotal' => 3500000.00, 'tax_amount' => 0.00, 'total_amount' => 3500000.00, 'paid_amount' => 0.00, 'balance_amount' => 3500000.00, 'notes' => 'Full payment due at registration', 'status' => 'unpaid', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['invoice_number' => 'SINV-2026-003', 'customer_id' => $userId3 ?: $userId ?: 1, 'invoice_date' => date('Y-m-d', strtotime('-15 days')), 'due_date' => date('Y-m-d', strtotime('-1 day')), 'subtotal' => 5000000.00, 'tax_amount' => 0.00, 'total_amount' => 5000000.00, 'paid_amount' => 5000000.00, 'balance_amount' => 0.00, 'status' => 'paid', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Pipeline Analytics', 'pipeline_analytics', [
    ['date' => $today, 'leads_in_stage' => 45, 'leads_entered' => 8, 'leads_exited' => 5, 'avg_time_in_stage' => 7.50, 'conversion_rate' => 11.11, 'stage_velocity' => 3.20, 'revenue_generated' => 2250000.00, 'deals_closed' => 3, 'created_at' => $now],
    ['date' => date('Y-m-d', strtotime('-7 days')), 'leads_in_stage' => 42, 'leads_entered' => 10, 'leads_exited' => 6, 'avg_time_in_stage' => 8.20, 'conversion_rate' => 9.52, 'stage_velocity' => 2.80, 'revenue_generated' => 1500000.00, 'deals_closed' => 2, 'created_at' => $now],
    ['date' => date('Y-m-d', strtotime('-30 days')), 'leads_in_stage' => 38, 'leads_entered' => 12, 'leads_exited' => 4, 'avg_time_in_stage' => 9.00, 'conversion_rate' => 7.89, 'stage_velocity' => 2.50, 'revenue_generated' => 500000.00, 'deals_closed' => 1, 'created_at' => $now],
]);

//============================================================================
// 10. PLOTS
//============================================================================
echo "\n--- PLOTS ---\n";

seedOrSkip($pdo, 'Plot Development', 'plot_development', [
    ['land_purchase_id' => $farmerLandHoldingId ?: 1, 'plot_number' => 'PD-001', 'plot_size' => 150.00, 'plot_type' => 'residential', 'development_cost' => 150000.00, 'selling_price' => 375000.00, 'status' => 'ready_to_sell', 'amenities' => json_encode(['Park facing', 'Corner plot']), 'plot_facing' => 'east', 'road_width' => 30, 'created_at' => $now, 'updated_at' => $now],
    ['land_purchase_id' => $farmerLandHoldingId ?: 1, 'plot_number' => 'PD-002', 'plot_size' => 200.00, 'plot_type' => 'residential', 'development_cost' => 200000.00, 'selling_price' => 500000.00, 'status' => 'sold', 'customer_id' => $userId ?: 1, 'sold_date' => date('Y-m-d', strtotime('-30 days')), 'sold_price' => 500000.00, 'profit_loss' => 300000.00, 'amenities' => json_encode(['Near park']), 'plot_facing' => 'north', 'road_width' => 40, 'created_at' => $now, 'updated_at' => $now],
    ['land_purchase_id' => $farmerLandHoldingId ?: 1, 'plot_number' => 'PD-003', 'plot_size' => 120.00, 'plot_type' => 'commercial', 'development_cost' => 180000.00, 'selling_price' => 600000.00, 'status' => 'ready_to_sell', 'amenities' => json_encode(['Main road facing']), 'plot_facing' => 'west', 'road_width' => 50, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Plot Development Costs', 'plot_development_costs', [
    ['colony_id' => $colonyId ?: 5, 'cost_type' => 'land_filling', 'description' => 'Land leveling and filling for plots 1-20', 'amount' => 250000.00, 'per_sqft_rate' => 12.50, 'total_area_sqft' => 20000, 'vendor_name' => 'EarthMovers Pvt Ltd', 'invoice_number' => 'INV-EM-001', 'invoice_date' => $today, 'payment_status' => 'paid', 'paid_amount' => 250000.00, 'created_at' => $now, 'updated_at' => $now],
    ['colony_id' => $colonyId ?: 5, 'cost_type' => 'road_construction', 'description' => 'Internal road network Phase 1', 'amount' => 850000.00, 'per_sqft_rate' => 42.50, 'vendor_name' => 'InfraBuild Solutions', 'invoice_number' => 'INV-IB-002', 'invoice_date' => $today, 'payment_status' => 'partial', 'paid_amount' => 400000.00, 'created_at' => $now, 'updated_at' => $now],
    ['colony_id' => $colonyId ?: 5, 'cost_type' => 'boundary_wall', 'description' => 'Perimeter boundary wall construction', 'amount' => 350000.00, 'per_sqft_rate' => 350.00, 'vendor_name' => 'SecureBuild', 'payment_status' => 'unpaid', 'paid_amount' => 0.00, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Plot Rate Calculations', 'plot_rate_calculations', [
    ['property_id' => $propId ?: 1, 'land_cost' => 1500000.00, 'development_cost' => 500000.00, 'total_commission' => 112500.00, 'profit_margin' => 15.00, 'final_rate_per_sqft' => 2875.00, 'calculated_by' => $adminId ?: 1, 'calculation_date' => $today],
    ['property_id' => $upropId ?: 1, 'land_cost' => 2000000.00, 'development_cost' => 750000.00, 'total_commission' => 175000.00, 'profit_margin' => 12.00, 'final_rate_per_sqft' => 3250.00, 'calculated_by' => $adminId ?: 1, 'calculation_date' => $today],
    ['property_id' => max(1, ($propId ?: 1) + 1), 'land_cost' => 3000000.00, 'development_cost' => 1000000.00, 'total_commission' => 250000.00, 'profit_margin' => 18.00, 'final_rate_per_sqft' => 4500.00, 'calculated_by' => $adminId ?: 1, 'calculation_date' => $today],
]);

seedOrSkip($pdo, 'Plot EMI Schedule', 'plot_emi_schedule', [
    ['booking_id' => $bookingId ?: 2, 'installment_number' => 1, 'amount' => 500000.00, 'due_date' => '2026-05-15', 'payment_date' => '2026-05-14', 'status' => 'paid', 'notes' => 'Booking amount', 'created_at' => $now, 'updated_at' => $now],
    ['booking_id' => $bookingId ?: 2, 'installment_number' => 2, 'amount' => 300000.00, 'due_date' => '2026-08-15', 'status' => 'pending', 'notes' => 'First installment', 'created_at' => $now, 'updated_at' => $now],
    ['booking_id' => $bookingId ?: 2, 'installment_number' => 3, 'amount' => 300000.00, 'due_date' => '2026-11-15', 'status' => 'pending', 'notes' => 'Second installment', 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Plot Images', 'plot_images', [
    ['plot_id' => $plotId ?: 11, 'image_path' => '/assets/images/plots/plot_11_1.jpg', 'image_type' => 'overview', 'caption' => 'Plot view from east side', 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now],
    ['plot_id' => $plotId ?: 11, 'image_path' => '/assets/images/plots/plot_11_2.jpg', 'image_type' => 'location', 'caption' => 'Road access view', 'sort_order' => 2, 'is_active' => 1, 'created_at' => $now],
    ['plot_id' => $plotId ?: 11, 'image_path' => '/assets/images/plots/plot_11_3.jpg', 'image_type' => 'survey', 'caption' => 'Survey marking', 'sort_order' => 3, 'is_active' => 1, 'created_at' => $now],
]);

seedOrSkip($pdo, 'Plot Cuttings', 'plot_cuttings', [
    ['project_id' => $projectId ?: 3, 'cutting_date' => '2026-04-15', 'total_plots_created' => 20, 'total_area_plotted' => 3500.00, 'layout_plan' => '/assets/layouts/suryoday_phase2.pdf', 'cutting_status' => 'completed', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['project_id' => $projectId ?: 3, 'cutting_date' => '2026-05-01', 'total_plots_created' => 15, 'total_area_plotted' => 2800.00, 'cutting_status' => 'completed', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['project_id' => $projectId ?: 3, 'cutting_date' => $today, 'total_plots_created' => 0, 'total_area_plotted' => 0.00, 'cutting_status' => 'planned', 'layout_plan' => '/assets/layouts/extension_draft.pdf', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

//============================================================================
// 11. LOANS
//============================================================================
echo "\n--- LOANS ---\n";

seedOrSkip($pdo, 'Loans', 'loans', [
    ['loan_id' => 1, 'customer_id' => $userId ?: 1, 'loan_amount' => 2000000.00, 'interest_rate' => 8.50, 'loan_tenure_months' => 240, 'emi_amount' => 17350.00, 'loan_type' => 'home_loan', 'status' => 'active', 'disbursement_date' => date('Y-m-d', strtotime('-60 days')), 'created_at' => $now],
    ['loan_id' => 2, 'customer_id' => $userId2 ?: $userId ?: 1, 'loan_amount' => 3500000.00, 'interest_rate' => 9.00, 'loan_tenure_months' => 240, 'emi_amount' => 31488.00, 'loan_type' => 'home_loan', 'status' => 'approved', 'created_at' => $now],
    ['loan_id' => 3, 'customer_id' => $userId3 ?: $userId ?: 1, 'loan_amount' => 1000000.00, 'interest_rate' => 10.50, 'loan_tenure_months' => 60, 'emi_amount' => 21490.00, 'loan_type' => 'personal_loan', 'status' => 'active', 'disbursement_date' => date('Y-m-d', strtotime('-90 days')), 'created_at' => $now],
]);

seedOrSkip($pdo, 'Loan EMI Schedule', 'loan_emi_schedule', [
    ['loan_id' => 1, 'emi_number' => 1, 'due_date' => date('Y-m-d', strtotime('-30 days')), 'emi_amount' => 17350.00, 'principal_amount' => 3200.00, 'interest_amount' => 14150.00, 'outstanding_balance' => 1966800.00, 'paid_date' => date('Y-m-d', strtotime('-28 days')), 'paid_amount' => 17350.00, 'status' => 'paid', 'created_at' => $now],
    ['loan_id' => 1, 'emi_number' => 2, 'due_date' => $today, 'emi_amount' => 17350.00, 'principal_amount' => 3250.00, 'interest_amount' => 14100.00, 'outstanding_balance' => 1943550.00, 'status' => 'pending', 'created_at' => $now],
    ['loan_id' => 3, 'emi_number' => 1, 'due_date' => date('Y-m-d', strtotime('-60 days')), 'emi_amount' => 21490.00, 'principal_amount' => 12740.00, 'interest_amount' => 8750.00, 'outstanding_balance' => 987260.00, 'paid_date' => date('Y-m-d', strtotime('-58 days')), 'paid_amount' => 21490.00, 'status' => 'paid', 'created_at' => $now],
]);

seedOrSkip($pdo, 'Mortgage Inquiries', 'mortgage_inquiries', [
    ['name' => 'Amit Sharma', 'email' => 'amit.sharma@email.com', 'phone' => '9876543210', 'property_value' => 3500000.00, 'down_payment' => 1000000.00, 'loan_amount' => 2500000.00, 'loan_tenure' => 20, 'employment_type' => 'salaried', 'monthly_income' => 85000.00, 'existing_loans' => 0, 'property_location' => 'Gorakhpur', 'urgency_level' => 'medium', 'status' => 'new', 'created_at' => $now, 'updated_at' => $now],
    ['name' => 'Priya Patel', 'email' => 'priya.patel@email.com', 'phone' => '9876543211', 'property_value' => 5000000.00, 'down_payment' => 1500000.00, 'loan_amount' => 3500000.00, 'loan_tenure' => 20, 'employment_type' => 'self_employed', 'monthly_income' => 120000.00, 'existing_loans' => 1, 'property_location' => 'Lucknow', 'urgency_level' => 'high', 'status' => 'in_progress', 'created_at' => $now, 'updated_at' => $now],
    ['name' => 'Rahul Verma', 'email' => 'rahul.verma@email.com', 'phone' => '9876543212', 'property_value' => 2500000.00, 'down_payment' => 500000.00, 'loan_amount' => 2000000.00, 'loan_tenure' => 15, 'employment_type' => 'salaried', 'monthly_income' => 65000.00, 'existing_loans' => 0, 'property_location' => 'Kushinagar', 'urgency_level' => 'low', 'additional_info' => 'Pre-approved by SBI', 'status' => 'qualified', 'created_at' => $now, 'updated_at' => $now],
]);

//============================================================================
// 12. LAND
//============================================================================
echo "\n--- LAND ---\n";

seedOrSkip($pdo, 'Land Acquisitions', 'land_acquisitions', [
    ['acquisition_number' => 'LA-2026-001', 'farmer_id' => $farmerId ?: 1, 'land_area' => 5.00, 'land_area_unit' => 'acre', 'location' => 'Village Sonbarsa, Gorakhpur', 'village' => 'Sonbarsa', 'tehsil' => 'Sadar', 'district' => 'Gorakhpur', 'state' => 'Uttar Pradesh', 'acquisition_date' => '2026-02-15', 'acquisition_cost' => 5000000.00, 'payment_status' => 'completed', 'land_type' => 'agricultural', 'soil_type' => 'alluvial', 'water_source' => 'borewell', 'road_access' => 'yes', 'status' => 'completed', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['acquisition_number' => 'LA-2026-002', 'farmer_id' => $farmerId ?: 1, 'land_area' => 3.00, 'land_area_unit' => 'acre', 'location' => 'Village Pipra, Kushinagar', 'village' => 'Pipra', 'tehsil' => 'Padrauna', 'district' => 'Kushinagar', 'state' => 'Uttar Pradesh', 'acquisition_date' => $today, 'acquisition_cost' => 3000000.00, 'payment_status' => 'partial', 'land_type' => 'agricultural', 'soil_type' => 'loamy', 'water_source' => 'canal', 'road_access' => 'yes', 'status' => 'in_progress', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['acquisition_number' => 'LA-2025-003', 'farmer_id' => $farmerId ?: 1, 'land_area' => 2.50, 'land_area_unit' => 'acre', 'location' => 'Village Belwa, Gorakhpur', 'village' => 'Belwa', 'tehsil' => 'Chauri Chaura', 'district' => 'Gorakhpur', 'state' => 'Uttar Pradesh', 'acquisition_date' => '2025-11-20', 'acquisition_cost' => 2500000.00, 'payment_status' => 'completed', 'land_type' => 'residential', 'soil_type' => 'clay', 'water_source' => 'municipal', 'road_access' => 'yes', 'status' => 'completed', 'created_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Land Management Activities', 'land_management_activities', [
    ['activity_type' => 'survey', 'description' => 'Boundary survey for 5-acre acquisition at Sonbarsa', 'user_id' => $empId ?: 1, 'status' => 'completed', 'created_at' => $now],
    ['activity_type' => 'legal_check', 'description' => 'Title verification and encumbrance check for village Pipra land', 'user_id' => $empId2 ?: $empId ?: 1, 'status' => 'in_progress', 'created_at' => $now],
    ['activity_type' => 'valuation', 'description' => 'Market valuation assessment for proposed acquisition in Belwa', 'user_id' => $empId ?: 1, 'status' => 'pending', 'created_at' => $now],
]);

seedOrSkip($pdo, 'Land Purchases', 'land_purchases', [
    ['farmer_id' => $farmerId ?: 1, 'purchase_date' => '2026-02-15', 'amount' => 5000000.00, 'registry_no' => 'REG-2026-4582', 'agreement_doc' => '/uploads/documents/land/agreement_la001.pdf'],
    ['farmer_id' => $farmerId ?: 1, 'purchase_date' => '2025-11-20', 'amount' => 2500000.00, 'registry_no' => 'REG-2025-12893', 'agreement_doc' => '/uploads/documents/land/agreement_la003.pdf'],
    ['farmer_id' => $farmerId ?: 1, 'purchase_date' => $today, 'amount' => 3000000.00, 'registry_no' => 'Pending', 'agreement_doc' => '/uploads/documents/land/agreement_la002_draft.pdf'],
]);

seedOrSkip($pdo, 'Land Records', 'land_records', [
    ['land_title' => 'Khasra No. 125', 'location' => 'Sonbarsa, Gorakhpur', 'area' => 5.00, 'owner_name' => 'Ram Vilas Yadav', 'created_at' => $now],
    ['land_title' => 'Khasra No. 231', 'location' => 'Pipra, Kushinagar', 'area' => 3.00, 'owner_name' => 'Sushila Devi', 'created_at' => $now],
    ['land_title' => 'Khasra No. 87', 'location' => 'Belwa, Gorakhpur', 'area' => 2.50, 'owner_name' => 'Harish Chandra', 'created_at' => $now],
]);

//============================================================================
// 13. HR
//============================================================================
echo "\n--- HR ---\n";

seedOrSkip($pdo, 'HR Reminders', 'hr_reminders', [
    ['title' => 'Performance review due for Sales Team', 'description' => 'Quarterly performance review for all sales associates. Deadline for submission.', 'reminder_type' => 'review', 'due_date' => date('Y-m-d', strtotime('+7 days')), 'assigned_to' => $empId ?: 1, 'status' => 'pending', 'created_at' => $now],
    ['title' => 'Employee birthday - Rajesh Kumar', 'description' => 'Send birthday wishes and arrange celebration.', 'reminder_type' => 'birthday', 'due_date' => date('Y-m-d', strtotime('+14 days')), 'assigned_to' => $adminId ?: 1, 'status' => 'pending', 'created_at' => $now],
    ['title' => 'Policy update communication', 'description' => 'Communicate updated leave policy to all employees.', 'reminder_type' => 'task', 'due_date' => date('Y-m-d', strtotime('+3 days')), 'assigned_to' => $empId2 ?: $empId ?: 1, 'status' => 'completed', 'created_at' => $now],
]);

seedOrSkip($pdo, 'Performance Goals', 'performance_goals', [
    ['employee_id' => $empId ?: 1, 'title' => 'Close 5 property deals this quarter', 'description' => 'Achieve sales target of Rs. 1 crore in Q2 2026', 'category' => 'sales', 'priority' => 'high', 'target_date' => '2026-06-30', 'status' => 'in_progress', 'progress_percentage' => 40, 'assigned_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['employee_id' => $empId ?: 1, 'title' => 'Complete CRM training module', 'description' => 'Finish the advanced CRM training and get certified', 'category' => 'learning', 'priority' => 'medium', 'target_date' => '2026-05-30', 'status' => 'in_progress', 'progress_percentage' => 70, 'assigned_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
    ['employee_id' => $empId2 ?: $empId ?: 1, 'title' => 'Reduce customer response time', 'description' => 'Achieve average response time under 2 hours for all inquiries', 'category' => 'service', 'priority' => 'high', 'target_date' => '2026-07-31', 'status' => 'not_started', 'progress_percentage' => 0, 'assigned_by' => $adminId ?: 1, 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Performance Reviews', 'performance_reviews', [
    ['employee_id' => $empId ?: 1, 'reviewer_id' => $adminId ?: 1, 'review_period_start' => '2026-01-01', 'review_period_end' => '2026-03-31', 'review_type' => 'quarterly', 'overall_rating' => 4.20, 'performance_level' => 'exceeds_expectations', 'goals_achievement' => 'Achieved 120% of sales target for Q1 2026. Closed 6 deals worth Rs. 1.2 crore.', 'strengths' => 'Excellent negotiation skills, strong customer relationships, proactive approach.', 'areas_for_improvement' => 'Could improve documentation timeliness and CRM data entry.', 'reviewer_comments' => 'Outstanding performance this quarter. Recommended for fast-track promotion.', 'status' => 'completed', 'review_date' => $today, 'next_review_date' => '2026-07-15', 'created_at' => $now, 'updated_at' => $now],
    ['employee_id' => $empId2 ?: $empId ?: 1, 'reviewer_id' => $adminId ?: 1, 'review_period_start' => '2026-01-01', 'review_period_end' => '2026-03-31', 'review_type' => 'quarterly', 'overall_rating' => 3.50, 'performance_level' => 'meets_expectations', 'goals_achievement' => 'Achieved 90% of targets. Good consistency but room for improvement in lead conversion.', 'strengths' => 'Reliable team player, good technical knowledge, punctual.', 'areas_for_improvement' => 'Lead conversion rate needs improvement. Follow-up process could be more systematic.', 'status' => 'completed', 'review_date' => $today, 'next_review_date' => '2026-07-15', 'created_at' => $now, 'updated_at' => $now],
    ['employee_id' => $empId ?: 1, 'reviewer_id' => $adminId ?: 1, 'review_period_start' => '2026-04-01', 'review_period_end' => '2026-06-30', 'review_type' => 'quarterly', 'overall_rating' => 4.50, 'performance_level' => 'exceeds_expectations', 'goals_achievement' => 'On track to exceed Q2 targets. Already closed 3 deals worth Rs. 75 lakhs.', 'strengths' => 'Strong pipeline management, excellent client feedback.', 'status' => 'in_progress', 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Performance Feedback', 'performance_feedback', [
    ['review_id' => 1, 'feedback_type' => 'manager_to_employee', 'feedback_by' => $adminId ?: 1, 'feedback_for' => $empId ?: 1, 'rating_overall' => 4, 'rating_communication' => 5, 'rating_technical_skills' => 4, 'rating_leadership' => 4, 'rating_teamwork' => 5, 'rating_quality' => 4, 'positive_feedback' => 'Excellent communication with clients, always follows up promptly.', 'areas_improvement' => 'Documentation details could be improved.', 'recommendations' => 'Consider advanced sales training program.', 'created_at' => $now],
    ['review_id' => 1, 'feedback_type' => 'self', 'feedback_by' => $empId ?: 1, 'feedback_for' => $empId ?: 1, 'rating_overall' => 4, 'rating_communication' => 4, 'rating_technical_skills' => 4, 'rating_leadership' => 3, 'rating_teamwork' => 5, 'rating_quality' => 4, 'positive_feedback' => 'I believe I have performed well this quarter and met all targets.', 'areas_improvement' => 'Looking to improve leadership skills through training.', 'is_anonymous' => 0, 'created_at' => $now],
    ['review_id' => 2, 'feedback_type' => 'manager_to_employee', 'feedback_by' => $adminId ?: 1, 'feedback_for' => $empId2 ?: $empId ?: 1, 'rating_overall' => 3, 'rating_communication' => 4, 'rating_technical_skills' => 4, 'rating_leadership' => 3, 'rating_teamwork' => 4, 'rating_quality' => 3, 'positive_feedback' => 'Consistent performer with good technical knowledge.', 'areas_improvement' => 'Needs to work on lead conversion and closing skills.', 'recommendations' => 'Sales training workshop recommended.', 'created_at' => $now],
]);

//============================================================================
// 14. SYSTEM
//============================================================================
echo "\n--- SYSTEM ---\n";

seedOrSkip($pdo, 'System Alerts', 'system_alerts', [
    ['level' => 'info', 'title' => 'Scheduled maintenance', 'message' => 'System maintenance scheduled for Sunday 2:00 AM - 4:00 AM. Expect brief downtime.', 'system' => 'all', 'created_at' => $now],
    ['level' => 'warning', 'title' => 'Database backup size increasing', 'message' => 'Backup size has exceeded 500MB. Consider archiving old data.', 'system' => 'database', 'created_at' => $now],
    ['level' => 'critical', 'title' => 'SSL certificate expiring', 'message' => 'SSL certificate for api.apsdreamhome.com expires in 7 days. Renew immediately.', 'system' => 'web', 'acknowledged_at' => $now, 'acknowledged_by' => $adminId, 'created_at' => $now],
]);

seedOrSkip($pdo, 'System Health', 'system_health', [
    ['component' => 'database', 'status' => 'healthy', 'response_time_ms' => 25, 'last_check' => $now],
]);

seedOrSkip($pdo, 'System Backups', 'system_backups', [
    ['backup_type' => 'full', 'file_path' => '/backups/apsdreamhome_full_2026-05-28.sql.gz', 'file_size' => 524288000, 'checksum' => 'sha256:a1b2c3d4e5f6a7b8c9d0', 'tables_backed' => '767', 'started_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 'completed_at' => date('Y-m-d H:i:s', strtotime('-1 day +30 minutes')), 'status' => 'completed', 'created_by' => $adminId ?: 1],
    ['backup_type' => 'incremental', 'file_path' => '/backups/apsdreamhome_inc_2026-05-29.sql.gz', 'file_size' => 12582912, 'checksum' => 'sha256:b2c3d4e5f6a7b8c9d0e1', 'tables_backed' => '50', 'started_at' => $now, 'completed_at' => $now, 'status' => 'completed', 'created_by' => $adminId ?: 1],
    ['backup_type' => 'full', 'file_path' => '/backups/apsdreamhome_full_2026-05-22.sql.gz', 'file_size' => 488636416, 'checksum' => 'sha256:c3d4e5f6a7b8c9d0e1f2', 'tables_backed' => '765', 'started_at' => date('Y-m-d H:i:s', strtotime('-7 days')), 'completed_at' => date('Y-m-d H:i:s', strtotime('-7 days +28 minutes')), 'status' => 'completed', 'created_by' => $adminId ?: 1],
]);

seedOrSkip($pdo, 'System Metrics', 'system_metrics', [
    ['timestamp' => $now, 'memory_current' => 262144000, 'memory_peak' => 314572800, 'cpu_load_1' => 0.45, 'cpu_load_5' => 0.82, 'cpu_load_15' => 0.65, 'disk_total' => 536870912000, 'disk_used' => 214748364800, 'disk_free' => 322122547200, 'server_uptime' => '15 days 6 hours 23 minutes', 'db_connections' => 8, 'active_sessions' => 12, 'created_at' => $now],
    ['timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')), 'memory_current' => 243302400, 'memory_peak' => 293601280, 'cpu_load_1' => 0.52, 'cpu_load_5' => 0.78, 'cpu_load_15' => 0.62, 'disk_total' => 536870912000, 'disk_used' => 214746728000, 'disk_free' => 322124184000, 'server_uptime' => '15 days 5 hours', 'db_connections' => 6, 'active_sessions' => 9, 'created_at' => $now],
    ['timestamp' => date('Y-m-d H:i:s', strtotime('-1 day')), 'memory_current' => 239075328, 'memory_peak' => 288568115, 'cpu_load_1' => 0.38, 'cpu_load_5' => 0.55, 'cpu_load_15' => 0.48, 'disk_total' => 536870912000, 'disk_used' => 214739520000, 'disk_free' => 322131392000, 'server_uptime' => '14 days 5 hours', 'db_connections' => 5, 'active_sessions' => 7, 'created_at' => $now],
]);

//============================================================================
// 15. OTHER
//============================================================================
echo "\n--- OTHER ---\n";

seedOrSkip($pdo, 'Site Visits', 'site_visits', [
    ['plot_id' => $plotId ?: 11, 'site_id' => $colonyId ?: 5, 'user_id' => $userId ?: 1, 'visitor_name' => 'Amit Sharma', 'visitor_phone' => '9876543210', 'visit_date' => $today, 'visit_time' => '10:30:00', 'status' => 'completed', 'notes' => 'Customer interested in corner plot. Showed available options 12, 15, 18.', 'created_at' => $now],
    ['plot_id' => $plotId ?: 11, 'site_id' => $colonyId ?: 5, 'user_id' => $userId2 ?: $userId ?: 1, 'visitor_name' => 'Priya Patel', 'visitor_phone' => '9876543211', 'visit_date' => $today, 'visit_time' => '15:00:00', 'status' => 'completed', 'notes' => 'Couple looking for 2BHK flat. Showed them the under-construction building.', 'created_at' => $now],
    ['visitor_name' => 'Rahul Verma', 'visitor_phone' => '9876543212', 'visit_date' => date('Y-m-d', strtotime('+2 days')), 'visit_time' => '11:00:00', 'status' => 'scheduled', 'notes' => 'NRI customer wants video tour of the colony.', 'created_at' => $now],
]);

seedOrSkip($pdo, 'Site Visit Requests', 'site_visit_requests', [
    ['user_id' => $userId ?: 1, 'user_name' => 'Amit Sharma', 'user_email' => 'amit.sharma@email.com', 'user_phone' => '9876543210', 'property_id' => $propId ?: 1, 'preferred_date' => date('Y-m-d', strtotime('+1 day')), 'preferred_time' => '10:00:00', 'notes' => 'Prefer morning slot on weekend', 'status' => 'approved', 'created_at' => $now, 'updated_at' => $now],
    ['user_id' => $userId2 ?: $userId ?: 1, 'user_name' => 'Priya Patel', 'user_email' => 'priya.patel@email.com', 'user_phone' => '9876543211', 'property_id' => $upropId ?: 1, 'preferred_date' => date('Y-m-d', strtotime('+3 days')), 'notes' => 'Will bring family for the visit', 'status' => 'pending', 'created_at' => $now, 'updated_at' => $now],
    ['user_name' => 'Neha Gupta (Walk-in)', 'user_email' => 'neha.gupta@email.com', 'user_phone' => '9876543220', 'preferred_date' => $today, 'preferred_time' => '16:00:00', 'status' => 'completed', 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Follow Ups', 'follow_ups', [
    ['lead_id' => $leadId ?: 1, 'user_id' => $empId ?: 1, 'follow_up_type' => 'call', 'notes' => 'Call customer to check if they received the brochure', 'scheduled_at' => $today, 'status' => 'pending', 'created_at' => $now],
    ['lead_id' => $leadId ?: 1, 'user_id' => $empId ?: 1, 'follow_up_type' => 'email', 'notes' => 'Send payment plan comparison for 2BHK vs 3BHK', 'scheduled_at' => date('Y-m-d', strtotime('+1 day')), 'status' => 'pending', 'created_at' => $now],
    ['lead_id' => max(1, $leadId ?: 1), 'user_id' => $empId2 ?: $empId ?: 1, 'follow_up_type' => 'site_visit', 'notes' => 'Arrange site visit for Saturday morning', 'scheduled_at' => $today, 'completed_at' => $now, 'status' => 'completed', 'created_at' => $now],
]);

seedOrSkip($pdo, 'Conversations', 'conversations', [
    ['conversation_type' => 'inquiry', 'title' => 'Property inquiry - Suryoday Heights', 'description' => 'Customer inquiry regarding plot availability and pricing', 'created_by' => $userId ?: 1, 'created_by_type' => 'user', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
    ['conversation_type' => 'support', 'title' => 'Documentation support ticket', 'description' => 'Assistance with sale deed registration documents', 'created_by' => $empId ?: 1, 'created_by_type' => 'employee', 'is_active' => 1, 'last_message_at' => $now, 'last_message_preview' => 'Please upload the scanned copy of your Aadhar card', 'created_at' => $now, 'updated_at' => $now],
    ['conversation_type' => 'internal', 'title' => 'Team discussion - Marketing strategy', 'description' => 'Internal discussion about Q3 marketing campaign', 'created_by' => $adminId ?: 1, 'created_by_type' => 'admin', 'is_active' => 1, 'last_message_at' => $now, 'last_message_preview' => 'I suggest we focus on Instagram Reels this quarter', 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Messages', 'messages', [
    ['conversation_id' => 1, 'sender_id' => $userId ?: 1, 'sender_type' => 'user', 'message_type' => 'text', 'content' => 'Hi, I am interested in purchasing a plot at Suryoday Heights. Can you share the current availability?', 'sent_at' => $now],
    ['conversation_id' => 1, 'sender_id' => $empId ?: 1, 'sender_type' => 'employee', 'message_type' => 'text', 'content' => 'Hello! Thank you for your interest. We have plots available in sizes from 120 to 300 sq yd. Would you like to visit the site?', 'sent_at' => $now],
    ['conversation_id' => 2, 'sender_id' => $userId ?: 1, 'sender_type' => 'user', 'message_type' => 'text', 'content' => 'I have submitted all my documents last week. When will the deed be ready?', 'sent_at' => $now],
]);

seedOrSkip($pdo, 'Chat Messages', 'chat_messages', [
    ['sender_email' => 'amit.sharma@email.com', 'message' => 'Hello! I am interested in your property listings in Gorakhpur.', 'created_at' => $now],
    ['sender_email' => 'support@apsdreamhome.com', 'message' => 'Welcome! We have excellent options in Suryoday Heights starting from Rs. 15 Lakhs.', 'created_at' => $now],
    ['sender_email' => 'priya.patel@email.com', 'message' => 'Do you have any 2BHK flats available near Lucknow?', 'created_at' => $now],
]);

// notifications already has 52 rows — skip check is built in
seedOrSkip($pdo, 'Favorites', 'favorites', [
    ['user_id' => $userId ?: 1, 'property_id' => $propId ?: 1, 'created_at' => $now],
    ['user_id' => $userId2 ?: $userId ?: 1, 'property_id' => $upropId ?: 1, 'created_at' => $now],
    ['user_id' => $userId3 ?: $userId ?: 1, 'property_id' => max(1, ($propId ?: 1) + 1), 'created_at' => $now],
]);

seedOrSkip($pdo, 'Saved Searches', 'saved_searches', [
    ['user_id' => $userId ?: 1, 'name' => 'Plots in Gorakhpur under 30L', 'search_params' => json_encode(['type' => 'plot', 'location' => 'Gorakhpur', 'max_price' => 3000000, 'min_area' => 100]), 'created_at' => $now, 'updated_at' => $now],
    ['user_id' => $userId2 ?: $userId ?: 1, 'name' => '2BHK Flats Lucknow', 'search_params' => json_encode(['type' => 'flat', 'location' => 'Lucknow', 'bedrooms' => 2, 'max_price' => 5000000]), 'created_at' => $now, 'updated_at' => $now],
    ['user_id' => $userId3 ?: $userId ?: 1, 'name' => 'Commercial properties', 'search_params' => json_encode(['type' => 'commercial', 'location' => 'Gorakhpur']), 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Saved Properties', 'saved_properties', [
    ['user_id' => $userId ?: 1, 'property_id' => $propId ?: 1, 'saved_at' => $now, 'notes' => 'Like this plot, need to discuss payment plan'],
    ['user_id' => $userId2 ?: $userId ?: 1, 'property_id' => $upropId ?: 1, 'saved_at' => $now],
    ['user_id' => $userId3 ?: $userId ?: 1, 'property_id' => max(1, ($propId ?: 1) + 1), 'saved_at' => $now, 'notes' => 'Compare with similar properties'],
]);

seedOrSkip($pdo, 'Integration Logs', 'integration_logs', [
    ['action' => 'sync_properties', 'direction' => 'outbound', 'data' => '{"property_ids":[1,2,3],"target":"website"}', 'status' => 'success', 'created_at' => $now, 'processed_at' => $now],
    ['action' => 'fetch_pincodes', 'direction' => 'inbound', 'data' => '{"api":"pincode_api","batch_size":500}', 'status' => 'success', 'created_at' => $now, 'processed_at' => $now],
    ['action' => 'send_newsletter', 'direction' => 'outbound', 'data' => '{"subscribers":120,"campaign":"summer_2026"}', 'status' => 'failed', 'error_message' => 'SMTP connection timeout', 'created_at' => $now, 'processed_at' => null],
]);

seedOrSkip($pdo, 'Task Executions', 'task_executions', [
    ['task_id' => 1, 'execution_status' => 'completed', 'started_at' => date('Y-m-d H:i:s', strtotime('-1 hour')), 'completed_at' => $now, 'output' => 'Successfully synced 45 new leads from website', 'error_message' => null],
    ['task_id' => 2, 'execution_status' => 'failed', 'started_at' => date('Y-m-d H:i:s', strtotime('-2 hours')), 'completed_at' => null, 'output' => null, 'error_message' => 'Database connection timeout after 30 seconds'],
    ['task_id' => 1, 'execution_status' => 'running', 'started_at' => $now, 'completed_at' => null, 'output' => null, 'error_message' => null],
]);

seedOrSkip($pdo, 'Files', 'files', [
    ['original_name' => 'Suryoday_Heights_Brochure.pdf', 'file_name' => 'brochure_sh_2026.pdf', 'file_path' => '/uploads/files/brochure_sh_2026.pdf', 'file_type' => 'document', 'file_category' => 'document', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size_bytes' => 2457600, 'uploaded_by' => $adminId ?: 1, 'uploaded_by_type' => 'admin', 'description' => 'Suryoday Heights project brochure with pricing', 'created_at' => $now, 'updated_at' => $now],
    ['original_name' => 'Plot_Map_Phase2.png', 'file_name' => 'plot_map_phase2.png', 'file_path' => '/uploads/files/plot_map_phase2.png', 'file_type' => 'image', 'file_category' => 'document', 'mime_type' => 'image/png', 'extension' => 'png', 'size_bytes' => 5120000, 'uploaded_by' => $adminId ?: 1, 'uploaded_by_type' => 'admin', 'description' => 'Detailed plot layout map for Phase 2', 'created_at' => $now, 'updated_at' => $now],
    ['original_name' => 'Customer_Agreement_Template.docx', 'file_name' => 'customer_agreement_v2.docx', 'file_path' => '/uploads/files/customer_agreement_v2.docx', 'file_type' => 'document', 'file_category' => 'document', 'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'extension' => 'docx', 'size_bytes' => 512000, 'uploaded_by' => $empId ?: 1, 'uploaded_by_type' => 'employee', 'description' => 'Standard customer sale agreement template v2', 'tags' => json_encode(['agreement','template','legal']), 'created_at' => $now, 'updated_at' => $now],
]);

seedOrSkip($pdo, 'Media', 'media', [
    ['filename' => 'banner_homepage_2026.jpg', 'original_filename' => 'homepage_banner_summer.jpg', 'type' => 'image', 'size' => 2048576, 'path' => '/uploads/media/banner_homepage_2026.jpg', 'uploaded_at' => $now, 'uploaded_by' => $adminId ?: 1],
    ['filename' => 'logo_aps_updated.png', 'original_filename' => 'APS_Logo_New.png', 'type' => 'image', 'size' => 128000, 'path' => '/uploads/media/logo_aps_updated.png', 'uploaded_at' => $now, 'uploaded_by' => $adminId ?: 1],
    ['filename' => 'intro_video_2026.mp4', 'original_filename' => 'Company_Introduction_2026.mp4', 'type' => 'video', 'size' => 52428800, 'path' => '/uploads/media/intro_video_2026.mp4', 'uploaded_at' => $now, 'uploaded_by' => $adminId ?: 1],
]);

seedOrSkip($pdo, 'Media Library', 'media_library', [
    ['media_type' => 'image', 'file_name' => 'gallery_01.jpg', 'original_name' => 'site_photo_aerial_view.jpg', 'file_path' => '/uploads/media_library/gallery_01.jpg', 'file_size' => 3145728, 'mime_type' => 'image/jpeg', 'alt_text' => 'Aerial view of Suryoday Heights colony', 'caption' => 'Suryoday Heights - Aerial View', 'tags' => json_encode(['aerial','colony','suryoday']), 'uploaded_by' => $adminId ?: 1, 'uploaded_at' => $now],
    ['media_type' => 'image', 'file_name' => 'gallery_02.jpg', 'original_name' => 'model_flat_interior.jpg', 'file_path' => '/uploads/media_library/gallery_02.jpg', 'file_size' => 2097152, 'mime_type' => 'image/jpeg', 'alt_text' => '2BHK model flat living room', 'caption' => '2BHK Model Flat - Living Room', 'tags' => json_encode(['interior','flat','model']), 'uploaded_by' => $adminId ?: 1, 'uploaded_at' => $now],
    ['media_type' => 'document', 'file_name' => 'price_list_2026.pdf', 'original_name' => 'Price List 2026-27.pdf', 'file_path' => '/uploads/media_library/price_list_2026.pdf', 'file_size' => 1024000, 'mime_type' => 'application/pdf', 'alt_text' => 'Complete price list for all properties', 'uploaded_by' => $adminId ?: 1, 'uploaded_at' => $now],
]);

echo "\n=== Seeding complete: " . date('Y-m-d H:i:s') . " ===\n";
