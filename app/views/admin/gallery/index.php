<?php
$categories = $categories ?? [];
$current_category = $current_category ?? '';
$imgList = $images ?? [];
$total = count($imgList);
$active = 0; $inactive = 0;
foreach ($imgList as $img) {
    if (($img['status'] ?? 'active') === 'active') { $active++; } else { $inactive++; }
}
$totalCategories = count($categories);
?>

<div class="container-fluid py-4">
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark"><i class="fas fa-images text-primary me-2"></i>Gallery Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/site-content" class="text-decoration-none">Site Content</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gallery</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/gallery" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-external-link-alt me-1"></i>Public View
            </a>
            <a href="<?= BASE_URL ?>/admin/site-content" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-th-large me-1"></i>Site Content
            </a>
            <a href="<?= BASE_URL ?>/admin/gallery/create" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-plus me-1"></i>Add New Photo
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0d6efd;">
                <div class="card-body text-center py-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
                        <i class="fas fa-photo-video text-primary"></i>
                    </div>
                    <div class="fw-bold fs-4 text-dark"><?= $total ?></div>
                    <div class="text-muted small">Total Photos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #198754;">
                <div class="card-body text-center py-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="fw-bold fs-4 text-dark"><?= $active ?></div>
                    <div class="text-muted small">Active / Visible</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #6c757d;">
                <div class="card-body text-center py-3">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
                        <i class="fas fa-eye-slash text-secondary"></i>
                    </div>
                    <div class="fw-bold fs-4 text-dark"><?= $inactive ?></div>
                    <div class="text-muted small">Inactive / Hidden</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0dcaf0;">
                <div class="card-body text-center py-3">
                    <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
                        <i class="fas fa-tags text-info"></i>
                    </div>
                    <div class="fw-bold fs-4 text-dark"><?= $totalCategories ?></div>
                    <div class="text-muted small">Categories</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Live Search Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-body py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- Category Pills -->
                <div class="d-flex flex-wrap gap-1 align-items-center">
                    <span class="text-muted small me-2 fw-semibold"><i class="fas fa-filter me-1"></i>Filter:</span>
                    <a href="<?= BASE_URL ?>/admin/gallery" class="btn btn-sm rounded-pill px-3 <?= empty($current_category) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        All <span class="badge bg-light text-dark ms-1"><?= $total ?></span>
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="<?= BASE_URL ?>/admin/gallery?category=<?= urlencode($cat['category']) ?>" class="btn btn-sm rounded-pill px-3 <?= $current_category === $cat['category'] ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        <?= ucfirst(htmlspecialchars($cat['category'])) ?>
                        <span class="badge bg-light text-dark ms-1"><?= (int)($cat['cnt'] ?? 0) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Client-Side Search -->
                <div class="position-relative" style="min-width: 240px;">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="gallerySearch" class="form-control form-control-sm rounded-pill ps-5" placeholder="Search photos, titles, tags...">
                </div>
            </div>
        </div>
    </div>

    <!-- Photos Grid -->
    <?php if (!empty($imgList)): ?>
    <div class="row g-4" id="galleryGrid">
        <?php foreach ($imgList as $img): 
            $st = $img['status'] ?? 'active';
            $catName = htmlspecialchars($img['category'] ?? 'General');
            $imgTitle = htmlspecialchars($img['title'] ?? '');
            $imgCaption = htmlspecialchars($img['caption'] ?? '');
            $imgPath = htmlspecialchars($img['image_path'] ?? '');
            $fullImgUrl = !empty($imgPath) ? (str_starts_with($imgPath, 'http') ? $imgPath : BASE_URL . '/' . ltrim($imgPath, '/')) : '';
        ?>
        <div class="col-sm-6 col-md-4 col-xl-3 gallery-item" data-search="<?= strtolower($imgTitle . ' ' . $imgCaption . ' ' . $catName) ?>">
            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius:14px; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                <!-- Image Container with Aspect Ratio -->
                <div class="position-relative bg-light overflow-hidden" style="height: 190px;">
                    <?php if (!empty($fullImgUrl)): ?>
                        <img src="<?= $fullImgUrl ?>" alt="<?= $imgTitle ?: $imgCaption ?>" class="w-100 h-100 object-fit-cover gallery-thumb" loading="lazy" style="transition: transform 0.3s ease;">
                    <?php else: ?>
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                            <i class="fas fa-image fa-3x opacity-40 mb-2"></i>
                            <span class="small">No preview file</span>
                        </div>
                    <?php endif; ?>

                    <!-- Category Badge -->
                    <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-2 rounded-pill px-2.5 py-1 small backdrop-blur">
                        <i class="fas fa-tag me-1 text-warning"></i><?= ucfirst($catName) ?>
                    </span>

                    <!-- Status Badge -->
                    <?php if ($st === 'active'): ?>
                        <span class="badge bg-success position-absolute top-0 end-0 m-2 rounded-pill px-2.5 py-1 small">
                            <i class="fas fa-check-circle me-1"></i>Active
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary position-absolute top-0 end-0 m-2 rounded-pill px-2.5 py-1 small">
                            <i class="fas fa-eye-slash me-1"></i>Hidden
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Card Body -->
                <div class="card-body p-3 d-flex flex-column">
                    <h6 class="fw-bold mb-1 text-dark text-truncate" title="<?= $imgTitle ?: 'Untitled Photo' ?>">
                        <?= $imgTitle ?: '<span class="text-muted fst-italic">Untitled Photo</span>' ?>
                    </h6>
                    <p class="text-muted small mb-2 text-truncate-2 flex-grow-1" style="min-height: 38px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="<?= $imgCaption ?>">
                        <?= $imgCaption ?: 'No caption provided.' ?>
                    </p>

                    <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top">
                        <span><i class="fas fa-sort me-1"></i>Order: <strong><?= (int)($img['sort_order'] ?? 0) ?></strong></span>
                        <span style="font-size: 11px;">
                            <?= !empty($img['created_at']) ? date('d M Y', strtotime($img['created_at'])) : '' ?>
                        </span>
                    </div>
                </div>

                <!-- Card Footer Actions -->
                <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-3">
                    <div class="btn-group w-100 btn-group-sm">
                        <?php if (!empty($fullImgUrl)): ?>
                        <a href="<?= $fullImgUrl ?>" target="_blank" class="btn btn-outline-secondary" title="View Full Image">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/admin/gallery/<?= (int)$img['id'] ?>/edit" class="btn btn-outline-primary" title="Edit Photo Details">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="<?= BASE_URL ?>/admin/gallery/<?= (int)$img['id'] ?>/destroy" class="btn btn-outline-danger" data-aps-confirm="Are you sure you want to delete this photo?" title="Delete Photo">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Empty State -->
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius:14px;">
        <div class="card-body py-5">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3 text-muted">
                <i class="fas fa-images fa-3x opacity-50"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">No Photos in Gallery</h5>
            <p class="text-muted small mb-3">Upload your first property, project, or company event photo.</p>
            <a href="<?= BASE_URL ?>/admin/gallery/create" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-1"></i>Add First Photo
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.gallery-item .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}
.gallery-item .card:hover .gallery-thumb {
    transform: scale(1.04);
}
.backdrop-blur {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
</style>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function () {
    var searchInput = document.getElementById('gallerySearch');
    if (!searchInput) return;
    searchInput.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll('#galleryGrid .gallery-item').forEach(function (item) {
            var searchData = item.getAttribute('data-search') || '';
            item.style.display = searchData.includes(q) ? '' : 'none';
        });
    });
})();
</script>
