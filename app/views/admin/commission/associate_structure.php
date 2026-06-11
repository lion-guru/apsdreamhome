<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-layer-group"></i> Associate Commission Structure</h4>
        <a href="/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card aps-cp-card">
                <div class="card-header bg-success text-white"><i class="fas fa-plus"></i> Add Level</div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="/admin/commission/associate/structure/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2"><label class="form-label">Level Number</label><input type="number" name="level_number" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Level Name</label><input type="text" name="level_name" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Commission %</label><input type="number" step="0.01" name="commission_percentage" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Min Property Value</label><input type="number" step="0.01" name="min_property_value" class="form-control" value="0"></div>
                        <div class="mb-2"><label class="form-label">Max Property Value</label><input type="number" step="0.01" name="max_property_value" class="form-control" value="999999999.99"></div>
                        <div class="mb-2"><label class="form-label">Status</label>
                            <select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-list"></i> Levels</div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>Level</th><th>Name</th><th>%</th><th>Min Value</th><th>Max Value</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($levels ?? [] as $l): ?>
                            <tr>
                                <td><?= (int)$l['level_number'] ?></td>
                                <td><?= htmlspecialchars($l['level_name']) ?></td>
                                <td><?= (float)$l['commission_percentage'] ?>%</td>
                                <td>&#8377;<?= number_format((float)$l['min_property_value']) ?></td>
                                <td>&#8377;<?= number_format((float)$l['max_property_value']) ?></td>
                                <td><span class="badge bg-<?= $l['status']=='active'?'success':'secondary' ?>"><?= $l['status'] ?></span></td>
                                <td><a href="/admin/commission/associate/structure/delete/<?= $l['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
