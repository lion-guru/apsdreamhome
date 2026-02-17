<?php
require_once __DIR__ . '/core/init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['opportunity_id'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_msg'] = 'अमान्य CSRF टोकन!';
        header('Location: opportunities.php');
        exit();
    }

    $opportunity_id = intval($_POST['opportunity_id']);
    $title = $_POST['title'];
    $lead_id = !empty($_POST['lead_id']) ? intval($_POST['lead_id']) : NULL;
    $value = floatval($_POST['value']);
    $stage = $_POST['stage'];
    $probability = intval($_POST['probability']);
    $expected_close_date = $_POST['expected_close_date'];
    $property_interest = !empty($_POST['property_interest']) ? intval($_POST['property_interest']) : NULL;
    $notes = $_POST['notes'];
    $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : NULL;

    $db = \App\Core\App::database();
    $query = "UPDATE opportunities SET 
                title = :title,
                lead_id = :lead_id,
                value = :value,
                stage = :stage,
                probability = :probability,
                expected_close_date = :expected_close_date,
                property_interest = :property_interest,
                notes = :notes,
                assigned_to = :assigned_to
              WHERE opportunity_id = :opportunity_id";

    $params = [
        'title' => $title,
        'lead_id' => $lead_id,
        'value' => $value,
        'stage' => $stage,
        'probability' => $probability,
        'expected_close_date' => $expected_close_date,
        'property_interest' => $property_interest,
        'notes' => $notes,
        'assigned_to' => $assigned_to,
        'opportunity_id' => $opportunity_id
    ];
    
    if ($db->execute($query, $params)) {
        $_SESSION['success_msg'] = 'अवसर सफलतापूर्वक अपडेट किया गया!';
    } else {
        $_SESSION['error_msg'] = 'अवसर अपडेट करने में त्रुटि।';
    }
} else {
    $_SESSION['error_msg'] = 'अमान्य अनुरोध!';
}

header('Location: opportunities.php');
exit();
?>
