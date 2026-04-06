<?php
/**
 * Admin Create Job View
 * HR/Admin can post new job openings
 */

$page_title = $page_title ?? 'Post New Job';
$departments = $departments ?? [];
$job_types = $job_types ?? ['Full-time', 'Part-time', 'Contract', 'Internship'];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2"></i>Post New Job</h1>
            <p class="text-muted mb-0">Create a new job posting for careers page</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/jobs" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Jobs
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="createJobForm" method="POST" action="<?php echo BASE_URL; ?>/admin/jobs/store">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Job Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required 
                               placeholder="e.g., Real Estate Sales Manager">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="department" class="form-label">Department *</label>
                        <select class="form-select" id="department" name="department" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="location" class="form-label">Location *</label>
                        <input type="text" class="form-control" id="location" name="location" required 
                               placeholder="e.g., Gorakhpur">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="job_type" class="form-label">Job Type *</label>
                        <select class="form-select" id="job_type" name="job_type" required>
                            <?php foreach ($job_types as $type): ?>
                                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="experience" class="form-label">Experience Required *</label>
                        <input type="text" class="form-control" id="experience" name="experience" required 
                               placeholder="e.g., 2-4 years">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="salary_range" class="form-label">Salary Range</label>
                        <input type="text" class="form-control" id="salary_range" name="salary_range" 
                               placeholder="e.g., ₹25,000 - ₹45,000/month">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="closing_date" class="form-label">Application Closing Date</label>
                        <input type="date" class="form-control" id="closing_date" name="closing_date">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Job Description *</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required
                              placeholder="Describe the job role, responsibilities, and what the candidate can expect..."></textarea>
                </div>

                <div class="mb-3">
                    <label for="requirements" class="form-label">Requirements & Qualifications</label>
                    <textarea class="form-control" id="requirements" name="requirements" rows="3"
                              placeholder="List required skills, education, certifications..."></textarea>
                </div>

                <div class="mb-3">
                    <label for="responsibilities" class="form-label">Key Responsibilities</label>
                    <textarea class="form-control" id="responsibilities" name="responsibilities" rows="3"
                              placeholder="List main duties and day-to-day tasks..."></textarea>
                </div>

                <div class="mb-3">
                    <label for="benefits" class="form-label">Benefits & Perks</label>
                    <textarea class="form-control" id="benefits" name="benefits" rows="2"
                              placeholder="List benefits like health insurance, bonuses, etc..."></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Post Job
                    </button>
                    <a href="<?php echo BASE_URL; ?>/admin/jobs" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('createJobForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Job posted successfully!');
            window.location.href = '<?php echo BASE_URL; ?>/admin/jobs';
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error posting job. Please try again.');
    });
});
</script>
