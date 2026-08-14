<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services">Services Directory</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services/jobs">Jobs</a></li>
            <li class="breadcrumb-item active">Post a Job</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="mb-2"><i class="fas fa-briefcase text-success me-2"></i>Post a Job / Listing</h2>
                    <p class="text-muted mb-4">Are you looking for work? Or do you need workers for your project? Post it here.</p>

                    <form method="POST">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Experienced Mason Needed or Looking for Plumbing Work">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Job Type</label>
                                <select name="job_type" class="form-control">
                                    <option value="gig">Gig / Daily Wage</option>
                                    <option value="full_time">Full Time</option>
                                    <option value="part_time">Part Time</option>
                                    <option value="contract">Contract</option>
                                    <option value="internship">Internship</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-control">
                                    <option value="">Select</option>
                                    <?php foreach ($jobCategories as $jc): ?>
                                        <option value="<?= $jc ?>"><?= $jc ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">I am...</label>
                                <select name="is_seeking" class="form-control">
                                    <option value="1">Seeking Work</option>
                                    <option value="0">Hiring Workers</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Linked to Business (Optional)</label>
                                <select name="listing_id" class="form-control">
                                    <option value="">None</option>
                                    <?php foreach ($listings as $l): ?>
                                        <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['business_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Describe the work, requirements, pay, timing..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g. Patna, Bihar">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Salary / Pay Range</label>
                                <input type="text" name="salary_range" class="form-control" placeholder="e.g. ₹500-800/day or ₹15,000-25,000/month">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Phone *</label>
                                <input type="text" name="contact_phone" class="form-control" required placeholder="9876543210">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" placeholder="Your name">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100"><i class="fas fa-paper-plane me-2"></i>Post Job</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
