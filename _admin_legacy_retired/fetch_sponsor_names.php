<?php
include 'config.php'; // Include your database connection file

header('Content-Type: application/json'); // Set the content type to JSON

$response = array();

if (isset($_POST['sponsor_id'])) {
    $sponsor_id = htmlspecialchars(trim($_POST['sponsor_id']));
    
    // Prepare statement to prevent SQL injection
    $stmt = $con->prepare("SELECT sponsor_id, uname FROM user WHERE sponsor_id LIKE CONCAT(?, '%')");
    $stmt->bind_param("s", $sponsor_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $sponsors = array();

        while ($row = $result->fetch_assoc()) {
            $sponsors[] = array(
                'sponsor_id' => htmlspecialchars($row['sponsor_id']),
                'uname' => htmlspecialchars($row['uname'])
            ); // Collect matching sponsors
        }

        if (count($sponsors) > 0) {
            $response['status'] = 'success';
            $response['sponsors'] = $sponsors; // Return sponsors
        } else {
            $response['status'] = 'error';
            $response['message'] = 'No sponsors found.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Database query failed: ' . $stmt->error;
    }

    $stmt->close();
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error: No sponsor ID provided.';
}

echo json_encode($response); // Return response as JSON
?>
