<?php
$page_title = $page_title ?? __('assoc_il_title', [], 'Import Leads');
$current_page = 'leads';
$imported = $imported ?? 0;
$skipped = $skipped ?? 0;
$errors = $errors ?? [];
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="container-fluid px-4 py-3">
    <a href="<?= BASE_URL ?>/associate/leads" class="text-decoration-none mb-3 d-inline-block">
        <i class="fas fa-arrow-left me-1"></i> <?= __('assoc_il_back', [], 'Back to Leads') ?>
    </a>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-file-import text-primary me-2"></i><?= __('assoc_il_title', [], 'Import Leads from CSV') ?></h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 mb-4" style="background:#eff6ff;">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i><?= __('assoc_il_format', [], 'CSV Format') ?></h6>
                        <p class="mb-2"><?= __('assoc_il_format_desc', [], 'Your CSV file should have these columns (first row = header):') ?></p>
                        <code class="d-block p-2 bg-white rounded mb-2">name, phone, email, source, budget, location, notes</code>
                        <ul class="mb-0" style="font-size:0.85rem;">
                            <li><strong>name</strong> — <?= __('assoc_il_col_name', [], "Lead's full name (required)") ?></li>
                            <li><strong>phone</strong> — <?= __('assoc_il_col_phone', [], "10-digit mobile number (required)") ?></li>
                            <li><strong>email</strong> — <?= __('assoc_il_col_email', [], "Email address (optional)") ?></li>
                            <li><strong>source</strong> — <?= __('assoc_il_col_source', [], 'Where did this lead come from? (default: csv_import)') ?></li>
                            <li><strong>budget</strong> — <?= __('assoc_il_col_budget', [], 'Budget range like "30-50 Lakh" (optional)') ?></li>
                            <li><strong>location</strong> — <?= __('assoc_il_col_location', [], 'Preferred location (optional)') ?></li>
                            <li><strong>notes</strong> — <?= __('assoc_il_col_notes', [], 'Any additional notes (optional)') ?></li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <a href="data:text/csv,name,phone,email,source,budget,location,notes%0ARahul Sharma,9876543210,rahul@email.com,referral,50-70 Lakh,Noida,Interested in 3BHK%0APriya Patel,9876543211,priya@email.com,website,30-50 Lakh,Greater Noida," class="btn btn-outline-secondary btn-sm" download="sample_leads.csv">
                            <i class="fas fa-download me-1"></i> <?= __('assoc_il_download_sample', [], 'Download Sample CSV') ?>
                        </a>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/associate/leads/import" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-4">
                            <label class="form-label fw-bold"><?= __('assoc_il_select_file', [], 'Select CSV File') ?></label>
                            <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                            <small class="text-muted"><?= __('assoc_il_limit', [], 'Maximum 500 leads per import. Duplicate phone numbers will be skipped.') ?></small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i> <?= __('assoc_il_import', [], 'Import Leads') ?>
                        </button>
                    </form>

                    <?php if (!empty($errors)): ?>
                    <div class="mt-4">
                        <h6 class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> <?= __('assoc_il_errors', ['count' => count($errors)], 'Import Errors (%count%)') ?></h6>
                        <div class="bg-light p-3 rounded" style="max-height:200px;overflow-y:auto;">
                            <?php foreach (array_slice($errors, 0, 20) as $err): ?>
                                <div style="font-size:0.82rem;color:#64748b;"><?= htmlspecialchars($err) ?></div>
                            <?php endforeach; ?>
                            <?php if (count($errors) > 20): ?>
                                <div class="text-muted mt-1">... <?= __('assoc_il_more', ['count' => count($errors) - 20], 'and %count% more') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
