ï»¿<!-- Payroll Batch Preview -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-calculator mr-2"></i> Payroll Preview — <?= $month ?>/<?= $year ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/salary">Salary</a></li>
                        <li class="breadcrumb-item active">Batch Preview</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Month Selector -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL ?>/admin/salary/batch/preview" class="form-inline">
                        <label class="mr-2">Month:</label>
                        <select name="month" class="form-control form-control-sm mr-2">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                            <?php endfor; ?>
                        </select>
                        <label class="mr-2">Year:</label>
                        <select name="year" class="form-control form-control-sm mr-2">
                            <?php for ($y = date('Y'); $y >= date('Y')-2; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-eye mr-1"></i> Preview</button>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box" class="style-75630">
                        <div class="inner"><h3><?= $total_employees ?></h3><p>Employees</p></div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box" class="style-55192">
                        <div class="inner"><h3>₹<?= number_format($total_gross) ?></h3><p>Total Gross</p></div>
                        <div class="icon"><i class="fas fa-arrow-up"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box" class="style-48582">
                        <div class="inner"><h3>₹<?= number_format($total_deductions) ?></h3><p>Total Deductions</p></div>
                        <div class="icon"><i class="fas fa-arrow-down"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box" class="style-23498">
                        <div class="inner"><h3>₹<?= number_format($total_net) ?></h3><p>Total Net Payable</p></div>
                        <div class="icon"><i class="fas fa-rupee-sign"></i></div>
                    </div>
                </div>
            </div>

            <!-- Preview Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Detailed Breakdown</h3>
                    <?php if (!empty($entries)): ?>
                        <form method="POST" action="<?= BASE_URL ?>/admin/salary/batch/generate" class="style-71727" data-aps-confirm="Generate <?= count($entries) ?> payslips for <?= $month ?>/<?= $year ?>?">
                            <input type="hidden" name="month" value="<?= $month ?>">
                            <input type="hidden" name="year" value="<?= $year ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check-circle mr-1"></i> Generate Payslips</button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap table-sm">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Dept</th>
                                <th>Basic</th>
                                <th>HRA</th>
                                <th>Gross</th>
                                <th>PF (EE)</th>
                                <th>ESI</th>
                                <th>TDS</th>
                                <th>PT</th>
                                <th>Total Ded.</th>
                                <th>Net Pay</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($entries)): ?>
                                <tr><td colspan="11" class="text-center text-muted py-4">No salary structures found. Create salary structures first.</td></tr>
                            <?php else: ?>
                                <?php foreach ($entries as $e): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($e['employee_name'] ?? '') ?></strong></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($e['department'] ?? '') ?></span></td>
                                        <td>₹<?= number_format($e['basic_salary']) ?></td>
                                        <td>₹<?= number_format($e['hra']) ?></td>
                                        <td><strong>₹<?= number_format($e['gross_salary']) ?></strong></td>
                                        <td>₹<?= number_format($e['pf_employee']) ?></td>
                                        <td>₹<?= number_format($e['esi_employee']) ?></td>
                                        <td>₹<?= number_format($e['tds']) ?></td>
                                        <td>₹<?= number_format($e['professional_tax']) ?></td>
                                        <td class="text-danger">₹<?= number_format($e['total_deductions']) ?></td>
                                        <td class="text-success font-weight-bold">₹<?= number_format($e['net_salary']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
