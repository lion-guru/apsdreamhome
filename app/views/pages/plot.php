<?php
/**
 * Plot Availability Page - APS Dream Homes
 * Modern Layout Integrated
 */

require_once __DIR__ . '/init.php';

$page_title = 'Plot Availability | APS Dream Homes';
$layout = 'modern';

// Database Interaction
$plot_categories = [];
$plots = [];

try {
    $db = \App\Core\App::database();
    // Retrieve plot categories
    $categories = $db->fetchAll("SELECT DISTINCT category_name FROM plot_categories ORDER BY category_name ASC");
    foreach ($categories as $cat) {
        $plot_categories[] = $cat['category_name'];
    }

    // Retrieve plots
    $plots = $db->fetchAll("SELECT * FROM plots ORDER BY plot_id ASC");
} catch (Exception $e) {
    error_log('Plot availability database error: ' . $e->getMessage());
}

// Fallback data if DB is empty for demo
if (empty($plots)) {
    $plots = [
        ['plot_id' => 'P101', 'status' => 'available', 'breadth' => '30', 'length' => '50', 'total_size' => '1500', 'description' => 'Corner plot near main gate.'],
        ['plot_id' => 'P102', 'status' => 'booked', 'breadth' => '30', 'length' => '40', 'total_size' => '1200', 'description' => 'East facing residential plot.'],
        ['plot_id' => 'P103', 'status' => 'sold', 'breadth' => '25', 'length' => '40', 'total_size' => '1000', 'description' => 'Near community park.'],
        ['plot_id' => 'P104', 'status' => 'hold', 'breadth' => '40', 'length' => '60', 'total_size' => '2400', 'description' => 'Commercial plot on 60ft road.'],
    ];
}

function get_plot_status_badge($status) {
    switch ($status) {
        case 'available': return '<span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> Available</span>';
        case 'booked': return '<span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 rounded-pill"><i class="fas fa-clock me-1"></i> Booked</span>';
        case 'hold': return '<span class="badge bg-info-subtle text-info border border-info px-3 py-2 rounded-pill"><i class="fas fa-hand-paper me-1"></i> Hold</span>';
        case 'sold': return '<span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i> Sold</span>';
        default: return '<span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2 rounded-pill">Unknown</span>';
    }
}

ob_start();
?>

<!-- Page Header -->
<div class="page-header py-5 bg-dark text-white text-center mb-0 position-relative overflow-hidden plot-header">
    <div class="container py-5 mt-4" data-aos="fade-up">
        <h1 class="display-3 fw-bold mb-3">Plot Availability</h1>
        <p class="lead opacity-75 mb-0 mx-auto header-desc">Real-time status of residential and commercial plots across our premium projects.</p>
    </div>
</div>

<div class="container py-5 mt-5">
    <!-- Legend & Filters -->
    <div class="row mb-5 align-items-end g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" data-aos="fade-right">
                <h6 class="fw-bold text-dark mb-3 text-uppercase small letter-spacing-1">Status Legend</h6>
                <div class="d-flex flex-wrap gap-4">
                    <div class="d-flex align-items-center">
                        <div class="legend-color bg-success rounded-circle me-2"></div>
                        <span class="small fw-medium">Available</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="legend-color bg-warning rounded-circle me-2"></div>
                        <span class="small fw-medium">Booked</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="legend-color bg-info rounded-circle me-2"></div>
                        <span class="small fw-medium">Hold</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="legend-color bg-danger rounded-circle me-2"></div>
                        <span class="small fw-medium">Sold</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" data-aos="fade-left">
                <label class="fw-bold text-dark mb-2 text-uppercase small letter-spacing-1">Filter by Category</label>
                <select class="form-select border-light bg-light rounded-3 py-2" id="categoryFilter">
                    <option value="ALL">All Categories</option>
                    <?php foreach ($plot_categories as $cat): ?>
                        <option value="<?= h($cat) ?>"><?= h($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Plot Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5" data-aos="fade-up">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="plotTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-4 border-0 text-secondary fw-bold text-uppercase small">Plot ID</th>
                        <th class="py-4 border-0 text-secondary fw-bold text-uppercase small">Status</th>
                        <th class="py-4 border-0 text-secondary fw-bold text-uppercase small">Dimensions (B x L)</th>
                        <th class="py-4 border-0 text-secondary fw-bold text-uppercase small">Total Size</th>
                        <th class="py-4 border-0 text-secondary fw-bold text-uppercase small">Description</th>
                        <th class="pe-4 py-4 border-0 text-secondary fw-bold text-uppercase small text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plots as $plot): ?>
                    <tr class="plot-row" data-category="<?= h($plot['category'] ?? 'ALL') ?>">
                        <td class="ps-4 py-4 fw-bold text-dark"><?= h($plot['plot_id']) ?></td>
                        <td class="py-4"><?= get_plot_status_badge($plot['status']) ?></td>
                        <td class="py-4 text-secondary"><?= h($plot['breadth']) ?>' x <?= h($plot['length']) ?>'</td>
                        <td class="py-4"><span class="fw-bold text-primary"><?= h($plot['total_size']) ?></span> <small class="text-muted">sq.ft</small></td>
                        <td class="py-4 text-muted small"><?= h($plot['description']) ?></td>
                        <td class="pe-4 py-4 text-center">
                            <?php if ($plot['status'] === 'available'): ?>
                                <a href="book_plot.php?id=<?= $plot['plot_id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                    <i class="fas fa-bookmark me-1"></i> Book Now
                                </a>
                            <?php else: ?>
                                <button class="btn btn-light btn-sm rounded-pill px-3 text-muted disabled">
                                    <i class="fas fa-lock me-1"></i> Reserved
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="row g-4 mt-2">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="p-4 bg-primary-subtle rounded-4 h-100 border-start border-primary border-4">
                <h6 class="fw-bold text-primary mb-2">Immediate Registration</h6>
                <p class="small text-secondary mb-0">Plots marked as available are ready for immediate registration and possession.</p>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="p-4 bg-success-subtle rounded-4 h-100 border-start border-success border-4">
                <h6 class="fw-bold text-success mb-2">Bank Finance</h6>
                <p class="small text-secondary mb-0">Loan facilities are available from major nationalized and private banks for all plots.</p>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="p-4 bg-info-subtle rounded-4 h-100 border-start border-info border-4">
                <h6 class="fw-bold text-info mb-2">Site Visit</h6>
                <p class="small text-secondary mb-0">Free site visits are arranged daily. Contact our relationship managers to schedule one.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .plot-header {
        background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('<?= get_asset_url('breadcromb.jpg', 'images') ?>') center/cover no-repeat;
    }
    .header-desc {
        max-width: 700px;
    }
    .legend-color {
        width: 12px;
        height: 12px;
    }
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
    .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1) !important; }
    .bg-info-subtle { background-color: rgba(13, 202, 240, 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }
    .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1) !important; }
    .letter-spacing-1 { letter-spacing: 1px; }

    .plot-row {
        transition: background-color 0.2s ease;
    }
    .plot-row:hover {
        background-color: #f8f9fa !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filter = document.getElementById('categoryFilter');
    const rows = document.querySelectorAll('.plot-row');

    if (filter) {
        filter.addEventListener('change', function() {
            const selected = this.value;
            rows.forEach(row => {
                if (selected === 'ALL' || row.dataset.category === selected) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
?>
