<?php
/**
 * Modern UI/UX Showcase Page
 * Demonstrates the new design system components
 */

require_once __DIR__ . '/core/init.php';

// Include header
if (file_exists('includes/new_header.php')) {
    include 'includes/new_header.php';
} else {
    // Fallback to standard admin header if new_header is missing
    include 'includes/admin_navigation.php';
}
?>

<div class="container-fluid fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Modern UI/UX Showcase</h1>
            <p class="text-muted mb-0">Demonstration of the new design system components</p>
        </div>
        <div>
            <button class="btn-modern btn-modern-primary" onclick="window.history.back()">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </button>
        </div>
    </div>

    <!-- Design System Overview -->
    <div class="alert-modern alert-modern-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>New Design System:</strong> This page showcases the modern UI components available in your APS Dream Home system.
    </div>

    <!-- Color Palette -->
    <div class="card-modern mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-palette me-2"></i>Color Palette</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <div class="text-center">
                        <div style="width: 60px; height: 60px; background: var(--primary-color); border-radius: var(--radius-md); margin: 0 auto 10px;"></div>
                        <small class="text-muted">Primary</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <div style="width: 60px; height: 60px; background: var(--success-color); border-radius: var(--radius-md); margin: 0 auto 10px;"></div>
                        <small class="text-muted">Success</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <div style="width: 60px; height: 60px; background: var(--warning-color); border-radius: var(--radius-md); margin: 0 auto 10px;"></div>
                        <small class="text-muted">Warning</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <div style="width: 60px; height: 60px; background: var(--error-color); border-radius: var(--radius-md); margin: 0 auto 10px;"></div>
                        <small class="text-muted">Error</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <div style="width: 60px; height: 60px; background: var(--info-color); border-radius: var(--radius-md); margin: 0 auto 10px;"></div>
                        <small class="text-muted">Info</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <div style="width: 60px; height: 60px; background: var(--secondary-color); border-radius: var(--radius-md); margin: 0 auto 10px;"></div>
                        <small class="text-muted">Secondary</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Button Showcase -->
    <div class="card-modern mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-mouse-pointer me-2"></i>Modern Buttons</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 mb-3">
                <button class="btn-modern btn-modern-primary">
                    <i class="fas fa-star me-2"></i>Primary Button
                </button>
                <button class="btn-modern btn-modern-success">
                    <i class="fas fa-check me-2"></i>Success Button
                </button>
                <button class="btn-modern btn-modern-warning">
                    <i class="fas fa-exclamation me-2"></i>Warning Button
                </button>
                <button class="btn-modern btn-modern-error">
                    <i class="fas fa-times me-2"></i>Error Button
                </button>
                <button class="btn-modern btn-modern-outline">
                    <i class="fas fa-heart me-2"></i>Outline Button
                </button>
            </div>
        </div>
    </div>

    <!-- Form Components -->
    <div class="card-modern mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modern Form Components</h5>
        </div>
        <div class="card-body">
            <form class="form-modern">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group-modern">
                            <input type="text" class="form-control-modern" id="demoName" placeholder=" ">
                            <label for="demoName" class="form-label-modern">
                                <i class="fas fa-user me-2"></i>Full Name
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-modern">
                            <input type="email" class="form-control-modern" id="demoEmail" placeholder=" ">
                            <label for="demoEmail" class="form-label-modern">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group-modern">
                            <textarea class="form-control-modern" id="demoMessage" rows="4" placeholder=" "></textarea>
                            <label for="demoMessage" class="form-label-modern">
                                <i class="fas fa-comment me-2"></i>Message
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="dashboard-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users text-primary"></i>
            </div>
            <div class="stat-number">1,234</div>
            <div class="stat-label">Total Users</div>
            <div class="mt-3">
                <span class="badge-modern badge-modern-success">+12% this month</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line text-success"></i>
            </div>
            <div class="stat-number">₹45,678</div>
            <div class="stat-label">Revenue</div>
            <div class="mt-3">
                <span class="badge-modern badge-modern-primary">+8% growth</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-home text-warning"></i>
            </div>
            <div class="stat-number">567</div>
            <div class="stat-label">Properties</div>
            <div class="mt-3">
                <span class="badge-modern badge-modern-warning">89 available</span>
            </div>
        </div>
    </div>

    <!-- Alert Examples -->
    <div class="card-modern mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Modern Alerts</h5>
        </div>
        <div class="card-body">
            <div class="alert-modern alert-modern-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> Your action was completed successfully.
            </div>
            <div class="alert-modern alert-modern-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Warning!</strong> Please review the information before proceeding.
            </div>
            <div class="alert-modern alert-modern-error">
                <i class="fas fa-times-circle me-2"></i>
                <strong>Error!</strong> Something went wrong. Please try again.
            </div>
            <div class="alert-modern alert-modern-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Info:</strong> Here's some helpful information for you.
            </div>
        </div>
    </div>

    <!-- Badge Examples -->
    <div class="card-modern mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Modern Badges</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <span class="badge-modern badge-modern-primary">Primary Badge</span>
                <span class="badge-modern badge-modern-success">Success Badge</span>
                <span class="badge-modern badge-modern-warning">Warning Badge</span>
                <span class="badge-modern badge-modern-error">Error Badge</span>
            </div>
        </div>
    </div>

    <!-- Table Example -->
    <div class="card-modern mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Modern Table</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-3">
                                        <span class="initials">AB</span>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Abhay Singh</div>
                                        <small class="text-muted">Administrator</small>
                                    </div>
                                </div>
                            </td>
                            <td>abhay@apsdreamhome.com</td>
                            <td>Admin</td>
                            <td><span class="badge-modern badge-modern-success">Active</span></td>
                            <td>
                                <button class="btn-modern btn-modern-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-3">
                                        <span class="initials">JD</span>
                                    </div>
                                    <div>
                                        <div class="fw-bold">John Doe</div>
                                        <small class="text-muted">Sales Manager</small>
                                    </div>
                                </div>
                            </td>
                            <td>john@apsdreamhome.com</td>
                            <td>Manager</td>
                            <td><span class="badge-modern badge-modern-warning">Pending</span></td>
                            <td>
                                <button class="btn-modern btn-modern-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Animation Examples -->
    <div class="card-modern">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-magic me-2"></i>Animations & Effects</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">All components include smooth animations and hover effects for better user experience.</p>
            <div class="d-flex gap-3">
                <button class="btn-modern btn-modern-primary" onclick="this.classList.add('fade-in')">
                    <i class="fas fa-play me-2"></i>Fade In Animation
                </button>
                <button class="btn-modern btn-modern-success" onclick="this.classList.add('slide-in')">
                    <i class="fas fa-arrow-right me-2"></i>Slide In Animation
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Demo interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add some demo functionality
    const buttons = document.querySelectorAll('.btn-modern');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (this.textContent.includes('Animation')) {
                e.preventDefault();
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
