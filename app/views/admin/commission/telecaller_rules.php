ï»¿<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-gavel"></i> Telecaller Commission Rules</h4>
        <a href="<?= BASE_URL ?>/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header" class="style-3150"><i class="fas fa-plus"></i> Add Rule</div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/commission/telecaller/rules/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2"><label class="form-label">Rule Name</label><input type="text" name="rule_name" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Commission Type</label>
                            <select name="commission_type" class="form-select">
                                <option value="per_call">Per Call</option>
                                <option value="per_lead">Per Lead</option>
                                <option value="per_conversion">Per Conversion</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label">Fixed Amount (&#8377;)</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Percentage (if type=percentage)</label><input type="number" step="0.01" name="percentage" class="form-control"></div>
                        <div class="mb-2"><label class="form-label">Min Calls</label><input type="number" name="min_calls" class="form-control" value="0"></div>
                        <div class="mb-2"><label class="form-label">Target Type</label>
                            <select name="target_type" class="form-select">
                                <option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly" selected>Monthly</option>
                            </select>
                        </div>
                        <div class="mb-2 form-check"><input type="checkbox" name="is_active" class="form-check-input" checked id="a"><label class="form-check-label" for="a">Active</label></div>
                        <button type="submit" class="btn" class="style-3150"><i class="fas fa-save"></i> Save Rule</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-list"></i> Rules</div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-striped mb-0">
                        <thead><tr><th>Rule</th><th>Type</th><th>Amount</th><th>%</th><th>Min Calls</th><th>Target</th><th>Active</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php if (empty($rules ?? [])): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-gavel fa-3x text-muted mb-3" class="style-82835"></i>
                                    <h5 class="text-muted">No telecaller rules defined</h5>
                                    <p class="text-muted mb-3">Create commission rules for telecallers based on calls, leads, or conversions.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($rules as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['rule_name'] ?? '') ?></td>
                                <td><span class="badge bg-info"><?= $r['commission_type'] ?></span></td>
                                <td>&#8377;<?= number_format((float)$r['amount'],2) ?></td>
                                <td><?= $r['percentage'] ? (float)$r['percentage'].'%' : '-' ?></td>
                                <td><?= (int)$r['min_calls'] ?></td>
                                <td><?= $r['target_type'] ?></td>
                                <td><span class="badge bg-<?= $r['is_active']?'success':'secondary' ?>"><?= $r['is_active']?'Yes':'No' ?></span></td>
                                <td>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/commission/telecaller/rules/toggle/<?= $r['id'] ?>" class="style-71727">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-toggle-<?= $r['is_active']?'on':'off' ?>"></i></button>
                                    </form>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/commission/telecaller/rules/delete/<?= $r['id'] ?>" class="style-71727" data-aps-confirm="Delete rule #<?= $r['id'] ?>?">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
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
