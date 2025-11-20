<?php
// Add Project - Enhanced Security Version
// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/project_error.log');
error_reporting(E_ALL);

// Set security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com \'unsafe-inline\'; style-src \'self\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com \'unsafe-inline\'; img-src \'self\' data:;');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Start secure session
$session_name = 'secure_admin_session';
$secure = true; // Only send cookies over HTTPS
$httponly = true; // Prevent JavaScript access to session cookie
$samesite = 'Strict';

if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}

session_name($session_name);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Session timeout check
if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > 1800) { // 30 minutes timeout
    session_unset();
    session_destroy();
    logError('Session Timeout', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/index.php?error=session_expired');
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Validate CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        logError('CSRF Token Mismatch', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        http_response_code(403);
        die('CSRF token validation failed');
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    logError('Unauthorized Access Attempt', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    header('Location: /admin/index.php');
    exit();
}

// Validate and include required files
$required_files = [
    __DIR__ . '/includes/session_manager.php',
    __DIR__ . '/../includes/db_config.php',
    __DIR__ . '/../includes/log_admin_activity.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file) || !is_readable($file)) {
        logError('Required File Missing', [
            'file_path' => $file,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        die('System configuration error');
    }
}

require_once $required_files[0];
require_once $required_files[1];
require_once $required_files[2];

initAdminSession();

// Initialize variables
$success = $error = '';
$max_file_size = 10 * 1024 * 1024; // 10MB
$allowed_file_types = [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'pdf' => ['pdf']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize inputs
        $name = validateInput($_POST['name'] ?? '', 'string', 255);
        $city = validateInput($_POST['city'] ?? '', 'string', 100);
        $location = validateInput($_POST['location'] ?? '', 'string', 255);
        $description = validateInput($_POST['description'] ?? '', 'string', 1000);
        $youtube_url = validateInput($_POST['youtube_url'] ?? '', 'url', 500);
        $status = isset($_POST['status']) ? 1 : 0;

        // Validate required fields
        if (empty($name) || empty($city) || empty($location) || empty($description)) {
            throw new Exception('All required fields must be filled');
        }

        // Use getDbConnection() to connect to DB
        global $con;
        $conn = $con;
        if (!$conn) {
            throw new Exception('Database connection failed');
        }

        // Begin transaction
        $conn->begin_transaction();

        try {
            // Insert project with prepared statement
            $stmt = $conn->prepare("INSERT INTO projects (name, city, location, description, status, youtube_url) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }

            $stmt->bind_param("ssssis", $name, $city, $location, $description, $status, $youtube_url);

            if (!$stmt->execute()) {
                throw new Exception('Failed to insert project: ' . $stmt->error);
            }

            $project_id = $stmt->insert_id;
            $stmt->close();

            // Handle brochure upload
            if (!empty($_FILES['brochure']['name'])) {
                $brochure_result = handleFileUpload($_FILES['brochure'], 'brochure', $max_file_size, $allowed_file_types['pdf']);
                if ($brochure_result['success']) {
                    $brochure_path = $brochure_result['path'];

                    // Update project with brochure path
                    $stmt = $conn->prepare("UPDATE projects SET brochure_path = ? WHERE id = ?");
                    if (!$stmt) {
                        throw new Exception('Failed to prepare brochure update statement');
                    }

                    $stmt->bind_param("si", $brochure_path, $project_id);
                    if (!$stmt->execute()) {
                        throw new Exception('Failed to update project with brochure: ' . $stmt->error);
                    }
                    $stmt->close();

                    // Handle Google Drive integration
                    handleGoogleDriveIntegration($brochure_path, 'projects', 'id', $project_id, 'brochure_drive_id', $name, $conn);

                    // Send notifications
                    sendProjectNotifications($name, $project_id, 'brochure', $brochure_path, $conn);
                } else {
                    logError('Brochure Upload Failed', [
                        'project_id' => $project_id,
                        'error' => $brochure_result['error']
                    ]);
                }
            }

            // Handle amenity icons upload
            if (!empty($_FILES['amenity_icons']['name'][0])) {
                handleAmenityUploads($_FILES['amenity_icons'], $_POST['amenity_labels'] ?? [], $project_id, $max_file_size, $allowed_file_types['image'], $conn);
            }

            // Handle gallery images upload
            if (!empty($_FILES['gallery_images']['name'][0])) {
                handleGalleryUploads($_FILES['gallery_images'], $project_id, $max_file_size, $allowed_file_types['image'], $name, $conn);
            }

            // Commit transaction
            $conn->commit();

            // Log admin activity
            log_admin_activity('add_project', 'Added project: ' . $name);

            $success = 'Project added successfully!';
            header("Location: /admin/projects.php?msg=" . urlencode('added'));
            exit();

        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            throw $e;
        }

    } catch (Exception $e) {
        logError('Project Creation Error', [
            'error_message' => $e->getMessage(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        $error = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}

// Helper functions
function validateInput($input, $type, $max_length = null) {
    $input = trim($input);

    switch ($type) {
        case 'string':
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'url':
            $input = filter_var($input, FILTER_SANITIZE_URL);
            break;
        default:
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    }

    if ($max_length && strlen($input) > $max_length) {
        throw new Exception("Input exceeds maximum length of $max_length characters");
    }

    return $input;
}

function handleFileUpload($file, $type, $max_size, $allowed_types) {
    $result = ['success' => false, 'path' => '', 'error' => ''];

    try {
        // Validate file upload
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        // Check file size
        if ($file['size'] > $max_size) {
            throw new Exception('File size exceeds maximum allowed size');
        }

        // Validate file extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            throw new Exception('File type not allowed');
        }

        // Generate secure filename
        $secure_filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
        $upload_path = __DIR__ . '/../uploads/' . $type . 's/';

        // Create directory if not exists
        if (!file_exists($upload_path)) {
            if (!mkdir($upload_path, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $upload_path . $secure_filename)) {
            throw new Exception('Failed to move uploaded file');
        }

        $result['success'] = true;
        $result['path'] = 'uploads/' . $type . 's/' . $secure_filename;

    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
        logError('File Upload Error', [
            'error_message' => $e->getMessage(),
            'file_type' => $type
        ]);
    }

    return $result;
}

function handleGoogleDriveIntegration($file_path, $table, $id_column, $id_value, $drive_column, $project_name, $conn) {
    try {
        require_once __DIR__ . '/includes/integration_helpers.php';

        if (!function_exists('upload_to_google_drive_and_save_id')) {
            logError('Google Drive Integration Function Missing', [
                'function' => 'upload_to_google_drive_and_save_id'
            ]);
            return;
        }

        upload_to_google_drive_and_save_id($file_path, $table, $id_column, $id_value, $drive_column);

    } catch (Exception $e) {
        logError('Google Drive Integration Error', [
            'error_message' => $e->getMessage(),
            'project_name' => $project_name
        ]);
    }
}

function sendProjectNotifications($project_name, $project_id, $type, $file_path, $conn) {
    try {
        // Get drive file ID
        $drive_column = $type === 'brochure' ? 'brochure_drive_id' : 'drive_file_id';
        $stmt = $conn->prepare("SELECT $drive_column FROM projects WHERE id = ?");
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $drive_id = $result->fetch_assoc()[$drive_column] ?? '';
        $stmt->close();

        $drive_link = $drive_id ? "https://drive.google.com/file/d/$drive_id/view" : '';

        $message = "🏢 *New Project " . ucfirst($type) . " Uploaded*\n" .
                  "Project: $project_name (#$project_id)\n" .
                  ($drive_link ? "[View on Google Drive]($drive_link)" : '');

        // Send notifications
        if (function_exists('send_slack_notification')) {
            send_slack_notification($message);
        }
        if (function_exists('send_telegram_notification')) {
            send_telegram_notification($message);
        }

        // Log upload event
        if (function_exists('log_upload_event')) {
            require_once __DIR__ . '/includes/upload_audit_log.php';
            log_upload_event($conn, 'project_' . $type, $project_id, 'projects', $file_path, $drive_id, $project_name, 'sent', 'sent');
        }

    } catch (Exception $e) {
        logError('Notification Error', [
            'error_message' => $e->getMessage(),
            'project_name' => $project_name
        ]);
    }
}

function handleAmenityUploads($files, $labels, $project_id, $max_size, $allowed_types, $conn) {
    foreach ($files['name'] as $idx => $icon_name) {
        if (!empty($icon_name)) {
            try {
                // Create temporary file array for single file
                $file = [
                    'name' => $icon_name,
                    'tmp_name' => $files['tmp_name'][$idx],
                    'size' => $files['size'][$idx],
                    'error' => $files['error'][$idx]
                ];

                $upload_result = handleFileUpload($file, 'amenity', $max_size, $allowed_types);
                if ($upload_result['success']) {
                    $icon_path = $upload_result['path'];
                    $label = validateInput($labels[$idx] ?? '', 'string', 100);

                    $stmt = $conn->prepare("INSERT INTO project_amenities (project_id, icon_path, label) VALUES (?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception('Failed to prepare amenity insert statement');
                    }

                    $stmt->bind_param("iss", $project_id, $icon_path, $label);
                    if (!$stmt->execute()) {
                        throw new Exception('Failed to insert amenity: ' . $stmt->error);
                    }
                    $stmt->close();
                }
            } catch (Exception $e) {
                logError('Amenity Upload Error', [
                    'error_message' => $e->getMessage(),
                    'project_id' => $project_id,
                    'amenity_index' => $idx
                ]);
            }
        }
    }
}

function handleGalleryUploads($files, $project_id, $max_size, $allowed_types, $project_name, $conn) {
    foreach ($files['name'] as $idx => $img_name) {
        if (!empty($img_name)) {
            try {
                // Create temporary file array for single file
                $file = [
                    'name' => $img_name,
                    'tmp_name' => $files['tmp_name'][$idx],
                    'size' => $files['size'][$idx],
                    'error' => $files['error'][$idx]
                ];

                $upload_result = handleFileUpload($file, 'gallery', $max_size, $allowed_types);
                if ($upload_result['success']) {
                    $img_path = $upload_result['path'];

                    $stmt = $conn->prepare("INSERT INTO project_gallery (project_id, image_path) VALUES (?, ?)");
                    if (!$stmt) {
                        throw new Exception('Failed to prepare gallery insert statement');
                    }

                    $stmt->bind_param("is", $project_id, $img_path);
                    if (!$stmt->execute()) {
                        throw new Exception('Failed to insert gallery image: ' . $stmt->error);
                    }

                    $gallery_id = $conn->insert_id;
                    $stmt->close();

                    // Handle Google Drive integration
                    handleGoogleDriveIntegration($img_path, 'project_gallery', 'id', $gallery_id, 'drive_file_id', $project_name, $conn);

                    // Send notifications
                    sendProjectNotifications($project_name, $project_id, 'gallery', $img_path, $conn);
                }
            } catch (Exception $e) {
                logError('Gallery Upload Error', [
                    'error_message' => $e->getMessage(),
                    'project_id' => $project_id,
                    'image_index' => $idx
                ]);
            }
        }
    }
}

function logError($message, $context = []) {
    $logDir = __DIR__ . '/../logs';
    try {
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/project_error.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = '';

        if (!empty($context)) {
            foreach ($context as $key => $value) {
                try {
                    if (is_null($value)) {
                        $strValue = 'NULL';
                    } elseif (is_bool($value)) {
                        $strValue = $value ? 'TRUE' : 'FALSE';
                    } elseif (is_scalar($value)) {
                        $strValue = (string)$value;
                    } elseif (is_array($value) || is_object($value)) {
                        $strValue = json_encode($value, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
                    } else {
                        $strValue = 'UNKNOWN_TYPE';
                    }

                    $strValue = mb_strlen($strValue) > 500 ? mb_substr($strValue, 0, 500) . '...' : $strValue;
                    $contextStr .= " | $key: $strValue";
                } catch (Exception $e) {
                    $contextStr .= " | $key: SERIALIZATION_ERROR";
                }
            }
        }

        $logMessage = "[{$timestamp}] {$message}{$contextStr}\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        error_log($logMessage);
    } catch (Exception $e) {
        error_log("CRITICAL LOGGING FAILURE: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Project - Secure</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
        .form-floating > .fa { position: absolute; left: 20px; top: 22px; color: #aaa; pointer-events: none; }
        .form-floating input, .form-floating textarea { padding-left: 2.5rem; }
        .alert { margin-bottom: 1rem; }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25); }
    </style>
</head>
<body>
<?php
// Validate and include header
$header_file = __DIR__ . '/../includes/templates/dynamic_header.php';
if (file_exists($header_file) && is_readable($header_file)) {
    include $header_file;
} else {
    logError('Header File Missing', ['file_path' => $header_file]);
    echo '<!-- Header not available -->';
}
?>
<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Add New Project</h1>
            <a href="/admin/projects.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Projects
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Project Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" autocomplete="off" class="needs-validation" novalidate enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating position-relative">
                                <input type="text" class="form-control" id="name" name="name" required
                                       placeholder="Project Name" maxlength="255"
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <label for="name"><i class="fa fa-building"></i> Project Name *</label>
                                <div class="invalid-feedback">Please enter the project name.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating position-relative">
                                <input type="text" class="form-control" id="city" name="city" required
                                       placeholder="City" maxlength="100"
                                       value="<?php echo htmlspecialchars($_POST['city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <label for="city"><i class="fa fa-city"></i> City *</label>
                                <div class="invalid-feedback">Please enter the city.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating position-relative">
                                <input type="text" class="form-control" id="location" name="location" required
                                       placeholder="Location" maxlength="255"
                                       value="<?php echo htmlspecialchars($_POST['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <label for="location"><i class="fa fa-map-marker-alt"></i> Location *</label>
                                <div class="invalid-feedback">Please enter the location.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating position-relative">
                                <input type="url" class="form-control" id="youtube_url" name="youtube_url"
                                       placeholder="YouTube URL" maxlength="500"
                                       value="<?php echo htmlspecialchars($_POST['youtube_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <label for="youtube_url"><i class="fab fa-youtube"></i> YouTube Video URL</label>
                                <div class="invalid-feedback">Please enter a valid YouTube URL.</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating position-relative">
                                <textarea class="form-control" id="description" name="description" required
                                          placeholder="Description" style="height: 100px" maxlength="1000"><?php
                                    echo htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8');
                                ?></textarea>
                                <label for="description"><i class="fa fa-align-left"></i> Description *</label>
                                <div class="invalid-feedback">Please enter the description.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <h6 class="mb-3">File Uploads</h6>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brochure" class="form-label">
                                    <i class="fas fa-file-pdf me-2"></i>Brochure (PDF) - Max 10MB
                                </label>
                                <input type="file" id="brochure" name="brochure" accept="application/pdf"
                                       class="form-control" onchange="validateFile(this, 'pdf', 10)">
                                <div class="form-text">Upload PDF brochure for the project</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amenity_icons" class="form-label">
                                    <i class="fas fa-icons me-2"></i>Amenity Icons
                                </label>
                                <input type="file" id="amenity_icons" name="amenity_icons[]" multiple
                                       accept="image/*" class="form-control"
                                       onchange="validateFile(this, 'image', 5)">
                                <div class="form-text">Upload amenity icons (multiple files allowed)</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gallery_images" class="form-label">
                                    <i class="fas fa-images me-2"></i>Gallery Images
                                </label>
                                <input type="file" id="gallery_images" name="gallery_images[]" multiple
                                       accept="image/*" class="form-control"
                                       onchange="validateFile(this, 'image', 5)">
                                <div class="form-text">Upload gallery images (multiple files allowed)</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amenity_labels" class="form-label">
                                    <i class="fas fa-tags me-2"></i>Amenity Labels
                                </label>
                                <input type="text" id="amenity_labels" name="amenity_labels[]" multiple
                                       class="form-control" placeholder="Enter amenity labels (comma separated)"
                                       value="<?php echo htmlspecialchars($_POST['amenity_labels'][0] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="form-text">Enter labels for amenity icons</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                                <label class="form-check-label" for="status">
                                    <i class="fa fa-check-circle me-2"></i>Active Project
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-plus me-2"></i>Add Project
                        </button>
                        <a href="/admin/projects.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Validate and include footer
$footer_file = __DIR__ . '/../includes/templates/new_footer.php';
if (file_exists($footer_file) && is_readable($footer_file)) {
    include $footer_file;
} else {
    logError('Footer File Missing', ['file_path' => $footer_file]);
    echo '<!-- Footer not available -->';
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script>
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();

// File validation function
function validateFile(input, type, maxSizeMB) {
    const file = input.files[0];
    if (!file) return;

    const maxSize = maxSizeMB * 1024 * 1024;
    const allowedTypes = {
        'pdf': ['application/pdf'],
        'image': ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    };

    if (file.size > maxSize) {
        alert(`File size must be less than ${maxSizeMB}MB`);
        input.value = '';
        return;
    }

    if (!allowedTypes[type].includes(file.type)) {
        alert(`Please select a valid ${type} file`);
        input.value = '';
        return;
    }
}

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('description');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
});
</script>
</body>
</html>