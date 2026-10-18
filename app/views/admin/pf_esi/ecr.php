<?php
$ecr = $ecr ?? ['month_year' => date('Y-m'), 'est_code' => '', 'esi_code' => '', 'content' => '', 'count' => 0, 'totals' => []];
$month_year = $month_year ?? date('Y-m');
$month_years = $month_years ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>ECR Filing (EPFO)</h1>
        <div>
            <form method="GET" class="d-inline">
                <label class="me-2" style="margin-bottom:0;">Month:</label>
                <select name="month_year" class="form-select form-select-sm d-inline w-auto">
                    <?php foreach ($month_years as $my): ?>
                        <option value="<?= $my['month_year'] ?>" <?= $my['month_year'] === $month_year ? 'selected' : '' ?>><?= htmlspecialchars($my['month_year']) ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="<?= BASE_URL ?>/admin/pf-esi/ecr/download?month_year=<?= $month_year ?>" class="btn btn-success btn-sm ml-2"><i class="fas fa-download me-2"></i>Download TXT</a>
            </form>
            <a href="<?= BASE_URL ?>/admin/pf-esi" class="btn btn-outline-secondary ml-2"><i class="fas fa-arrow-left me-2"></i>Back</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="card-title mb-0">ECR Summary</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h4><?= $ecr['count'] ?></h4><small>Employees</small></div></div></div>
                <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h4><?= number_format((float)($ecr['totals']['pf_employee'] ?? 0), 2) ?></h4><small>PF Employee</small></div></div></div>
                <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h4><?= number_format((float)($ecr['totals']['pf_employer'] ?? 0), 2) ?></h4><small>PF Employer</small></div></div></div>
                <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h4><?= number_format((float)($ecr['totals']['esi_employee'] ?? 0), 2) ?></h4><small>ESI Employee</small></div></div></div>
                <div class="col-md-3"><div class="card bg-danger text-white"><div class="card-body"><h4><?= number_format((float)($ecr['totals']['esi_employer'] ?? 0), 2) ?></h4><small>ESI Employer</small></div></div></div>
                <div class="col-md-3"><div class="card bg-secondary text-white"><div class="card-body"><h4><?= number_format((float)($ecr['totals']['pf_wage'] ?? 0) + (float)($ecr['totals']['esi_wage'] ?? 0), 2) ?></h4><small>Total Wages</small></div></div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">ECR Text Content (Month: <?= htmlspecialchars($month_year) ?>)</h5></div>
        <div class="card-body p-0">
            <pre class="bg-light p-3 mb-0" style="white-space: pre-wrap; font-size: 0.85rem;"><?= htmlspecialchars($ecr['content']) ?></pre>
        </div>
    </div>
</div>
