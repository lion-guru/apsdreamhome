<?php $rules = $rules ?? []; ?>
<div class="container-fluid px-4">
    <h4 class="mb-4"><i class="fas fa-cogs text-primary me-2"></i>Commission Calculation Rules</h4>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo e($msg); ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Rule</th>
                    <th>Rank</th>
                    <th>Rate (%)</th>
                    <th>Priority</th>
                    <th>Active</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rules)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="fas fa-cogs fa-3x text-muted mb-3" class="style-82835"></i>
                        <h5 class="text-muted">No commission rules found</h5>
                        <p class="text-muted mb-3">Define commission rules to configure rank-based payout rates.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($rules as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['rule_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($r['mlm_rank'] ?? ''); ?></td>
                    <td>
                        <form method="POST" action="<?php echo BASE_URL; ?>/admin/mlm-settings/rules/update/<?php echo e($r['id']); ?>" class="d-flex align-items-center gap-2">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="number" step="0.01" name="rate_percentage" value="<?php echo e($r['rate_percentage']); ?>" class="form-control form-control-sm" class="style-31652">
                    </td>
                    <td><?php echo e($r['priority']); ?></td>
                    <td>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" <?php echo $r['is_active'] ? 'checked' : ''; ?>>
                        </div>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-sm btn-primary" aria-label="Save"><i class="fas fa-save"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
