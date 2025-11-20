<?php
// Logo Test Page for APS Dream Home
$page_title = 'Logo Test - APS Dream Home';
$page_description = 'Testing logo display';
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/apsdreamhome';
$current_url = $base_url . $_SERVER['REQUEST_URI'];

// Include the professional header
require_once 'includes/templates/professional_header.php';
?>

<!-- Logo Test Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h1 class="display-4 fw-bold text-dark">🎨 Logo Display Test</h1>
                <p class="lead text-muted">Testing APS Dream Home logo and branding</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Logo Information -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Logo Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Logo Path:</strong>
                            <code>assets/images/logo/apslogo.png</code>
                        </div>
                        <div class="mb-3">
                            <strong>File Exists:</strong>
                            <span class="badge bg-<?php echo file_exists('assets/images/logo/apslogo.png') ? 'success' : 'danger'; ?>">
                                <?php echo file_exists('assets/images/logo/apslogo.png') ? '✅ Yes' : '❌ No'; ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>File Size:</strong>
                            <?php
                            if (file_exists('assets/images/logo/apslogo.png')) {
                                echo number_format(filesize('assets/images/logo/apslogo.png')) . ' bytes';
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </div>
                        <div class="mb-3">
                            <strong>Company Name:</strong>
                            <span class="fw-bold text-primary">
                                <?php
                                $site_title = getSiteSetting('site_title', 'APS Dream Homes');
                                echo htmlspecialchars(str_replace(' Pvt Ltd', '', $site_title));
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logo Preview -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-image me-2"></i>Logo Preview</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $logoPath = getSiteSetting('logo_path', 'assets/images/logo/apslogo.png');
                        if (!empty($logoPath) && file_exists($logoPath)) {
                            echo '<img src="' . $logoPath . '" alt="APS Dream Homes Logo" class="img-fluid mb-3" style="max-height: 150px;">';
                            echo '<div class="alert alert-success">';
                            echo '<i class="fas fa-check-circle me-2"></i>Logo is loading correctly!';
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-warning">';
                            echo '<i class="fas fa-exclamation-triangle me-2"></i>Logo file not found at: ' . $logoPath;
                            echo '</div>';
                            echo '<div class="text-center mt-3">';
                            echo '<i class="fas fa-home fa-4x text-muted"></i>';
                            echo '<p class="mt-2 text-muted">Fallback icon shown</p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Available Logo Files -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-folder me-2"></i>Available Logo Files</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php
                            $logoDir = 'assets/images/logo/';
                            if (is_dir($logoDir)) {
                                $files = scandir($logoDir);
                                foreach ($files as $file) {
                                    if ($file !== '.' && $file !== '..' && !is_dir($logoDir . $file)) {
                                        $filePath = $logoDir . $file;
                                        $fileSize = filesize($filePath);
                                        echo '<div class="col-md-3 col-sm-6">';
                                        echo '<div class="card border">';
                                        echo '<div class="card-body text-center p-2">';
                                        echo '<img src="' . $filePath . '" alt="' . $file . '" class="img-fluid mb-2" style="max-height: 60px;">';
                                        echo '<small class="text-muted d-block">' . $file . '</small>';
                                        echo '<small class="text-muted">' . number_format($fileSize) . ' bytes</small>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                }
                            }
                            ?>
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

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
</style>

<?php
// Include Bootstrap JS for animations
echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
?>

</body>
</html>
