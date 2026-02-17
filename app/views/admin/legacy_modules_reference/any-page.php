<?php
require_once __DIR__ . '/core/init.php';

// Include header
include 'admin_header.php';
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>

            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fw-bold"><?php echo $mlSupport->translate('Page Title'); ?></h1>
                </div>

                <!-- Verify CSRF token for POST requests -->
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                        die($mlSupport->translate('CSRF token validation failed'));
                    }
                }
                ?>

                <!-- Your page content starts here -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <?php echo $mlSupport->translate('Add your content here...'); ?>
                    </div>
                </div>

            </main>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
