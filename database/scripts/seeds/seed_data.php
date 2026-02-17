<?php
// डेटाबेस कनेक्शन
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("डेटाबेस कनेक्शन विफल: " . $e->getMessage());
}

// मैसेज दिखाने के लिए फंक्शन
function showMessage($message, $type = 'info') {
    $color = 'black';
    $icon = '❓';
    
    if ($type == 'success') {
        $color = 'green';
        $icon = '✅';
    } elseif ($type == 'error') {
        $color = 'red';
        $icon = '❌';
    } elseif ($type == 'warning') {
        $color = 'orange';
        $icon = '⚠️';
    }
    
    echo "<div style='color: $color; margin: 10px 0;'>$icon $message</div>";
}

// HTML हेडर
echo "<!DOCTYPE html>
<html>
<head>
    <title>डेटाबेस सीडिंग</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .warning {
            color: orange;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>डेटाबेस सीडिंग</h1>";

try {
    // वर्कफ्लो टेबल में डेटा सीड करना
    showMessage("वर्कफ्लो टेबल में डेटा सीड किया जा रहा है...", 'info');
    
    // पहले चेक करें कि टेबल मौजूद है या नहीं
    $stmt = $pdo->query("SHOW TABLES LIKE 'workflows'");
    $workflowsTableExists = $stmt->rowCount() > 0;
    
    if (!$workflowsTableExists) {
        showMessage("workflows टेबल मौजूद नहीं है, इसे बनाया जा रहा है...", 'warning');
        
        // workflows टेबल बनाना
        $pdo->exec("CREATE TABLE workflows (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            trigger_event VARCHAR(50) NOT NULL,
            conditions TEXT,
            actions TEXT,
            status ENUM('active', 'inactive', 'draft') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        showMessage("workflows टेबल सफलतापूर्वक बनाया गया", 'success');
    }

    // चेक करें कि workflows टेबल में 'description' कॉलम मौजूद है या नहीं
    $stmt = $pdo->query("SHOW COLUMNS FROM workflows LIKE 'description'");
    $descriptionColumnExists = $stmt->rowCount() > 0;

    if (!$descriptionColumnExists) {
        showMessage("workflows टेबल में 'description' कॉलम मौजूद नहीं है, इसे जोड़ा जा रहा है...", 'warning');
        $pdo->exec("ALTER TABLE workflows ADD COLUMN description TEXT");
        showMessage("'description' कॉलम सफलतापूर्वक जोड़ा गया", 'success');
    }

    // चेक करें कि workflows टेबल में 'trigger_event' कॉलम मौजूद है या नहीं
    $stmt = $pdo->query("SHOW COLUMNS FROM workflows LIKE 'trigger_event'");
    $triggerEventColumnExists = $stmt->rowCount() > 0;

    if (!$triggerEventColumnExists) {
        showMessage("workflows टेबल में 'trigger_event' कॉलम मौजूद नहीं है, इसे जोड़ा जा रहा है...", 'warning');
        $pdo->exec("ALTER TABLE workflows ADD COLUMN trigger_event VARCHAR(50) NOT NULL");
        showMessage("'trigger_event' कॉलम सफलतापूर्वक जोड़ा गया", 'success');
    }

    // चेक करें कि workflows टेबल में 'conditions' कॉलम मौजूद है या नहीं
    $stmt = $pdo->query("SHOW COLUMNS FROM workflows LIKE 'conditions'");
    $conditionsColumnExists = $stmt->rowCount() > 0;

    if (!$conditionsColumnExists) {
        showMessage("workflows टेबल में 'conditions' कॉलम मौजूद नहीं है, इसे जोड़ा जा रहा है...", 'warning');
        $pdo->exec("ALTER TABLE workflows ADD COLUMN conditions TEXT");
        showMessage("'conditions' कॉलम सफलतापूर्वक जोड़ा गया", 'success');
    }

    // चेक करें कि workflows टेबल में 'actions' कॉलम मौजूद है या नहीं
    $stmt = $pdo->query("SHOW COLUMNS FROM workflows LIKE 'actions'");
    $actionsColumnExists = $stmt->rowCount() > 0;

    if (!$actionsColumnExists) {
        showMessage("workflows टेबल में 'actions' कॉलम मौजूद नहीं है, इसे जोड़ा जा रहा है...", 'warning');
        $pdo->exec("ALTER TABLE workflows ADD COLUMN actions TEXT");
        showMessage("'actions' कॉलम सफलतापूर्वक जोड़ा गया", 'success');
    }

    // चेक करें कि workflows टेबल में 'status' कॉलम मौजूद है या नहीं
    $stmt = $pdo->query("SHOW COLUMNS FROM workflows LIKE 'status'");
    $statusColumnExists = $stmt->rowCount() > 0;

    if (!$statusColumnExists) {
        showMessage("workflows टेबल में 'status' कॉलम मौजूद नहीं है, इसे जोड़ा जा रहा है...", 'warning');
        $pdo->exec("ALTER TABLE workflows ADD COLUMN status ENUM('active', 'inactive', 'draft') DEFAULT 'draft'");
        showMessage("'status' कॉलम सफलतापूर्वक जोड़ा गया", 'success');
    }
    
    // workflows टेबल में डेटा सीड करना
    $workflowsData = [
        [
            'name' => 'नया लीड असाइनमेंट',
            'description' => 'जब नया लीड आता है तो उसे स्वचालित रूप से सेल्स टीम को असाइन करें',
            'trigger_event' => 'new_lead_created',
            'conditions' => '{"lead_source": ["website", "referral"]}',
            'actions' => '{"assign_to_team": "sales", "send_notification": true, "priority": "high"}'
        ],
        [
            'name' => 'प्रॉपर्टी बुकिंग कन्फर्मेशन',
            'description' => 'जब प्रॉपर्टी बुक होती है तो ग्राहक को कन्फर्मेशन ईमेल और एसएमएस भेजें',
            'trigger_event' => 'property_booked',
            'conditions' => '{"payment_status": "confirmed"}',
            'actions' => '{"send_email": true, "send_sms": true, "update_inventory": true}'
        ],
        [
            'name' => 'पेमेंट रिमाइंडर',
            'description' => 'EMI भुगतान की तारीख से 3 दिन पहले ग्राहक को रिमाइंडर भेजें',
            'trigger_event' => 'payment_due',
            'conditions' => '{"days_before_due": 3}',
            'actions' => '{"send_email": true, "send_sms": true, "add_to_call_list": true}'
        ],
        [
            'name' => 'एजेंट परफॉरमेंस अपडेट',
            'description' => 'महीने के अंत में एजेंट परफॉरमेंस रिपोर्ट जनरेट करें और मैनेजर को भेजें',
            'trigger_event' => 'month_end',
            'conditions' => '{"day_of_month": 28}',
            'actions' => '{"generate_report": true, "send_to_manager": true}'
        ],
        [
            'name' => 'प्रॉपर्टी विजिट फॉलोअप',
            'description' => 'प्रॉपर्टी विजिट के 2 दिन बाद ग्राहक से फॉलोअप करें',
            'trigger_event' => 'property_visit_completed',
            'conditions' => '{"days_after_visit": 2, "visit_status": "completed"}',
            'actions' => '{"create_followup_task": true, "assign_to_agent": "original"}'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO workflows (name, description, trigger_event, conditions, actions, status) VALUES (?, ?, ?, ?, ?, 'active')");
    
    foreach ($workflowsData as $workflow) {
        $stmt->execute([
            $workflow['name'],
            $workflow['description'],
            $workflow['trigger_event'],
            $workflow['conditions'],
            $workflow['actions']
        ]);
    }
    
    showMessage("workflows टेबल में " . count($workflowsData) . " रिकॉर्ड्स सफलतापूर्वक जोड़े गए", 'success');
    
    // workflow_automations टेबल में डेटा अपडेट करना
    showMessage("workflow_automations टेबल में डेटा अपडेट किया जा रहा है...", 'info');
    
    // पहले चेक करें कि टेबल में पहले से कितने रिकॉर्ड्स हैं
    $stmt = $pdo->query("SELECT COUNT(*) FROM workflow_automations");
    $existingCount = $stmt->fetchColumn();
    
    if ($existingCount >= 5) {
        showMessage("workflow_automations टेबल में पहले से ही $existingCount रिकॉर्ड्स हैं, अतिरिक्त डेटा जोड़ने की आवश्यकता नहीं है", 'info');
    } else {
        // workflow_automations टेबल में डेटा सीड करना
        $automationsData = [
            [
                'workflow_id' => 1,
                'name' => 'लीड असाइनमेंट ऑटोमेशन',
                'description' => 'नए लीड्स को स्वचालित रूप से असाइन करें',
                'trigger_type' => 'event',
                'trigger_details' => '{"event": "new_lead", "source": ["website", "phone"]}',
                'action_type' => 'assign',
                'action_details' => '{"assign_to": "sales_team", "priority": "high"}'
            ],
            [
                'workflow_id' => 2,
                'name' => 'ईमेल नोटिफिकेशन',
                'description' => 'प्रॉपर्टी बुकिंग पर ईमेल भेजें',
                'trigger_type' => 'event',
                'trigger_details' => '{"event": "property_booked"}',
                'action_type' => 'notify',
                'action_details' => '{"method": "email", "template": "booking_confirmation"}'
            ],
            [
                'workflow_id' => 3,
                'name' => 'एसएमएस अलर्ट',
                'description' => 'पेमेंट ड्यू डेट से पहले एसएमएस भेजें',
                'trigger_type' => 'schedule',
                'trigger_details' => '{"days_before": 3, "event": "payment_due"}',
                'action_type' => 'notify',
                'action_details' => '{"method": "sms", "template": "payment_reminder"}'
            ],
            [
                'workflow_id' => 4,
                'name' => 'रिपोर्ट जनरेशन',
                'description' => 'मासिक परफॉरमेंस रिपोर्ट जनरेट करें',
                'trigger_type' => 'schedule',
                'trigger_details' => '{"frequency": "monthly", "day": "last"}',
                'action_type' => 'generate',
                'action_details' => '{"report_type": "performance", "format": "pdf"}'
            ],
            [
                'workflow_id' => 5,
                'name' => 'टास्क क्रिएशन',
                'description' => 'प्रॉपर्टी विजिट के बाद फॉलोअप टास्क बनाएं',
                'trigger_type' => 'event',
                'trigger_details' => '{"event": "property_visit", "status": "completed"}',
                'action_type' => 'create',
                'action_details' => '{"entity": "task", "assign_to": "property_agent"}'
            ]
        ];
        
        // पहले चेक करें कि workflow_automations टेबल मौजूद है या नहीं
        $stmt = $pdo->query("SHOW TABLES LIKE 'workflow_automations'");
        $automationsTableExists = $stmt->rowCount() > 0;
        
        if (!$automationsTableExists) {
            showMessage("workflow_automations टेबल मौजूद नहीं है, इसे बनाया जा रहा है...", 'warning');
            
            // workflow_automations टेबल बनाना
            $pdo->exec("CREATE TABLE workflow_automations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                workflow_id INT,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                trigger_type VARCHAR(50) NOT NULL,
                trigger_details TEXT,
                action_type VARCHAR(50) NOT NULL,
                action_details TEXT,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE SET NULL
            )");
            
            showMessage("workflow_automations टेबल सफलतापूर्वक बनाया गया", 'success');
        }
        
        $stmt = $pdo->prepare("INSERT INTO workflow_automations (workflow_id, name, description, trigger_type, trigger_details, action_type, action_details) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $insertedCount = 0;
        foreach ($automationsData as $automation) {
            try {
                $stmt->execute([
                    $automation['workflow_id'],
                    $automation['name'],
                    $automation['description'],
                    $automation['trigger_type'],
                    $automation['trigger_details'],
                    $automation['action_type'],
                    $automation['action_details']
                ]);
                $insertedCount++;
            } catch (PDOException $e) {
                // फॉरेन की कंस्ट्रेंट एरर को हैंडल करें
                if ($e->getCode() == '23000') {
                    showMessage("वर्कफ्लो ID {$automation['workflow_id']} के लिए फॉरेन की कंस्ट्रेंट एरर: " . $e->getMessage(), 'warning');
                    
                    // फॉरेन की कंस्ट्रेंट को हटाकर फिर से प्रयास करें
                    $stmt2 = $pdo->prepare("INSERT INTO workflow_automations (workflow_id, name, description, trigger_type, trigger_details, action_type, action_details) VALUES (NULL, ?, ?, ?, ?, ?, ?)");
                    $stmt2->execute([
                        $automation['name'],
                        $automation['description'],
                        $automation['trigger_type'],
                        $automation['trigger_details'],
                        $automation['action_type'],
                        $automation['action_details']
                    ]);
                    $insertedCount++;
                } else {
                    throw $e;
                }
            }
        }
        
        showMessage("workflow_automations टेबल में $insertedCount नए रिकॉर्ड्स सफलतापूर्वक जोड़े गए", 'success');
    }
    
    // सीडिंग के परिणामों को दिखाएं
    echo "<h2>सीडिंग परिणाम:</h2>";
    
    // workflows टेबल के डेटा को दिखाएं
    $stmt = $pdo->query("SELECT * FROM workflows");
    $workflows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($workflows) > 0) {
        echo "<h3>Workflows टेबल:</h3>";
        echo "<table>";
        echo "<tr>";
        foreach (array_keys($workflows[0]) as $column) {
            echo "<th>$column</th>";
        }
        echo "</tr>";
        
        foreach ($workflows as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . (strlen($value) > 50 ? substr($value, 0, 50) . "..." : $value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // workflow_automations टेबल के डेटा को दिखाएं
    $stmt = $pdo->query("SELECT * FROM workflow_automations");
    $automations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($automations) > 0) {
        echo "<h3>Workflow Automations टेबल:</h3>";
        echo "<table>";
        echo "<tr>";
        foreach (array_keys($automations[0]) as $column) {
            echo "<th>$column</th>";
        }
        echo "</tr>";
        
        foreach ($automations as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . (strlen($value) > 50 ? substr($value, 0, 50) . "..." : $value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    showMessage("त्रुटि: " . $e->getMessage(), 'error');
} finally {
    echo "<div style='margin-top: 20px;'>
        <h3>सीडिंग सारांश:</h3>
        <ul>
            <li>workflows टेबल में डेटा सीड किया गया</li>
            <li>workflow_automations टेबल में डेटा सीड किया गया</li>
        </ul>
        <p><a href='check_database.php'>डेटाबेस जांच रिपोर्ट देखें</a> | <a href='index.php'>होम पेज पर जाएं</a></p>
    </div>";
}

echo "</div></body></html>";
?>