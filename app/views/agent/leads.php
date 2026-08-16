<?php
$leads = $leads ?? [];
$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$filter = $_GET['status'] ?? '';

$stats = ['total' => count($leads), 'new' => 0, 'contacted' => 0, 'qualified' => 0, 'converted' => 0, 'lost' => 0];
foreach ($leads as $l) {
    $s = strtolower($l['status'] ?? 'new');
    if (isset($stats[$s])) $stats[$s]++;
}

$filtered = $leads;
if ($filter && $filter !== 'all') {
    $filtered = array_filter($leads, function($l) use ($filter) {
        return strtolower($l['status'] ?? '') === $filter;
    });
}

$statusColors = [
    'new' => 'bg-primary',
    'contacted' => 'bg-info',
    'qualified' => 'bg-warning text-dark',
    'proposal' => 'bg-info text-white',
    'negotiation' => 'bg-warning text-dark',
    'converted' => 'bg-success',
    'lost' => 'bg-danger',
];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.agent-lead-stat { border: none; border-radius: 12px; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; text-decoration: none; }
.agent-lead-stat:hover { transform: translateY(-2px); }
.agent-lead-stat.active { border-color: #15803d; }
.agent-lead-stat .stat-num { font-size: 1.4rem; font-weight: 700; }
.agent-lead-row:hover { background: #f0fdf4; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" class="style-613"><i class="fas fa-users me-2"></i>My Leads</h4>
        <p class="text-muted mb-0">Manage your assigned leads and track conversions</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-success fs-6"><?= count($leads) ?> Total</span>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-2 mb-3">
    <div class="col">
        <a href="<?= $base ?>/agent/leads" class="card agent-lead-stat <?= !$filter || $filter === 'all' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-dark"><?= $stats['total'] ?></div>
                <div class="text-muted small">All</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/agent/leads?status=new" class="card agent-lead-stat <?= $filter === 'new' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-primary"><?= $stats['new'] ?></div>
                <div class="text-muted small">New</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/agent/leads?status=contacted" class="card agent-lead-stat <?= $filter === 'contacted' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-info"><?= $stats['contacted'] ?></div>
                <div class="text-muted small">Contacted</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/agent/leads?status=qualified" class="card agent-lead-stat <?= $filter === 'qualified' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-warning"><?= $stats['qualified'] ?></div>
                <div class="text-muted small">Qualified</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/agent/leads?status=converted" class="card agent-lead-stat <?= $filter === 'converted' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-success"><?= $stats['converted'] ?></div>
                <div class="text-muted small">Converted</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/agent/leads?status=lost" class="card agent-lead-stat <?= $filter === 'lost' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-danger"><?= $stats['lost'] ?></div>
                <div class="text-muted small">Lost</div>
            </div>
        </a>
    </div>
</div>

<?php if (empty($filtered)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div class="style-84169">
            <i class="fas fa-user-plus fa-2x" class="style-93945"></i>
        </div>
        <h5 class="text-muted"><?= $filter ? 'No leads with this status' : 'No leads assigned yet' ?></h5>
        <p class="text-muted mb-0">Leads assigned to you will appear here</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="style-15087">
                    <tr>
                        <th class="px-3 py-3" class="style-83276">Name</th>
                        <th class="px-3 py-3" class="style-83276">Contact</th>
                        <th class="px-3 py-3" class="style-83276">Property</th>
                        <th class="px-3 py-3" class="style-83276">Budget</th>
                        <th class="px-3 py-3" class="style-83276">Status</th>
                        <th class="px-3 py-3" class="style-83276">Date</th>
                        <th class="px-3 py-3" class="style-83276">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filtered as $lead): ?>
                    <tr class="agent-lead-row">
                        <td class="px-3">
                            <div class="d-flex align-items-center">
                                <div class="style-88532">
                                    <?= strtoupper(substr($lead['name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></strong>
                                    <?php if (!empty($lead['city'])): ?>
                                        <div><small class="text-muted"><?= htmlspecialchars($lead['city']) ?></small></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-3">
                            <div><i class="fas fa-phone me-1 text-muted"></i><?= htmlspecialchars($lead['phone'] ?? '-') ?></div>
                            <div><i class="fas fa-envelope me-1 text-muted"></i><small class="text-muted"><?= htmlspecialchars($lead['email'] ?? '-') ?></small></div>
                        </td>
                        <td class="px-3"><small><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></small></td>
                        <td class="px-3"><small class="fw-semibold"><?= !empty($lead['budget']) ? '₹' . number_format($lead['budget']) : '-' ?></small></td>
                        <td class="px-3">
                            <?php
                            $status = $lead['status'] ?? 'new';
                            $cls = $statusColors[$status] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?= $cls ?>"><?= ucfirst($status) ?></span>
                        </td>
                        <td class="px-3"><small class="text-muted"><?= date('d M Y', strtotime($lead['created_at'] ?? 'now')) ?></small></td>
                        <td class="px-3">
                            <div class="d-flex gap-1">
                                <a href="<?= $base ?>/agent/leads/<?= $lead['id'] ?>" class="btn btn-sm btn-outline-success" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (!empty($lead['phone'])): ?>
                                    <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="btn btn-sm btn-outline-primary" title="Call">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                <?php endif; ?>
                                <form method="POST" action="<?= $base ?>/agent/leads/<?= (int)$lead['id'] ?>/delete" class="style-35851" onsubmit="return confirm('Move this lead to trash?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
