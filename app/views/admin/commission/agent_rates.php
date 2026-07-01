<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-dollar-sign"></i> Agent Commission Rates</h4>
        <a href="/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card aps-cp-card">
                <div class="card-header bg-primary text-white"><i class="fas fa-plus"></i> Add Rate</div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="/admin/commission/agent-rates/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2"><label class="form-label">Min Sqft</label><input type="number" name="min_sqft" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Max Sqft</label><input type="number" name="max_sqft" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Commission Per Sqft (&#8377;)</label><input type="number" step="0.01" name="commission_per_sqft" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Commission %</label><input type="number" step="0.01" name="commission_percentage" class="form-control" value="0"></div>
                        <div class="mb-2"><label class="form-label">Status</label>
                            <select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-list"></i> Rate Cards</div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-striped mb-0">
                        <thead><tr><th>#</th><th>Sqft Range</th><th>Per Sqft</th><th>%</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($rates ?? [] as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td><?= (int)$r['min_sqft'] ?> - <?= (int)$r['max_sqft'] ?></td>
                                <td>&#8377;<?= number_format((float)$r['commission_per_sqft'],2) ?></td>
                                <td><?= (float)$r['commission_percentage'] ?>%</td>
                                <td><span class="badge bg-<?= $r['status']=='active'?'success':'secondary' ?>"><?= $r['status'] ?></span></td>
                                <td><a href="/admin/commission/agent-rates/delete/<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
