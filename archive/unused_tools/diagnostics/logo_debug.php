<?php
// Logo Debug Page for APS Dream Home

require_once dirname(__DIR__, 2) . '/app/helpers.php';

$page_title = 'Logo Debug - APS Dream Home';
$page_description = 'Debugging logo display issues';
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/apsdreamhomefinal';
$current_url = $base_url . $_SERVER['REQUEST_URI'];

// Include the professional header
require_once 'includes/templates/professional_header.php';
?>

<!-- Logo Debug Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h1 class="display-4 fw-bold text-dark">🔍 Logo Debug Page</h1>
                <p class="lead text-muted">Diagnosing logo display issues in APS Dream Home header</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Logo Path Information -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Logo Path Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Logo Path:</strong>
                            <code>assets/images/logo/apslogo.png</code>
                        </div>
                        <div class="mb-3">
                            <strong>Full URL:</strong>
                            <code><?php echo h($base_url); ?>/assets/images/logo/apslogo.png</code>
                        </div>
                        <div class="mb-3">
                            <strong>File Exists:</strong>
                            <span class="badge bg-<?php echo file_exists('assets/images/logo/apslogo.png') ? 'success' : 'danger'; ?>">
                                <?php echo file_exists('assets/images/logo/apslogo.png') ? '✅ Yes' : '❌ No'; ?>
                            </span>
                        </div>
                        <?php if (file_exists('assets/images/logo/apslogo.png')): ?>
                        <div class="mb-3">
                            <strong>File Size:</strong>
                            <?php echo number_format(filesize('assets/images/logo/apslogo.png')); ?> bytes
                        </div>
                        <div class="mb-3">
                            <strong>File Type:</strong>
                            <?php
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            echo h(finfo_file($finfo, 'assets/images/logo/apslogo.png'));
                            finfo_close($finfo);
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Logo Display Test -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-image me-2"></i>Logo Display Test</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $logoPath = 'assets/images/logo/apslogo.png';
                        if (file_exists($logoPath)) {
                            echo '<h6 class="mb-3">Logo should appear below:</h6>';
                            echo '<img src="' . h($logoPath) . '" alt="APS Dream Homes Logo" class="img-fluid mb-3 border" style="max-height: 100px; background: white; padding: 10px;">';
                            echo '<div class="alert alert-success">';
                            echo '<i class="fas fa-check-circle me-2"></i>Logo file is accessible and should be visible in header!';
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-warning">';
                            echo '<i class="fas fa-exclamation-triangle me-2"></i>Logo file not found at: ' . h($logoPath);
                            echo '</div>';
                            echo '<div class="text-center mt-3">';
                            echo '<i class="fas fa-home fa-4x text-muted"></i>';
                            echo '<p class="mt-2 text-muted">Fallback icon would show here</p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Header Brand Test -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-code me-2"></i>Header Brand HTML Output</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Generated HTML:</strong>
                        </div>
                        <pre class="bg-light p-3 border rounded"><code><?php
$site_title = getSiteSetting('site_title', 'APS Dream Homes');
$logoPath = getSiteSetting('logo_path', 'assets/images/logo/apslogo.png');

if (!empty($logoPath) && file_exists($logoPath)) {
    echo h('<img src="' . $logoPath . '" alt="' . $site_title . ' Logo" class="brand-logo" loading="lazy">');
} else {
    echo h('<div class="brand-icon" title="APS Dream Homes"><i class="fas fa-home" aria-hidden="true"></i></div>');
}
?></code></pre>
                    </div>
                </div>
            </div>

            <!-- Troubleshooting Steps -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Troubleshooting Steps</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>✅ If Logo Shows Here:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Logo file is working correctly</li>
                                    <li><i class="fas fa-check text-success me-2"></i>CSS styling is applied properly</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Header should display logo</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>❌ If Logo Doesn't Show:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-times text-danger me-2"></i>Check browser cache (Ctrl+F5)</li>
                                    <li><i class="fas fa-times text-danger me-2"></i>Verify file permissions</li>
                                    <li><i class="fas fa-times text-danger me-2"></i>Check CSS for display: none</li>
                                    <li><i class="fas fa-times text-danger me-2"></i>Try different logo file</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-3 p-3 bg-light rounded">
                            <h6><i class="fas fa-lightbulb me-2"></i>Quick Fix:</h6>
                            <p>If logo still doesn't show in header, try:</p>
                            <ol class="mb-0">
                                <li>Hard refresh browser (Ctrl+Shift+R)</li>
                                <li>Clear browser cache completely</li>
                                <li>Check if logo appears in header after refresh</li>
                                <li>If still not visible, check browser console for errors</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

pre {
    font-size: 0.875rem;
}

code {
    word-break: break-all;
}
</style>

<?php
// Include Bootstrap JS for animations
echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
?>

</body>
</html>
