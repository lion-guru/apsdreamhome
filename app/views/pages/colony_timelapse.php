<?php
/**
 * Public & Customer Viewer: Colony Construction Timelapse with Interactive Slider
 */
$colony = $colony ?? [];
$milestones = $milestones ?? [];
$overall_progress = $overall_progress ?? 0;
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>

<style>
/* Before/After Split Comparison Slider */
.ba-slider-container {
    position: relative;
    width: 100%;
    height: 440px;
    overflow: hidden;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    user-select: none;
}
.ba-slider-img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.ba-slider-before {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 50%;
    overflow: hidden;
    z-index: 2;
}
.ba-slider-before img {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    object-fit: cover;
}
.ba-slider-handle {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50%;
    width: 4px;
    background: #fff;
    z-index: 3;
    cursor: ew-resize;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
}
.ba-slider-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #0d6efd;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    font-size: 14px;
}
.ba-badge-label {
    position: absolute;
    padding: 6px 14px;
    border-radius: 20px;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    z-index: 4;
    letter-spacing: 0.5px;
}
.ba-badge-before { top: 16px; left: 16px; }
.ba-badge-after { top: 16px; right: 16px; }

/* Timeline Stepper */
.timeline-stepper {
    position: relative;
    padding-left: 30px;
}
.timeline-stepper::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: #e2e8f0;
}
.timeline-step {
    position: relative;
    margin-bottom: 25px;
}
.timeline-step-dot {
    position: absolute;
    left: -30px;
    top: 2px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #0d6efd;
}
.timeline-step-dot.completed {
    background: #198754;
    border-color: #198754;
}
.timeline-step-dot.in_progress {
    background: #ffc107;
    border-color: #ffc107;
}
</style>

<div class="container py-4">
    <!-- Header Breadcrumb & Title -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/colonies">Townships</a></li>
                    <li class="breadcrumb-item active"><?= $e($colony['name']) ?></li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1"><i class="fas fa-layer-group text-primary me-2"></i><?= $e($colony['name']) ?> Timelapse</h2>
            <p class="text-muted mb-0">Verified engineering milestones, Before/After progress slider &amp; aerial drone surveillance</p>
        </div>
        <div>
            <a href="https://wa.me/919999999999?text=Hi,%20I%20am%20interested%20in%20<?= urlencode($colony['name']) ?>%20site%20visit" target="_blank" class="btn btn-success">
                <i class="fab fa-whatsapp me-1"></i> Book Sunday Site Visit
            </a>
        </div>
    </div>

    <!-- Progress Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold fs-6">Overall Township Development</span>
                    <span class="badge bg-primary fs-6 px-3 py-1"><?= (int)$overall_progress ?>% Complete</span>
                </div>
                <div class="progress" style="height: 14px; border-radius: 8px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" 
                         style="width: <?= (int)$overall_progress ?>%;"></div>
                </div>
            </div>
            <div class="col-md-4 text-md-end pt-3 pt-md-0">
                <div class="text-muted small">Location: <strong><?= $e($colony['location'] ?: 'Gorakhpur') ?></strong></div>
                <div class="text-muted small">Total Milestones Logged: <strong><?= count($milestones) ?> Stages</strong></div>
            </div>
        </div>
    </div>

    <!-- Featured Before/After Comparison Slider (from the latest milestone with both photos) -->
    <?php
    $featuredSlider = null;
    foreach (array_reverse($milestones) as $m) {
        if (!empty($m['photo_before']) && !empty($m['photo_after'])) {
            $featuredSlider = $m;
            break;
        }
    }
    ?>

    <?php if ($featuredSlider): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold mb-0">
                <i class="fas fa-sliders-h text-primary me-2"></i>
                Transformation Slider: <?= $e($featuredSlider['title']) ?>
            </h5>
            <small class="text-muted">Drag the vertical bar left and right to compare Raw Land vs Current Progress</small>
        </div>
        <div class="card-body p-3">
            <div class="ba-slider-container" id="baContainer">
                <!-- After Image (Background) -->
                <img src="<?= $base ?>/<?= $e($featuredSlider['photo_after']) ?>" class="ba-slider-img" id="afterImg" alt="After Transformation">
                <span class="ba-badge-label ba-badge-after"><i class="fas fa-check-circle text-success me-1"></i>CURRENT STATE</span>

                <!-- Before Image (Overlay clipped) -->
                <div class="ba-slider-before" id="beforeClip">
                    <img src="<?= $base ?>/<?= $e($featuredSlider['photo_before']) ?>" id="beforeImg" alt="Before Development">
                </div>
                <span class="ba-badge-label ba-badge-before"><i class="fas fa-history text-warning me-1"></i>INITIAL RAW LAND</span>

                <!-- Draggable Divider -->
                <div class="ba-slider-handle" id="sliderHandle">
                    <div class="ba-slider-button">
                        <i class="fas fa-arrows-alt-h"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Milestone Stepper Breakdown -->
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-tasks text-primary me-2"></i>Engineering Lifecycle Stepper</h5>
                </div>
                <div class="card-body p-4">
                    <div class="timeline-stepper">
                        <?php foreach ($milestones as $m): ?>
                            <div class="timeline-step">
                                <div class="timeline-step-dot <?= $e($m['status']) ?>"></div>
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                    <h6 class="fw-bold mb-0"><?= $e($m['title']) ?></h6>
                                    <span class="badge bg-<?= $m['status'] === 'completed' ? 'success' : ($m['status'] === 'in_progress' ? 'warning text-dark' : 'secondary') ?>">
                                        <?= ucfirst(str_replace('_', ' ', $m['status'])) ?> (<?= (int)$m['completion_pct'] ?>%)
                                    </span>
                                </div>
                                <?php if (!empty($m['description'])): ?>
                                    <p class="text-muted small mb-2"><?= nl2br($e($m['description'])) ?></p>
                                <?php endif; ?>

                                <!-- Before/After thumbnails -->
                                <?php if (!empty($m['photo_before']) || !empty($m['photo_after'])): ?>
                                    <div class="row g-2 mb-2">
                                        <?php if (!empty($m['photo_before'])): ?>
                                            <div class="col-6 col-md-4">
                                                <img src="<?= $base ?>/<?= $e($m['photo_before']) ?>" class="img-fluid rounded border" style="height: 100px; width: 100%; object-fit: cover;" alt="Before">
                                                <span class="badge bg-dark small d-block mt-1">Before</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($m['photo_after'])): ?>
                                            <div class="col-6 col-md-4">
                                                <img src="<?= $base ?>/<?= $e($m['photo_after']) ?>" class="img-fluid rounded border" style="height: 100px; width: 100%; object-fit: cover;" alt="After">
                                                <span class="badge bg-success small d-block mt-1">After</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($m['drone_video_url'])): ?>
                                    <div class="small my-1">
                                        <a href="<?= $e($m['drone_video_url']) ?>" target="_blank" class="btn btn-sm btn-outline-danger py-0">
                                            <i class="fab fa-youtube me-1"></i> Watch Aerial Drone Video
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <div class="small text-muted mt-1">
                                    <?= !empty($m['completed_date']) ? 'Completed: ' . date('d M Y', strtotime($m['completed_date'])) : '' ?>
                                    <?= !empty($m['verified_by']) ? ' · Verified by: ' . $e($m['verified_by']) : '' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar: Drone surveillance & Live Layout link -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white p-4">
                <h5 class="fw-bold mb-2"><i class="fas fa-map-marked-alt me-2"></i>Explore Live Plot Layout</h5>
                <p class="small text-white-50 mb-3">Check real-time plot availability, road width, and corner plot premiums on our interactive Leaflet map.</p>
                <a href="<?= $base ?>/colony/<?= (int)$colony['id'] ?>/plots" class="btn btn-light text-primary fw-bold">
                    <i class="fas fa-map me-1"></i> View Interactive Layout
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-shield-alt text-success me-2"></i>APS Development Guarantee</h6>
                <ul class="list-unstyled small text-muted mb-0">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>40ft &amp; 30ft Wide Bitumen Tarred Roads</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Dedicated Heavy-Duty Electric Transformer</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Underground Sewerage &amp; Drainage Lines</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>24x7 Security Guard Cabin &amp; CCTV Posts</li>
                    <li><i class="fas fa-check text-success me-2"></i>Park Boundary &amp; Plantation Landscaping</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('baContainer');
    var beforeClip = document.getElementById('beforeClip');
    var beforeImg = document.getElementById('beforeImg');
    var afterImg = document.getElementById('afterImg');
    var handle = document.getElementById('sliderHandle');

    if (!container || !beforeClip || !handle) return;

    function setWidths() {
        var width = container.offsetWidth;
        if (beforeImg) beforeImg.style.width = width + 'px';
    }
    window.addEventListener('resize', setWidths);
    setWidths();

    var isDragging = false;

    function onMove(e) {
        if (!isDragging) return;
        var rect = container.getBoundingClientRect();
        var clientX = e.clientX || (e.touches && e.touches[0].clientX);
        if (!clientX) return;

        var offsetX = clientX - rect.left;
        if (offsetX < 0) offsetX = 0;
        if (offsetX > rect.width) offsetX = rect.width;

        var pct = (offsetX / rect.width) * 100;
        beforeClip.style.width = pct + '%';
        handle.style.left = pct + '%';
    }

    handle.addEventListener('mousedown', function() { isDragging = true; });
    window.addEventListener('mouseup', function() { isDragging = false; });
    window.addEventListener('mousemove', onMove);

    handle.addEventListener('touchstart', function() { isDragging = true; });
    window.addEventListener('touchend', function() { isDragging = false; });
    window.addEventListener('touchmove', onMove);
});
</script>
