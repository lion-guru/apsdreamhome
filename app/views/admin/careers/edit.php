<?php $pageTitle = 'Edit Job'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-edit me-2"></i>Edit Job</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/admin/careers">Careers</a></li>
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/admin/careers/<?= htmlspecialchars($job['id'] ?? 0, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($job['title'] ?? 'Job', ENT_QUOTES, 'UTF-8') ?></a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/admin/careers/update/<?= htmlspecialchars($job['id'] ?? 0, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Job Title <span class="text-danger">*</span></label><input aria-label="Title" type="text" name="title" class="form-control" value="<?= htmlspecialchars($job['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Department</label><input aria-label="Department" type="text" name="department" class="form-control" value="<?= htmlspecialchars($job['department'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Location</label><input aria-label="Location" type="text" name="location" class="form-control" value="<?= htmlspecialchars($job['location'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Employment Type</label><select aria-label="Employment type" name="employment_type" class="form-select"><option value="full_time" <?= htmlspecialchars(($job['employment_type'] ?? '') === 'full_time' ? 'selected' : '', ENT_QUOTES, 'UTF-8') ?>>Full Time</option><option value="part_time" <?= htmlspecialchars(($job['employment_type'] ?? '') === 'part_time' ? 'selected' : '', ENT_QUOTES, 'UTF-8') ?>>Part Time</option><option value="contract" <?= htmlspecialchars(($job['employment_type'] ?? '') === 'contract' ? 'selected' : '', ENT_QUOTES, 'UTF-8') ?>>Contract</option></select></div>
                    <div class="col-md-4"><label class="form-label">Experience</label><input aria-label="Experience" type="text" name="experience" class="form-control" value="<?= htmlspecialchars($job['experience'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Salary Range</label><input aria-label="Salary" type="text" name="salary" class="form-control" value="<?= htmlspecialchars($job['salary'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Vacancies</label><input aria-label="Vacancies" type="number" name="vacancies" class="form-control" value="<?= htmlspecialchars($job['vacancies'] ?? 1, ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Status</label><select aria-label="Status" name="status" class="form-select"><option value="active" <?= htmlspecialchars(($job['status'] ?? '') === 'active' ? 'selected' : '', ENT_QUOTES, 'UTF-8') ?>>Active</option><option value="closed" <?= htmlspecialchars(($job['status'] ?? '') === 'closed' ? 'selected' : '', ENT_QUOTES, 'UTF-8') ?>>Closed</option><option value="draft" <?= htmlspecialchars(($job['status'] ?? '') === 'draft' ? 'selected' : '', ENT_QUOTES, 'UTF-8') ?>>Draft</option></select></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea aria-label="Description" name="description" class="form-control" rows="5"><?= htmlspecialchars($job['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Job</button> <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/admin/careers" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
