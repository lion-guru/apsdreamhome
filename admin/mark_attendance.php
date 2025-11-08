<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
$employees = $conn->query("SELECT id, name FROM employees WHERE status='active' ORDER BY name");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $date = $_POST['date'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];
    $stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, status, remarks) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $employee_id, $date, $status, $remarks);
    if ($stmt->execute()) {
        require_once __DIR__ . '/../includes/functions/notification_util.php';
        addNotification($conn, 'Attendance', 'Attendance marked for employee ID: ' . $employee_id, $_SESSION['auser']);
        header('Location: attendance.php?msg=' . urlencode('Attendance marked successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Mark Attendance</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Mark Attendance</h2><form method="POST"><div class="mb-3"><label>Employee</label><select name="employee_id" class="form-control" required><?php while($e = $employees->fetch_assoc()): ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option><?php endwhile; ?></select></div><div class="mb-3"><label>Date</label><input type="date" name="date" class="form-control" required></div><div class="mb-3"><label>Status</label><select name="status" class="form-control"><option value="present">Present</option><option value="absent">Absent</option><option value="leave">Leave</option></select></div><div class="mb-3"><label>Remarks</label><input type="text" name="remarks" class="form-control"></div><button type="submit" class="btn btn-success">Mark Attendance</button></form></div></body></html>
