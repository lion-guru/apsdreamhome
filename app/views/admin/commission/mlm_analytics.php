<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-chart-bar"></i> MLM Commission Analytics</h4>
        <a href="/admin/commission" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-md-2"><div class="card bg-success text-white text-center p-2"><h5>&#8377;<?= number_format((float)($summary['earned']??0)) ?></h5><small>Earned</small></div></div>
        <div class="col-md-2"><div class="card bg-info text-white text-center p-2"><h5>&#8377;<?= number_format((float)($summary['paid']??0)) ?></h5><small>Paid</small></div></div>
        <div class="col-md-2"><div class="card bg-warning text-dark text-center p-2"><h5>&#8377;<?= number_format((float)($summary['pending']??0)) ?></h5><small>Pending</small></div></div>
        <div class="col-md-2"><div class="card bg-primary text-white text-center p-2"><h5>&#8377;<?= number_format((float)($summary['direct']??0)) ?></h5><small>Direct</small></div></div>
        <div class="col-md-2"><div class="card bg-secondary text-white text-center p-2"><h5>&#8377;<?= number_format((float)($summary['team']??0)) ?></h5><small>Team</small></div></div>
        <div class="col-md-2"><div class="card bg-dark text-white text-center p-2"><h5>&#8377;<?= number_format((float)($summary['bonus']??0)) ?></h5><small>Bonus</small></div></div>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header"><i class="fas fa-table"></i> Period-wise Analytics</div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead><tr><th>Associate</th><th>Period</th><th>Earned</th><th>Paid</th><th>Pending</th><th>Direct</th><th>Team</th><th>Bonus</th></tr></thead>
                <tbody>
                    <?php foreach ($analytics ?? [] as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['associate_name'] ?? 'N/A') ?></td>
                        <td><?= $a['period_date'] ?></td>
                        <td>&#8377;<?= number_format((float)$a['total_earned'],2) ?></td>
                        <td>&#8377;<?= number_format((float)$a['total_paid'],2) ?></td>
                        <td>&#8377;<?= number_format((float)$a['pending_amount'],2) ?></td>
                        <td>&#8377;<?= number_format((float)($a['direct_commissions']??0),2) ?></td>
                        <td>&#8377;<?= number_format((float)($a['team_commissions']??0),2) ?></td>
                        <td>&#8377;<?= number_format((float)($a['bonus_commissions']??0),2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
