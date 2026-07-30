<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$like = '%Gorakhpur%'; $start = 'Gorakhpur%';
$stmt = $pdo->prepare("
    (SELECT id, name AS label, 'property' AS type, address AS sub, 1 AS ord FROM user_properties WHERE status='approved' AND name LIKE ? LIMIT 5)
    UNION ALL
    (SELECT id, address AS label, 'address' AS type, name AS sub, 2 AS ord FROM user_properties WHERE status='approved' AND address LIKE ? LIMIT 3)
    UNION ALL
    (SELECT id, name AS label, 'location' AS type, '' AS sub, 3 AS ord FROM cities WHERE name LIKE ? LIMIT 3)
    UNION ALL
    (SELECT 0 AS id, property_type AS label, 'type' AS type, '' AS sub, 4 AS ord FROM user_properties WHERE status='approved' AND property_type LIKE ? AND property_type != '' GROUP BY property_type LIMIT 3)
    ORDER BY ord, label LIMIT 8
");
$stmt->execute([$like, $like, $start, $start]);
$rows = $stmt->fetchAll();
echo "Row count: " . count($rows) . "\n";
foreach ($rows as $r) print_r($r);
