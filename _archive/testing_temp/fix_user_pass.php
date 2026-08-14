<?php
$hash = password_hash('Test@123', PASSWORD_DEFAULT);
echo "New hash: $hash\n";

$mysqli = new mysqli('127.0.0.1', 'root', '', 'apsdreamhome', 3307);
if ($mysqli->connect_error) die("Connection failed\n");

$stmt = $mysqli->prepare("UPDATE users SET password = ?, status = 'active' WHERE email = ?");
$stmt->bind_param('ss', $hash, $email);

$email = 'testuser@example.com';
$stmt->execute();
echo "testuser@example.com: " . ($stmt->affected_rows > 0 ? "UPDATED" : "NOT FOUND") . "\n";

$email = 'test@aps.com';
$stmt->execute();
echo "test@aps.com: " . ($stmt->affected_rows > 0 ? "UPDATED" : "NOT FOUND, creating...") . "\n";

// Create test@aps.com if not found
if ($stmt->affected_rows === 0) {
    $hash2 = password_hash('test123', PASSWORD_DEFAULT);
    $stmt2 = $mysqli->prepare("INSERT INTO users (email, name, password, user_type, role, status, created_at, updated_at) VALUES (?, ?, ?, 'customer', 'user', 'active', NOW(), NOW())");
    $stmt2->bind_param('sss', $e, $n, $h);
    $e = 'test@aps.com';
    $n = 'Test User';
    $h = $hash2;
    $stmt2->execute();
    echo "Created test@aps.com: " . ($stmt2->affected_rows > 0 ? "OK" : "FAILED: " . $stmt2->error) . "\n";
    $stmt2->close();
}
$stmt->close();

// Verify
$result = $mysqli->query("SELECT email, LEFT(password, 35) as pw FROM users WHERE email IN ('testuser@example.com', 'test@aps.com')");
while ($row = $result->fetch_assoc()) {
    echo "Verified: {$row['email']} => {$row['pw']}...\n";
}

$mysqli->close();?>