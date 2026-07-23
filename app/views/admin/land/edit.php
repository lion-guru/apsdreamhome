<?php $pageTitle = 'Edit Land Record'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-edit me-2"></i>Edit Land Record</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/land">Land</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/land/<?= $land['id'] ?? 0 ?>"><?= $land['title'] ?? 'Land' ?></a></li>
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
                    <div class="col-md-6"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" value="<?= $land['title'] ?? '' ?>" required></div>
                    <div class="col-md-3"><label class="form-label">Area (sqft)</label><input type="number" name="area_sqft" class="form-control" value="<?= $land['area_sqft'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Price</label><input type="number" name="price" class="form-control" value="<?= $land['price'] ?? '' ?>"></div>
                    <div class="col-md-6"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= $land['location'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Survey Number</label><input type="text" name="survey_number" class="form-control" value="<?= $land['survey_number'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="available" <?= ($land['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option><option value="negotiation" <?= ($land['status'] ?? '') === 'negotiation' ? 'selected' : '' ?>>Under Negotiation</option><option value="sold" <?= ($land['status'] ?? '') === 'sold' ? 'selected' : '' ?>>Sold</option></select></div>
                    <div class="col-md-6"><label class="form-label">Owner Name</label><input type="text" name="owner_name" class="form-control" value="<?= $land['owner_name'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Owner Phone</label><input type="text" name="owner_phone" class="form-control" value="<?= $land['owner_phone'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Zoning</label><input type="text" name="zoning" class="form-control" value="<?= $land['zoning'] ?? '' ?>"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= $land['description'] ?? '' ?></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Land</button> <a href="<?= BASE_URL ?>/admin/land" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
