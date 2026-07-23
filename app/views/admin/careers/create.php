<?php $pageTitle = 'Post a Job'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-plus-circle me-2"></i>Post a Job</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/careers">Careers</a></li>
                    <li class="breadcrumb-item active">Post Job</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/careers/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Job Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Department</label><input type="text" name="department" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Location</label><input type="text" name="location" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Employment Type</label><select name="employment_type" class="form-select"><option value="full_time">Full Time</option><option value="part_time">Part Time</option><option value="contract">Contract</option><option value="internship">Internship</option></select></div>
                    <div class="col-md-4"><label class="form-label">Experience Required</label><input type="text" name="experience" class="form-control" placeholder="e.g. 2-3 years"></div>
                    <div class="col-md-6"><label class="form-label">Salary Range</label><input type="text" name="salary" class="form-control" placeholder="e.g. ₹3L - ₹5L"></div>
                    <div class="col-md-3"><label class="form-label">Vacancies</label><input type="number" name="vacancies" class="form-control" value="1"></div>
                    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="closed">Closed</option><option value="draft">Draft</option></select></div>
                    <div class="col-12"><label class="form-label">Description / Requirements</label><textarea name="description" class="form-control" rows="5"></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Post Job</button> <a href="<?= BASE_URL ?>/admin/careers" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
