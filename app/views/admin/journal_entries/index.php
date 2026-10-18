<?php
// Journal Entries Index View
$entries = $entries ?? [];
$filters = $filters ?? ['search' => '', 'status' => '', 'type' => '', 'from_date' => '', 'to_date' => ''];
$page = $page ?? 1;
$perPage = $perPage ?? 20;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;
$statuses = $statuses ?? ['draft', 'approved', 'rejected'];
$types = $types ?? ['manual', 'system', 'adjustment', 'closing'];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Journal Entries</h1>
        <a href="<?= BASE_URL ?>/admin/journal-entries/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Journal Entry
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Journal #, Reference, Description...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($types as $t): ?>
                        <option value="<?= $t ?>" <?= $filters['type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($filters['from_date']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($filters['to_date']) ?>">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Journal #</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entries)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No journal entries found</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($entry['journal_number']) ?></code></td>
                            <td><?= date('M d, Y', strtotime($entry['entry_date'])) ?></td>
                            <td><?= htmlspecialchars($entry['reference_number'] ?? '—') ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($entry['entry_type'] ?? 'manual') ?></span></td>
                            <td class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($entry['description'] ?? '') ?></td>
                            <td class="text-end fw-bold text-success"><?= number_format((float)$entry['total_debit'], 2) ?></td>
                            <td class="text-end fw-bold text-danger"><?= number_format((float)$entry['total_credit'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= match($entry['status']) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' } ?>">
                                    <?= ucfirst($entry['status'] ?? 'draft') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($entry['created_by_name'] ?? '—') ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/journal-entries/<?= $entry['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if (($entry['status'] ?? 'draft') === 'draft'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/journal-entries/approve/<?= $entry['id'] ?>" class="d-inline" onsubmit="return confirm('Approve this journal entry?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="btn btn-outline-success" title="Approve"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/journal-entries/reject/<?= $entry['id'] ?>" class="d-inline" onsubmit="return confirm('Reject this journal entry?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Reject"><i class="fas fa-times"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="card-footer">
            <nav aria-label="Journal entries pagination">
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query(array_filter($filters)) ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query(array_filter($filters)) ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>