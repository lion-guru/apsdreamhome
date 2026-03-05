<?php
// app/views/pages/downloads.php
// Available variables: $downloads, $categories, $pagination
?>

<!-- Hero Section -->
<section class="downloads-hero text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Downloads & Resources</h1>
        <p class="lead mb-0">Access important documents, brochures, and forms.</p>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Downloads</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <!-- Categories Filter -->
        <?php if (!empty($categories)): ?>
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <div class="btn-group flex-wrap" role="group">
                        <a href="<?= BASE_URL ?>downloads" class="btn btn-outline-primary <?= !isset(Security::sanitize($_GET['category'])) || Security::sanitize($_GET['category']) == 'all' ? 'active' : '' ?>">All</a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="<?= BASE_URL ?>downloads?category=<?= urlencode($cat) ?>" class="btn btn-outline-primary <?= isset(Security::sanitize($_GET['category'])) && Security::sanitize($_GET['category']) == $cat ? 'active' : '' ?>">
                                <?= htmlspecialchars(ucfirst($cat)) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Downloads Grid -->
        <?php if (!empty($downloads)): ?>
            <div class="row">
                <?php foreach ($downloads as $download): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 download-card p-4 text-center position-relative shadow-sm border-0">
                            <?php if (!empty($download->category)): ?>
                                <span class="download-category-badge"><?= htmlspecialchars($download->category) ?></span>
                            <?php endif; ?>

                            <div class="download-icon text-primary mb-3">
                                <i class="fas fa-file-pdf fa-3x"></i>
                            </div>

                            <h5 class="card-title mb-2"><?= htmlspecialchars($download->title) ?></h5>
                            <p class="card-text text-muted small mb-4"><?= htmlspecialchars($download->description ?? '') ?></p>

                            <div class="mt-auto">
                                <a href="<?= !empty($download->file_path) ? get_asset_url($download->file_path) : '#' ?>" class="btn btn-primary download-btn rounded-pill px-4" download>
                                    <i class="fas fa-download me-2"></i> Download
                                </a>
                                <div class="download-meta mt-3 small text-muted">
                                    <span><i class="fas fa-calendar-alt me-1"></i> <?= date('M d, Y', strtotime($download->created_at ?? 'now')) ?></span>
                                    <?php if (!empty($download->file_size)): ?>
                                        <span class="ms-2 border-start ps-2"><i class="fas fa-weight-hanging me-1"></i> <?= $download->file_size ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if (isset($pagination['total_pages']) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?= $pagination['current_page'] == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= isset(Security::sanitize($_GET['category'])) ? '&category=' . urlencode(Security::sanitize($_GET['category'])) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-cloud-download-alt fa-4x text-muted opacity-50"></i>
                </div>
                <h3 class="h4 text-muted">No downloads available at the moment.</h3>
                <p class="text-muted">Please check back later for updates.</p>
            </div>
        <?php endif; ?>
    </div>
</section>