<?php
require_once __DIR__ . '/core/init.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$response = array();

if (isset($_POST['sponsor_id'])) {
    $sponsor_id = h(trim($_POST['sponsor_id']));
    
    // Fetch matching sponsors
    $results = \App\Core\App::database()->fetchAll("SELECT sponsor_id, uname FROM user WHERE sponsor_id LIKE CONCAT(?, '%')", [$sponsor_id]);
    
    if (!empty($results)) {
        $sponsors = array();

        foreach ($results as $row) {
            $sponsors[] = array(
                'sponsor_id' => h($row['sponsor_id']),
                'uname' => h($row['uname'])
            ); // Collect matching sponsors
        }

        $response['status'] = 'success';
        $response['sponsors'] = $sponsors; // Return sponsors
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No sponsors found.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error: No sponsor ID provided.';
}

echo json_encode($response); // Return response as JSON
?>
