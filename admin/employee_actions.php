<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['admin','superadmin']);
require_once(__DIR__ . '/../src/Database/Database.php');
$db = new Database();
$con = $db->getConnection();

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid request'];

if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? 'employee');
    $status = trim($_POST['status'] ?? 'active');
    if ($name && $email && $phone) {
        $stmt = $con->prepare("INSERT INTO employees (name, email, phone, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $role, $status);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Employee added successfully'];
        } else {
            $response['message'] = 'Failed to add employee.';
        }
    } else {
        $response['message'] = 'All fields are required.';
    }
} elseif ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? 'employee');
    $status = trim($_POST['status'] ?? 'active');
    if ($id && $name && $email && $phone) {
        $stmt = $con->prepare("UPDATE employees SET name=?, email=?, phone=?, role=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $email, $phone, $role, $status, $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Employee updated successfully'];
        } else {
            $response['message'] = 'Failed to update employee.';
        }
    } else {
        $response['message'] = 'All fields are required.';
    }
} elseif ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $stmt = $con->prepare("DELETE FROM employees WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Employee deleted successfully'];
        } else {
            $response['message'] = 'Failed to delete employee.';
        }
    } else {
        $response['message'] = 'Invalid employee ID.';
    }
}

// Assuming the following code is present somewhere in the file
// to display employee data in a table
// $result = $con->query("SELECT * FROM employees");
// while ($row = $result->fetch_assoc()) {
//     echo '<tr>';
//     echo '<td>' . htmlspecialchars($row['employee_name']) . '</td>';
//     echo '<td>' . htmlspecialchars($row['action']) . '</td>';
//     echo '<td>' . htmlspecialchars($row['date']) . '</td>';
//     echo '</tr>';
// }

// If the above code is not present, you need to add it to display employee data
// and escape the output using htmlspecialchars()

header('Content-Type: application/json');
echo json_encode($response);
