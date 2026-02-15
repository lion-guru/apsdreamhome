<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
$employees = $conn->query("SELECT id, name FROM employees WHERE status='active' ORDER BY name");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $leave_type = $_POST['leave_type'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $remarks = $_POST['remarks'];
    $stmt = $conn->prepare("INSERT INTO leaves (employee_id, leave_type, from_date, to_date, remarks) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $employee_id, $leave_type, $from_date, $to_date, $remarks);
    if ($stmt->execute()) {
        require_once __DIR__ . '/../includes/functions/notification_util.php';
        addNotification($conn, 'Leave', 'Leave applied for employee ID: ' . $employee_id, $_SESSION['auser']);
        header('Location: leaves.php?msg=' . urlencode('Leave applied successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Apply Leave</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Apply Leave</h2><form method="POST"><div class="mb-3"><label>Employee</label><select name="employee_id" class="form-control" required><?php while($e = $employees->fetch_assoc()): ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option><?php endwhile; ?></select></div><div class="mb-3"><label>Leave Type</label><input type="text" name="leave_type" class="form-control" required></div><div class="mb-3"><label>From Date</label><input type="date" name="from_date" class="form-control" required></div><div class="mb-3"><label>To Date</label><input type="date" name="to_date" class="form-control" required></div><div class="mb-3"><label>Remarks</label><input type="text" name="remarks" class="form-control"></div><button type="submit" class="btn btn-success">Apply Leave</button></form></div></body></html>
