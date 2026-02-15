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
    <title>Payment Summary View Fix</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1 {
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
        <h1>Payment Summary View Fix</h1>";

try {
    // आवश्यक टेबल्स की जांच और निर्माण
    
    // payment_logs टेबल की जांच
    $stmt = $pdo->query("SHOW TABLES LIKE 'payment_logs'");
    $paymentLogsTableExists = $stmt->rowCount() > 0;
    
    if (!$paymentLogsTableExists) {
        showMessage("payment_logs टेबल मौजूद नहीं है, इसे बनाया जा रहा है...", 'warning');
        
        // payment_logs टेबल बनाना
        $pdo->exec("CREATE TABLE payment_logs (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            amount DECIMAL(10,2) NOT NULL,
            payment_date DATE NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            status VARCHAR(20) NOT NULL,
            transaction_id VARCHAR(100),
            notes TEXT
        )");
        
        // सैंपल डेटा जोड़ना
        $pdo->exec("INSERT INTO payment_logs (customer_id, amount, payment_date, payment_method, status, transaction_id, notes) VALUES
            (1, 25000.00, '2023-01-20', 'नेट बैंकिंग', 'सफल', 'TXN123456', 'प्रॉपर्टी बुकिंग के लिए भुगतान'),
            (2, 15000.00, '2023-02-25', 'क्रेडिट कार्ड', 'सफल', 'TXN234567', 'EMI भुगतान'),
            (3, 30000.00, '2023-03-15', 'UPI', 'सफल', 'TXN345678', 'प्रॉपर्टी बुकिंग के लिए भुगतान'),
            (4, 12000.00, '2023-04-10', 'डेबिट कार्ड', 'सफल', 'TXN456789', 'EMI भुगतान'),
            (5, 50000.00, '2023-05-15', 'NEFT', 'सफल', 'TXN567890', 'प्रॉपर्टी बुकिंग के लिए भुगतान')");
        
        showMessage("payment_logs टेबल सफलतापूर्वक बनाया गया और सैंपल डेटा जोड़ा गया", 'success');
    } else {
        showMessage("payment_logs टेबल पहले से मौजूद है", 'success');
    }
    
    // customers टेबल की जांच
    $stmt = $pdo->query("SHOW TABLES LIKE 'customers'");
    $customersTableExists = $stmt->rowCount() > 0;
    
    if (!$customersTableExists) {
        showMessage("customers टेबल मौजूद नहीं है, इसे बनाया जा रहा है...", 'warning');
        
        // customers टेबल बनाना
        $pdo->exec("CREATE TABLE customers (
            customer_id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(50) NOT NULL,
            customer_email VARCHAR(100) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_address VARCHAR(255) NOT NULL,
            registration_date DATE NOT NULL
        )");
        
        // सैंपल डेटा जोड़ना
        $pdo->exec("INSERT INTO customers (customer_name, customer_email, customer_phone, customer_address, registration_date) VALUES
            ('राजेश शर्मा', 'rajesh@example.com', '9876543210', 'दिल्ली, भारत', '2023-01-15'),
            ('प्रिया पटेल', 'priya@example.com', '8765432109', 'मुंबई, भारत', '2023-02-20'),
            ('अमित सिंह', 'amit@example.com', '7654321098', 'बैंगलोर, भारत', '2023-03-10'),
            ('सुनीता गुप्ता', 'sunita@example.com', '6543210987', 'हैदराबाद, भारत', '2023-04-05'),
            ('विकास मेहता', 'vikas@example.com', '5432109876', 'अहमदाबाद, भारत', '2023-05-12')");
        
        showMessage("customers टेबल सफलतापूर्वक बनाया गया और सैंपल डेटा जोड़ा गया", 'success');
    } else {
        showMessage("customers टेबल पहले से मौजूद है", 'success');
    }
    
    // payment_summary व्यू को ड्रॉप और रीक्रिएट करना
    showMessage("payment_summary व्यू को अपडेट किया जा रहा है...", 'info');
    
    // पहले व्यू को ड्रॉप करें यदि मौजूद है
    $pdo->exec("DROP VIEW IF EXISTS payment_summary");
    
    // सरल व्यू बनाएं जो केवल मौजूद टेबल्स का उपयोग करता है
    $pdo->exec("CREATE VIEW payment_summary AS
        SELECT 
            p.payment_id,
            c.customer_name,
            c.customer_email,
            p.amount,
            p.payment_date,
            p.payment_method,
            p.status,
            p.transaction_id
        FROM 
            payment_logs p
        LEFT JOIN 
            customers c ON p.customer_id = c.customer_id");
    
    showMessage("payment_summary व्यू सफलतापूर्वक अपडेट किया गया", 'success');
    
    // व्यू का परीक्षण करें
    showMessage("payment_summary व्यू का परीक्षण किया जा रहा है...", 'info');
    
    $stmt = $pdo->query("SELECT * FROM payment_summary");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($results) > 0) {
        showMessage("payment_summary व्यू सफलतापूर्वक परीक्षण किया गया", 'success');
        
        echo "<h2>Payment Summary View Results:</h2>";
        echo "<table>";
        echo "<tr>";
        foreach (array_keys($results[0]) as $column) {
            echo "<th>$column</th>";
        }
        echo "</tr>";
        
        foreach ($results as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        showMessage("payment_summary व्यू में कोई डेटा नहीं मिला", 'warning');
    }
    
} catch (PDOException $e) {
    showMessage("त्रुटि: " . $e->getMessage(), 'error');
} finally {
    echo "<div style='margin-top: 20px;'>
        <h3>किए गए सुधार:</h3>
        <ul>
            <li>payment_summary व्यू को सरल बनाया गया</li>
            <li>अमान्य टेबल/कॉलम संदर्भों को हटाया गया</li>
            <li>आवश्यक टेबल्स की जांच की गई और यदि वे मौजूद नहीं थे तो उन्हें बनाया गया</li>
        </ul>
        <p><a href='check_database.php'>डेटाबेस जांच रिपोर्ट देखें</a> | <a href='index.php'>होम पेज पर जाएं</a></p>
    </div>";
}

echo "</div></body></html>";
?>