<?php $pageTitle = 'Generate Report'; ?>
<?php $reportTypes = $reportTypes ?? ['sales' => 'Sales Report', 'properties' => 'Properties Report', 'financial' => 'Financial Report', 'user_activity' => 'User Activity Report', 'associate' => 'Associate Report', 'customer' => 'Customer Report']; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>reports/dashboard">Reports</a></li><li class="breadcrumb-item active">Generate Report</li></ol></nav>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Generate New Report</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>reports/generate">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Report Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" required>
                                <option value="">Select report type...</option>
                                <?php foreach ($reportTypes as $k => $v): ?>
                                <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Report Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g. Q1 2026 Sales Report">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Brief description of this report"></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="date_from">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="date_to">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Format</label>
                            <div class="d-flex gap-3">
                                <div class="form-check"><input class="form-check-input" type="radio" name="format" id="fmt_pdf" value="pdf" checked><label class="form-check-label" for="fmt_pdf"><i class="fas fa-file-pdf text-danger me-1"></i>PDF</label></div>
                                <div class="form-check"><input class="form-check-input" type="radio" name="format" id="fmt_excel" value="excel"><label class="form-check-label" for="fmt_excel"><i class="fas fa-file-excel text-success me-1"></i>Excel</label></div>
                                <div class="form-check"><input class="form-check-input" type="radio" name="format" id="fmt_csv" value="csv"><label class="form-check-label" for="fmt_csv"><i class="fas fa-file-csv text-primary me-1"></i>CSV</label></div>
                            </div>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="schedule" id="schedule" value="1">
                            <label class="form-check-label" for="schedule">Schedule this report to run daily</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-file-export me-2"></i>Generate Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
