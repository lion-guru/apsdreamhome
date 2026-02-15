<?php
require_once __DIR__ . '/core/init.php';

// Fetch manager-specific analytics with caching
require_once __DIR__ . '/../includes/performance_manager.php';
$perfManager = getPerformanceManager();

$totalProperties_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as count FROM properties", 3600);
$totalProperties = $totalProperties_data[0]['count'] ?? 0;

$totalBookings_data = $perfManager->executeCachedQuery("SELECT COUNT(*) as count FROM bookings", 3600);
$totalBookings = $totalBookings_data[0]['count'] ?? 0;

// Include header
include 'admin_header.php';
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>
            
            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fw-bold"><?php echo $mlSupport->translate('Manager Dashboard'); ?></h1>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body d-flex flex-column align-items-center text-center py-4">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-3">
                                    <i class="fas fa-building fa-2x"></i>
                                </div>
                                <h5 class="card-title fw-bold text-muted mb-2"><?php echo $mlSupport->translate('Total Properties'); ?></h5>
                                <h2 class="display-5 fw-bold mb-0"><?php echo number_format($totalProperties); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body d-flex flex-column align-items-center text-center py-4">
                                <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 mb-3">
                                    <i class="fas fa-calendar-check fa-2x"></i>
                                </div>
                                <h5 class="card-title fw-bold text-muted mb-2"><?php echo $mlSupport->translate('Total Bookings'); ?></h5>
                                <h2 class="display-5 fw-bold mb-0"><?php echo number_format($totalBookings); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>


