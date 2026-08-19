<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-compress-alt me-2 text-warning"></i>Lead Deduplication</h4>
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5>Find Duplicate Leads</h5>
                    <form method="GET" action="<?= BASE_URL ?>/admin/crm/dedup" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <select name="field" class="form-select">
                                <option value="phone">Phone</option>
                                <option value="email">Email</option>
                                <option value="name">Name</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="threshold" class="form-select">
                                <option value="exact">Exact Match</option>
                                <option value="fuzzy">Fuzzy Match</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-warning w-100"><i class="fas fa-search me-1"></i>Find Duplicates</button>
                        </div>
                    </form>
                    <?php if (!empty($duplicates)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Lead 1</th><th>Lead 2</th><th>Match Field</th><th>Action</th></tr></thead>
                                <tbody>
                                    <?php foreach ($duplicates as $d): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($d['name1'] ?? $d['lead1_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($d['name2'] ?? $d['lead2_name'] ?? '') ?></td>
                                            <td><span class="badge bg-warning"><?= htmlspecialchars($d['match_type'] ?? $d['match_field'] ?? '') ?></span></td>
                                            <td>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/crm/dedup/merge" class="style-71727">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <input type="hidden" name="keep_id" value="<?= $d['id1'] ?? $d['lead1_id'] ?? '' ?>">
                                                    <input type="hidden" name="remove_id" value="<?= $d['id2'] ?? $d['lead2_id'] ?? '' ?>">
                                                    <button class="btn btn-sm btn-success" data-aps-confirm="Merge leads?"><i class="fas fa-code-branch me-1"></i>Merge</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <form method="POST" action="<?= BASE_URL ?>/admin/crm/dedup/bulk-merge">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button class="btn btn-sm btn-outline-warning" data-aps-confirm="Auto-merge all?"><i class="fas fa-bolt me-1"></i>Bulk Merge All</button>
                        </form>
                    <?php else: ?>
                        <p class="text-muted mb-0">Click "Find Duplicates" to search.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle me-1"></i>About Dedup</h6>
                    <p class="small text-muted mb-1">Duplicate leads waste time and skew analytics. Use this tool to find and merge duplicates.</p>
                    <p class="small text-muted mb-0">Merging combines all interactions, tasks, and deals from the removed lead into the kept lead.</p>
                </div>
            </div>
        </div>
    </div>
</div>