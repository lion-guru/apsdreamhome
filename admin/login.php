<?php
// Redirect to the actual admin login page
// This file exists to prevent confusion and redirect loops
header('Location: index.php');
exit();
?>