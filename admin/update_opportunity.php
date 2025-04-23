<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['opportunity_id'])) {
    $opportunity_id = intval($_POST['opportunity_id']);
    $title = mysqli_real_escape_string($con, $_POST['title']);
    $lead_id = !empty($_POST['lead_id']) ? intval($_POST['lead_id']) : 'NULL';
    $value = floatval($_POST['value']);
    $stage = mysqli_real_escape_string($con, $_POST['stage']);
    $probability = intval($_POST['probability']);
    $expected_close_date = mysqli_real_escape_string($con, $_POST['expected_close_date']);
    $property_interest = !empty($_POST['property_interest']) ? intval($_POST['property_interest']) : 'NULL';
    $notes = mysqli_real_escape_string($con, $_POST['notes']);
    $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : 'NULL';

    $query = "UPDATE opportunities SET 
                title = '$title',
                lead_id = $lead_id,
                value = $value,
                stage = '$stage',
                probability = $probability,
                expected_close_date = '$expected_close_date',
                property_interest = $property_interest,
                notes = '$notes',
                assigned_to = $assigned_to
              WHERE opportunity_id = $opportunity_id";

    if (mysqli_query($con, $query)) {
        $_SESSION['success_msg'] = 'अवसर सफलतापूर्वक अपडेट किया गया!';
    } else {
        $_SESSION['error_msg'] = 'अवसर अपडेट करने में त्रुटि: ' . mysqli_error($con);
    }
} else {
    $_SESSION['error_msg'] = 'अमान्य अनुरोध!';
}

header('Location: opportunities.php');
exit();
?>