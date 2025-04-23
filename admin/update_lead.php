<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['lead_id'])) {
    $lead_id = intval($_POST['lead_id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $source = mysqli_real_escape_string($con, $_POST['source']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $notes = mysqli_real_escape_string($con, $_POST['notes']);
    $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : 'NULL';

    $query = "UPDATE leads SET 
                name = '$name',
                email = '$email',
                phone = '$phone',
                address = '$address',
                source = '$source',
                status = '$status',
                notes = '$notes',
                assigned_to = $assigned_to
              WHERE lead_id = $lead_id";

    if (mysqli_query($con, $query)) {
        $_SESSION['success_msg'] = 'लीड सफलतापूर्वक अपडेट की गई!';
    } else {
        $_SESSION['error_msg'] = 'लीड अपडेट करने में त्रुटि: ' . mysqli_error($con);
    }
} else {
    $_SESSION['error_msg'] = 'अमान्य अनुरोध!';
}

header('Location: leads.php');
exit();
?>