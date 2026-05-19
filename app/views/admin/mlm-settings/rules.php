<?php $rules = $rules ?? []; ?>
<div class="container-fluid px-4">
    <h4 class="mb-4"><i class="fas fa-cogs text-primary me-2"></i>Commission Calculation Rules</h4>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo $msg; ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
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
                <?php foreach ($rules as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['rule_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['mlm_rank']); ?></td>
                    <td>
                        <form method="POST" action="<?php echo BASE_URL; ?>/admin/mlm-settings/rules/update/<?php echo $r['id']; ?>" class="d-flex align-items-center gap-2">
                            <input type="number" step="0.01" name="rate_percentage" value="<?php echo $r['rate_percentage']; ?>" class="form-control form-control-sm" style="width:80px">
                    </td>
                    <td><?php echo $r['priority']; ?></td>
                    <td>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" <?php echo $r['is_active'] ? 'checked' : ''; ?>>
                        </div>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
