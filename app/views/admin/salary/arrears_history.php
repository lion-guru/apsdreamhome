<?php
/**
 * Arrears History View
 * Lists all processed arrears payments
*/

$page_title = $page_title ?? 'Arrears History';
$arrears = $arrears ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <?php $total = array_sum(array_column($arrears, 'net_amount')); ?>
                    <h3>₹<?= number_format($total, 2) ?></h3>
                    <small>Total Arrears Paid</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= count($arrears) ?></h3>
                    <small>Arrears Entries</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <?php $employees = array_unique(array_column($arrears, 'employee_id')); ?>
                    <h3><?= count($employees) ?></h3>
                    <small>Employees</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <?php $totalBasic = array_sum(array_column($arrears, 'basic_amount')); ?>
                    <h3>₹<?= number_format($totalBasic, 2) ?></h3>
                    <small>Total Basic Arrears</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        <?php 
                        $empIds = array_unique(array_column($arrears, 'employee_id'));
                        $empNames = [];
                        foreach ($empIds as $eid) {
                            $first = array_filter($arrears, fn($a) => $a['employee_id']==$eid);
                            $first = array_values($first)[0] ?? null;
                            if ($first) {
                                echo '<option value="'.$eid.'"'.($_GET['employee_id']??''==$eid?' selected':'').'>'.htmlspecialchars($first['employee_name']??'').'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <option value="">All</option>
                        <?php for($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>"<?=($_GET['month']??'')==$m?' selected':''?>><?= date('M', mktime(0,0,0,$m,1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="">All</option>
                        <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?>
                        <option value="<?= $y ?>"<?=($_GET['year']??'')==$y?' selected':''?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="paid"<?=($_GET['status']??'')=='paid'?' selected':''?>>Paid</option>
                        <option value="pending"<?=($_GET['status']??'')=='pending'?' selected':''?>>Pending</option>
                        <option value="cancelled"<?=($_GET['status']??'')=='cancelled'?' selected':''?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="<?= BASE_URL ?>/admin/salary/arrears/history" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Arrears Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Arrears Payments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Period</th>
                            <th>Basic Arrears</th>
                            <th>Gross Arrears</th>
                            <th>Deductions</th>
                            <th class="text-success">Net Arrears</th>
                            <th>Payment Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($arrears as $a): ?>
                        <tr>
                            <td><?= $a['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($a['employee_name'] ?? 'Unknown') ?></strong><br>
                                <small class="text-muted">#<?= $a['employee_id'] ?></small>
                            </td>
                            <td>
                                <?= date('M Y', mktime(0,0,0, $a['payment_month'], 1, $a['payment_year'])) ?>
                            </td>
                            <td>₹<?= number_format($a['basic_amount'] ?? 0, 2) ?></td>
                            <td>₹<?= number_format($a['gross_amount'] ?? 0, 2) ?></td>
                            <td>₹<?= number_format($a['deduction_amount'] ?? 0, 2) ?></td>
                            <td class="text-success fw-bold">₹<?= number_format($a['net_amount'] ?? 0, 2) ?></td>
                            <td><?= $a['payment_date'] ? date('d M Y', strtotime($a['payment_date'])) : '-' ?></td>
                            <td>
                                <?php
                                $badge = ['paid'=>'success','pending'=>'warning','cancelled'=>'danger','processed'=>'info','failed'=>'danger'];
                                $st = $a['payment_status'] ?? 'pending';
                                echo '<span class="badge bg-'.($badge[$st]??'secondary').'">'.ucfirst($st).'</span>';
                                ?>
                            </td>
                            <td>
                                <small><?= htmlspecialchars(substr($a['remarks'] ?? '', 0, 80)) ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/salary/view-payment/<?= $a['id'] ?>" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (($a['payment_status'] ?? '') === 'pending'): ?>
                                    <a href="<?= BASE_URL ?>/admin/salary/process-payment/<?= $a['id'] ?>" class="btn btn-outline-success" title="Process">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($arrears)): ?>
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                No arrears records found
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>