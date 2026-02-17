<?php
// plots-availability.php
// Modern interactive plot status page for APS Dream Homes

require_once __DIR__ . '/../../includes/db_connection.php';
require_once __DIR__ . '/../../includes/enhanced_universal_template.php';

$page_title = 'Plots Availability | APS Dream Homes';
$page_description = 'Explore live availability across APS Dream Homes colonies. Check which plots are available, booked, or sold in real time.';

$template = new EnhancedUniversalTemplate();
$template->setTitle($page_title)
    ->setDescription($page_description)
    ->addMeta('keywords', 'plots availability, APS Dream Homes, plot status, Gorakhpur real estate')
    ->addCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css')
    ->addCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css')
    ->addCustomCss(<<<'CSS'
.availability-hero {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9)), url('assets/img/plots-hero.jpg') center/cover;
    color: white;
    padding: 80px 0;
}
.plot-card {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    border: none;
}
.legend-dot { display:inline-block; width: 14px; height: 14px; border-radius:50%; margin-right:6px; }
.legend-available { background:#22c55e; }
.legend-booked { background:#facc15; }
.legend-sold { background:#ef4444; }
.plot-status-available { background:#ecfdf5; color:#15803d; font-weight:600; }
.plot-status-booked { background:#fefce8; color:#ca8a04; font-weight:600; }
.plot-status-sold { background:#fef2f2; color:#b91c1c; font-weight:600; }
.plot-map-img { border-radius:16px; box-shadow:0 10px 30px rgba(30,64,175,0.15); }
.info-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:999px; background:#f1f5f9; font-size:0.85rem; font-weight:600; }
CSS
    )->addJS('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', true, false);

ob_start();

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
?>

<section class="availability-hero text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <span class="info-chip mb-3"><i class="fas fa-certificate text-warning"></i> RERA Registered Projects</span>
                <h1 class="display-5 fw-bold mb-3">Live Plot Availability</h1>
                <p class="lead opacity-85">Stay updated with the latest inventory across APS Dream Homes colonies. Filter availability status, explore layouts, and schedule a site visit.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
                    <span class="info-chip"><span class="legend-dot legend-available"></span>Available</span>
                    <span class="info-chip"><span class="legend-dot legend-booked"></span>Booked</span>
                    <span class="info-chip"><span class="legend-dot legend-sold"></span>Sold</span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <h1 class="mb-4 text-center text-primary fw-bold">Plots Availability</h1>
    <p class="lead text-center mb-5">Check real-time status of plots in our colonies. See which plots are available, booked, or sold out.</p>
    <div class="card plot-card mb-5">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Suryoday Colony</h2>
            <span class="badge bg-light text-primary">Phase 1</span>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h5>Plot Status Summary</h5>
                    <ul class="list-group mb-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="legend-dot legend-available"></span>Available</span>
                            <span class="fw-bold text-success"><?= $summary['available'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="legend-dot legend-booked"></span>Booked</span>
                            <span class="fw-bold text-warning"><?= $summary['booked'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="legend-dot legend-sold"></span>Sold</span>
                            <span class="fw-bold text-danger"><?= $summary['sold'] ?></span>
                        </li>
                    </ul>
                    <h6 class="mb-2">Plots Status Table</h6>
                    <table class="table table-bordered plot-status-table">
                        <thead class="table-light">
                        <tr><th>Plot No.</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($plots as $plot): ?>
                            <tr>
                                <td><?= $plot['number'] ?></td>
                                <td class="plot-status-<?= $plot['status'] ?> text-capitalize"><?= ucfirst($plot['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6">
                    <h5 class="mb-3">Colony Map (Sample)</h5>
                    <img src="assets/img/suryoday-colony-map-sample.jpg" alt="Suryoday Colony Map" class="plot-map-img mb-3">
                    <div class="small text-muted">*Map numbering matches table above. For interactive map, contact admin.</div>
                    <a href="mailto:sales@apsdreamhomes.com" class="btn btn-outline-primary btn-sm mt-3">
                        <i class="fas fa-calendar-check me-1"></i>Schedule a Site Visit
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Repeat above block for other colonies/projects as needed -->
</div>
<?php
$content = ob_get_clean();

$template->addCustomJs(<<<'JS'
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.plot-status-table tbody tr').forEach((row) => {
        row.addEventListener('mouseenter', () => row.classList.add('table-active'));
        row.addEventListener('mouseleave', () => row.classList.remove('table-active'));
    });
});
JS
);

page($content, $page_title);
?>
