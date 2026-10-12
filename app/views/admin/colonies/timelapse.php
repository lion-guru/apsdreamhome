<?php
/**
 * Admin: Colony Construction Timelapse & Milestone Manager
 */
$colony = $colony ?? [];
$milestones = $milestones ?? [];
$overall_progress = $overall_progress ?? 0;
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-video text-primary me-2"></i>Construction Timelapse &amp; Drone Feed</h4>
        <p class="text-muted mb-0">Manage development stages &amp; Before/After photography for <strong><?= $e($colony['name']) ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/colony/<?= (int)$colony['id'] ?>/timelapse" target="_blank" class="btn btn-outline-primary">
            <i class="fas fa-external-link-alt me-1"></i> Preview Customer View
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#milestoneModal">
            <i class="fas fa-plus me-1"></i> Add Milestone Stage
        </button>
    </div>
</div>

<!-- Progress Bar Summary -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold">Overall Development Progress</span>
            <span class="fw-bold fs-5 text-primary"><?= (int)$overall_progress ?>% Complete</span>
        </div>
        <div class="progress" style="height: 12px;">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" 
                 style="width: <?= (int)$overall_progress ?>%;" aria-valuenow="<?= (int)$overall_progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted mt-2">
            <span>Raw Land Demarcation</span>
            <span>Earth Leveling</span>
            <span>Drainage &amp; Sewerage</span>
            <span>Electrification</span>
            <span>Road Tarring</span>
            <span>Gated Security Arch</span>
        </div>
    </div>
</div>

<!-- Milestones List -->
<div class="row g-4">
    <?php if (empty($milestones)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 py-5 text-center text-muted">
                <i class="fas fa-hammer fa-3x mb-3 text-secondary opacity-50"></i>
                <h5>No construction milestones added yet</h5>
                <p class="mb-3">Start logging development progress with Before/After photos and drone videos.</p>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#milestoneModal">
                        <i class="fas fa-plus me-1"></i> Add First Stage
                    </button>
                </div>
            </div>
        </div>
    <?php else: foreach ($milestones as $m): ?>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-light text-dark border me-1">Stage <?= (int)$m['stage_order'] ?></span>
                        <strong class="fs-6"><?= $e($m['title']) ?></strong>
                    </div>
                    <?php
                    $badgeCls = match ($m['status']) {
                        'completed' => 'bg-success',
                        'in_progress' => 'bg-warning text-dark',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge <?= $badgeCls ?>"><?= ucfirst(str_replace('_', ' ', $m['status'])) ?> (<?= (int)$m['completion_pct'] ?>%)</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($m['description'])): ?>
                        <p class="text-muted small mb-3"><?= nl2br($e($m['description'])) ?></p>
                    <?php endif; ?>

                    <!-- Before/After Image Preview Thumbnails -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="small text-muted fw-semibold">Before</label>
                            <?php if (!empty($m['photo_before'])): ?>
                                <img src="<?= $base ?>/<?= $e($m['photo_before']) ?>" class="img-fluid rounded border w-100" style="height: 140px; object-fit: cover;" alt="Before">
                            <?php else: ?>
                                <div class="bg-light border rounded text-center py-4 small text-muted" style="height: 140px;">No 'Before' Photo</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted fw-semibold">After / Current</label>
                            <?php if (!empty($m['photo_after'])): ?>
                                <img src="<?= $base ?>/<?= $e($m['photo_after']) ?>" class="img-fluid rounded border w-100" style="height: 140px; object-fit: cover;" alt="After">
                            <?php else: ?>
                                <div class="bg-light border rounded text-center py-4 small text-muted" style="height: 140px;">No 'After' Photo</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($m['drone_video_url'])): ?>
                        <div class="small mb-3">
                            <i class="fas fa-play-circle text-danger me-1"></i>
                            <a href="<?= $e($m['drone_video_url']) ?>" target="_blank" class="text-decoration-none">Watch Drone Aerial Video</a>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between small text-muted border-top pt-2">
                        <span>Target: <?= !empty($m['target_date']) ? date('d M Y', strtotime($m['target_date'])) : 'N/A' ?></span>
                        <span><?= !empty($m['verified_by']) ? 'Verified: ' . $e($m['verified_by']) : '' ?></span>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-2 d-flex justify-content-end gap-2">
                    <form method="POST" action="<?= $base ?>/admin/colonies/<?= (int)$colony['id'] ?>/timelapse/delete/<?= (int)$m['id'] ?>" onsubmit="return confirm('Delete this milestone stage?')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>Delete</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<!-- Modal: Add Milestone -->
<div class="modal fade" id="milestoneModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="<?= $base ?>/admin/colonies/<?= (int)$colony['id'] ?>/timelapse/store" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Add Development Stage Milestone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Stage Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. 40ft Main Road Tarring" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Stage Order (1-10)</label>
                        <input type="number" name="stage_order" class="form-control" value="<?= count($milestones) + 1 ?>" min="1" max="20" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Stage Code</label>
                        <select name="milestone_code" class="form-select">
                            <option value="DEMARCATION">Demarcation &amp; Boundaries</option>
                            <option value="LEVELING">Earth Leveling</option>
                            <option value="DRAINAGE">Sewerage &amp; Drainage</option>
                            <option value="ELECTRIC">Transformer &amp; Poles</option>
                            <option value="ROAD_TARRING">Pitch / Asphalt Roads</option>
                            <option value="PARK_GATE">Gated Arch &amp; Park</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Engineering Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Describe the materials used, road width, transformer capacity, etc."></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="planned">Planned</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Completion %</label>
                        <input type="number" name="completion_pct" class="form-control" min="0" max="100" value="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Site Engineer / Supervisor</label>
                        <input type="text" name="verified_by" class="form-control" placeholder="e.g. Er. Rajesh Verma">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Before Photo (Raw/Initial State)</label>
                        <input type="file" name="photo_before" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">After / Current Photo (Developed State)</label>
                        <input type="file" name="photo_after" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small">Drone Aerial Video URL (YouTube / Vimeo / MP4)</label>
                        <input type="url" name="drone_video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Target Date</label>
                        <input type="date" name="target_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Completed Date</label>
                        <input type="date" name="completed_date" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Milestone</button>
            </div>
        </form>
    </div>
</div>
