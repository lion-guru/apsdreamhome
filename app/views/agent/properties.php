<?php
$properties = $properties ?? [];
$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$filter = $_GET['status'] ?? '';

$stats = ['total' => count($properties), 'active' => 0, 'sold' => 0, 'pending' => 0, 'inactive' => 0];
foreach ($properties as $p) {
    $s = strtolower($p['status'] ?? 'active');
    if (isset($stats[$s])) $stats[$s]++;
}

$filtered = $properties;
if ($filter && $filter !== 'all') {
    $filtered = array_filter($properties, function($p) use ($filter) {
        return strtolower($p['status'] ?? '') === $filter;
    });
}
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.agent-prop-stat { border: none; border-radius: 12px; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; text-decoration: none; }
.agent-prop-stat:hover { transform: translateY(-2px); }
.agent-prop-stat.active { border-color: #15803d; }
.agent-prop-stat .stat-num { font-size: 1.4rem; font-weight: 700; }
.agent-prop-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; overflow: hidden; }
.agent-prop-card:hover { border-color: #15803d; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transform: translateY(-1px); }
.agent-prop-card .prop-img { height: 160px; object-fit: cover; width: 100%; }
.agent-prop-card .prop-placeholder { height: 160px; background: linear-gradient(135deg,#15803d,#22c55e); display: flex; align-items: center; justify-content: center; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" class="style-613"><i class="fas fa-building me-2"></i>My Properties</h4>
        <p class="text-muted mb-0">Properties assigned to you or listed by you</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-success fs-6"><?= count($properties) ?> Properties</span>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-2 mb-3">
    <div class="col">
        <a href="<?= $base ?>/agent/properties" class="card agent-prop-stat <?= !$filter || $filter === 'all' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-dark"><?= $stats['total'] ?></div>
                <div class="text-muted small">All</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/agent/properties?status=active" class="card agent-prop-stat <?= $filter === 'active' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-success"><?= $stats['active'] ?></div>
                <div class="text-muted small">Active</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/agent/properties?status=sold" class="card agent-prop-stat <?= $filter === 'sold' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-danger"><?= $stats['sold'] ?></div>
                <div class="text-muted small">Sold</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/agent/properties?status=pending" class="card agent-prop-stat <?= $filter === 'pending' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-warning"><?= $stats['pending'] ?></div>
                <div class="text-muted small">Pending</div>
            </div>
        </a>
    </div>
</div>

<?php if (empty($filtered)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div class="style-33323">
            <i class="fas fa-home fa-2x" class="style-8693"></i>
        </div>
        <h5 class="text-muted"><?= $filter ? 'No properties with this status' : 'No properties yet' ?></h5>
        <p class="text-muted mb-0">Your assigned properties will appear here</p>
    </div>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($filtered as $prop): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card agent-prop-card h-100">
            <?php if (!empty($prop['image'])): ?>
                <img src="<?= htmlspecialchars($prop['image']) ?>" alt="<?= htmlspecialchars($prop['title'] ?? 'Property') ?>" class="prop-img">
            <?php else: ?>
                <div class="prop-placeholder">
                    <i class="fas fa-home fa-3x" class="style-22607"></i>
                </div>
            <?php endif; ?>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0 fw-semibold"><?= htmlspecialchars($prop['title'] ?? 'Property') ?></h6>
                    <?php
                    $status = $prop['status'] ?? 'active';
                    $sColor = $status === 'sold' ? 'bg-danger' : ($status === 'active' ? 'bg-success' : 'bg-warning text-dark');
                    ?>
                    <span class="badge <?= $sColor ?>"><?= ucfirst($status) ?></span>
                </div>
                <p class="text-muted mb-2 small"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($prop['location'] ?? $prop['city_name'] ?? '-') ?></p>
                <div class="d-flex gap-3 mb-2">
                    <?php if (!empty($prop['area_sqft'])): ?>
                        <span class="small text-muted"><i class="fas fa-ruler-combined me-1"></i><?= number_format($prop['area_sqft']) ?> sqft</span>
                    <?php endif; ?>
                    <?php if (!empty($prop['bedrooms'])): ?>
                        <span class="small text-muted"><i class="fas fa-bed me-1"></i><?= $prop['bedrooms'] ?> BHK</span>
                    <?php endif; ?>
                </div>
                <h5 class="style-613">â‚¹<?= number_format($prop['price'] ?? 0) ?></h5>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 d-flex gap-2">
                <a href="<?= $base ?>/properties/<?= $prop['id'] ?>" class="btn btn-sm btn-outline-success flex-grow-1" target="_blank">
                    <i class="fas fa-eye me-1"></i>View
                </a>
                <small class="text-muted align-self-center">Listed <?= date('d M Y', strtotime($prop['created_at'] ?? 'now')) ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
