<?php $pageTitle = 'Edit Land Record'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-edit me-2"></i>Edit Land Record</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/land">Land</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/land/<?= $land['id'] ?? 0 ?>"><?= $land['survey_number'] ?? 'Land' ?></a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/land/update/<?= $land['id'] ?? 0 ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Survey Number <span class="text-danger">*</span></label><input type="text" name="survey_number" class="form-control" value="<?= $land['survey_number'] ?? '' ?>" required></div>
                    <div class="col-md-3"><label class="form-label">Land Area (sqft) <span class="text-danger">*</span></label><input type="number" name="land_area" class="form-control" step="0.01" min="0.01" value="<?= $land['land_area'] ?? '' ?>" required></div>
                    <div class="col-md-3"><label class="form-label">Land Type <span class="text-danger">*</span></label><input type="text" name="land_type" class="form-control" value="<?= $land['land_type'] ?? '' ?>" required placeholder="e.g., Agricultural, Residential"></div>
                    <div class="col-md-6"><label class="form-label">Location <span class="text-danger">*</span></label><input type="text" name="location" class="form-control" value="<?= $land['location'] ?? '' ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Owner Name <span class="text-danger">*</span></label><input type="text" name="owner_name" class="form-control" value="<?= $land['owner_name'] ?? '' ?>" required></div>
                    <div class="col-md-3"><label class="form-label">Owner Contact</label><input type="text" name="owner_contact" class="form-control" value="<?= $land['owner_contact'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Acquisition Status</label><select name="acquisition_status" class="form-select"><option value="identified" <?= ($land['acquisition_status'] ?? '') === 'identified' ? 'selected' : '' ?>>Identified</option><option value="negotiation" <?= ($land['acquisition_status'] ?? '') === 'negotiation' ? 'selected' : '' ?>>Under Negotiation</option><option value="acquired" <?= ($land['acquisition_status'] ?? '') === 'acquired' ? 'selected' : '' ?>>Acquired</option><option value="disputed" <?= ($land['acquisition_status'] ?? '') === 'disputed' ? 'selected' : '' ?>>Disputed</option></select></div>
                    <div class="col-md-3"><label class="form-label">Acquisition Cost</label><input type="number" name="acquisition_cost" class="form-control" step="0.01" min="0" value="<?= $land['acquisition_cost'] ?? '0.00' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Colony</label><input type="number" name="colony_id" class="form-control" value="<?= $land['colony_id'] ?? '' ?>" placeholder="Colony ID (optional)"></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Land</button> <a href="<?= BASE_URL ?>/admin/land" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
