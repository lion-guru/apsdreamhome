<?php $pageTitle = 'Edit Site'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-edit me-2"></i>Edit Site</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/sites">Sites</a></li>
                    <li class="breadcrumb-item"><a href="/admin/sites/show/<?= $site['id'] ?? 0 ?>"><?= $site['name'] ?? 'Site' ?></a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="/admin/sites/update/<?= $site['id'] ?? 0 ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Site Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="<?= $site['name'] ?? '' ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= ($site['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($site['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
                    <div class="col-md-6"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= $site['location'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Total Area (sqft)</label><input type="number" name="total_area" class="form-control" value="<?= $site['total_area'] ?? '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Developed Area</label><input type="number" name="developed_area" class="form-control" value="<?= $site['developed_area'] ?? '' ?>"></div>
                    <div class="col-md-4"><label class="form-label">Total Plots</label><input type="number" name="total_plots" class="form-control" value="<?= $site['total_plots'] ?? '' ?>"></div>
                    <div class="col-md-4"><label class="form-label">Available Plots</label><input type="number" name="available_plots" class="form-control" value="<?= $site['available_plots'] ?? '' ?>"></div>
                    <div class="col-md-4"><label class="form-label">Amenities</label><input type="text" name="amenities" class="form-control" value="<?= $site['amenities'] ?? '' ?>" placeholder="Park, Club, Temple"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= $site['description'] ?? '' ?></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Site</button> <a href="/admin/sites" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
