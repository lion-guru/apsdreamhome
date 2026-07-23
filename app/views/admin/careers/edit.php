<?php $pageTitle = 'Edit Job'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-edit me-2"></i>Edit Job</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/careers">Careers</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/careers/<?= $job['id'] ?? 0 ?>"><?= $job['title'] ?? 'Job' ?></a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/careers/update/<?= $job['id'] ?? 0 ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Job Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" value="<?= $job['title'] ?? '' ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Department</label><input type="text" name="department" class="form-control" value="<?= $job['department'] ?? '' ?>"></div>
                    <div class="col-md-4"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= $job['location'] ?? '' ?>"></div>
                    <div class="col-md-4"><label class="form-label">Employment Type</label><select name="employment_type" class="form-select"><option value="full_time" <?= ($job['employment_type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Full Time</option><option value="part_time" <?= ($job['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part Time</option><option value="contract" <?= ($job['employment_type'] ?? '') === 'contract' ? 'selected' : '' ?>>Contract</option></select></div>
                    <div class="col-md-4"><label class="form-label">Experience</label><input type="text" name="experience" class="form-control" value="<?= $job['experience'] ?? '' ?>"></div>
                    <div class="col-md-4"><label class="form-label">Salary Range</label><input type="text" name="salary" class="form-control" value="<?= $job['salary'] ?? '' ?>"></div>
                    <div class="col-md-4"><label class="form-label">Vacancies</label><input type="number" name="vacancies" class="form-control" value="<?= $job['vacancies'] ?? 1 ?>"></div>
                    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= ($job['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option><option value="closed" <?= ($job['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option><option value="draft" <?= ($job['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option></select></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="5"><?= $job['description'] ?? '' ?></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Job</button> <a href="<?= BASE_URL ?>/admin/careers" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
