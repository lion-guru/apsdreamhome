<?php
$leads = $leads ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$currentStatus = $status ?? '';
$search = $search ?? '';
$base = BASE_URL ?? '';

$stats = ['total' => $total, 'new' => 0, 'contacted' => 0, 'qualified' => 0, 'site_visit' => 0, 'won' => 0, 'lost' => 0];
foreach ($leads as $l) {
    $s = strtolower($l['status'] ?? 'new');
    if (isset($stats[$s])) $stats[$s]++;
}

$statusColors = [
    'new' => 'bg-primary',
    'contacted' => 'bg-info',
    'qualified' => 'bg-warning text-dark',
    'site_visit' => 'bg-purple text-white',
    'proposal' => 'bg-info text-white',
    'negotiation' => 'bg-warning text-dark',
    'booking' => 'bg-success',
    'won' => 'bg-success',
    'lost' => 'bg-danger',
    'nurture' => 'bg-orange text-white',
];

if (empty($statusColors['site_visit'])) $statusColors['site_visit'] = 'bg-purple text-white';
if (empty($statusColors['booking'])) $statusColors['booking'] = 'bg-success';
if (empty($statusColors['nurture'])) $statusColors['nurture'] = 'bg-orange text-white';
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-lead-stat { border: none; border-radius: 12px; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; text-decoration: none; color: inherit; }
.emp-lead-stat:hover { transform: translateY(-2px); text-decoration: none; color: inherit; }
.emp-lead-stat.active { border-color: #1e40af; background: #eff6ff; }
.emp-lead-stat .stat-num { font-size: 1.4rem; font-weight: 700; }
.emp-lead-row:hover { background: #f0f4ff; }
.score-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
.score-cold { background: #94a3b8; }
.score-lukewarm { background: #f59e0b; }
.score-warm { background: #f97316; }
.score-hot { background: #ef4444; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" style="color:#1e40af;font-weight:700;">
            <i class="fas fa-user-tie me-2"></i>My Leads
        </h4>
        <p class="text-muted mb-0">Leads assigned to you — track and convert</p>
    </div>
    <div class="d-flex gap-2">
        <form class="d-flex gap-2" method="GET" action="<?= $base ?>/employee/leads">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0 ps-0" name="search" placeholder="Search name, phone, email..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <?php if ($currentStatus): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-sm btn-primary">Search</button>
            <?php if ($search || $currentStatus): ?>
                <a href="<?= $base ?>/employee/leads" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-2 mb-3">
    <div class="col">
        <a href="<?= $base ?>/employee/leads<?= $search ? '?search=' . urlencode($search) : '' ?>" class="card emp-lead-stat <?= !$currentStatus || $currentStatus === 'all' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-dark"><?= $total ?></div>
                <div class="text-muted small">All</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/employee/leads?status=new<?= $search ? '&search=' . urlencode($search) : '' ?>" class="card emp-lead-stat <?= $currentStatus === 'new' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-primary"><?= $stats['new'] ?></div>
                <div class="text-muted small">New</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/employee/leads?status=contacted<?= $search ? '&search=' . urlencode($search) : '' ?>" class="card emp-lead-stat <?= $currentStatus === 'contacted' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-info"><?= $stats['contacted'] ?></div>
                <div class="text-muted small">Contacted</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/employee/leads?status=qualified<?= $search ? '&search=' . urlencode($search) : '' ?>" class="card emp-lead-stat <?= $currentStatus === 'qualified' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-warning"><?= $stats['qualified'] ?></div>
                <div class="text-muted small">Qualified</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/employee/leads?status=site_visit<?= $search ? '&search=' . urlencode($search) : '' ?>" class="card emp-lead-stat <?= $currentStatus === 'site_visit' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num" style="color:#7c3aed;"><?= $stats['site_visit'] ?></div>
                <div class="text-muted small">Site Visit</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/employee/leads?status=won<?= $search ? '&search=' . urlencode($search) : '' ?>" class="card emp-lead-stat <?= $currentStatus === 'won' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-success"><?= $stats['won'] ?></div>
                <div class="text-muted small">Won</div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?= $base ?>/employee/leads?status=lost<?= $search ? '&search=' . urlencode($search) : '' ?>" class="card emp-lead-stat <?= $currentStatus === 'lost' ? 'active shadow-sm' : 'shadow-none' ?>">
            <div class="card-body py-2 px-3 text-center">
                <div class="stat-num text-danger"><?= $stats['lost'] ?></div>
                <div class="text-muted small">Lost</div>
            </div>
        </a>
    </div>
</div>

<?php if (empty($leads)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div style="width:80px;height:80px;border-radius:50%;background:#dbeafe;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <i class="fas fa-user-plus fa-2x" style="color:#1e40af;"></i>
        </div>
        <h5 class="text-muted"><?= $currentStatus ? 'No leads with this status' : ($search ? 'No leads match your search' : 'No leads assigned yet') ?></h5>
        <p class="text-muted mb-0">Leads assigned to you will appear here</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#eff6ff;">
                    <tr>
                        <th class="px-3 py-3" style="color:#1e40af;font-weight:600;">Name</th>
                        <th class="px-3 py-3" style="color:#1e40af;font-weight:600;">Contact</th>
                        <th class="px-3 py-3" style="color:#1e40af;font-weight:600;">Property</th>
                        <th class="px-3 py-3" style="color:#1e40af;font-weight:600;">Budget</th>
                        <th class="px-3 py-3" style="color:#1e40af;font-weight:600;">Score</th>
                        <th class="px-3 py-3" style="color:#1e40af;font-weight:600;">Status</th>
                        <th class="px-3 py-3" style="color:#1e40af;font-weight:600;">Date</th>
                        <th class="px-3 py-3" style="color:#1e40af;font-weight:600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                    <tr class="emp-lead-row">
                        <td class="px-3">
                            <div class="d-flex align-items-center">
                                <?php
                                $scoreVal = (int)($lead['lead_score'] ?? 0);
                                $scoreClass = $scoreVal >= 70 ? 'score-hot' : ($scoreVal >= 40 ? 'score-warm' : ($scoreVal >= 20 ? 'score-lukewarm' : 'score-cold'));
                                ?>
                                <span class="score-dot <?= $scoreClass ?> me-2" title="Score: <?= $scoreVal ?>"></span>
                                <div>
                                    <a href="<?= $base ?>/employee/leads/<?= (int)$lead['id'] ?>" class="text-decoration-none fw-semibold" style="color:#1e40af;">
                                        <?= htmlspecialchars($lead['name'] ?? 'Unknown') ?>
                                    </a>
                                    <?php if (!empty($lead['city'])): ?>
                                        <div><small class="text-muted"><?= htmlspecialchars($lead['city']) ?></small></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-3">
                            <div><i class="fas fa-phone me-1 text-muted"></i><?= htmlspecialchars($lead['phone'] ?? '-') ?></div>
                            <div><small class="text-muted"><?= htmlspecialchars($lead['email'] ?? '-') ?></small></div>
                        </td>
                        <td class="px-3"><small><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></small></td>
                        <td class="px-3"><small class="fw-semibold"><?= !empty($lead['budget']) ? '₹' . number_format((float)$lead['budget']) : '-' ?></small></td>
                        <td class="px-3">
                            <span class="badge <?= $scoreClass === 'score-hot' ? 'bg-danger' : ($scoreClass === 'score-warm' ? 'bg-warning text-dark' : ($scoreClass === 'score-lukewarm' ? 'bg-info' : 'bg-secondary')) ?>">
                                <?= $scoreVal ?>
                            </span>
                        </td>
                        <td class="px-3">
                            <?php
                            $leadStatus = $lead['status'] ?? 'new';
                            $cls = $statusColors[$leadStatus] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?= $cls ?>"><?= ucfirst(str_replace('_', ' ', $leadStatus)) ?></span>
                        </td>
                        <td class="px-3"><small class="text-muted"><?= date('d M Y', strtotime($lead['created_at'] ?? 'now')) ?></small></td>
                        <td class="px-3">
                            <div class="d-flex gap-1">
                                <a href="<?= $base ?>/employee/leads/<?= (int)$lead['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (!empty($lead['phone'])): ?>
                                    <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="btn btn-sm btn-outline-success" title="Call">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $base ?>/employee/leads?page=<?= $page - 1 ?>&status=<?= urlencode($currentStatus) ?>&search=<?= urlencode($search) ?>">Prev</a>
        </li>
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= $base ?>/employee/leads?page=<?= $i ?>&status=<?= urlencode($currentStatus) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $base ?>/employee/leads?page=<?= $page + 1 ?>&status=<?= urlencode($currentStatus) ?>&search=<?= urlencode($search) ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>
