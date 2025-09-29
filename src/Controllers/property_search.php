<?php
include 'config.php';

// बुरा तरीका
// $query = "SELECT * FROM properties WHERE location = '$location'";

// अच्छा तरीका
$stmt = $con->prepare("SELECT * FROM properties WHERE location = ?");
$stmt->bind_param("s", $location);
$stmt->execute();
$result = $stmt->get_result();
?>