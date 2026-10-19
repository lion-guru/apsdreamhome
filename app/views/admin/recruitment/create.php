<?php
/** Admin Recruitment - Create/Edit Job Posting */

$page_title = $page_title ?? ($mode === 'edit' ? 'Edit Job Posting' : 'Create Job Posting');
$mode = $mode ?? 'create';
$job = $job ?? ['title' => '', 'job_code' => '', 'description' => '', 'requirements' => '', 'responsibilities' => '', 'benefits' => '', 'skills_required' => [], 'education_requirement' => '', 'openings' => 1, 'status' => 'draft', 'department' => '', 'designation' => '', 'employment_type' => 'full_time', 'experience_min' => 0, 'experience_max' => 0, 'salary_min' => null, 'salary_max' => null, 'salary_currency' => 'INR', 'location' => '', 'is_remote' => false, 'published_on' => [], 'hiring_manager_id' => '', 'recruiter_id' => '', 'created_by' => 1];
$managers = $managers ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-briefcase me-2 text-primary"></i><?= $page_title ?></h4>
            <p class="text-muted mb-0 small">Create a new job posting</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/recruitment" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Jobs
        </a>
    </div>

    <form method="POST" id="jobForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="mode" value="<?= $mode ?>">
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
        <?php endif; ?>

        <!-- Basic Info -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-briefcase me-2"></i>Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Job Code <span class="text-danger">*</span></label>
                        <input type="text" name="job_code" class="form-control" required value="<?= htmlspecialchars($job['job_code'] ?? '') ?>" placeholder="e.g., ENG-2024-001" <?= $mode === 'edit' ? 'readonly' : '' ?>>
                        <?php if ($mode === 'edit'): ?>
                            <div class="form-text">Code cannot be changed after creation</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($job['title'] ?? '') ?>" placeholder="e.g., Senior PHP Developer">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="department" class="form-select">
                            <option value="">Select Department</option>
                            <option value="sales" <?= ($job['department'] ?? '') === 'sales' ? 'selected' : '' ?>>Sales</option>
                            <option value="engineering" <?= ($job['department'] ?? '') === 'engineering' ? 'selected' : '' ?>>Engineering</option>
                            <option value="marketing" <?= ($job['department'] ?? '') === 'marketing' ? 'selected' : '' ?>>Marketing</option>
                            <option value="hr" <?= ($job['department'] ?? '') === 'hr' ? 'selected' : '' ?>>HR</option>
                            <option value="finance" <?= ($job['department'] ?? '') === 'finance' ? 'selected' : '' ?>>Finance</option>
                            <option value="operations" <?= ($job['department'] ?? '') === 'operations' ? 'selected' : '' ?>>Operations</option>
                            <option value="it" <?= ($job['department'] ?? '') === 'it' ? 'selected' : '' ?>>IT</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Designation</label>
                        <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($job['designation'] ?? '') ?>" placeholder="e.g., Senior Developer">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Employment Type</label>
                        <select name="employment_type" class="form-select">
                            <option value="full_time" <?= ($job['employment_type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                            <option value="part_time" <?= ($job['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                            <option value="contract" <?= ($job['employment_type'] ?? '') === 'contract' ? 'selected' : '' ?>>Contract</option>
                            <option value="intern" <?= ($job['employment_type'] ?? '') === 'intern' ? 'selected' : '' ?>>Intern</option>
                            <option value="freelance" <?= ($job['employment_type'] ?? '') === 'freelance' ? 'selected' : '' ?>>Freelance</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Experience (Min Years)</label>
                        <input type="number" min="0" max="30" name="experience_min" class="form-control" value="<?= htmlspecialchars($job['experience_min'] ?? 0) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Experience (Max Years)</label>
                        <input type="number" min="0" max="50" name="experience_max" class="form-control" value="<?= htmlspecialchars($job['experience_max'] ?? 0) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary & Location -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-rupee-sign me-2"></i>Salary & Location</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Min Salary (₹)</label>
                        <input type="number" min="0" step="10000" name="salary_min" class="form-control" value="<?= htmlspecialchars($job['salary_min'] ?? '') ?>" placeholder="e.g., 800000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Max Salary (₹)</label>
                        <input type="number" min="0" step="10000" name="salary_max" class="form-control" value="<?= htmlspecialchars($job['salary_max'] ?? '') ?>" placeholder="e.g., 1500000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Currency</label>
                        <select name="salary_currency" class="form-select">
                            <option value="INR" <?= ($job['salary_currency'] ?? '') === 'INR' ? 'selected' : '' ?>>INR (₹)</option>
                            <option value="USD" <?= ($job['salary_currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($job['location'] ?? '') ?>" placeholder="e.g., Bangalore, India">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_remote" value="1" id="is_remote" <?= !empty($job['is_remote']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_remote">Remote Eligible</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Experience Min (Years)</label>
                        <input type="number" min="0" name="experience_min" class="form-control" value="<?= htmlspecialchars($job['experience_min'] ?? 0) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Experience Max (Years)</label>
                        <input type="number" min="0" name="experience_max" class="form-control" value="<?= htmlspecialchars($job['experience_max'] ?? 0) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Openings</label>
                        <input type="number" min="1" name="openings" class="form-control" value="<?= htmlspecialchars($job['openings'] ?? 1) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Details -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-file-alt me-2"></i>Job Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Job Code <span class="text-danger">*</span></label>
                        <input type="text" name="job_code" class="form-control" required value="<?= htmlspecialchars($job['job_code'] ?? '') ?>" placeholder="e.g., ENG-2024-001" <?= $mode === 'edit' ? 'readonly' : '' ?>>
                        <?php if ($mode === 'edit'): ?>
                            <div class="form-text">Code cannot be changed after creation</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($job['title'] ?? '') ?>" placeholder="e.g., Senior PHP Developer">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="department" class="form-select">
                            <option value="">Select Department</option>
                            <option value="sales" <?= ($job['department'] ?? '') === 'sales' ? 'selected' : '' ?>>Sales</option>
                            <option value="engineering" <?= ($job['department'] ?? '') === 'engineering' ? 'selected' : '' ?>>Engineering</option>
                            <option value="marketing" <?= ($job['department'] ?? '') === 'marketing' ? 'selected' : '' ?>>Marketing</option>
                            <option value="hr" <?= ($job['department'] ?? '') === 'hr' ? 'selected' : '' ?>>HR</option>
                            <option value="finance" <?= ($job['department'] ?? '') === 'finance' ? 'selected' : '' ?>>Finance</option>
                            <option value="operations" <?= ($job['department'] ?? '') === 'operations' ? 'selected' : '' ?>>Operations</option>
                            <option value="it" <?= ($job['department'] ?? '') === 'it' ? 'selected' : '' ?>>IT</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Designation</label>
                        <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($job['designation'] ?? '') ?>" placeholder="e.g., Senior Developer">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Employment Type</label>
                        <select name="employment_type" class="form-select">
                            <option value="full_time" <?= ($job['employment_type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                            <option value="part_time" <?= ($job['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                            <option value="contract" <?= ($job['employment_type'] ?? '') === 'contract' ? 'selected' : '' ?>>Contract</option>
                            <option value="intern" <?= ($job['employment_type'] ?? '') === 'intern' ? 'selected' : '' ?>>Intern</option>
                            <option value="freelance" <?= ($job['employment_type'] ?? '') === 'freelance' ? 'selected' : '' ?>>Freelance</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary & Location -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-rupee-sign me-2"></i>Salary & Location</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Min Salary (₹)</label>
                        <input type="number" min="0" step="10000" name="salary_min" class="form-control" value="<?= htmlspecialchars($job['salary_min'] ?? '') ?>" placeholder="e.g., 800000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Max Salary (₹)</label>
                        <input type="number" min="0" step="10000" name="salary_max" class="form-control" value="<?= htmlspecialchars($job['salary_max'] ?? '') ?>" placeholder="e.g., 1500000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Currency</label>
                        <select name="salary_currency" class="form-select">
                            <option value="INR" <?= ($job['salary_currency'] ?? '') === 'INR' ? 'selected' : '' ?>>INR (₹)</option>
                            <option value="USD" <?= ($job['salary_currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($job['location'] ?? '') ?>" placeholder="e.g., Bangalore, India">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_remote" value="1" id="is_remote" <?= !empty($job['is_remote']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_remote">Remote Eligible</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Experience Min (Years)</label>
                        <input type="number" min="0" name="experience_min" class="form-control" value="<?= htmlspecialchars($job['experience_min'] ?? 0) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Experience Max (Years)</label>
                        <input type="number" min="0" name="experience_max" class="form-control" value="<?= htmlspecialchars($job['experience_max'] ?? 0) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Openings</label>
                        <input type="number" min="1" name="openings" class="form-control" value="<?= htmlspecialchars($job['openings'] ?? 1) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Details -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-file-alt me-2"></i>Job Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Job Code <span class="text-danger">*</span></label>
                        <input type="text" name="job_code" class="form-control" required value="<?= htmlspecialchars($job['job_code'] ?? '') ?>" placeholder="e.g., ENG-2024-001" <?= $mode === 'edit' ? 'readonly' : '' ?>>
                        <?php if ($mode === 'edit'): ?>
                            <div class="form-text">Code cannot be changed after creation</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($job['title'] ?? '') ?>" placeholder="e.g., Senior PHP Developer">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="department" class="form-select">
                            <option value="">Select Department</option>
                            <option value="sales" <?= ($job['department'] ?? '') === 'sales' ? 'selected' : '' ?>>Sales</option>
                            <option value="engineering" <?= ($job['department'] ?? '') === 'engineering' ? 'selected' : '' ?>>Engineering</option>
                            <option value="marketing" <?= ($job['department'] ?? '') === 'marketing' ? 'selected' : '' ?>>Marketing</option>
                            <option value="hr" <?= ($job['department'] ?? '') === 'hr' ? 'selected' : '' ?>>HR</option>
                            <option value="finance" <?= ($job['department'] ?? '') === 'finance' ? 'selected' : '' ?>>Finance</option>
                            <option value="operations" <?= ($job['department'] ?? '') === 'operations' ? 'selected' : '' ?>>Operations</option>
                            <option value="it" <?= ($job['department'] ?? '') === 'it' ? 'selected' : '' ?>>IT</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Designation</label>
                        <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($job['designation'] ?? '') ?>" placeholder="e.g., Senior Developer">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Employment Type</label>
                        <select name="employment_type" class="form-select">
                            <option value="full_time" <?= ($job['employment_type'] ?? '') === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                            <option value="part_time" <?= ($job['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                            <option value="contract" <?= ($job['employment_type'] ?? '') === 'contract' ? 'selected' : '' ?>>Contract</option>
                            <option value="intern" <?= ($job['employment_type'] ?? '') === 'intern' ? 'selected' : '' ?>>Intern</option>
                            <option value="freelance" <?= ($job['employment_type'] ?? '') === 'freelance' ? 'selected' : '' ?>>Freelance</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary & Location -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-rupee-sign me-2"></i>Salary & Location</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Min Salary (₹)</label>
                        <input type="number" min="0" step="10000" name="salary_min" class="form-control" value="<?= htmlspecialchars($job['salary_min'] ?? '') ?>" placeholder="e.g., 800000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Max Salary (₹)</label>
                        <input type="number" min="0" step="10000" name="salary_max" class="form-control" value="<?= htmlspecialchars($job['salary_max'] ?? '') ?>" placeholder="e.g., 1500000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Currency</label>
                        <select name="salary_currency" class="form-select">
                            <option value="INR" <?= ($job['salary_currency'] ?? '') === 'INR' ? 'selected' : '' ?>>INR (₹)</option>
                            <option value="USD" <?= ($job['salary_currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($job['location'] ?? '') ?>" placeholder="e.g., Bangalore, India">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_remote" value="1" id="is_remote" <?= !empty($job['is_remote']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_remote">Remote Eligible</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Experience Min (Years)</label>
                        <input type="number" min="0" name="experience_min" class="form-control" value="<?= htmlspecialchars($job['experience_min'] ?? 0) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Experience Max (Years)</label>
                        <input type="number" min="0" name="experience_max" class="form-control" value="<?= htmlspecialchars($job['experience_max'] ?? 0) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Openings</label>
                        <input type="number" min="1" name="openings" class="form-control" value="<?= htmlspecialchars($job['openings'] ?? 1) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Details -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-file-alt me-2"></i>Job Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="5" required placeholder="Role overview, company culture, impact..."><?= htmlspecialchars($job['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Requirements</label>
                        <textarea name="requirements" class="form-control" rows="4" placeholder="Technical skills, experience, education..."><?= htmlspecialchars($job['requirements'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Responsibilities</label>
                        <textarea name="responsibilities" class="form-control" rows="4" placeholder="Day-to-day responsibilities..."><?= htmlspecialchars($job['responsibilities'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Benefits</label>
                        <textarea name="benefits" class="form-control" rows="3" placeholder="Health insurance, PF, leave policy, learning budget..."><?= htmlspecialchars($job['benefits'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Skills Required (one per line)</label>
                        <textarea name="skills_required" class="form-control" rows="4" placeholder="PHP&#10;Laravel&#10;MySQL&#10;REST APIs"><?= is_array($job['skills_required'] ?? null) ? implode("\n", $job['skills_required']) : ($job['skills_required'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Education Requirement</label>
                        <input type="text" name="education_requirement" class="form-control" value="<?= htmlspecialchars($job['education_requirement'] ?? '') ?>" placeholder="e.g., Bachelor's in CS or equivalent">
                    </div>
                </div>
            </div>
        </div>

        <!-- Hiring Team -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-users me-2"></i>Hiring Team</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hiring Manager</label>
                        <select name="hiring_manager_id" class="form-select">
                            <option value="">Select Manager</option>
                            <?php foreach ($managers as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($job['hiring_manager_id'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Recruiter</label>
                        <select name="recruiter_id" class="form-select">
                            <option value="">Select Recruiter</option>
                            <?php foreach ($managers as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($job['recruiter_id'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Published On</label>
                        <select name="published_on[]" class="form-select" multiple>
                            <option value="careers_page" <?= in_array('careers_page', $job['published_on'] ?? []) ? 'selected' : '' ?>>Careers Page</option>
                            <option value="linkedin" <?= in_array('linkedin', $job['published_on'] ?? []) ? 'selected' : '' ?>>LinkedIn</option>
                            <option value="naukri" <?= in_array('naukri', $job['published_on'] ?? []) ? 'selected' : '' ?>>Naukri</option>
                            <option value="indeed" <?= in_array('indeed', $job['published_on'] ?? []) ? 'selected' : '' ?>>Indeed</option>
                            <option value="referral" <?= in_array('referral', $job['published_on'] ?? []) ? 'selected' : '' ?>>Employee Referral</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Closes At</label>
                        <input type="datetime-local" name="closes_at" class="form-control" value="<?= htmlspecialchars($job['closes_at'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/admin/recruitment" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <?php if ($mode === 'edit' && $job['status'] === 'draft'): ?>
                <button type="button" class="btn btn-success" onclick="publishJob()">
                    <i class="fas fa-paper-plane me-1"></i> Publish Job
                </button>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Save Job
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.getElementById('jobForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});

function publishJob() {
    if (!confirm('Publish this job posting?')) return;
    const form = document.getElementById('jobForm');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'mode';
    input.value = 'publish';
    form.appendChild(input);
    form.submit();
}
</script>