ï»¿<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-chart-line"></i> Daily Revenue Commission</h4>
        <a href="<?= BASE_URL ?>/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-md-4"><div class="card bg-primary text-white text-center p-2"><h5>&#8377;<?= number_format((float)($summary['total_rev']??0)) ?></h5><small>Total Revenue</small></div></div>
        <div class="col-md-4"><div class="card bg-success text-white text-center p-2"><h5>&#8377;<?= number_format((float)($summary['total_comm']??0)) ?></h5><small>Total Commission</small></div></div>
        <div class="col-md-4"><div class="card bg-info text-white text-center p-2"><h5><?= (int)($summary['total_deals']??0) ?></h5><small>Total Deals</small></div></div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card aps-cp-card">
                <div class="card-header bg-primary text-white"><i class="fas fa-plus"></i> Add Daily Record</div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/commission/revenue/daily/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-2"><label class="form-label">Date</label><input type="date" name="stat_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                        <div class="mb-2"><label class="form-label">Agent</label>
                            <select name="agent_id" class="form-select" required>
                                <option value="">Select</option>
                                <?php foreach ($users ?? [] as $a): ?>
                                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label">Revenue (&#8377;)</label><input type="number" step="0.01" name="revenue" class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Deals</label><input type="number" name="deals" class="form-control" value="0"></div>
                        <div class="mb-2"><label class="form-label">Commission (&#8377;)</label><input type="number" step="0.01" name="commission" class="form-control" required></div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-table"></i> Daily Records</div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-striped mb-0">
                        <thead><tr><th>Date</th><th>Agent</th><th>Revenue</th><th>Deals</th><th>Commission</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php if (empty($daily ?? [])): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3" class="style-82835"></i>
                                    <h5 class="text-muted">No daily revenue records</h5>
                                    <p class="text-muted mb-3">Track daily agent revenue and commission earned using the form on the left.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($daily as $d): ?>
                            <tr>
                                <td><?= $d['stat_date'] ?></td>
                                <td><?= htmlspecialchars($d['agent_name'] ?? 'N/A') ?></td>
                                <td>&#8377;<?= number_format((float)$d['revenue'],2) ?></td>
                                <td><?= (int)$d['deals'] ?></td>
                                <td><strong>&#8377;<?= number_format((float)$d['commission'],2) ?></strong></td>
                                <td><form method="POST" action="<?= BASE_URL ?>/admin/commission/revenue/daily/delete/<?= $d['id'] ?>" class="style-71727" onsubmit="return confirm('Delete?')"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form></td>
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
