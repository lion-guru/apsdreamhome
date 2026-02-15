<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-bullseye me-2"></i>Sales Performance Dashboard</h2>
        </div>
    </div>

    <!-- Sales Targets -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Monthly Target Progress</h5>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" 
                             style="width: <?php echo $sales_targets['achieved']; ?>%" 
                             aria-valuenow="<?php echo $sales_targets['achieved']; ?>" aria-valuemin="0" aria-valuemax="100">
                             <?php echo $sales_targets['achieved']; ?>% Achieved
                        </div>
                    </div>
                    <p class="text-muted small">Target: <?php echo $sales_targets['target']; ?> units | Current: <?php echo $sales_targets['achieved']; ?> units</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lead Pipeline -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Leads Pipeline</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="text-center p-3 border rounded bg-light" style="flex: 1; margin: 5px;">
                            <h3 class="text-danger"><?php echo $leads_pipeline['hot']; ?></h3>
                            <span class="text-uppercase small fw-bold">Hot</span>
                        </div>
                        <div class="text-center p-3 border rounded bg-light" style="flex: 1; margin: 5px;">
                            <h3 class="text-warning"><?php echo $leads_pipeline['warm']; ?></h3>
                            <span class="text-uppercase small fw-bold">Warm</span>
                        </div>
                        <div class="text-center p-3 border rounded bg-light" style="flex: 1; margin: 5px;">
                            <h3 class="text-info"><?php echo $leads_pipeline['cold']; ?></h3>
                            <span class="text-uppercase small fw-bold">Cold</span>
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-outline-primary w-100">View All Leads</a>
                </div>
            </div>
        </div>

        <!-- Recent Sales Actions -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Sales Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary p-3"><i class="fas fa-plus-circle me-2"></i>Add New Lead</button>
                        <button class="btn btn-info text-white p-3"><i class="fas fa-calendar-check me-2"></i>Schedule Site Visit</button>
                        <button class="btn btn-success p-3"><i class="fas fa-file-invoice-dollar me-2"></i>Generate Quotation</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
