<?php
/**
 * Plots Availability View - APS Dream Homes
 * Migrated from resources/views/Views/plots-availability.php
 */

require_once __DIR__ . '/init.php';

$page_title = 'Plots Availability | APS Dream Homes';
$page_description = 'Explore live availability across APS Dream Homes colonies. Check which plots are available, booked, or sold in real time.';

// Sample plot data (to be replaced with dynamic source in future)
$plots = [
    [ 'number' => '1',  'status' => 'sold' ],
    [ 'number' => '2',  'status' => 'sold' ],
    [ 'number' => '3',  'status' => 'booked' ],
    [ 'number' => '4',  'status' => 'available' ],
    [ 'number' => '5',  'status' => 'available' ],
    [ 'number' => '6',  'status' => 'sold' ],
    [ 'number' => '7',  'status' => 'booked' ],
    [ 'number' => '8',  'status' => 'available' ],
    [ 'number' => '9',  'status' => 'sold' ],
    [ 'number' => '10', 'status' => 'available' ],
];

$summary = ['sold' => 0, 'booked' => 0, 'available' => 0];
foreach ($plots as $plot) {
    $summary[$plot['status']]++;
}

$layout = 'modern';

// Capture the content for layout injection
ob_start();
?>

<style>
.availability-hero {
    background: linear-gradient(135deg, rgba(30, 58, 138, 0.9), rgba(30, 64, 175, 0.9)), url('/assets/img/plots-hero.jpg') center/cover;
    color: white;
    padding: 60px 0;
    border-radius: 20px;
    margin-bottom: 40px;
}
.plot-card {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: none;
}
.legend-dot { display:inline-block; width: 14px; height: 14px; border-radius:50%; margin-right:6px; }
.legend-available { background:#22c55e; }
.legend-booked { background:#facc15; }
.legend-sold { background:#ef4444; }
.plot-status-available { background:#ecfdf5; color:#15803d; font-weight:600; }
.plot-status-booked { background:#fefce8; color:#ca8a04; font-weight:600; }
.plot-status-sold { background:#fef2f2; color:#b91c1c; font-weight:600; }
.plot-map-img { border-radius:16px; box-shadow:0 10px 30px rgba(30,64,175,0.15); max-width: 100%; height: auto; }
.info-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:999px; background:#f1f5f9; font-size:0.85rem; font-weight:600; color: #475569; }
</style>

<section class="availability-hero text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <span class="info-chip mb-3 bg-white"><i class="fas fa-certificate text-warning"></i> RERA Registered Projects</span>
                <h1 class="display-5 fw-bold mb-3">Live Plot Availability</h1>
                <p class="lead opacity-90">Stay updated with the latest inventory across APS Dream Homes colonies. Check real-time status and schedule site visits.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
                    <span class="badge bg-white text-dark px-3 py-2"><span class="legend-dot legend-available"></span>Available</span>
                    <span class="badge bg-white text-dark px-3 py-2"><span class="legend-dot legend-booked"></span>Booked</span>
                    <span class="badge bg-white text-dark px-3 py-2"><span class="legend-dot legend-sold"></span>Sold</span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="row">
    <div class="col-12">
        <div class="card plot-card mb-5">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h2 class="h5 mb-0">Suryoday Colony</h2>
                <span class="badge bg-light text-primary">Phase 1</span>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h5 class="fw-bold mb-3">Plot Status Summary</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="p-3 text-center border rounded-3 bg-light">
                                    <div class="h3 fw-bold text-success mb-0"><?= $summary['available'] ?></div>
                                    <small class="text-muted">Available</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 text-center border rounded-3 bg-light">
                                    <div class="h3 fw-bold text-warning mb-0"><?= $summary['booked'] ?></div>
                                    <small class="text-muted">Booked</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 text-center border rounded-3 bg-light">
                                    <div class="h3 fw-bold text-danger mb-0"><?= $summary['sold'] ?></div>
                                    <small class="text-muted">Sold</small>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3">Detailed Plot Status</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle plot-status-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Plot No.</th>
                                        <th>Current Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($plots as $plot): ?>
                                        <tr>
                                            <td class="fw-bold">#<?= $plot['number'] ?></td>
                                            <td>
                                                <span class="badge plot-status-<?= $plot['status'] ?> px-3 py-2 text-capitalize">
                                                    <?= $plot['status'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h5 class="fw-bold mb-3">Colony Layout Map</h5>
                        <div class="text-center">
                            <img src="/assets/img/suryoday-colony-map-sample.jpg" alt="Suryoday Colony Map" class="plot-map-img mb-3 border">
                            <div class="alert alert-info py-2 small">
                                <i class="fas fa-info-circle me-2"></i>Map numbering matches the table. For an interactive experience, contact our sales team.
                            </div>
                            <div class="d-grid gap-2">
                                <a href="/contact" class="btn btn-primary py-3">
                                    <i class="fas fa-calendar-check me-2"></i>Schedule a Site Visit
                                </a>
                                <a href="tel:+91XXXXXXXXXX" class="btn btn-outline-secondary py-2">
                                    <i class="fas fa-phone-alt me-2"></i>Call for Inquiry
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom scripts
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.plot-status-table tbody tr').forEach((row) => {
        row.addEventListener('mouseenter', () => row.classList.add('table-active'));
        row.addEventListener('mouseleave', () => row.classList.remove('table-active'));
    });
});
</script>
<?php
$scripts = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
