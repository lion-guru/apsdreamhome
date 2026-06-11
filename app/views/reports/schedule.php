<?php $pageTitle = 'Schedule Report'; ?>
<?php $reportTypes = $reportTypes ?? ['sales' => 'Sales Report', 'properties' => 'Properties Report', 'financial' => 'Financial Report', 'user_activity' => 'User Activity Report', 'associate' => 'Associate Report', 'customer' => 'Customer Report']; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/scheduled">Scheduled</a></li><li class="breadcrumb-item active">New Schedule</li></ol></nav>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Schedule a Report</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>reports/schedule">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Report Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" required>
                                <option value="">Select type...</option>
                                <?php foreach ($reportTypes as $k => $v): ?><option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Report Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g. Weekly Sales Summary">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Frequency <span class="text-danger">*</span></label>
                                <select class="form-select" name="frequency" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekly" selected>Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Format</label>
                                <select class="form-select" name="format">
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Day of Week/Month</label>
                                <select class="form-select" name="day">
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday" selected>Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="1">1st of Month</option>
                                    <option value="15">15th of Month</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Recipients (comma-separated)</label>
                            <input type="text" class="form-control" name="recipients" placeholder="admin@example.com, manager@example.com">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Any special instructions"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-calendar-check me-2"></i>Create Schedule</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
