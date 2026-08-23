ï»¿<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-layer-group"></i> Rank Commission Benefits</h4>
        <a href="<?= BASE_URL ?>/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card aps-cp-card">
                <div class="card-header bg-success text-white"><i class="fas fa-edit"></i> Edit Rank Benefits</div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/commission/associate/structure/store">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2"><label class="form-label">Rank Name</label>
                            <select name="rank_name" class="form-select" required>
                                <option value="associate">Associate</option>
                                <option value="bronze">Bronze</option>
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                                <option value="platinum">Platinum</option>
                                <option value="diamond">Diamond</option>
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label">Direct Sale %</label><input type="number" step="0.01" name="commission_percentage" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Gen 1 Override %</label><input type="number" step="0.01" name="gen1_override_pct" class="form-control" value="0"></div>
                        <div class="mb-2"><label class="form-label">Gen 2 Override %</label><input type="number" step="0.01" name="gen2_override_pct" class="form-control" value="0"></div>
                        <div class="mb-2"><label class="form-label">Gen 3 Override %</label><input type="number" step="0.01" name="gen3_override_pct" class="form-control" value="0"></div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-list"></i> Rank Benefits</div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-striped mb-0">
                        <thead><tr><th>Rank</th><th>Direct %</th><th>Gen1 Override</th><th>Gen2 Override</th><th>Gen3 Override</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php if (empty($levels ?? [])): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-layer-group fa-3x text-muted mb-3 style-82835"></i>
                                    <h5 class="text-muted">No rank benefits found</h5>
                                    <p class="text-muted mb-3">Add rank benefits using the form on the left to configure commission percentages.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($levels ?? [] as $l): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($l['rank_name'] ?? '') ?></strong></td>
                                <td><?= (float)$l['commission_percentage'] ?>%</td>
                                <td><?= (float)($l['gen1_override_pct'] ?? 0) ?>%</td>
                                <td><?= (float)($l['gen2_override_pct'] ?? 0) ?>%</td>
                                <td><?= (float)($l['gen3_override_pct'] ?? 0) ?>%</td>
                                <td><span class="badge bg-primary">Edit to modify</span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>