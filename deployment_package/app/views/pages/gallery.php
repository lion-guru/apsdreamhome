<!-- Hero Section -->
<section class="gallery-hero text-white py-5" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?= get_asset_url('assets/images/hero-3.jpg') ?>'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Our Gallery</h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Explore our stunning collection of properties, projects, and developments through our comprehensive photo gallery.
                </p>
            </div>
        </div>
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
                    <li class="breadcrumb-item active" aria-current="page">Gallery</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Gallery Content -->
<section class="py-5">
    <div class="container">
        <!-- Category Filter -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="gallery-filter text-center">
                    <a href="<?= BASE_URL ?>gallery" class="btn <?= $current_category === 'all' ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2">
                        All Photos
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <?php $catName = is_object($cat) ? $cat->category : $cat['category']; ?>
                        <a href="<?= BASE_URL ?>gallery?category=<?= urlencode($catName) ?>"
                            class="btn <?= $current_category === $catName ? 'btn-primary' : 'btn-outline-primary' ?> me-2 mb-2">
                            <?= htmlspecialchars(ucwords($catName)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="row g-4" id="galleryGrid">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $image): ?>
                    <?php
                    $imgPath = is_object($image) ? $image->image_path : $image['image_path'];
                    $imgCaption = is_object($image) ? $image->caption : $image['caption'];
                    $imgCategory = is_object($image) ? $image->category : $image['category'];
                    ?>
                    <div class="col-md-6 col-lg-4 gallery-item-wrapper" data-category="<?= htmlspecialchars($imgCategory) ?>">
                        <div class="gallery-item position-relative overflow-hidden rounded shadow-sm">
                            <a href="<?= get_asset_url($imgPath) ?>" data-lightbox="gallery" data-title="<?= htmlspecialchars($imgCaption) ?>">
                                <img src="<?= get_asset_url($imgPath) ?>" alt="<?= htmlspecialchars($imgCaption) ?>" class="img-fluid gallery-img">
                                <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 opacity-0 hover-opacity-100 transition-opacity">
                                    <div class="text-white text-center p-3">
                                        <i class="fas fa-search-plus fa-2x mb-2"></i>
                                        <h5 class="mb-0"><?= htmlspecialchars($imgCaption) ?></h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-images fa-4x text-muted mb-3"></i>
                        <h3>No images found</h3>
                        <p class="text-muted">We couldn't find any images in this category.</p>
                        <a href="<?= BASE_URL ?>gallery" class="btn btn-primary mt-3">View All Photos</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Lightbox assets are now included via PageController -->