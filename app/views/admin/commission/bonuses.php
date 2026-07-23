<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-gift"></i> Commission Bonuses</h4>
        <a href="<?= BASE_URL ?>/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card aps-cp-card">
                <div class="card-header bg-warning"><i class="fas fa-plus"></i> Add Bonus</div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/commission/bonuses/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2"><label class="form-label">Associate</label>
                            <select name="associate_id" class="form-select" required>
                                <option value="">Select</option>
                                <?php foreach ($users ?? [] as $a): ?>
                                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?> (<?= htmlspecialchars($a['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label">Bonus % (if fixed amount=0)</label><input type="number" step="0.01" name="bonus_percentage" class="form-control" value="0"></div>
                        <div class="mb-2"><label class="form-label">Fixed Amount (&#8377;)</label><input type="number" step="0.01" name="bonus_amount" class="form-control" value="0"></div>
                        <div class="mb-2"><label class="form-label">Achievement ID (optional)</label><input type="number" name="achievement_id" class="form-control"></div>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Add Bonus</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-list"></i> Bonus List</div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-striped mb-0">
                        <thead><tr><th>#</th><th>Associate</th><th>%</th><th>Amount</th><th>Achievement</th><th>Date</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php if (empty($bonuses ?? [])): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-gift fa-3x text-muted mb-3" style="opacity:0.2"></i>
                                    <h5 class="text-muted">No bonuses awarded yet</h5>
                                    <p class="text-muted mb-3">Award performance bonuses to top-performing associates to incentivize growth.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($bonuses as $b): ?>
                            <tr>
                                <td><?= $b['id'] ?></td>
                                <td><?= htmlspecialchars($b['associate_name'] ?? 'N/A') ?></td>
                                <td><?= (float)$b['bonus_percentage'] ?>%</td>
                                <td>&#8377;<?= number_format((float)$b['bonus_amount'],2) ?></td>
                                <td><?= $b['achievement_id'] ? 'A#' . $b['achievement_id'] : '-' ?></td>
                                <td><?= date('d-m-Y', strtotime($b['created_at'])) ?></td>
                                <td><form method="POST" action="<?= BASE_URL ?>/admin/commission/bonuses/delete/<?= $b['id'] ?>" style="display:inline" onsubmit="return confirm('Delete bonus #<?= $b['id'] ?>?')"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form></td>
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
