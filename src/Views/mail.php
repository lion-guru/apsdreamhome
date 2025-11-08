<?php
// Include configuration file
include("config.php");

// Get form data
$name = $_POST['name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$msg = $_POST['msg'];
$file = $_FILES['file'];

// Validate form data
if (empty($name) || empty($phone) || empty($email) || empty($msg)) {
    echo 'Please fill in all fields.';
    exit;
}

// Upload file
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_type = $file['type'];
$file_size = $file['size'];

if ($file_size > 0) {
    move_uploaded_file($file_tmp, $upload_dir . $file_name);
} else {
    $file_name = '';
}

// Insert data into database
$sql = "INSERT INTO job_applications (name, phone, email, message, file_path) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $name, $phone, $email, $msg, $file_name);
$stmt->execute();

// Check if data was inserted successfully
if ($stmt->affected_rows == 1) {
    echo 'Data inserted successfully!';
} else {
    echo 'Error inserting data: ' . $conn->error;
}

// Close database connection
$conn->close();
?>