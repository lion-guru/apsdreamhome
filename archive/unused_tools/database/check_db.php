<?php
require_once 'config.php';

echo "Checking properties table structure...\n";
$result = $conn->query("DESCRIBE properties");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
