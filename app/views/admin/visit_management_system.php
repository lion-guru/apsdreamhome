<?php
/**
 * APS Dream Home - Advanced Visit Management System
 * Complete property visit scheduling, tracking, and follow-up system
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
require_once __DIR__ . '/../includes/config/DatabaseConfig.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>APS Dream Home - Visit Management System</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet'>
    <style>
        .visit-card { margin-bottom: 20px; border-left: 4px solid #28a745; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; }
        .status-scheduled { background-color: #fff3cd; }
        .status-completed { background-color: #d1e7dd; }
        .status-cancelled { background-color: #f8d7da; }
        .status-rescheduled { background-color: #d3d3f2; }
        .visit-timeline { position: relative; padding-left: 30px; }
        .visit-timeline::before { content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: #dee2e6; }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .timeline-marker { position: absolute; left: -23px; width: 16px; height: 16px; border-radius: 50%; background: #007bff; border: 3px solid #fff; }
    </style>
</head>
<body>
<div class='container mt-4'>
    <div class='text-center mb-4'>
        <h1><i class='fas fa-calendar-alt'></i> APS Dream Home - Visit Management</h1>
        <p class='lead'>Advanced Property Visit Scheduling & Customer Journey Tracking</p>
    </div>";

try {
    $pdo = DatabaseConfig::getConnection('pdo');
    if (!$pdo) {
        throw new Exception("Failed to connect to the database.");
    }
    
    echo "<div class='alert alert-success'>✅ Database Connection: SUCCESS</div>";
    
    // Check if visits table exists, create if not
    $check_visits_table = $pdo->query("SHOW TABLES LIKE 'property_visits'");
    if ($check_visits_table->rowCount() == 0) {
        echo "<div class='alert alert-info'>🔧 Creating property visits table...</div>";
        
        $create_visits_table = "
        CREATE TABLE `property_visits` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `property_id` int(11) NOT NULL,
            `associate_id` int(11) DEFAULT NULL,
            `visit_date` datetime NOT NULL,
            `visit_type` enum('site_visit','virtual_tour','office_meeting','follow_up') DEFAULT 'site_visit',
            `status` enum('scheduled','confirmed','completed','cancelled','rescheduled','no_show') DEFAULT 'scheduled',
            `notes` text,
            `feedback_rating` int(1) DEFAULT NULL,
            `feedback_comments` text,
            `interest_level` enum('low','medium','high','very_high') DEFAULT 'medium',
            `follow_up_required` tinyint(1) DEFAULT 0,
            `follow_up_date` datetime DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `customer_id` (`customer_id`),
            KEY `property_id` (`property_id`),
            KEY `associate_id` (`associate_id`),
            KEY `visit_date` (`visit_date`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($create_visits_table);
        echo "<div class='alert alert-success'>✅ Property visits table created successfully!</div>";
    }
    
    // Sample Visit Creation
    echo "<div class='card visit-card'>
        <div class='card-header bg-primary text-white'>
            <h5><i class='fas fa-plus-circle'></i> Create Sample Property Visits</h5>
        </div>
        <div class='card-body'>";
    
    // Get sample data for creating visits
    $customers = $pdo->query("SELECT c.id, u.uname as name, u.uemail as email FROM customers c LEFT JOIN user u ON c.user_id = u.uid LIMIT 3")->fetchAll();
    $properties = $pdo->query("SELECT id, title, price FROM properties LIMIT 2")->fetchAll();
    $associates = $pdo->query("SELECT id, name FROM associates WHERE status = 'active' LIMIT 1")->fetchAll();
    
    if (!empty($customers) && !empty($properties) && !empty($associates)) {
        $sample_visits = [
            [
                'customer_id' => $customers[0]['id'],
                'property_id' => $properties[0]['id'],
                'associate_id' => $associates[0]['id'],
                'visit_date' => date('Y-m-d H:i:s', strtotime('+2 days 10:00')),
                'visit_type' => 'site_visit',
                'status' => 'scheduled',
                'notes' => 'Initial property viewing. Customer interested in investment opportunity.',
                'interest_level' => 'high',
                'follow_up_required' => 1,
                'follow_up_date' => date('Y-m-d H:i:s', strtotime('+3 days 14:00')),
                'created_by' => 1
            ],
            [
                'customer_id' => $customers[1]['id'] ?? $customers[0]['id'],
                'property_id' => $properties[1]['id'] ?? $properties[0]['id'],
                'associate_id' => $associates[0]['id'],
                'visit_date' => date('Y-m-d H:i:s', strtotime('+1 day 15:00')),
                'visit_type' => 'virtual_tour',
                'status' => 'confirmed',
                'notes' => 'Virtual tour scheduled. Customer unable to visit physically due to location.',
                'interest_level' => 'medium',
                'follow_up_required' => 1,
                'follow_up_date' => date('Y-m-d H:i:s', strtotime('+2 days 11:00')),
                'created_by' => 1
            ],
            [
                'customer_id' => $customers[2]['id'] ?? $customers[0]['id'],
                'property_id' => $properties[0]['id'],
                'associate_id' => $associates[0]['id'],
                'visit_date' => date('Y-m-d H:i:s', strtotime('-1 day 16:00')),
                'visit_type' => 'site_visit',
                'status' => 'completed',
                'notes' => 'Customer visited property and showed strong interest.',
                'feedback_rating' => 5,
                'feedback_comments' => 'Excellent property with great amenities. Considering purchase.',
                'interest_level' => 'very_high',
                'follow_up_required' => 1,
                'follow_up_date' => date('Y-m-d H:i:s', strtotime('+1 day 10:00')),
                'created_by' => 1
            ]
        ];
        
        $visits_created = 0;
        foreach ($sample_visits as $visit) {
            // Check if visit already exists
            $check_visit = $pdo->prepare("SELECT id FROM property_visits WHERE customer_id = ? AND property_id = ? AND visit_date = ?");
            $check_visit->execute([$visit['customer_id'], $visit['property_id'], $visit['visit_date']]);
            
            if (!$check_visit->fetch()) {
                $insert_visit = $pdo->prepare("
                    INSERT INTO property_visits 
                    (customer_id, property_id, associate_id, visit_date, visit_type, status, notes, feedback_rating, feedback_comments, interest_level, follow_up_required, follow_up_date, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insert_visit->execute([
                    $visit['customer_id'], $visit['property_id'], $visit['associate_id'], 
                    $visit['visit_date'], $visit['visit_type'], $visit['status'], 
                    $visit['notes'], $visit['feedback_rating'], $visit['feedback_comments'], 
                    $visit['interest_level'], $visit['follow_up_required'], $visit['follow_up_date'], 
                    $visit['created_by']
                ]);
                $visits_created++;
            }
        }
        
        echo "<p class='success'>✅ Created $visits_created sample property visits</p>";
        
    } else {
        echo "<p class='warning'>⚠️ Insufficient sample data. Please ensure customers, properties, and associates exist.</p>";
    }
    
    echo "</div></div>";
    
    // Visit Statistics Dashboard
    echo "<div class='card visit-card'>
        <div class='card-header bg-success text-white'>
            <h5><i class='fas fa-chart-pie'></i> Visit Statistics Dashboard</h5>
        </div>
        <div class='card-body'>";
    
    $visit_stats = [
        'total_visits' => $pdo->query("SELECT COUNT(*) FROM property_visits")->fetchColumn(),
        'scheduled_visits' => $pdo->query("SELECT COUNT(*) FROM property_visits WHERE status IN ('scheduled', 'confirmed')")->fetchColumn(),
        'completed_visits' => $pdo->query("SELECT COUNT(*) FROM property_visits WHERE status = 'completed'")->fetchColumn(),
        'cancelled_visits' => $pdo->query("SELECT COUNT(*) FROM property_visits WHERE status = 'cancelled'")->fetchColumn(),
        'high_interest' => $pdo->query("SELECT COUNT(*) FROM property_visits WHERE interest_level IN ('high', 'very_high')")->fetchColumn(),
        'follow_ups_due' => $pdo->query("SELECT COUNT(*) FROM property_visits WHERE follow_up_required = 1 AND follow_up_date <= NOW() AND status != 'completed'")->fetchColumn()
    ];
    
    echo "<div class='row'>";
    
    $stat_cards = [
        ['title' => 'Total Visits', 'value' => $visit_stats['total_visits'], 'icon' => 'fas fa-calendar-check', 'color' => 'primary'],
        ['title' => 'Scheduled', 'value' => $visit_stats['scheduled_visits'], 'icon' => 'fas fa-clock', 'color' => 'warning'],
        ['title' => 'Completed', 'value' => $visit_stats['completed_visits'], 'icon' => 'fas fa-check-circle', 'color' => 'success'],
        ['title' => 'Cancelled', 'value' => $visit_stats['cancelled_visits'], 'icon' => 'fas fa-times-circle', 'color' => 'danger'],
        ['title' => 'High Interest', 'value' => $visit_stats['high_interest'], 'icon' => 'fas fa-heart', 'color' => 'info'],
        ['title' => 'Follow-ups Due', 'value' => $visit_stats['follow_ups_due'], 'icon' => 'fas fa-exclamation-triangle', 'color' => 'secondary']
    ];
    
    foreach ($stat_cards as $card) {
        echo "<div class='col-md-4 col-lg-2 mb-3'>";
        echo "<div class='card text-center h-100'>";
        echo "<div class='card-body'>";
        echo "<i class='{$card['icon']} fa-2x text-{$card['color']} mb-2'></i>";
        echo "<h4 class='text-{$card['color']}'>{$card['value']}</h4>";
        echo "<p class='card-text small'>{$card['title']}</p>";
        echo "</div></div></div>";
    }
    
    echo "</div>";
    echo "</div></div>";
    
    // Recent Visits List
    echo "<div class='card visit-card'>
        <div class='card-header bg-info text-white'>
            <h5><i class='fas fa-list'></i> Recent Property Visits</h5>
        </div>
        <div class='card-body'>";
    
    $recent_visits = $pdo->query("
        SELECT 
            pv.*,
            u.uname as customer_name,
            u.uemail as customer_email,
            p.title as property_title,
            p.price as property_price,
            a.name as associate_name
        FROM property_visits pv
        LEFT JOIN customers c ON pv.customer_id = c.id
        LEFT JOIN user u ON c.user_id = u.uid
        LEFT JOIN properties p ON pv.property_id = p.id
        LEFT JOIN associates a ON pv.associate_id = a.id
        ORDER BY pv.visit_date DESC
        LIMIT 10
    ");
    
    if ($recent_visits->rowCount() > 0) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>Customer</th><th>Property</th><th>Associate</th><th>Visit Date</th><th>Type</th><th>Status</th><th>Interest</th><th>Actions</th></tr></thead>";
        echo "<tbody>";
        
        while ($visit = $recent_visits->fetch()) {
            $status_class = [
                'scheduled' => 'warning',
                'confirmed' => 'info',
                'completed' => 'success',
                'cancelled' => 'danger',
                'rescheduled' => 'secondary',
                'no_show' => 'dark'
            ][$visit['status']] ?? 'secondary';
            
            $interest_class = [
                'low' => 'secondary',
                'medium' => 'primary',
                'high' => 'warning',
                'very_high' => 'danger'
            ][$visit['interest_level']] ?? 'secondary';
            
            echo "<tr class='status-{$visit['status']}'>";
            echo "<td>";
            echo "<strong>{$visit['customer_name']}</strong><br>";
            echo "<small class='text-muted'>{$visit['customer_email']}</small>";
            echo "</td>";
            echo "<td>";
            echo "<strong>{$visit['property_title']}</strong><br>";
            echo "<small class='text-muted'>₹" . number_format($visit['property_price']) . "</small>";
            echo "</td>";
            echo "<td>{$visit['associate_name']}</td>";
            echo "<td>" . date('M d, Y H:i', strtotime($visit['visit_date'])) . "</td>";
            echo "<td><span class='badge bg-light text-dark'>" . ucfirst(str_replace('_', ' ', $visit['visit_type'])) . "</span></td>";
            echo "<td><span class='badge bg-$status_class'>" . ucfirst($visit['status']) . "</span></td>";
            echo "<td><span class='badge bg-$interest_class'>" . ucfirst(str_replace('_', ' ', $visit['interest_level'])) . "</span></td>";
            echo "<td>";
            echo "<button class='btn btn-sm btn-outline-primary me-1' title='View Details'><i class='fas fa-eye'></i></button>";
            echo "<button class='btn btn-sm btn-outline-success me-1' title='Edit'><i class='fas fa-edit'></i></button>";
            if ($visit['follow_up_required']) {
                echo "<button class='btn btn-sm btn-outline-warning' title='Follow-up Required'><i class='fas fa-phone'></i></button>";
            }
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
        echo "</div>";
    } else {
        echo "<p class='text-muted'>No visits found. Create some sample visits to see them here.</p>";
    }
    
    echo "</div></div>";
    
    // Visit Performance Analytics
    echo "<div class='card visit-card'>
        <div class='card-header bg-warning text-white'>
            <h5><i class='fas fa-analytics'></i> Visit Performance Analytics</h5>
        </div>
        <div class='card-body'>";
    
    // Conversion rates
    $total_visits = $visit_stats['total_visits'];
    $completed_visits = $visit_stats['completed_visits'];
    $high_interest_visits = $visit_stats['high_interest'];
    
    $completion_rate = $total_visits > 0 ? ($completed_visits / $total_visits) * 100 : 0;
    $interest_rate = $completed_visits > 0 ? ($high_interest_visits / $completed_visits) * 100 : 0;
    
    // Visit types breakdown
    $visit_types = $pdo->query("
        SELECT visit_type, COUNT(*) as count 
        FROM property_visits 
        GROUP BY visit_type 
        ORDER BY count DESC
    ")->fetchAll();
    
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<h6>Performance Metrics</h6>";
    echo "<div class='mb-3'>";
    echo "<label class='form-label'>Visit Completion Rate</label>";
    echo "<div class='progress'>";
    echo "<div class='progress-bar bg-success' style='width: {$completion_rate}%'>" . number_format($completion_rate, 1) . "%</div>";
    echo "</div>";
    echo "<small class='text-muted'>$completed_visits completed out of $total_visits total visits</small>";
    echo "</div>";
    
    echo "<div class='mb-3'>";
    echo "<label class='form-label'>High Interest Rate</label>";
    echo "<div class='progress'>";
    echo "<div class='progress-bar bg-info' style='width: {$interest_rate}%'>" . number_format($interest_rate, 1) . "%</div>";
    echo "</div>";
    echo "<small class='text-muted'>$high_interest_visits high interest out of $completed_visits completed visits</small>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='col-md-6'>";
    echo "<h6>Visit Types Distribution</h6>";
    if (!empty($visit_types)) {
        foreach ($visit_types as $type) {
            $percentage = $total_visits > 0 ? ($type['count'] / $total_visits) * 100 : 0;
            echo "<div class='mb-2'>";
            echo "<div class='d-flex justify-content-between'>";
            echo "<span>" . ucfirst(str_replace('_', ' ', $type['visit_type'])) . "</span>";
            echo "<span>{$type['count']} (" . number_format($percentage, 1) . "%)</span>";
            echo "</div>";
            echo "<div class='progress' style='height: 8px;'>";
            echo "<div class='progress-bar' style='width: {$percentage}%'></div>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p class='text-muted'>No visit data available for analysis.</p>";
    }
    echo "</div>";
    echo "</div>";
    
    echo "</div></div>";
    
    // Customer Journey Timeline
    if ($total_visits > 0) {
        echo "<div class='card visit-card'>
            <div class='card-header bg-secondary text-white'>
                <h5><i class='fas fa-route'></i> Customer Journey Timeline</h5>
            </div>
            <div class='card-body'>";
        
        $journey_data = $pdo->query("
            SELECT 
                pv.*,
                u.uname as customer_name,
                p.title as property_title,
                a.name as associate_name
            FROM property_visits pv
            LEFT JOIN customers c ON pv.customer_id = c.id
            LEFT JOIN user u ON c.user_id = u.uid
            LEFT JOIN properties p ON pv.property_id = p.id
            LEFT JOIN associates a ON pv.associate_id = a.id
            ORDER BY pv.visit_date DESC
            LIMIT 5
        ");
        
        echo "<div class='visit-timeline'>";
        while ($journey = $journey_data->fetch()) {
            $timeline_color = [
                'scheduled' => 'warning',
                'confirmed' => 'info',
                'completed' => 'success',
                'cancelled' => 'danger'
            ][$journey['status']] ?? 'secondary';
            
            echo "<div class='timeline-item'>";
            echo "<div class='timeline-marker bg-$timeline_color'></div>";
            echo "<div class='card'>";
            echo "<div class='card-body'>";
            echo "<div class='d-flex justify-content-between align-items-start mb-2'>";
            echo "<h6 class='card-title mb-0'>{$journey['customer_name']} - {$journey['property_title']}</h6>";
            echo "<span class='badge bg-$timeline_color'>" . ucfirst($journey['status']) . "</span>";
            echo "</div>";
            echo "<p class='card-text'>";
            echo "<small class='text-muted'>";
            echo "<i class='fas fa-calendar'></i> " . date('M d, Y H:i', strtotime($journey['visit_date'])) . " | ";
            echo "<i class='fas fa-user'></i> {$journey['associate_name']} | ";
            echo "<i class='fas fa-tag'></i> " . ucfirst(str_replace('_', ' ', $journey['visit_type']));
            echo "</small>";
            echo "</p>";
            if ($journey['notes']) {
                echo "<p class='card-text'><small>{$journey['notes']}</small></p>";
            }
            if ($journey['feedback_rating']) {
                echo "<div class='mb-2'>";
                for ($i = 1; $i <= 5; $i++) {
                    $star_class = $i <= $journey['feedback_rating'] ? 'fas fa-star text-warning' : 'far fa-star text-muted';
                    echo "<i class='$star_class'></i> ";
                }
                echo "<small class='text-muted'>({$journey['feedback_rating']}/5)</small>";
                echo "</div>";
            }
            if ($journey['follow_up_required'] && $journey['follow_up_date']) {
                echo "<div class='alert alert-info py-2 mb-2'>";
                echo "<small><i class='fas fa-phone'></i> Follow-up scheduled: " . date('M d, Y H:i', strtotime($journey['follow_up_date'])) . "</small>";
                echo "</div>";
            }
            echo "</div></div></div>";
        }
        echo "</div>";
        
        echo "</div></div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>❌ Database Error: " . $e->getMessage() . "</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ System Error: " . $e->getMessage() . "</div>";
}

echo "
    <div class='text-center mt-4 mb-5'>
        <a href='admin/' class='btn btn-success btn-lg me-2'>
            <i class='fas fa-tachometer-alt'></i> Back to Admin
        </a>
        <a href='system_test_complete.php' class='btn btn-info btn-lg me-2'>
            <i class='fas fa-chart-line'></i> System Overview
        </a>
        <a href='system_maintenance.php' class='btn btn-warning btn-lg'>
            <i class='fas fa-tools'></i> System Maintenance
        </a>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>
</body>
</html>";
?>
