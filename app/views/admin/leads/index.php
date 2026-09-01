<?php
$stats = $stats ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$per_page = $per_page ?? 25;
$total_pages = $total_pages ?? 1;
$filters = $filters ?? [];
$base = BASE_URL ?? '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-bullseye me-2"></i>Lead Management</h2>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/admin/leads/trash" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt me-1"></i>Trash</a>
            <a href="<?= $base ?>/admin/leads/import" class="btn btn-success btn-sm"><i class="fas fa-upload me-1"></i>Import</a>
            <a href="<?= $base ?>/admin/leads/export/csv" class="btn btn-outline-primary btn-sm"><i class="fas fa-download me-1"></i>Export CSV</a>
            <a href="<?= $base ?>/admin/leads/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Lead</a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-4 mb-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-primary fw-bold fs-4"><?= number_format($stats['total_leads'] ?? $total) ?></div>
                    <small class="text-muted">Total Leads</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 mb-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-info fw-bold fs-4"><?= $stats['today_leads'] ?? 0 ?></div>
                    <small class="text-muted">Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 mb-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-warning fw-bold fs-4"><?= $stats['week_leads'] ?? 0 ?></div>
                    <small class="text-muted">This Week</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 mb-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-danger fw-bold fs-4"><?= $stats['hot_leads'] ?? 0 ?></div>
                    <small class="text-muted">Hot Leads</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 mb-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-success fw-bold fs-4"><?= $stats['converted'] ?? 0 ?></div>
                    <small class="text-muted">Converted</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 mb-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-3">
                    <div class="text-secondary fw-bold fs-4"><?= ($stats['conversion_rate'] ?? 0) ?>%</div>
                    <small class="text-muted">Conv. Rate</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= $base ?>/admin/leads" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, phone, email..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <?php foreach (['new','contacted','qualified','proposal','negotiation','converted','closed','lost','dead'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Source</label>
                    <select name="source" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        <?php foreach (['website','referral','walk_in','phone_call','social_media','campaign','manual','api'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($filters['source'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (['low','medium','high','urgent'] as $p): ?>
                            <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i> Filter</button>
                </div>
                <div class="col-md-1">
                    <a href="<?= $base ?>/admin/leads" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions Bar (hidden by default) -->
    <div class="card border-0 shadow-sm mb-3" id="bulkActionsBar" style="display:none">
        <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
            <span class="fw-semibold"><span id="selectedCount">0</span> selected</span>
            <select id="bulkAction" class="form-select form-select-sm" style="min-width:140px">
                <option value="">Bulk Actions...</option>
                <option value="status">Change Status</option>
                <option value="assign">Assign To</option>
                <option value="delete">Delete Selected</option>
            </select>
            <select id="bulkStatus" class="form-select form-select-sm" style="display:none;min-width:140px">
                <?php foreach (['new','contacted','qualified','proposal','negotiation','converted','closed','lost','dead'] as $s): ?>
                    <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="bulkAssign" class="form-select form-select-sm" style="display:none;min-width:160px">
                <option value="">Select User...</option>
                <?php
                $bulkUsers = $assignees ?? $users ?? [];
                if (empty($bulkUsers)) {
                    try { $bulkUsers = \App\Core\Database\Database::getInstance()->getConnection()->query("SELECT id, name FROM users WHERE status='active' ORDER BY name ASC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC) ?: []; } catch(Throwable $e) { $bulkUsers = []; }
                }
                foreach ($bulkUsers as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-sm btn-warning" id="bulkApply" style="display:none">Apply</button>
            <button type="button" class="btn btn-sm btn-outline-danger" id="bulkDelete" style="display:none">Delete</button>
        </div>
    </div>

    <!-- Lead Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($leads)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No leads found!</p>
                    <a href="<?= $base ?>/admin/leads/create" class="btn btn-primary btn-sm">Add Lead</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="30"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                <th>Lead</th>
                                <th>Contact</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Assigned</th>
                                <th>Created</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead): ?>
                            <tr data-lead-id="<?= $lead['id'] ?>">
                                <td><input type="checkbox" class="form-check-input lead-checkbox" value="<?= $lead['id'] ?>"></td>
                                <td>
                                    <a href="<?= $base ?>/admin/leads/<?= $lead['id'] ?>" class="text-decoration-none fw-semibold">
                                        <?= htmlspecialchars($lead['name'] ?? 'N/A') ?>
                                    </a>
                                    <?php if (!empty($lead['company'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($lead['company'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($lead['phone'])): ?>
                                        <i class="fas fa-phone fa-sm text-muted"></i> <?= htmlspecialchars($lead['phone'] ?? '') ?><br>
                                    <?php endif; ?>
                                    <?php if (!empty($lead['email'])): ?>
                                        <i class="fas fa-envelope fa-sm text-muted"></i> <?= htmlspecialchars($lead['email'] ?? '') ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $lead['source'] ?? 'Direct'))) ?></span></td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'new' => 'primary', 'contacted' => 'info', 'qualified' => 'success',
                                        'proposal' => 'warning', 'negotiation' => 'dark', 'converted' => 'success',
                                        'closed' => 'secondary', 'lost' => 'danger', 'dead' => 'danger',
                                    ];
                                    $st = $lead['status'] ?? 'new';
                                    $color = $statusColors[$st] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($st) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $score = (int)($lead['lead_score'] ?? 0);
                                    $scoreColor = $score >= 70 ? 'danger' : ($score >= 40 ? 'warning' : 'secondary');
                                    ?>
                                    <span class="badge bg-<?= $scoreColor ?>"><?= $score ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($lead['assigned_name'])): ?>
                                        <small><?= htmlspecialchars($lead['assigned_name'] ?? '') ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Unassigned</small>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= date('d M Y', strtotime($lead['created_at'] ?? 'now')) ?></small></td>
                                <td>
                                    <a href="<?= $base ?>/admin/leads/<?= $lead['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= $base ?>/admin/leads/<?= $lead['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                    <small class="text-muted">Showing <?= (($page - 1) * $per_page) + 1 ?>â€“<?= min($page * $per_page, $total) ?> of <?= number_format($total) ?> leads</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query(array_filter($filters, fn($v) => $v !== null && $v !== '')) ?>">Prev</a>
                            </li>
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_filter($filters, fn($v) => $v !== null && $v !== '')) ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query(array_filter($filters, fn($v) => $v !== null && $v !== '')) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.lead-checkbox');
    const bulkBar = document.getElementById('bulkActionsBar');
    const countEl = document.getElementById('selectedCount');
    const bulkAction = document.getElementById('bulkAction');
    const bulkStatus = document.getElementById('bulkStatus');
    const bulkAssign = document.getElementById('bulkAssign');
    const bulkApply = document.getElementById('bulkApply');
    const bulkDelete = document.getElementById('bulkDelete');

    function getSelected() {
        return [...checkboxes].filter(cb => cb.checked).map(cb => parseInt(cb.value));
    }

    function updateUI() {
        const count = getSelected().length;
        countEl.textContent = count;
        bulkBar.style.display = count > 0 ? 'block' : 'none';
    }

    if (selectAll) selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateUI();
    });
    checkboxes.forEach(cb => cb.addEventListener('change', updateUI));

    bulkAction.addEventListener('change', function() {
        bulkStatus.style.display = this.value === 'status' ? 'inline-block' : 'none';
        bulkAssign.style.display = this.value === 'assign' ? 'inline-block' : 'none';
        bulkApply.style.display = this.value !== '' && this.value !== 'delete' ? 'inline-block' : 'none';
        bulkDelete.style.display = this.value === 'delete' ? 'inline-block' : 'none';
    });

    bulkApply.addEventListener('click', function() {
        const ids = getSelected();
        const action = bulkAction.value;
        let value = action === 'status' ? bulkStatus.value : bulkAssign.value;
        if (!value) { if (typeof showToast==='function') showToast('Select a value first', 'info'); else alert('Select a value'); return; }
        if (typeof showLoader==='function') showLoader();
        fetch('<?= $base ?>/admin/leads/bulk-action', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'},
            body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>&action=' + encodeURIComponent(action) + '&value=' + encodeURIComponent(value) + '&ids=' + ids.join(',')
        }).then(r => r.json()).then(d => {
            if (d.success) location.reload();
            else { if (typeof showToast==='function') showToast(d.error || 'Failed', 'danger'); else alert(d.error||'Failed'); }
        }).catch(err => console.error('Request failed:', err))
          .finally(() => { if (typeof hideLoader==='function') hideLoader(); });
    });

    bulkDelete.addEventListener('click', function() {
        const ids = getSelected();
        if (!ids.length) return;
        apsConfirm('Delete ' + ids.length + ' leads? This cannot be undone.').then(function(ok) {
            if (!ok) return;
            if (typeof showLoader==='function') showLoader();
            fetch('<?= $base ?>/admin/leads/bulk-action', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?? '' ?>'},
                body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>&action=delete&ids=' + ids.join(',')
            }).then(r => r.json()).then(d => {
                if (d.success) location.reload();
                else { if (typeof showToast==='function') showToast(d.error || 'Failed', 'danger'); else alert(d.error||'Failed'); }
            }).catch(err => console.error('Request failed:', err))
              .finally(() => { if (typeof hideLoader==='function') hideLoader(); });
        });
    });
});
</script>
