<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4"><i class="fas fa-briefcase me-2 text-primary"></i>Apply for Position</h2>
            <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error ?? '') ?></div><?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>/careers/submit-application" enctype="multipart/form-data" class="card shadow-sm p-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="career_id" value="<?= (int)($career['id'] ?? 0) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Experience (Years)</label>
                        <input type="number" name="experience_years" class="form-control" step="0.5">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Current Company</label>
                        <input type="text" name="current_company" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Resume (PDF/DOC/DOCX, max 5MB) <span class="text-danger">*</span></label>
                        <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Cover Letter</label>
                        <textarea name="cover_letter" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-4"><i class="fas fa-paper-plane me-2"></i>Submit Application</button>
            </form>
        </div>
    </div>
</div>