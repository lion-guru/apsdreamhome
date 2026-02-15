<?php
session_start();
require("config.php");

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get the resume file ID from the URL parameter and validate it
$resume_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($resume_id <= 0) {
    logError("Invalid resume ID: $resume_id");
    echo "Invalid resume ID.";
    exit;
}

// Prepare the SQL statement to prevent SQL injection
$query = "SELECT file_name, file_type, file_size, resume_url FROM career_applications WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $resume_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if the resume file exists
if ($row = mysqli_fetch_assoc($result)) {
    // Validate file size (e.g., limit to 5MB)
    $maxFileSize = 5 * 1024 * 1024; // 5 MB
    if ($row['file_size'] > $maxFileSize) {
        logError("File size exceeds limit: {$row['file_size']} bytes for file: {$row['file_name']}");
        echo "File size exceeds the limit.";
        exit;
    }

    // Validate MIME type
    $allowedMimeTypes = [
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    if (!in_array($row['file_type'], $allowedMimeTypes)) {
        logError("Unsupported MIME type: {$row['file_type']} for file: {$row['file_name']}");
        echo "Unsupported file type.";
        exit;
    }

    // Set the HTTP headers to display the resume file
    header("Content-Type: " . $row['file_type']);
    header("Content-Disposition: inline; filename=\"" . htmlspecialchars($row['file_name']) . "\"");
    header("Content-Length: " . $row['file_size']);

    // Output the resume file contents
    readfile($row['upload\job_application_resum']); // Use readfile to output the file directly
} else {
    logError("Resume file not found for ID: $resume_id");
    echo "Resume file not found.";
}

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($con);

// Function to log errors with rotation
if (!function_exists('logError')) {
function logError($message) {
    $logFile = 'error_log.txt'; // Specify your log file path
    $maxFileSize = 1 * 1024 * 1024; // 1 MB
    $timestamp = date("Y-m-d H:i:s");

    // Check if the log file exists and its size
    if (file_exists($logFile) && filesize($logFile) > $maxFileSize) {
        // Rotate the log file
        rename($logFile, 'error_log_' . date("Y-m-d_H-i-s") . '.txt');
    }

    // Write the error message to the log file
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}
}

?>

<html>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<?php
// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get the resume file ID from the URL parameter and validate it
$resume_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($resume_id <= 0) {
    logError("Invalid resume ID: $resume_id");
    echo "Invalid resume ID.";
    exit;
}

// Prepare the SQL statement to prevent SQL injection
$query = "SELECT file_name, file_type, file_size, resume_url FROM career_applications WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $resume_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if the resume file exists
if ($row = mysqli_fetch_assoc($result)) {
    // Validate file size (e.g., limit to 5MB)
    $maxFileSize = 5 * 1024 * 1024; // 5 MB
    if ($row['file_size'] > $maxFileSize) {
        logError("File size exceeds limit: {$row['file_size']} bytes for file: {$row['file_name']}");
        echo "File size exceeds the limit.";
        exit;
    }

    // Validate MIME type
    $allowedMimeTypes = [
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    if (!in_array($row['file_type'], $allowedMimeTypes)) {
        logError("Unsupported MIME type: {$row['file_type']} for file: {$row['file_name']}");
        echo "Unsupported file type.";
        exit;
    }

    // Set the HTTP headers to display the resume file
    header("Content-Type: " . $row['file_type']);
    header("Content-Disposition: inline; filename=\"" . htmlspecialchars($row['file_name']) . "\"");
    header("Content-Length: " . $row['file_size']);

    // Output the resume file contents
    readfile($row['upload\job_application_resum']); // Use readfile to output the file directly
} else {
    logError("Resume file not found for ID: $resume_id");
    echo "Resume file not found.";
}

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($con);
?>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
