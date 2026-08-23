<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chart-line text-primary me-2"></i>Attendance Report</h1>
        <a href="<?= BASE_URL ?>/admin/hr/attendance" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Roster
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt me-2"></i>Select Period</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold text-uppercase">Month</label>
                    <select name="month" class="form-select">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= ($month ?? date('m')) === str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold text-uppercase">Year</label>
                    <select name="year" class="form-select">
                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm"><i class="fas fa-sync-alt me-2"></i>Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card shadow mb-4 border-bottom-primary">
        <div class="card-header py-3 bg-white d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table me-2"></i> Report for <?= date('F Y', mktime(0, 0, 0, $month ?? 1, 1, $year ?? date('Y'))) ?>
            </h6>
            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="reportTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th class="text-center text-success"><i class="fas fa-check-circle me-1"></i> Present</th>
                            <th class="text-center text-danger"><i class="fas fa-times-circle me-1"></i> Absent</th>
                            <th class="text-center text-warning"><i class="fas fa-adjust me-1"></i> Half Day</th>
                            <th class="text-center text-info"><i class="fas fa-plane-departure me-1"></i> Leave</th>
                            <th class="text-center text-secondary"><i class="fas fa-calendar-day me-1"></i> Holiday</th>
                            <th class="text-center">Total Days</th>
                            <th class="style-10944">Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report ?? [])): ?>
                            <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-folder-open fa-3x d-block mb-3 text-gray-300" aria-hidden="true"></i>No attendance data for this period.</td></tr>
                        <?php else: ?>
                            <?php foreach ($report ?? [] as $r): ?>
                                <?php 
                                    $totalPresent = ($r['present'] ?? 0) + ($r['half_day'] ?? 0) * 0.5; 
                                    $rate = ($r['total_days'] ?? 0) > 0 ? round(($totalPresent / ($r['total_days'] ?? 1)) * 100, 1) : 0; 
                                    $rateClass = $rate >= 90 ? 'success' : ($rate >= 75 ? 'warning' : 'danger');
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-gray-800">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light text-primary rounded-circle me-3 d-flex align-items-center justify-content-center border" class="style-68946">
                                                <?= strtoupper(substr(htmlspecialchars($r['name'] ?? 'E'), 0, 1)) ?>
                                            </div>
                                            <?= htmlspecialchars($r['name'] ?? '') ?>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 fs-6"><?= (int)($r['present'] ?? 0) ?></span></td>
                                    <td class="text-center"><span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 fs-6"><?= (int)($r['absent'] ?? 0) ?></span></td>
                                    <td class="text-center"><span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 fs-6"><?= (int)($r['half_day'] ?? 0) ?></span></td>
                                    <td class="text-center"><span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 fs-6"><?= (int)($r['leave_count'] ?? 0) ?></span></td>
                                    <td class="text-center"><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 fs-6"><?= (int)($r['holiday'] ?? 0) ?></span></td>
                                    <td class="text-center fw-bold text-gray-700 fs-5"><?= (int)($r['total_days'] ?? 0) ?></td>
                                    <td class="pe-4">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="small font-weight-bold text-<?= $rateClass ?>"><?= $rate ?>%</span>
                                        </div>
                                        <div class="progress" class="style-29939">
                                            <div class="progress-bar bg-<?= $rateClass ?>" role="progressbar" class="style-19807" aria-valuenow="<?= $rate ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #reportTable, #reportTable * {
        visibility: visible;
    }
    #reportTable {
        position: absolute;
        left: 0;
        top: 0;
    }
    .btn, form, .card-header button {
        display: none !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if($.fn.DataTable) {
        $('#reportTable').DataTable({
            responsive: true,
            pageLength: 50,
            order: [[0, 'asc']],
            bInfo: false,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search employee..."
            }
        });
    }
});
</script>
