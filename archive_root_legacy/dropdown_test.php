<?php
// Dropdown Test Page for APS Dream Home
$page_title = 'Dropdown Test - APS Dream Home';
$page_description = 'Testing dropdown functionality';
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/apsdreamhomefinal';
$current_url = $base_url . $_SERVER['REQUEST_URI'];

// Include the professional header
require_once 'includes/templates/professional_header.php';
?>

<!-- Test Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h1 class="display-4 fw-bold text-dark">🔧 Dropdown Test Page</h1>
                <p class="lead text-muted">Testing if all menu dropdowns are working properly</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Test Instructions -->
            <div class="col-md-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Test Instructions</h5>
                    </div>
                    <div class="card-body">
                        <ol class="mb-4">
                            <li><strong>Click on menu items above:</strong> Projects, Properties, About, Resources, Services</li>
                            <li><strong>Check if dropdown opens:</strong> Menu should expand showing submenu items</li>
                            <li><strong>Click submenu items:</strong> Should navigate to respective pages</li>
                            <li><strong>Test on mobile:</strong> Use browser dev tools to test mobile view</li>
                            <li><strong>Check console:</strong> Press F12 and check console for any errors</li>
                        </ol>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Expected Behavior:</h6>
                            <ul class="mb-0">
                                <li>✅ Dropdown menus should open smoothly</li>
                                <li>✅ All submenu links should be clickable</li>
                                <li>✅ Menu should close when clicking outside</li>
                                <li>✅ Mobile hamburger menu should work</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Check -->
            <div class="col-md-8 mx-auto mt-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Status Check</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-6 mb-3">
                                <div class="p-3 border rounded">
                                    <h6>Bootstrap Loaded</h6>
                                    <div id="bootstrapStatus" class="fw-bold text-muted">Checking...</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="p-3 border rounded">
                                    <h6>Dropdown Count</h6>
                                    <div id="dropdownCount" class="fw-bold text-muted">Checking...</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button class="btn btn-primary me-2" onclick="testDropdowns()">Test Dropdowns</button>
                            <button class="btn btn-secondary" onclick="window.location.reload()">Refresh Page</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Test dropdown functionality
function testDropdowns() {
    console.log('Testing dropdowns...');

    // Check if Bootstrap is loaded
    const bootstrapStatus = document.getElementById('bootstrapStatus');
    if (typeof bootstrap !== 'undefined') {
        bootstrapStatus.textContent = '✅ Loaded';
        bootstrapStatus.className = 'fw-bold text-success';
    } else {
        bootstrapStatus.textContent = '❌ Not Loaded';
        bootstrapStatus.className = 'fw-bold text-danger';
    }

    // Count dropdown elements
    const dropdownCount = document.getElementById('dropdownCount');
    const dropdownElements = document.querySelectorAll('.dropdown-toggle');
    dropdownCount.textContent = dropdownElements.length + ' found';
    dropdownCount.className = 'fw-bold ' + (dropdownElements.length > 0 ? 'text-success' : 'text-danger');

    // Log dropdown elements
    console.log('Found dropdown elements:', dropdownElements);
    console.log('Bootstrap version:', typeof bootstrap !== 'undefined' ? bootstrap : 'Not loaded');

    // Test each dropdown
    dropdownElements.forEach((dropdown, index) => {
        console.log(`Dropdown ${index + 1}:`, dropdown);
        console.log(`Dropdown ${index + 1} menu:`, dropdown.nextElementSibling);

        // Add test click handler
        dropdown.addEventListener('click', function() {
            console.log(`Dropdown ${index + 1} clicked!`);
        });
    });
}

// Run test on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(testDropdowns, 1000);
});
</script>

<style>
.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}
</style>

<?php
// Include Bootstrap JS for animations
echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
?>

</body>
</html>
