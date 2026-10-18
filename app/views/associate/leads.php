<?php
$page_title = $page_title ?? __('assoc_leads_title', [], 'My Leads - APS Dream Home');
$leads = $leads ?? [];
$total_count = $total_count ?? 0;
$status_filter = $status_filter ?? '';
$search = $search ?? '';
$current_page_no = $current_page_no ?? 1;
$total_pages = $total_pages ?? 1;
$pagination_url = $pagination_url ?? '';

$statuses = [
    'new' => ['label' => __('assoc_status_new', [], 'New'), 'color' => 'primary', 'icon' => 'fa-star'],
    'contacted' => ['label' => __('assoc_status_contacted', [], 'Contacted'), 'color' => 'info', 'icon' => 'fa-phone'],
    'qualified' => ['label' => __('assoc_status_qualified', [], 'Qualified'), 'color' => 'warning', 'icon' => 'fa-check-circle'],
    'site_visit' => ['label' => __('assoc_status_site_visit', [], 'Site Visit'), 'color' => 'info', 'icon' => 'fa-map-marker-alt'],
    'proposal' => ['label' => __('assoc_status_proposal', [], 'Proposal'), 'color' => 'danger', 'icon' => 'fa-file-alt'],
    'negotiation' => ['label' => __('assoc_status_negotiation', [], 'Negotiation'), 'color' => 'warning', 'icon' => 'fa-handshake'],
    'closed_won' => ['label' => __('assoc_status_won', [], 'Won'), 'color' => 'success', 'icon' => 'fa-trophy'],
    'closed_lost' => ['label' => __('assoc_status_lost', [], 'Lost'), 'color' => 'secondary', 'icon' => 'fa-times-circle'],
    'nurture' => ['label' => __('assoc_status_nurture', [], 'Nurture'), 'color' => 'success', 'icon' => 'fa-seedling'],
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
            <h4 class="mb-1"><i class="fas fa-funnel-dollar text-primary me-2"></i><?= __('assoc_leads_pipeline', [], 'Lead Pipeline') ?></h4>
            <small class="text-muted"><?= number_format($total_count) ?> <?= __('assoc_leads_total_leads', [], 'total leads') ?></small>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="<?= BASE_URL ?>/associate/leads/recalculate-all-scores" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-sm" onclick="return confirm('Recalculate AI scores for all your leads?')">
                    <i class="fas fa-brain me-1"></i> <?= __('assoc_leads_score_all', [], 'Score All') ?>
                </button>
            </form>
            <a href="<?= BASE_URL ?>/associate/leads/add" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> <?= __('assoc_leads_add_new', [], 'Add New Lead') ?>
            </a>
        </div>
    </div>

    <!-- Pipeline Summary Cards -->
    <div class="crm-pipeline">
        <?php
        $pipelineCounts = $pipeline_counts ?? [];
        ?>
        <div class="crm-stage" onclick="filterLeads('')">
            <i class="fas fa-layer-group"></i> <?= __('assoc_leads_all', [], 'All') ?>
            <span class="count"><?= number_format($total_count) ?></span>
        </div>
        <?php foreach ($statuses as $key => $s): ?>
        <div class="crm-stage <?= $status_filter === $key ? 'active' : '' ?>"
             onclick="filterLeads('<?= $key ?>')">
            <i class="fas <?= $s['icon'] ?>"></i> <?= $s['label'] ?>
            <span class="count"><?= $pipelineCounts[$key] ?? 0 ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Speed-to-lead SLA: untouched leads older than 30 min -->
    <?php if (!empty($sla_breach)): ?>
    <div class="alert alert-danger d-flex align-items-center mt-3 mb-0" role="alert">
        <i class="fas fa-stopwatch me-2"></i>
        <div><?= sprintf(__('assoc_leads_sla_breach', [], '%d leads waiting over 30 min for first response — call now!'), (int)$sla_breach) ?></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($stale_count)): ?>
    <div class="alert alert-warning d-flex align-items-center justify-content-between mt-3 mb-0" role="alert">
        <div><i class="fas fa-moon me-2"></i><?= sprintf(__('assoc_leads_stale', [], '%d leads quiet 14+ days'), (int)$stale_count) ?></div>
        <a href="<?= BASE_URL ?>/associate/leads/revive" class="btn btn-sm btn-warning"><?= __('assoc_leads_revive_go', [], 'Revive them') ?></a>
    </div>
    <?php endif; ?>

    <!-- Search -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form class="d-flex gap-2" method="GET" action="<?= BASE_URL ?>/associate/leads">
            <div class="input-group search-box">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0" name="q" placeholder="<?= __('assoc_leads_search_placeholder', [], 'Search name, phone, email, notes...') ?>" value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <?php if ($status_filter): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter ?? '') ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-outline-primary btn-sm"><?= __('assoc_leads_search_btn', [], 'Search') ?></button>
            <?php if ($search || $status_filter): ?>
                <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times"></i> <?= __('assoc_leads_clear', [], 'Clear') ?></a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Leads Table -->
    <div class="card border-0 shadow-sm mb-2 d-none" id="bulkBar"><div class="card-body py-2">
    <form method="POST" action="<?= BASE_URL ?>/associate/leads/bulk" id="bulkForm" class="d-flex flex-wrap gap-2 align-items-center">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <div id="bulkIds"></div>
        <strong><span id="bulkCount">0</span> <?= __('assoc_leads_selected', [], 'selected') ?></strong>
        <select name="bulk_action" class="form-select form-select-sm" style="width:auto">
            <option value="status"><?= __('assoc_leads_bulk_status', [], 'Set status') ?></option>
            <option value="assign"><?= __('assoc_leads_bulk_assign', [], 'Assign to') ?></option>
        </select>
        <select name="bulk_status" class="form-select form-select-sm" style="width:auto">
            <option value="contacted">contacted</option><option value="qualified">qualified</option><option value="site_visit">site_visit</option><option value="proposal">proposal</option><option value="negotiation">negotiation</option><option value="closed_won">closed_won</option><option value="closed_lost">closed_lost</option><option value="nurture">nurture</option><option value="new">new</option>
        </select>
        <input type="number" name="bulk_assign_to" class="form-control form-control-sm" style="width:110px" placeholder="<?= __('assoc_leads_bulk_userid', [], 'user id') ?>">
        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('<?= __('assoc_leads_bulk_confirm', [], 'Apply to selected leads?') ?>')"><?= __('assoc_leads_apply', [], 'Apply') ?></button>
    </form>
    </div></div>
    <script>
    (function() {
        function refreshBulk() {
            var boxes = document.querySelectorAll('.bulk-check:checked');
            document.getElementById('bulkCount').textContent = boxes.length;
            document.getElementById('bulkBar').classList.toggle('d-none', boxes.length === 0);
        }
        document.querySelectorAll('.bulk-check').forEach(function(b) {
            b.addEventListener('change', refreshBulk);
        });
        var bulkAll = document.getElementById('bulkAll');
        if (bulkAll) {
            bulkAll.addEventListener('change', function() {
                document.querySelectorAll('.bulk-check').forEach(function(b) { b.checked = bulkAll.checked; });
                refreshBulk();
            });
        }
        document.getElementById('bulkForm').addEventListener('submit', function() {
            var holder = document.getElementById('bulkIds');
            holder.innerHTML = '';
            document.querySelectorAll('.bulk-check:checked').forEach(function(b) {
                var h = document.createElement('input');
                h.type = 'hidden'; h.name = 'ids[]'; h.value = b.value;
                holder.appendChild(h);
            });
        });
    })();
    </script>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($leads)): ?>
                <div class="empty-state">
                    <i class="fas fa-funnel-dollar text-muted d-block"></i>
                    <h5 class="text-muted"><?= __('assoc_leads_empty', [], 'No leads found') ?></h5>
                    <p class="text-muted mb-3">
                        <?= $search ? __('assoc_leads_empty_search', [], 'No results for') . ' "' . htmlspecialchars($search ?? '') . '"' : __('assoc_leads_empty_add', [], 'Start building your pipeline by adding leads') ?>
                    </p>
            <a href="<?= BASE_URL ?>/associate/reports/business" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-chart-line me-1"></i> <?= __('assoc_leads_reports', [], 'Reports') ?>
            </a>
            <a href="<?= BASE_URL ?>/associate/leads/revive" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-fire me-1"></i> <?= __('assoc_leads_revive', [], 'Revive') ?>
            </a>
            <a href="<?= BASE_URL ?>/associate/leads/trash" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-trash-alt me-1"></i> <?= __('assoc_leads_trash', [], 'Trash') ?>
            </a>
            <a href="<?= BASE_URL ?>/associate/leads/add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> <?= __('assoc_leads_add_first', [], 'Add Your First Lead') ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width:36px"><input type="checkbox" class="form-check-input" id="bulkAll" title="Select all"></th>
                                <th><?= __('assoc_leads_th_lead', [], 'Lead') ?></th>
                                <th><?= __('assoc_leads_th_contact', [], 'Contact') ?></th>
                                <th><?= __('assoc_leads_th_interest', [], 'Interest') ?></th>
                                <th><?= __('assoc_leads_th_status', [], 'Status') ?></th>
                                <th><?= __('assoc_leads_th_priority', [], 'Priority') ?></th>
                                <th><?= __('assoc_leads_th_score', [], 'Score') ?></th>
                                <th><?= __('assoc_leads_th_followup', [], 'Follow-up') ?></th>
                                <th><?= __('assoc_leads_th_actions', [], 'Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead): ?>
                            <tr class="lead-row">
                                <td><input type="checkbox" class="form-check-input bulk-check" value="<?= (int)$lead['id'] ?>"></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/associate/leads/<?= $lead['id'] ?>" class="text-decoration-none fw-bold text-dark">
                                        <?= htmlspecialchars($lead['name'] ?? '') ?>
                                    </a>
                                    <?php if ($lead['source']): ?>
                                        <br><small class="text-muted"><i class="fas fa-link me-1"></i><?= htmlspecialchars($lead['source'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($lead['phone'] ?: '—') ?></div>
                                    <?php if ($lead['email']): ?>
                                        <div><small class="text-muted"><?= htmlspecialchars($lead['email'] ?? '') ?></small></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lead['property_interest']): ?>
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($lead['property_interest'] ?? '') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                    <?php if ($lead['budget_range']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($lead['budget_range'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $s = $statuses[$lead['status']] ?? $statuses['new']; ?>
                                    <span class="status-badge bg-<?= $s['color'] ?> text-white">
                                        <i class="fas <?= $s['icon'] ?> me-1"></i><?= $s['label'] ?>
                                    </span>
                                    <?php if (empty($lead['first_response_at']) && strtotime($lead['created_at'] ?? '') < time() - 1800): ?>
                                        <br><span class="badge bg-danger mt-1"><i class="fas fa-bolt me-1"></i><?= __('assoc_leads_respond_now', [], 'Respond now') ?></span>
                                    <?php endif; ?>
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
                                            <?php if ($isOverdue): ?><span class="badge bg-danger ms-1"><?= __('assoc_leads_overdue', [], 'Overdue') ?></span><?php endif; ?>
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
                                        <a href="tel:<?= htmlspecialchars($lead['phone'] ?? '') ?>" class="btn btn-sm btn-outline-success" title="Call">
                                            <i class="fas fa-phone"></i>
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>/associate/leads/<?= (int)$lead['id'] ?>/delete" onsubmit="return confirm('Move this lead to trash?')">
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

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <small class="text-muted"><?= __('assoc_leads_page_of', [], 'Page') ?> <?= $current_page_no ?> <?= __('assoc_leads_of', [], 'of') ?> <?= $total_pages ?></small>
                    <div class="d-flex gap-1">
                        <?php if ($current_page_no > 1): ?>
                            <a href="<?= esc_url($pagination_url ?? '') ?>page=<?= $current_page_no - 1 ?>" class="btn btn-sm btn-outline-secondary"><?= __('assoc_leads_prev', [], 'Prev') ?></a>
                        <?php endif; ?>
                        <?php if ($current_page_no < $total_pages): ?>
                            <a href="<?= esc_url($pagination_url ?? '') ?>page=<?= $current_page_no + 1 ?>" class="btn btn-sm btn-outline-secondary"><?= __('assoc_leads_next', [], 'Next') ?></a>
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
