<?php
// Feed extensive MLM associate data for 7-level tree demo
require_once __DIR__ . '/includes/config/config.php';

$con = $con ?? null;
if (!$con) {
    die("Could not connect to DB\n");
}

function insertAssociate($con, $id, $name, $email, $phone, $parent_id, $commission_percent, $level, $status) {
    $check = $con->prepare("SELECT id FROM associates WHERE id=?");
    $check->bind_param('i', $id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows == 0) {
        $stmt = $con->prepare("INSERT INTO associates (id, name, email, phone, parent_id, commission_percent, level, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssiids', $id, $name, $email, $phone, $parent_id, $commission_percent, $level, $status);
        $stmt->execute();
        $stmt->close();
    }
    $check->close();
}

// Level 1 (root)
insertAssociate($con, 1, 'You', 'you@example.com', '9000000001', null, 10, 1, 'active');

$nextId = 2;
$parentIds = [1];
for ($level = 2; $level <= 7; $level++) {
    $newParentIds = [];
    foreach ($parentIds as $parent) {
        $numChildren = ($level <= 3) ? 5 : 2; // Wider at top, then binary style
        for ($j = 1; $j <= $numChildren; $j++) {
            $name = 'L'.$level.'-'.$parent.'-'.$j;
            $email = strtolower($name).'@example.com';
            $phone = '90000'.str_pad($nextId, 5, '0', STR_PAD_LEFT);
            insertAssociate($con, $nextId, $name, $email, $phone, $parent, 10, $level, 'active');
            $newParentIds[] = $nextId;
            $nextId++;
        }
    }
    $parentIds = $newParentIds;
}
echo "7-level large MLM tree seeded.\n";
