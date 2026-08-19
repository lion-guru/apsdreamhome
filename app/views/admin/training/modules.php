<?php
$page_title = 'Training Modules';
$page_description = 'Manage course modules';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">Training Modules</h1>
                <p class="text-muted">Manage course modules and content</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                <i class="fas fa-plus me-1"></i>Add Module
            </button>
        </div>
    </div>

    <?php if (empty($modules)): ?>
    <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No modules found. Add your first module.</div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Course</th>
                        <th>Module Title</th>
                        <th>Order</th>
                        <th>Content Type</th>
                        <th>Duration</th>
                        <th>Required</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $m): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($m['course_title'] ?? '-'); ?></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($m['title'] ?? ''); ?></td>
                        <td><span class="badge bg-secondary"><?php echo $m['order_index'] ?? 0; ?></span></td>
                        <td>
                            <span class="badge bg-<?php echo match($m['content_type'] ?? '') {
                                'video' => 'danger',
                                'document' => 'primary',
                                'quiz' => 'warning',
                                'assignment' => 'info',
                                'live_session' => 'success',
                                default => 'secondary'
                            }; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $m['content_type'] ?? 'unknown')); ?>
                            </span>
                        </td>
                        <td><?php echo $m['duration_minutes'] ?? 0; ?> min</td>
                        <td>
                            <?php if ($m['is_required'] ?? 0): ?>
                            <span class="badge bg-danger">Required</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Optional</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-info" title="View" aria-label="View"><i class="fas fa-eye"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Module Modal -->
<div class="modal fade" id="addModuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo BASE_URL; ?>/admin/training/modules/store" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-cube me-2"></i>Add New Module</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php if (!empty($courses)): ?>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_title'] ?? ''); ?></option>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Module Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Order Index</label>
                            <input type="number" name="order_index" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Content Type</label>
                            <select name="content_type" class="form-select">
                                <option value="video">Video</option>
                                <option value="document">Document</option>
                                <option value="quiz">Quiz</option>
                                <option value="assignment">Assignment</option>
                                <option value="live_session">Live Session</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duration (Minutes)</label>
                            <input type="number" name="duration_minutes" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content URL</label>
                        <input type="url" name="content_url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_required" class="form-check-input" id="isRequired" value="1" checked>
                        <label class="form-check-label" for="isRequired">Required module</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Module</button>
                </div>
            </form>
        </div>
    </div>
</div>
