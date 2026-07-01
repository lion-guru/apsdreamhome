<?php
$page_title = $page_title ?? 'My Leads - APS Dream Home';
$leads = $leads ?? [];
$total_count = $total_count ?? 0;
$status_filter = $status_filter ?? '';
$search = $search ?? '';
$current_page_no = $current_page_no ?? 1;
$total_pages = $total_pages ?? 1;
$pagination_url = $pagination_url ?? '';

$statuses = [
    'new' => ['label' => 'New', 'color' => 'primary', 'icon' => 'fa-star'],
    'contacted' => ['label' => 'Contacted', 'color' => 'info', 'icon' => 'fa-phone'],
    'qualified' => ['label' => 'Qualified', 'color' => 'warning', 'icon' => 'fa-check-circle'],
    'site_visit' => ['label' => 'Site Visit', 'color' => 'purple', 'icon' => 'fa-map-marker-alt'],
    'proposal' => ['label' => 'Proposal', 'color' => 'pink', 'icon' => 'fa-file-alt'],
    'negotiation' => ['label' => 'Negotiation', 'color' => 'orange', 'icon' => 'fa-handshake'],
    'closed_won' => ['label' => 'Won', 'color' => 'success', 'icon' => 'fa-trophy'],
    'closed_lost' => ['label' => 'Lost', 'color' => 'secondary', 'icon' => 'fa-times-circle'],
    'nurture' => ['label' => 'Nurture', 'color' => 'teal', 'icon' => 'fa-seedling'],
];

$priorityColors = ['high' => 'danger', 'medium' => 'warning', 'low' => 'info'];
?>

<style>
    .pipeline-filter { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
    .pipeline-filter .badge { cursor: pointer; padding: 6px 14px; font-size: 0.8rem; border-radius: 20px; transition: all 0.2s; opacity: 0.7; }
    .pipeline-filter .badge:hover { opacity: 1; transform: translateY(-1px); }
    .pipeline-filter .badge.active { opacity: 1; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
    .lead-row { transition: all 0.2s; }
    .lead-row:hover { background: #f8fafc; }
    .lead-row td { vertical-align: middle; }
    .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
    .priority-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .search-box { max-width: 300px; }
    .crm-pipeline { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 20px; }
    .crm-stage { min-width: 120px; padding: 10px 14px; border-radius: 10px; text-align: center; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
    .crm-stage:hover { transform: translateY(-2px); }
    .crm-stage .count { font-size: 1.3rem; display: block; margin-top: 2px; }
    .empty-state { padding: 60px 20px; text-align: center; }
    .empty-state i { font-size: 4rem; opacity: 0.15; margin-bottom: 16px; }
</style>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-funnel-dollar text-primary me-2"></i>Lead Pipeline</h4>
            <small class="text-muted"><?= number_format($total_count) ?> total leads</small>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="<?= BASE_URL ?>/associate/leads/recalculate-all-scores" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-outline-purple btn-sm" style="border-color:#14b8a6;color:#14b8a6;" onclick="return confirm('Recalculate AI scores for all your leads?')">
                    <i class="fas fa-brain me-1"></i> Score All
                </button>
            </form>
            <a href="<?= BASE_URL ?>/associate/leads/add" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Lead
            </a>
        </div>
    </div>

    <!-- Pipeline Summary Cards -->
    <div class="crm-pipeline">
        <?php
        $pipelineCounts = [];
        try {
            $db = \App\Core\Database\Database::getInstance();
            $userId = $_SESSION['user_id'] ?? 0;
            $cntRows = $db->fetchAll("SELECT status, COUNT(*) as cnt FROM leads WHERE created_by = ? AND deleted_at IS NULL GROUP BY status", [$userId]);
            foreach ($cntRows as $r) $pipelineCounts[$r['status']] = (int)$r['cnt'];
        } catch (\Exception $e) {}
        ?>
        <div class="crm-stage" style="background: #eff6ff; color: #2563eb;" onclick="filterLeads('')">
            <i class="fas fa-layer-group"></i> All
            <span class="count"><?= number_format($total_count) ?></span>
        </div>
        <?php foreach ($statuses as $key => $s): ?>
        <div class="crm-stage <?= $status_filter === $key ? 'active' : '' ?>" 
             style="background: <?= $status_filter === $key ? 'var(--bs-' . $s['color'] . ')' : 'rgba(var(--bs-' . $s['color'] . '-rgb), 0.1)' ?>; color: <?= $status_filter === $key ? '#fff' : '' ?>;"
             onclick="filterLeads('<?= $key ?>')">
            <i class="fas <?= $s['icon'] ?>"></i> <?= $s['label'] ?>
            <span class="count"><?= $pipelineCounts[$key] ?? 0 ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Search -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form class="d-flex gap-2" method="GET" action="<?= BASE_URL ?>/associate/leads">
            <div class="input-group search-box">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0" name="q" placeholder="Search name, phone, email..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <?php if ($status_filter): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-outline-primary btn-sm">Search</button>
            <?php if ($search || $status_filter): ?>
                <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Leads Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($leads)): ?>
                <div class="empty-state">
                    <i class="fas fa-funnel-dollar text-muted d-block"></i>
                    <h5 class="text-muted">No leads found</h5>
                    <p class="text-muted mb-3">
                        <?= $search ? 'No results for "' . htmlspecialchars($search) . '"' : 'Start building your pipeline by adding leads' ?>
                    </p>
                    <a href="<?= BASE_URL ?>/associate/leads/add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Your First Lead
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Lead</th>
                                <th>Contact</th>
                                <th>Interest</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Score</th>
                                <th>Follow-up</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead): ?>
                            <tr class="lead-row">
                                <td>
                                    <a href="<?= BASE_URL ?>/associate/leads/<?= $lead['id'] ?>" class="text-decoration-none fw-bold text-dark">
                                        <?= htmlspecialchars($lead['name']) ?>
                                    </a>
                                    <?php if ($lead['source']): ?>
                                        <br><small class="text-muted"><i class="fas fa-link me-1"></i><?= htmlspecialchars($lead['source']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($lead['phone'] ?: '—') ?></div>
                                    <?php if ($lead['email']): ?>
                                        <div><small class="text-muted"><?= htmlspecialchars($lead['email']) ?></small></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lead['property_interest']): ?>
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($lead['property_interest']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                    <?php if ($lead['budget_range']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($lead['budget_range']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $s = $statuses[$lead['status']] ?? $statuses['new']; ?>
                                    <span class="status-badge bg-<?= $s['color'] ?> text-white">
                                        <i class="fas <?= $s['icon'] ?> me-1"></i><?= $s['label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="priority-dot bg-<?= $priorityColors[$lead['priority']] ?? 'secondary' ?>"></span>
                                    <?= ucfirst($lead['priority'] ?? 'medium') ?>
                                </td>
                                <td>
                                    <?php
                                    $score = (int)($lead['lead_score'] ?? 0);
                                    $scoreColor = $score >= 70 ? 'success' : ($score >= 40 ? 'warning' : 'secondary');
                                    ?>
                                    <span class="text-<?= $scoreColor ?> fw-bold"><?= $score ?></span>
                                </td>
                                <td>
                                    <?php if ($lead['next_activity_date']): ?>
                                        <?php
                                        $nextDate = strtotime($lead['next_activity_date']);
                                        $isOverdue = $nextDate < time();
                                        ?>
                                        <small class="<?= $isOverdue ? 'text-danger fw-bold' : 'text-muted' ?>">
                                            <i class="fas fa-clock me-1"></i><?= date('M d', $nextDate) ?>
                                            <?php if ($isOverdue): ?><span class="badge bg-danger ms-1">Overdue</span><?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= BASE_URL ?>/associate/leads/<?= $lead['id'] ?>" class="btn btn-sm btn-outline-primary" title="View / Update">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="btn btn-sm btn-outline-success" title="Call">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <small class="text-muted">Page <?= $current_page_no ?> of <?= $total_pages ?></small>
                    <div class="d-flex gap-1">
                        <?php if ($current_page_no > 1): ?>
                            <a href="<?= $pagination_url ?>page=<?= $current_page_no - 1 ?>" class="btn btn-sm btn-outline-secondary">Prev</a>
                        <?php endif; ?>
                        <?php if ($current_page_no < $total_pages): ?>
                            <a href="<?= $pagination_url ?>page=<?= $current_page_no + 1 ?>" class="btn btn-sm btn-outline-secondary">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function filterLeads(status) {
    var url = '<?= BASE_URL ?>/associate/leads';
    var params = [];
    if (status) params.push('status=' + status);
    var q = document.querySelector('input[name="q"]');
    if (q && q.value) params.push('q=' + encodeURIComponent(q.value));
    if (params.length) url += '?' + params.join('&');
    window.location.href = url;
}
</script>
