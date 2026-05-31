<?php

/**
 * Step 3: Implement activities table for CRM
 * Populate the empty activities table with actual lead activity data
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== STEP 3: IMPLEMENT ACTIVITIES TABLE FOR CRM ===\n\n";

try {
    // Check current state
    $activitiesCount = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
    $leadsCount = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();

    echo "Current state:\n";
    echo "  activities: $activitiesCount records\n";
    echo "  leads: $leadsCount records\n\n";

    // Check activities table schema
    $activityColumns = $pdo->query("DESCRIBE activities")->fetchAll(PDO::FETCH_ASSOC);
    echo "Activities table structure:\n";
    foreach ($activityColumns as $col) {
        echo "  {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n";

    // If already has data, show summary and exit
    if ($activitiesCount > 0) {
        echo "Activities table already has data. Seeding additional activities...\n";
    } else {
        echo "Seeding activities table with lead activities...\n";
    }

    // Get leads data
    $leads = $pdo->query("SELECT id, name, email, phone, status, source, created_at, assigned_to FROM leads WHERE id IS NOT NULL LIMIT 50")->fetchAll();

    $insertActivity = $pdo->prepare("INSERT INTO activities (activity_id, lead_id, opportunity_id, type, subject, description, due_date, completed, completed_date, created_by, assigned_to, created_at, updated_at) 
VALUES (NULL, :lead_id, NULL, :type, :subject, :description, :due_date, 0, NULL, :created_by, :assigned_to, :created_at, NOW())");

    $activitiesCreated = 0;
    $activityTypes = ['call', 'email', 'meeting', 'task', 'note'];

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

        // Add follow-up task based on lead status
        if ($lead['status'] === 'new' || $lead['status'] === 'pending') {
            $insertActivity->execute([
                ':lead_id' => $lead['id'],
                ':type' => 'call',
                ':subject' => 'Initial Follow-up Call',
                ':description' => 'Call to introduce our services and understand requirements',
                ':due_date' => date('Y-m-d H:i:s', strtotime($createdAt . ' + 2 days')),
                ':created_by' => $assignedTo,
                ':assigned_to' => $assignedTo,
                ':created_at' => $createdAt
            ]);
            $activitiesCreated++;
        }

        // Add email task for all leads
        $insertActivity->execute([
            ':lead_id' => $lead['id'],
            ':type' => 'email',
            ':subject' => 'Send Property Information',
            ':description' => 'Email property catalog and pricing information',
            ':due_date' => date('Y-m-d H:i:s', strtotime($createdAt . ' + 3 days')),
            ':created_by' => $assignedTo,
            ':assigned_to' => $assignedTo,
            ':created_at' => $createdAt
        ]);
        $activitiesCreated++;

        // For hot leads, add meeting task
        if (in_array($lead['status'], ['hot', 'warm', 'qualified'])) {
            $insertActivity->execute([
                ':lead_id' => $lead['id'],
                ':type' => 'meeting',
                ':subject' => 'Schedule Site Visit',
                ':description' => 'Schedule property site visit with the lead',
                ':due_date' => date('Y-m-d H:i:s', strtotime($createdAt . ' + 5 days')),
                ':created_by' => $assignedTo,
                ':assigned_to' => $assignedTo,
                ':created_at' => $createdAt
            ]);
            $activitiesCreated++;
        }
    }

    echo "✓ Created $activitiesCreated activities\n\n";

    // Verify data
    $totalActivities = $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn();
    $activitiesByType = $pdo->query("SELECT type, COUNT(*) as count FROM activities GROUP BY type")->fetchAll(PDO::FETCH_ASSOC);

    echo "Activities summary:\n";
    echo "  Total activities: $totalActivities\n";
    echo "  By type:\n";
    foreach ($activitiesByType as $type) {
        echo "    {$type['type']}: {$type['count']}\n";
    }

    // Find code references to activities
    echo "\nChecking code references to activities...\n";

    $projectRoot = dirname(__DIR__);
    $searchDirs = ['app', 'routes'];
    $filesWithReferences = [];

    foreach ($searchDirs as $dir) {
        $fullDir = $projectRoot . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($fullDir)) continue;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            if (strpos($file->getPathname(), 'vendor') !== false) continue;
            if (strpos($file->getPathname(), 'node_modules') !== false) continue;

            $content = file_get_contents($file->getPathname());
            if ($content && stripos($content, 'activities') !== false && stripos($content, 'activity_logs') === false) {
                $filesWithReferences[] = str_replace($projectRoot, '', $file->getPathname());
            }
        }
    }

    echo "  Found " . count($filesWithReferences) . " files with activities references:\n";
    foreach (array_slice($filesWithReferences, 0, 10) as $file) {
        echo "    - $file\n";
    }
    if (count($filesWithReferences) > 10) {
        echo "    ... and " . (count($filesWithReferences) - 10) . " more\n";
    }

    echo "\n=== STEP 3 COMPLETE ===\n";
    echo "Summary:\n";
    echo "  - Created $activitiesCreated lead activities\n";
    echo "  - Total activities in database: $totalActivities\n";
    echo "  - Found " . count($filesWithReferences) . " code references\n";
    echo "  - Activities table now ready for CRM use\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
