<?php
$leads = $leads ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$per_page = $per_page ?? 25;
$total_pages = $total_pages ?? 1;
$filters = $filters ?? [];
$base = BASE_URL ?? '';
?>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.trash-header { background: linear-gradient(135deg, #dc2626, #ef4444); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.trash-header h4 { margin: 0; font-weight: 700; }
.lead-row:hover { background: #fef2f2; }
.restore-btn { color: #16a34a; cursor: pointer; }
.delete-btn { color: #dc2626; cursor: pointer; }
</style>

<div class="trash-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4><i class="fas fa-trash-alt me-2"></i>Lead Trash / Recycle Bin</h4>
            <p class="mb-0 mt-1 style-91298">Deleted leads are recoverable. Permanent deletion is irreversible.</p>
        </div>
        <div class="text-end">
            <div class="style-95615"><?= $total ?></div>
            <div class="style-50584">Deleted Leads</div>
        </div>
    </div>
</div>

<!-- Search -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= $base ?>/admin/leads/trash/list" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search deleted leads..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            <button type="submit" class="btn btn-danger"><i class="fas fa-search me-1"></i>Search</button>
            <a href="<?= $base ?>/admin/leads/trash/list" class="btn btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<!-- Leads Table -->
<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($leads)): ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="text-muted">Trash is empty!</h5>
                <p class="text-muted">No deleted leads found.</p>
                <a href="<?= $base ?>/admin/leads" class="btn btn-primary">Back to Leads</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Deleted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                        <tr class="lead-row">
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></div>
                                <small class="text-muted">ID: #<?= $lead['id'] ?></small>
                            </td>
                            <td>
                                <?php if (!empty($lead['phone'])): ?>
                                    <div><i class="fas fa-phone me-1"></i><?= htmlspecialchars($lead['phone'] ?? '') ?></div>
                                <?php endif; ?>
                                <?php if (!empty($lead['email'])): ?>
                                    <div><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($lead['email'] ?? '') ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status = $lead['status'] ?? 'new';
                                $statusCls = ['new'=>'bg-primary','contacted'=>'bg-info','qualified'=>'bg-warning','won'=>'bg-success','lost'=>'bg-danger'];
                                ?>
                                <span class="badge <?= $statusCls[$status] ?? 'bg-secondary' ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                            </td>
                            <td>
                                <?php $score = (int)($lead['lead_score'] ?? 0); ?>
                                <span class="badge <?= $score >= 70 ? 'bg-danger' : ($score >= 40 ? 'bg-warning' : 'bg-secondary') ?>"><?= $score ?></span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="fas fa-trash me-1"></i>
                                    <?= date('d M Y H:i', strtotime($lead['deleted_at'] ?? 'now')) ?>
                                </small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form method="POST" action="<?= $base ?>/admin/leads/<?= (int)$lead['id'] ?>/restore" class="style-35851">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Restore Lead">
                                            <i class="fas fa-undo"></i> Restore
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= $base ?>/admin/leads/<?= (int)$lead['id'] ?>/permanent-delete" class="style-35851" data-aps-confirm="PERMANENTLY delete this lead? This cannot be undone!">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Permanently Delete">
                                            <i class="fas fa-trash"></i> Delete Forever
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
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">Showing <?= ($page - 1) * $per_page + 1 ?>-<?= min($page * $per_page, $total) ?> of <?= $total ?></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>">Prev</a></li>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($filters['search'] ?? '') ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
