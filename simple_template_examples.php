<?php
/**
 * Simple Template Usage Examples
 */

// Example 1: Basic page with header and footer
function example_basic_page() {
    require_once __DIR__ . '/includes/simple_template.php';

    $content = "
    <div class='container py-5'>
        <h1 class='text-center mb-4'>Welcome to APS Dream Home</h1>
        <p class='text-center'>This is a simple page using the development template.</p>
    </div>";

    simple_page($content, 'Basic Page Example');
}

// Example 2: Page without navigation
function example_no_nav_page() {
    require_once __DIR__ . '/includes/simple_template.php';

    $content = "
    <div class='container py-5'>
        <h1>Login Required</h1>
        <p>You need to login to access this page.</p>
        " . simple_button('Go to Login', 'customer_login.php', 'btn-primary', 'sign-in-alt') . "
    </div>";

    simple_page($content, 'Login Required', false); // false = no navigation
}

// Example 3: Dashboard page with custom styling
function example_dashboard() {
    require_once __DIR__ . '/includes/simple_template.php';

    $content = "
    <div class='container py-5'>
        <div class='row'>
            <div class='col-md-4 mb-4'>
                " . simple_card('Total Properties', '125', 'text-center') . "
            </div>
            <div class='col-md-4 mb-4'>
                " . simple_card('Active Listings', '89', 'text-center') . "
            </div>
            <div class='col-md-4 mb-4'>
                " . simple_card('Total Views', '1,234', 'text-center') . "
            </div>
        </div>
    </div>";

    simple_page($content, 'Dashboard');
}

// Example 4: Form page
function example_form_page() {
    require_once __DIR__ . '/includes/simple_template.php';

    $content = "
    <div class='container py-5'>
        <div class='row justify-content-center'>
            <div class='col-md-6'>
                " . simple_card('Contact Form', '
                    <form class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="name" required>
                            <div class="invalid-feedback">Please enter your name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" required>
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" rows="5" required></textarea>
                            <div class="invalid-feedback">Please enter your message.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                ') . "
            </div>
        </div>
    </div>";

    simple_page($content, 'Contact Us');
}

// Example 5: Page with alerts
function example_alerts_page() {
    require_once __DIR__ . '/includes/simple_template.php';

    $content = "
    <div class='container py-5'>
        <h1>Alert Examples</h1>

        " . simple_alert('This is a success message!', 'success') . "
        " . simple_alert('This is an error message!', 'danger') . "
        " . simple_alert('This is a warning message!', 'warning') . "
        " . simple_alert('This is an info message!', 'info') . "

        <p>These alerts will auto-dismiss after a few seconds.</p>
    </div>";

    simple_page($content, 'Alert Examples');
}

// Example 6: Development debug page
function example_debug_page() {
    require_once __DIR__ . '/includes/simple_template.php';

    // Show debug info if debug parameter is set
    debug_session();
    debug_post();

    $content = "
    <div class='container py-5'>
        <h1>Development Debug Page</h1>
        <p>Add ?debug=session or ?debug=post to URL to see debug information.</p>

        <div class='row'>
            <div class='col-md-6'>
                <h3>Current Session</h3>
                <p>Session data will appear above if debug=session is in URL.</p>
            </div>
            <div class='col-md-6'>
                <h3>POST Data</h3>
                <p>POST data will appear above if debug=post is in URL.</p>
            </div>
        </div>
    </div>";

    simple_page($content, 'Debug Page');
}
?>
