// ... existing form code ...

<div class="form-group">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
</div>

<div class="form-group">
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
</div>

<?php 
require_once __DIR__ . '/../includes/csrf_protection.php';
echo getAdminCSRFTokenField(); 
?>

<button type="submit" class="btn btn-primary">Login</button>

// ... remaining form code ...
