<?php
$u = $upload ?? [];
$success = $success ?? null;
$error = $error ?? null;
?>
<div class="container-fluid py-4">
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-microphone me-2"></i>Voice Upload #<?= $u['id'] ?? '' ?></h4>
        <a href="<?= BASE_URL ?>/admin/voice-uploads" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to List</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Voice Upload Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr><th style="width:160px">ID</th><td>#<?= $u['id'] ?? '' ?></td></tr>
                            <tr><th>Language</th><td><?= htmlspecialchars(strtoupper($u['language'] ?? 'hi')) ?></td></tr>
                            <tr><th>Duration</th><td><?= (int)($u['duration_seconds'] ?? 0) ?> seconds</td></tr>
                            <tr><th>File Size</th><td><?php
                                $bytes = (int)($u['file_size_bytes'] ?? 0);
                                if ($bytes >= 1048576) echo round($bytes / 1048576, 2) . ' MB';
                                elseif ($bytes >= 1024) echo round($bytes / 1024, 1) . ' KB';
                                else echo $bytes . ' B';
                            ?></td></tr>
                            <tr><th>Status</th><td>
                                <?php
                                $statusColors = ['pending' => 'warning', 'processed' => 'success', 'failed' => 'danger', 'deleted' => 'secondary'];
                                $color = $statusColors[$u['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?> fs-6"><?= ucfirst($u['status'] ?? 'Pending') ?></span>
                            </td></tr>
                            <tr><th>Created</th><td><?= date('d M Y H:i', strtotime($u['created_at'] ?? 'now')) ?></td></tr>
                            <tr><th>Updated</th><td><?= date('d M Y H:i', strtotime($u['updated_at'] ?? 'now')) ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-user me-2"></i>User Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr><th style="width:160px">Name</th><td><?= htmlspecialchars($u['user_name'] ?? '—') ?></td></tr>
                            <tr><th>Phone</th><td><?= htmlspecialchars($u['user_phone'] ?? '—') ?></td></tr>
                            <tr><th>User ID</th><td><?= (int)($u['user_id'] ?? 0) ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-file-audio me-2"></i>Voice Sample</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Path</label>
                        <div class="form-control bg-light" style="word-break:break-all"><?= htmlspecialchars($u['voice_sample_path'] ?? '—') ?></div>
                    </div>
                    <?php if (!empty($u['voice_sample_path']) && $u['status'] !== 'deleted'): ?>
                        <?php
                        $audioUrl = $u['voice_sample_path'];
                        if (strpos($audioUrl, 'http') !== 0) {
                            $audioUrl = '/' . ltrim($audioUrl, '/');
                        }
                        ?>
                        <audio controls class="w-100">
                            <source src="<?= htmlspecialchars($audioUrl) ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-align-left me-2"></i>Transcript & Notes</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Transcript</label>
                        <div class="form-control bg-light" style="min-height:80px;white-space:pre-wrap"><?= htmlspecialchars($u['transcript'] ?? 'No transcript available') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <div class="form-control bg-light" style="min-height:60px;white-space:pre-wrap"><?= htmlspecialchars($u['notes'] ?? 'No notes') ?></div>
                    </div>
                </div>
            </div>

            <?php if (!empty($u['processed_by']) || !empty($u['processed_at'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-check-double me-2"></i>Processing Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr><th style="width:160px">Processed By</th><td><?= htmlspecialchars($u['processed_by_name'] ?? '—') ?></td></tr>
                            <tr><th>Processed At</th><td><?= !empty($u['processed_at']) ? date('d M Y H:i', strtotime($u['processed_at'])) : '—' ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <?php if (($u['status'] ?? '') !== 'processed' && ($u['status'] ?? '') !== 'deleted'): ?>
                            <a href="<?= BASE_URL ?>/admin/voice-uploads/process/<?= (int)($u['id'] ?? 0) ?>" class="btn btn-success" onclick="return confirm('Mark this upload as processed?')">
                                <i class="fas fa-check me-1"></i>Mark Processed
                            </a>
                        <?php endif; ?>
                        <?php if (($u['status'] ?? '') !== 'deleted'): ?>
                            <a href="<?= BASE_URL ?>/admin/voice-uploads/delete/<?= (int)($u['id'] ?? 0) ?>" class="btn btn-danger" onclick="return confirm('Delete this voice upload?')">
                                <i class="fas fa-trash me-1"></i>Delete
                            </a>
                        <?php else: ?>
                            <span class="text-muted"><i class="fas fa-ban me-1"></i>Already deleted</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
