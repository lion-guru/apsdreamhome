<?php
// preview.php - Shows a live preview of the frontend header and footer for admin settings
session_start();
// Simulate a user not logged in for preview
unset($_SESSION['user_id']);
// You can customize this to preview as a logged-in user by setting $_SESSION['user_id']

// Use the dynamic header and footer
require_once __DIR__ . '/includes/templates/dynamic_header.php';
?>
<main style="min-height: 400px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
    <div class="text-center">
        <h2 style="color: #1e3c72;">APS Dream Homes Frontend Preview</h2>
        <p class="lead">This is a live preview of your site's header, navigation, and footer.<br>
        Any changes made in the admin panel will appear here instantly after saving.</p>
        <div class="alert alert-info mt-4" style="max-width: 500px; margin: 0 auto;">
            <strong>Tip:</strong> Use the admin panel tabs to change logo, menu, header color, footer links, etc. and see the result here before going live!
        </div>
    </div>
</main>
<?php
require_once __DIR__ . '/includes/dynamic_footer.php';
?>
