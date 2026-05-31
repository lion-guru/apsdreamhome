<?php

/**
 * Fix activities table and implement properly
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== FIX ACTIVITIES TABLE AND IMPLEMENT ===\n\n";

try {
    // Check current structure
    echo "Current activities table structure:\n";
    $cols = $pdo->query("SHOW COLUMNS FROM activities")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  {$c['Field']} ({$c['Type']}) " . ($c['Extra'] ? "[{$c['Extra']}]" : "") . "\n";
    }

    // Check if activity_id has auto_increment
    $activityIdCol = $pdo->query("SHOW COLUMNS FROM activities LIKE 'activity_id'")->fetch();
    if (strpos($activityIdCol['Extra'], 'auto_increment') === false) {
        echo "\nAdding auto_increment to activity_id...\n";

        // First, delete the problematic record with activity_id = 0
        $pdo->exec("DELETE FROM activities WHERE activity_id = 0");
        echo "  ✓ Deleted problematic record\n";

        // Add auto_increment without redeclaring primary key
        $pdo->exec("ALTER TABLE activities MODIFY COLUMN activity_id INT(11) NOT NULL AUTO_INCREMENT");
        echo "  ✓ Added auto_increment to activity_id\n";
    } else {
        echo "\nactivity_id already has auto_increment\n";
    }

    // Now seed activities properly
    echo "\nSeeding activities with lead data...\n";

    $leads = $pdo->query("SELECT id, name, email, phone, status, source, created_at, assigned_to FROM leads WHERE id IS NOT NULL LIMIT 100")->fetchAll();

    $insertActivity = $pdo->prepare("INSERT INTO activities (lead_id, type, subject, description, due_date, completed, created_by, assigned_to, created_at) 
VALUES (:lead_id, :type, :subject, :description, :due_date, 0, :created_by, :assigned_to, :created_at)");

    $activitiesCreated = 0;

    foreach ($leads as $lead) {
        $assignedTo = $lead['assigned_to'] ?? 1;
        $createdAt = $lead['created_at'];

        // Add initial note when lead was created
        $insertActivity->execute([
            ':lead_id' => $lead['id'],
            ':type' => 'note',
            ':subject' => 'Lead Created',
            ':description' => "New lead created via {$lead['source']}. Name: {$lead['name']}, Email: {$lead['email']}, Phone: {$lead['phone']}",
            ':due_date' => date('Y-m-d H:i:s', strtotime($createdAt . ' + 1 day')),
            ':created_by' => $assignedTo,
            ':assigned_to' => $assignedTo,
            ':created_at' => $createdAt
        ]);
        $activitiesCreated++;

        // Add follow-up call task
        $insertActivity->execute([
            ':lead_id' => $lead['id'],
            ':type' => 'call',
            ':subject' => 'Follow-up Call',
            ':description' => 'Call to discuss property requirements and schedule meeting',
            ':due_date' => date('Y-m-d H:i:s', strtotime($createdAt . ' + 2 days')),
            ':created_by' => $assignedTo,
            ':assigned_to' => $assignedTo,
            ':created_at' => $createdAt
        ]);
        $activitiesCreated++;

        // Add email task
        $insertActivity->execute([
            ':lead_id' => $lead['id'],
            ':type' => 'email',
            ':subject' => 'Send Property Catalog',
            ':description' => 'Email property catalog and current offers',
            ':due_date' => date('Y-m-d H:i:s', strtotime($createdAt . ' + 3 days')),
            ':created_by' => $assignedTo,
            ':assigned_to' => $assignedTo,
            ':created_at' => $createdAt
        ]);
        $activitiesCreated++;

        // For qualified leads, add meeting task
        if (in_array($lead['status'], ['qualified', 'hot', 'warm'])) {
            $insertActivity->execute([
                ':lead_id' => $lead['id'],
                ':type' => 'meeting',
                ':subject' => 'Site Visit Meeting',
                ':description' => 'Schedule property site visit',
                ':due_date' => date('Y-m-d H:i:s', strtotime($createdAt . ' + 5 days')),
                ':created_by' => $assignedTo,
                ':assigned_to' => $assignedTo,
                ':created_at' => $createdAt
            ]);
            $activitiesCreated++;
        }
    }

    echo "  ✓ Created $activitiesCreated activities\n";

    // Show summary
    $totalActivities = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
    $activitiesByType = $pdo->query("SELECT type, COUNT(*) as count FROM activities GROUP BY type")->fetchAll(PDO::FETCH_ASSOC);

    echo "\nActivities summary:\n";
    echo "  Total activities: $totalActivities\n";
    echo "  By type:\n";
    foreach ($activitiesByType as $type) {
        echo "    {$type['type']}: {$type['count']}\n";
    }

    echo "\n=== STEP 3 COMPLETE ===\n";
    echo "Activities table now properly implemented with $totalActivities records\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
