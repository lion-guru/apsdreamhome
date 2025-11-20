<?php
require_once 'config/database.php';

global $con;

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$result = mysqli_query($con, "SHOW TABLES LIKE 'remember_me_tokens'");

if (mysqli_num_rows($result) > 0) {
    echo "Table 'remember_me_tokens' exists.";
} else {
    echo "Table 'remember_me_tokens' does not exist.";
}

mysqli_close($con);
?>