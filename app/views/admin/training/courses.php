<?php
/** Admin Training Courses List */

$page_title = $page_title ?? 'Training Courses';
$courses = $courses ?? [];
$stats = $stats ?? [];
$categories = $categories ?? [];
$filters = $filters ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-graduation-cap me-2 text-primary"></i>Training Courses</h4>
            <p class="text-muted mb-0 small">Manage training courses and learning content</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/training/courses/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Course
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total_courses'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Courses</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total_assignments'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Assignments</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['completed_assignments'] ?? 0 ?></div>
                    <div class="text-white-50 small">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['completion_rate'] ?? 0 ?>%</div>
                    <div class="text-white-50 small">Completion Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Title, code, description...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($filters['category'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="is_active" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/training/courses" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Courses Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($courses)): ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Courses Found</h5>
                <p class="text-muted small mb-3">Get started by creating your first training course</p>
                <a href="<?= BASE_URL ?>/admin/training/courses/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Course</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px">Code</th>
                            <th>Title</th>
                            <th style="width: 150px">Category</th>
                            <th style="width: 100px">Format</th>
                            <th style="width: 100px">Duration</th>
                            <th style="width: 120px">Assigned</th>
                            <th style="width: 120px">Completed</th>
                            <th style="width: 100px">Status</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><code class="small"><?= htmlspecialchars($course['code']) ?></code></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($course['title']) ?></div>
                                <?php if (!empty($course['description'])): ?>
                                    <div class="small text-muted text-truncate" style="max-width: 300px;"><?= htmlspecialchars($course['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" style="background: #f1f5f9; color: #475569;">
                                    <?= htmlspecialchars($categories[$course['category']] ?? $course['category']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($course['format'])) ?></span>
                            </td>
                            <td><?= $course['duration_hours'] ?> hrs</td>
                            <td><?= $course['total_assigned'] ?? 0 ?></td>
                            <td>
                                <span class="fw-semibold text-success"><?= $course['total_completed'] ?? 0 ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $course['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $course['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/training/courses/edit/<?= $course['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/training/assignments?course_id=<?= $course['id'] ?>" class="btn btn-outline-info" title="View Assignments"><i class="fas fa-users"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/training/courses/delete/<?= $course['id'] ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Delete this course?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>