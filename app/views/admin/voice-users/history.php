<?php $calls = $calls ?? []; $agents = $agents ?? []; $pagination = $pagination ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Call History</h4>
</div>
<div class="card mb-3">
    <div class="card-body aps-cp-card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="failed" <?= ($_GET['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="busy" <?= ($_GET['status'] ?? '') === 'busy' ? 'selected' : '' ?>>Busy</option>
                    <option value="no_answer" <?= ($_GET['status'] ?? '') === 'no_answer' ? 'selected' : '' ?>>No Answer</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Agent</label>
                <select name="agent_id" class="form-select">
                    <option value="">All Agents</option>
                    <?php foreach ($agents as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= (($_GET['agent_id'] ?? '') == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search"></i> Filter</button>
            </div>
        </form>
    </div>
</div>
<div class="card aps-cp-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Agent</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Date/Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($calls)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No call records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($calls as $i => $c): ?>
                            <tr>
                                <td><?= ($pagination['offset'] ?? 0) + $i + 1 ?></td>
                                <td><?= htmlspecialchars($c['customer_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($c['phone'] ?? $c['customer_phone'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($c['agent_name'] ?? $c['agent'] ?? 'Auto') ?></td>
                                <td><?= $c['duration'] ?? $c['call_duration'] ?? '0:00' ?></td>
                                <td><span class="badge bg-<?= ($c['status'] ?? '') === 'completed' ? 'success' : (($c['status'] ?? '') === 'failed' ? 'danger' : 'warning') ?>"><?= ucfirst($c['status'] ?? 'pending') ?></span></td>
                                <td><small><?= htmlspecialchars($c['created_at'] ?? $c['call_time'] ?? '-') ?></small></td>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/voice-users/history/<?= $c['id'] ?? 0 ?>" class="btn btn-sm btn-outline-info" title="Details"><i class="fas fa-info-circle"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if (!empty($pagination['total_pages']) && $pagination['total_pages'] > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= ($pagination['page'] ?? 1) <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= ($pagination['page'] ?? 1) - 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">Previous</a>
            </li>
            <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                <li class="page-item <?= $p === ($pagination['page'] ?? 1) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($pagination['page'] ?? 1) >= ($pagination['total_pages'] ?? 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= ($pagination['page'] ?? 1) + 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
