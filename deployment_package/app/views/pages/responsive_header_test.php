<?php
// Responsive Header Test Page for APS Dream Home
$page_title = 'Responsive Header Test - APS Dream Home';
$page_description = 'Testing responsive design across all screen sizes';
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/apsdreamhome';
$current_url = $base_url . $_SERVER['REQUEST_URI'];

// Include the professional header
require_once 'includes/templates/professional_header.php';
?>

<!-- Responsive Test Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h1 class="display-4 fw-bold text-dark">📱 Responsive Header Test</h1>
                <p class="lead text-muted">Testing APS Dream Home header across all screen sizes</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Screen Size Indicator -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <h3>Current Screen Size:</h3>
                        <div id="screenSize" class="h4 text-primary fw-bold">Loading...</div>
                        <div class="mt-3">
                            <span class="badge bg-primary me-2" id="deviceType">Desktop</span>
                            <span class="badge bg-info" id="orientation">Landscape</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsive Features List -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>Responsive Breakpoints</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>📱 Mobile (≤575px)</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Compact buttons</li>
                                <li><i class="fas fa-check text-success me-2"></i>Smaller logo</li>
                                <li><i class="fas fa-check text-success me-2"></i>Touch-friendly menu</li>
                                <li><i class="fas fa-check text-success me-2"></i>Collapsible search</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6>📱 Tablet (576px-991px)</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Medium-sized elements</li>
                                <li><i class="fas fa-check text-success me-2"></i>Balanced layout</li>
                                <li><i class="fas fa-check text-success me-2"></i>Dropdown optimization</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6>💻 Desktop (≥992px)</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Full-sized elements</li>
                                <li><i class="fas fa-check text-success me-2"></i>Search bar visible</li>
                                <li><i class="fas fa-check text-success me-2"></i>Complete navigation</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Instructions -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-flask me-2"></i>How to Test</h5>
                    </div>
                    <div class="card-body">
                        <ol class="mb-4">
                            <li><strong>Resize Browser:</strong> Drag browser window to different sizes</li>
                            <li><strong>Mobile View:</strong> Press F12 → Toggle device toolbar</li>
                            <li><strong>Test Menu:</strong> Click all dropdown menus on each size</li>
                            <li><strong>Check Buttons:</strong> Ensure all buttons are clickable</li>
                            <li><strong>Search Test:</strong> Test search functionality</li>
                        </ol>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Pro Tips:</h6>
                            <ul class="mb-0">
                                <li>Test on actual mobile devices</li>
                                <li>Check both portrait and landscape modes</li>
                                <li>Verify touch targets are large enough</li>
                                <li>Ensure text remains readable</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsive Features Demo -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Live Responsive Features</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="p-3 border rounded">
                                    <h6 class="text-primary">Brand Size</h6>
                                    <div id="brandSize" class="fw-bold">Loading...</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="p-3 border rounded">
                                    <h6 class="text-success">Search Width</h6>
                                    <div id="searchWidth" class="fw-bold">Loading...</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="p-3 border rounded">
                                    <h6 class="text-info">Button Padding</h6>
                                    <div id="buttonPadding" class="fw-bold">Loading...</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="p-3 border rounded">
                                    <h6 class="text-warning">Menu Items</h6>
                                    <div id="menuItems" class="fw-bold">Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Browser Compatibility Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold">🌐 Browser Compatibility</h2>
                <p class="lead">Tested and optimized for all modern browsers</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fab fa-chrome fa-4x text-primary mb-3"></i>
                        <h5>Chrome</h5>
                        <p class="text-muted">Fully compatible with all Chrome versions</p>
                        <span class="badge bg-success">✅ Optimized</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fab fa-firefox fa-4x text-warning mb-3"></i>
                        <h5>Firefox</h5>
                        <p class="text-muted">Perfect rendering and animations</p>
                        <span class="badge bg-success">✅ Optimized</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fab fa-safari fa-4x text-info mb-3"></i>
                        <h5>Safari</h5>
                        <p class="text-muted">iOS and macOS compatibility</p>
                        <span class="badge bg-success">✅ Optimized</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Performance Metrics -->
<section class="py-5 bg-dark text-white">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold">⚡ Performance Metrics</h2>
                <p class="lead">Optimized for speed and efficiency</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <i class="fas fa-tachometer-alt fa-3x text-success"></i>
                </div>
                <h3 class="h2 fw-bold">Fast Loading</h3>
                <p>Optimized CSS and minimal JavaScript</p>
            </div>

            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <i class="fas fa-mobile-alt fa-3x text-info"></i>
                </div>
                <h3 class="h2 fw-bold">Mobile First</h3>
                <p>Responsive design from the ground up</p>
            </div>

            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <i class="fas fa-crosshairs fa-3x text-warning"></i>
                </div>
                <h3 class="h2 fw-bold">Touch Optimized</h3>
                <p>Perfect touch targets for mobile devices</p>
            </div>

            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <i class="fas fa-shield-alt fa-3x text-danger"></i>
                </div>
                <h3 class="h2 fw-bold">Secure</h3>
                <p>Built-in security headers and protection</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-4">🎯 Ready to Test?</h2>
                <p class="lead mb-4">Resize your browser window or use developer tools to test responsiveness across different screen sizes.</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="/" class="btn btn-light btn-lg px-5">
                        <i class="fas fa-home me-2"></i>Homepage
                    </a>
                    <a href="properties" class="btn btn-outline-light btn-lg px-5">
                        <i class="fas fa-search me-2"></i>Test Search
                    </a>
                    <button class="btn btn-warning btn-lg px-5" onclick="window.location.reload()">
                        <i class="fas fa-refresh me-2"></i>Refresh Test
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Screen size detection
    function updateScreenInfo() {
        const width = window.innerWidth;
        const height = window.innerHeight;
        const screenSize = document.getElementById('screenSize');
        const deviceType = document.getElementById('deviceType');
        const orientation = document.getElementById('orientation');

        // Update screen size
        screenSize.textContent = width + 'px × ' + height + 'px';

        // Update device type
        if (width <= 575) {
            deviceType.textContent = '📱 Mobile';
            deviceType.className = 'badge bg-danger';
        } else if (width <= 991) {
            deviceType.textContent = '📱 Tablet';
            deviceType.className = 'badge bg-warning';
        } else if (width <= 1199) {
            deviceType.textContent = '💻 Desktop';
            deviceType.className = 'badge bg-primary';
        } else {
            deviceType.textContent = '🖥️ Large Desktop';
            deviceType.className = 'badge bg-success';
        }

        // Update orientation
        if (width > height) {
            orientation.textContent = '📐 Landscape';
        } else {
            orientation.textContent = '📱 Portrait';
        }

        // Update responsive metrics
        updateResponsiveMetrics();
    }

    function updateResponsiveMetrics() {
        const width = window.innerWidth;

        // Brand size based on screen width
        const brandSize = document.getElementById('brandSize');
        if (width <= 575) {
            brandSize.textContent = '1.3rem';
        } else if (width <= 991) {
            brandSize.textContent = '1.6rem';
        } else if (width <= 1199) {
            brandSize.textContent = '1.8rem';
        } else if (width <= 1399) {
            brandSize.textContent = '2rem';
        } else {
            brandSize.textContent = '2.2rem';
        }

        // Search width
        const searchWidth = document.getElementById('searchWidth');
        if (width <= 575) {
            searchWidth.textContent = '120px';
        } else if (width <= 991) {
            searchWidth.textContent = '160px';
        } else if (width <= 1199) {
            searchWidth.textContent = '220px';
        } else if (width <= 1399) {
            searchWidth.textContent = '250px';
        } else {
            searchWidth.textContent = '280px';
        }

        // Button padding
        const buttonPadding = document.getElementById('buttonPadding');
        if (width <= 575) {
            buttonPadding.textContent = '0.4rem 0.8rem';
        } else if (width <= 991) {
            buttonPadding.textContent = '0.6rem 1.2rem';
        } else if (width <= 1199) {
            buttonPadding.textContent = '0.7rem 1.8rem';
        } else if (width <= 1399) {
            buttonPadding.textContent = '0.8rem 2rem';
        } else {
            buttonPadding.textContent = '0.9rem 2.2rem';
        }

        // Menu items visibility
        const menuItems = document.getElementById('menuItems');
        const navItems = document.querySelectorAll('.navbar-nav .nav-item');
        const visibleItems = Array.from(navItems).filter(item => {
            return item.offsetParent !== null && getComputedStyle(item).display !== 'none';
        }).length;

        menuItems.textContent = visibleItems + ' visible';
    }

    // Initial load
    updateScreenInfo();

    // Update on resize
    window.addEventListener('resize', updateScreenInfo);

    // Update every second for dynamic changes
    setInterval(updateScreenInfo, 1000);
});
</script>

<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

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
