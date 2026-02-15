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
    <title>Customer Summary View Fix</title>
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
        <h1>Customer Summary View Fix</h1>";

try {
    // आवश्यक टेबल्स की जांच और निर्माण
    
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
    
    // customer_documents टेबल की जांच
    $stmt = $pdo->query("SHOW TABLES LIKE 'customer_documents'");
    $customerDocsTableExists = $stmt->rowCount() > 0;
    
    if (!$customerDocsTableExists) {
        showMessage("customer_documents टेबल मौजूद नहीं है, इसे बनाया जा रहा है...", 'warning');
        
        // customer_documents टेबल बनाना
        $pdo->exec("CREATE TABLE customer_documents (
            document_id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            document_type VARCHAR(50) NOT NULL,
            document_path VARCHAR(255) NOT NULL,
            upload_date DATE NOT NULL,
            CONSTRAINT fk_customer_docs FOREIGN KEY (customer_id) 
            REFERENCES customers(customer_id) ON DELETE CASCADE
        )");
        
        // सैंपल डेटा जोड़ना
        $pdo->exec("INSERT INTO customer_documents (customer_id, document_type, document_path, upload_date) VALUES
            (1, 'आधार कार्ड', '/documents/aadhar/1.pdf', '2023-01-20'),
            (1, 'पैन कार्ड', '/documents/pan/1.pdf', '2023-01-20'),
            (2, 'आधार कार्ड', '/documents/aadhar/2.pdf', '2023-02-25'),
            (3, 'पैन कार्ड', '/documents/pan/3.pdf', '2023-03-15'),
            (4, 'आधार कार्ड', '/documents/aadhar/4.pdf', '2023-04-10')");
        
        showMessage("customer_documents टेबल सफलतापूर्वक बनाया गया और सैंपल डेटा जोड़ा गया", 'success');
    } else {
        showMessage("customer_documents टेबल पहले से मौजूद है", 'success');
    }
    
    // customer_summary व्यू को ड्रॉप और रीक्रिएट करना
    showMessage("customer_summary व्यू को अपडेट किया जा रहा है...", 'info');
    
    // पहले व्यू को ड्रॉप करें यदि मौजूद है
    $pdo->exec("DROP VIEW IF EXISTS customer_summary");
    
    // सरल व्यू बनाएं जो केवल मौजूद टेबल्स का उपयोग करता है
    $pdo->exec("CREATE VIEW customer_summary AS
        SELECT 
            c.customer_id,
            c.customer_name,
            c.customer_email,
            c.customer_phone,
            c.customer_address,
            c.registration_date,
            COUNT(d.document_id) AS document_count
        FROM 
            customers c
        LEFT JOIN 
            customer_documents d ON c.customer_id = d.customer_id
        GROUP BY 
            c.customer_id, c.customer_name, c.customer_email, c.customer_phone, c.customer_address, c.registration_date");
    
    showMessage("customer_summary व्यू सफलतापूर्वक अपडेट किया गया", 'success');
    
    // व्यू का परीक्षण करें
    showMessage("customer_summary व्यू का परीक्षण किया जा रहा है...", 'info');
    
    $stmt = $pdo->query("SELECT * FROM customer_summary");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($results) > 0) {
        showMessage("customer_summary व्यू सफलतापूर्वक परीक्षण किया गया", 'success');
        
        echo "<h2>Customer Summary View Results:</h2>";
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
        showMessage("customer_summary व्यू में कोई डेटा नहीं मिला", 'warning');
    }
    
} catch (PDOException $e) {
    showMessage("त्रुटि: " . $e->getMessage(), 'error');
} finally {
    echo "<div style='margin-top: 20px;'>
        <h3>किए गए सुधार:</h3>
        <ul>
            <li>customer_summary व्यू को सरल बनाया गया</li>
            <li>अमान्य टेबल/कॉलम संदर्भों को हटाया गया</li>
            <li>आवश्यक टेबल्स की जांच की गई और यदि वे मौजूद नहीं थे तो उन्हें बनाया गया</li>
        </ul>
        <p><a href='check_database.php'>डेटाबेस जांच रिपोर्ट देखें</a> | <a href='index.php'>होम पेज पर जाएं</a></p>
    </div>";
}

echo "</div></body></html>";
?>