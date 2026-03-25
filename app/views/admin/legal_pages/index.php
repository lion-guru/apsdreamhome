<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login');
    exit;
}

// Set page variables
$$page_title = 'Legal Pages Management - Admin';
$active_page = 'legal_pages';

// Content for base layout
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-none d-md-block sidebar">
            <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Legal Pages Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previewTerms()">
                            <i class="fas fa-eye me-1"></i> Preview Terms
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previewPrivacy()">
                            <i class="fas fa-eye me-1"></i> Preview Privacy
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Messages -->
            <div id="statusMessage" class="alert" style="display: none;"></div>

            <!-- Terms and Conditions Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract me-2 text-primary"></i>
                        Terms and Conditions
                    </h5>
                    <span class="badge bg-info" id="termsLastUpdated">
                        Last updated: <?php echo date('F j, Y, g:i a', strtotime($terms_content['updated_at'])); ?>
                    </span>
                </div>
                <div class="card-body">
                    <form id="termsForm">
                        <div class="mb-3">
                            <label for="termsTitle" class="form-label fw-semibold">Page Title</label>
                            <input type="text" class="form-control" id="termsTitle" name="title"
                                value="<?php echo htmlspecialchars($terms_content['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="termsContent" class="form-label fw-semibold">Content</label>
                            <textarea class="form-control" id="termsContent" name="content" rows="15" required><?php echo htmlspecialchars($terms_content['content']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Use HTML formatting for better presentation. Changes will be reflected immediately on the public site.
                            </small>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Terms & Conditions
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Privacy Policy Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt me-2 text-success"></i>
                        Privacy Policy
                    </h5>
                    <span class="badge bg-info" id="privacyLastUpdated">
                        Last updated: <?php echo date('F j, Y, g:i a', strtotime($privacy_content['updated_at'])); ?>
                    </span>
                </div>
                <div class="card-body">
                    <form id="privacyForm">
                        <div class="mb-3">
                            <label for="privacyTitle" class="form-label fw-semibold">Page Title</label>
                            <input type="text" class="form-control" id="privacyTitle" name="title"
                                value="<?php echo htmlspecialchars($privacy_content['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="privacyContent" class="form-label fw-semibold">Content</label>
                            <textarea class="form-control" id="privacyContent" name="content" rows="15" required><?php echo htmlspecialchars($privacy_content['content']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Use HTML formatting for better presentation. Changes will be reflected immediately on the public site.
                            </small>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Update Privacy Policy
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Legal Compliance Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-gavel me-2 text-warning"></i>
                        Legal Compliance Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary">Current Status</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check-circle text-success me-2"></i>Terms page accessible at <code>/terms</code></li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Privacy page accessible at <code>/privacy</code></li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Both pages linked in footer navigation</li>
                                <li><i class="fas fa-info-circle text-info me-2"></i>Content editable by admin users</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-warning">Important Notes</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Review content regularly for compliance</li>
                                <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Consult legal counsel for major changes</li>
                                <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Keep backup of previous versions</li>
                                <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Notify users of significant policy changes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rich Text Editor -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    .editor-toolbar {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-bottom: none;
        padding: 0.5rem;
        border-radius: 0.375rem 0.375rem 0 0;
    }

    .editor-btn {
        background: none;
        border: 1px solid #dee2e6;
        padding: 0.25rem 0.5rem;
        margin: 0 0.125rem;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .editor-btn:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }

    .editor-btn.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    textarea {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Terms form submission
        document.getElementById('termsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            updateLegalPage('terms', this);
        });

        // Privacy form submission
        document.getElementById('privacyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            updateLegalPage('privacy', this);
        });

        // Auto-save functionality
        let autoSaveTimer;
        const autoSaveDelay = 30000; // 30 seconds

        function setupAutoSave(formId, pageType) {
            const form = document.getElementById(formId);
            const textarea = form.querySelector('textarea');

            textarea.addEventListener('input', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(() => {
                    autoSave(pageType, form);
                }, autoSaveDelay);
            });
        }

        setupAutoSave('termsForm', 'terms');
        setupAutoSave('privacyForm', 'privacy');
    });

    function updateLegalPage(pageType, form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        form.classList.add('loading');

        fetch('/admin/legal-pages/update-' + pageType, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', data.message);

                    // Update last updated timestamp
                    const timestampId = pageType + 'LastUpdated';
                    const timestamp = document.getElementById(timestampId);
                    if (timestamp && data.last_updated) {
                        timestamp.textContent = 'Last updated: ' + new Date(data.last_updated).toLocaleString();
                    }
                } else {
                    showMessage('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'An error occurred while updating content.');
            })
            .finally(() => {
                // Reset loading state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                form.classList.remove('loading');
            });
    }

    function autoSave(pageType, form) {
        const formData = new FormData(form);

        fetch('/admin/legal-pages/update-' + pageType, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show subtle notification
                    const timestampId = pageType + 'LastUpdated';
                    const timestamp = document.getElementById(timestampId);
                    if (timestamp && data.last_updated) {
                        timestamp.textContent = 'Last updated: ' + new Date(data.last_updated).toLocaleString() + ' (Auto-saved)';
                    }
                }
            })
            .catch(error => {
                console.error('Auto-save error:', error);
            });
    }

    function showMessage(type, message) {
        const messageDiv = document.getElementById('statusMessage');
        messageDiv.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
        messageDiv.textContent = message;
        messageDiv.style.display = 'block';

        // Auto-hide after 5 seconds
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }

    function previewTerms() {
        window.open('/terms', '_blank');
    }

    function previewPrivacy() {
        window.open('/privacy', '_blank');
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+S to save active form
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const activeElement = document.activeElement;
            const form = activeElement.closest('form');
            if (form) {
                form.dispatchEvent(new Event('submit'));
            }
        }
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/base.php';
echo $content;
?>