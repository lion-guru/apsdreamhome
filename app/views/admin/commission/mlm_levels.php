<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-level-up-alt"></i> MLM Commission Levels</h4>
        <a href="/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card aps-cp-card">
                <div class="card-header bg-success text-white"><i class="fas fa-plus"></i> Add Level</div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="/admin/commission/mlm/levels/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2"><label class="form-label">Plan ID</label><input type="number" name="plan_id" class="form-control" value="1" required></div>
                        <div class="mb-2"><label class="form-label">Level</label><input type="number" name="level" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Commission Rate %</label><input type="number" step="0.01" name="commission_rate" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Min users</label><input type="number" name="min_associates" class="form-control" value="0"></div>
                        <div class="mb-2"><label class="form-label">Direct %</label><input type="number" step="0.01" name="direct_percentage" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Min Business (&#8377;)</label><input type="number" step="0.01" name="min_business" class="form-control" value="0"></div>
                        <div class="mb-2"><label class="form-label">Max Business (&#8377;)</label><input type="number" step="0.01" name="max_business" class="form-control"></div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-list"></i> Levels (Plan <?= ($levels[0]['plan_id'] ?? '?') ?>)</div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-striped mb-0">
                        <thead><tr><th>Plan</th><th>Level</th><th>Name</th><th>Rate %</th><th>Min Assoc</th><th>Direct %</th><th>Min Biz</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($levels ?? [] as $l): ?>
                            <tr>
                                <td><?= (int)$l['plan_id'] ?></td>
                                <td><?= (int)$l['level'] ?></td>
                                <td><?= htmlspecialchars($l['name'] ?? '') ?></td>
                                <td><?= (float)$l['commission_rate'] ?>%</td>
                                <td><?= (int)$l['min_associates'] ?></td>
                                <td><?= (float)$l['direct_percentage'] ?>%</td>
                                <td>&#8377;<?= number_format((float)$l['min_business']) ?></td>
                                <td><a href="/admin/commission/mlm/levels/delete/<?= $l['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete level #<?= $l['id'] ?>?')"><i class="fas fa-trash"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
