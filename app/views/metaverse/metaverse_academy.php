<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">Academy</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-graduation-cap me-3 text-primary"></i><?= ($page_title ?? 'Metaverse Academy') ?></h1>
        </div>
    </div>

    <?php $courses = $courses ?? []; ?>

    <div class="row g-4">
        <?php if (empty($courses)): ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-graduation-cap fa-5x text-muted mb-3"></i>
                <h3>No Courses Available</h3>
                <p class="text-muted">Courses are being developed. Check back soon.</p>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($courses as $key => $course): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <i class="fas fa-<?= ($key === 'vr_basics') ? 'vr-cardboard' : (($key === 'property_tours') ? 'building' : 'briefcase') ?> fa-3x text-primary"></i>
                        <span class="badge bg-<?= (($course['difficulty'] ?? '') === 'Beginner') ? 'success' : (($course['difficulty'] ?? '') === 'Intermediate' ? 'warning' : 'danger') ?>">
                            <?= ($course['difficulty'] ?? 'All Levels') ?>
                        </span>
                    </div>
                    <h5><?= ($course['title'] ?? 'Untitled Course') ?></h5>
                    <p class="card-text text-muted small"><?= ($course['description'] ?? '') ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted"><i class="fas fa-clock me-1"></i><?= ($course['duration'] ?? 'N/A') ?></small>
                        <small class="text-muted"><i class="fas fa-user me-1"></i><?= number_format($course['enrolled'] ?? 0) ?> enrolled</small>
                    </div>
                    <button class="btn btn-outline-primary w-100 mt-3" onclick="showToast('Course enrollment coming soon', 'info')"><i class="fas fa-play me-2"></i>Start Course</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
