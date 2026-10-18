<?php
/**
 * Play Call Recording
 */

$page_title = $page_title ?? 'Play Call Recording';
$recording = $recording ?? [];
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-play-circle text-primary me-2"></i><?= htmlspecialchars($recording['lead_name'] ?? 'Recording') ?></h4>
            <small class="text-muted"><?= date('M d, Y H:i', strtotime($recording['created_at'])) ?></small>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/associate/call-recordings" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <a href="<?= BASE_URL ?>/associate/call-recordings/<?= $recording['id'] ?>/download" class="btn btn-primary">
                <i class="fas fa-download me-1"></i> Download
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <!-- Player Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-file-audio me-2"></i><?= htmlspecialchars($recording['file_name']) ?></h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="detail-row"><span class="detail-label">Lead</span><span class="detail-value"><?= htmlspecialchars($recording['lead_name'] ?? 'Unknown') ?></span></div>
                    <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value"><?= htmlspecialchars($recording['lead_phone'] ?? '-') ?></span></div>
                    <div class="detail-row"><span class="detail-label">Lead #</span><span class="detail-value"><?= htmlspecialchars($recording['lead_number'] ?? '-') ?></span></div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row"><span class="detail-label">Type</span><span class="detail-value"><span class="badge bg-primary"><?= ucfirst($recording['call_type']) ?></span></span></div>
                    <div class="detail-row"><span class="detail-label">Direction</span><span class="detail-value"><span class="badge bg-<?= $recording['direction'] === 'inbound' ? 'success' : 'primary' ?>"><?= ucfirst($recording['direction']) ?></span></span></div>
                    <div class="detail-row"><span class="detail-label">Duration</span><span class="detail-value"><strong><?= gmdate('H:i:s', $recording['duration']) ?></strong></span></div>
                    <div class="detail-row"><span class="detail-label">Caller #</span><span class="detail-value"><?= htmlspecialchars($recording['caller_number'] ?? '-') ?></span></div>
                    <div class="detail-row"><span class="detail-label">Date</span><span class="detail-value"><?= date('M d, Y H:i', strtotime($recording['created_at'])) ?></span></div>
                </div>
            </div>

            <!-- Audio Player -->
            <div class="card bg-light p-4 rounded">
                <h6 class="mb-3"><i class="fas fa-volume-up me-2"></i>Audio Player</h6>
                <audio controls controlsList="nodownload" style="width: 100%;">
                    <source src="<?= BASE_URL ?>/associate/call-recordings/<?= $recording['id'] ?>/download" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                <div class="mt-2 text-muted small">
                    <i class="fas fa-info-circle me-1"></i>Use the player controls to play/pause, seek, and adjust volume.
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($recording['notes'])): ?>
                <div class="mb-3 p-3 bg-light rounded">
                    <?= nl2br(htmlspecialchars($recording['notes'])) ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-3">No notes yet.</p>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/associate/call-recordings/<?= $recording['id'] ?>/note" class="d-flex gap-2">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <textarea class="form-control flex-grow-1" name="note" rows="2" placeholder="Add a note..." required></textarea>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Note
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.detail-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9; }
.detail-row:last-child { border-bottom: none; }
.detail-label { color: #64748b; font-size: 0.85rem; font-weight: 500; }
.detail-value { font-weight: 600; color: #1e293b; font-size: 0.85rem; text-align: right; }
</style>