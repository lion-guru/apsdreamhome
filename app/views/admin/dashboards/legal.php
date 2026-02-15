<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-gavel me-2"></i>Legal & Compliance Dashboard</h2>
        </div>
    </div>

    <!-- Legal Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Documents for Review</h6>
                    <h3><?php echo $document_status['pending_review'] ?? '8'; ?></h3>
                    <p class="text-warning mb-0"><i class="fas fa-clock me-1"></i>3 Urgent</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Pending Verifications</h6>
                    <h3><?php echo $pending_verifications['count'] ?? '12'; ?></h3>
                    <p class="text-muted mb-0">Customer KYC/Property Docs</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small">Compliance Status</h6>
                    <h3><?php echo $document_status['compliance_score'] ?? '98%'; ?></h3>
                    <p class="text-success mb-0"><i class="fas fa-check-circle me-1"></i>Fully Compliant</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Document Queue -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Document Verification Queue</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Type</th>
                                    <th>Project/Customer</th>
                                    <th>Date Added</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center">
                                    <td colspan="4" class="py-4 text-muted small">No documents awaiting verification</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance Checklist -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Compliance Checklist</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" checked disabled>
                        <label class="form-check-label small text-muted">RERA Registration Renewed</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" checked disabled>
                        <label class="form-check-label small text-muted">Quarterly Tax Filings Done</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" disabled>
                        <label class="form-check-label small">New Project Layout Approval</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" disabled>
                        <label class="form-check-label small">Annual Audit Review</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
