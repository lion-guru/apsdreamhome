<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Job</h1>
        <a href="/admin/careers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="/admin/careers/update/<?= $career->id ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($career->title) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Job Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="Full Time" <?= $career->type == 'Full Time' ? 'selected' : '' ?>>Full Time</option>
                            <option value="Part Time" <?= $career->type == 'Part Time' ? 'selected' : '' ?>>Part Time</option>
                            <option value="Contract" <?= $career->type == 'Contract' ? 'selected' : '' ?>>Contract</option>
                            <option value="Freelance" <?= $career->type == 'Freelance' ? 'selected' : '' ?>>Freelance</option>
                            <option value="Internship" <?= $career->type == 'Internship' ? 'selected' : '' ?>>Internship</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" name="location" required value="<?= htmlspecialchars($career->location) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="salary_range" class="form-label">Salary Range</label>
                        <input type="text" class="form-control" id="salary_range" name="salary_range" value="<?= htmlspecialchars($career->salary_range) ?>" placeholder="e.g. 5LPA - 8LPA">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?= $career->status == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $career->status == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="filled" <?= $career->status == 'filled' ? 'selected' : '' ?>>Filled</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Job Description</label>
                    <textarea class="form-control" id="description" name="description" rows="10"><?= htmlspecialchars($career->description) ?></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Job
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('description');
</script>
