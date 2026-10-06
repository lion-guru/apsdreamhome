<?php
/**
 * Gratuity Eligibility & Liability Report
*/

$page_title = $page_title ?? 'Gratuity Eligibility & Liability Report';
$report = $report ?? ['total_employees' => 0, 'eligible_employees' => 0, 'total_liability' => 0, 'details' => []];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Gratuity Eligibility & Liability Report</h1>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= $report['total_employees'] ?? 0 ?></h3>
                    <small>Total Active Employees</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= $report['eligible_employees'] ?? 0 ?></h3>
                    <small>Eligible for Gratuity</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <?php $notEligible = ($report['total_employees'] ?? 0) - ($report['eligible_employees'] ?? 0); ?>
                    <h3><?= $notEligible ?></h3>
                    <small>Not Yet Eligible</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3>₹<?= number_format($report['total_liability'] ?? 0, 0) ?></h3>
                    <small>Total Gratuity Liability</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Employee Gratuity Details</h5>
            <a href="<?= BASE_URL ?>/admin/gratuity/calculator" class="btn btn-sm btn-primary">
                <i class="fas fa-calculator me-1"></i> Calculate Individual
            </a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($report['details'])): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Eligible Employees</h5>
                <p class="text-muted">No employees meet the 5-year service requirement for gratuity.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Designation</th>
                            <th>Department</th>
                            <th>Joining Date</th>
                            <th class="text-center">Tenure</th>
                            <th class="text-end">Basic Salary</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Gratuity Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['details'] as $emp): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($emp['name']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($emp['employee_code']) ?></td>
                            <td><?= htmlspecialchars($emp['designation']) ?></td>
                            <td><?= htmlspecialchars($emp['department']) ?></td>
                            <td><?= $emp['joining_date'] ? date('d M Y', strtotime($emp['joining_date'])) : '-' ?></td>
                            <td class="text-center">
                                <?= $emp['tenure']['years'] ?? 0 ?>y <?= $emp['tenure']['months'] ?? 0 ?>m
                            </td>
                            <td class="text-end">₹<?= number_format($emp['basic_salary'] ?? 0, 2) ?></td>
                            <td class="text-center">
                                <span class="badge bg-success">Eligible</span>
                            </td>
                            <td class="text-end text-success fw-bold">
                                ₹<?= number_format($emp['gratuity_amount'] ?? 0, 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-primary fw-bold">
                            <td colspan="8" class="text-end">TOTAL LIABILITY</td>
                            <td class="text-end text-success">₹<?= number_format($report['total_liability'] ?? 0, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="mt-4 d-flex gap-2">
        <button onclick="exportToCSV()" class="btn btn-outline-success">
            <i class="fas fa-file-csv me-1"></i> Export CSV
        </button>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
    </div>

    <!-- Report Metadata -->
    <div class="card mt-4">
        <div class="card-body">
            <small class="text-muted">
                <strong>Report Generated:</strong> <?= $report['generated_at'] ?? date('d M Y H:i:s') ?> |
                <strong>Total Active Employees:</strong> <?= $report['total_employees'] ?? 0 ?> |
                <strong>Eligible for Gratuity:</strong> <?= $report['eligible_employees'] ?? 0 ?> |
                <strong>Statutory Cap:</strong> ₹20,00,000 per employee (Payment of Gratuity Act, 1972)
            </small>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
</style>

<script>
function exportToCSV() {
    const rows = [];
    rows.push(['Employee', 'Code', 'Designation', 'Department', 'Joining Date', 'Tenure', 'Basic Salary', 'Status', 'Gratuity Amount']);
    
    document.querySelectorAll('tbody tr:not(:last-child)').forEach(tr => {
        const cells = tr.querySelectorAll('td');
        const row = [];
        cells.forEach((cell, i) => {
            if (i === 0) row.push('"' + cell.querySelector('strong')?.textContent?.trim() || cell.textContent.trim() + '"');
            else if (i === cells.length - 1) row.push(cell.querySelector('.fw-bold')?.textContent?.trim() || cell.textContent.trim());
            else row.push('"' + cell.textContent.trim() + '"');
        });
        rows.push(row);
    });
    
    // Add total row
    const totalRow = document.querySelector('tbody tr:last-child');
    if (totalRow) {
        const cells = totalRow.querySelectorAll('td');
        rows.push(['', '', '', '', '', '', '', 'TOTAL LIABILITY', cells[cells.length - 1].textContent.trim()]);
    }
    
    const csv = rows.map(r => r.join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'gratuity_liability_report_' + new Date().toISOString().split('T')[0] + '.csv';
    link.click();
}
</script>