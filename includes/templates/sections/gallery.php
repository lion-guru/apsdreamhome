<?php
/**
 * Property Details: Gallery Section
 *
 * @var array $property Property data, expects 'gallery_images' and 'title'.
 */

if (empty($property) || empty($property['gallery_images'])) {
    // Optionally, display a placeholder or nothing if no images
    // echo '<p>No images available for this property.</p>';
    return;
}

$main_image = '';
$thumbnail_images = [];

foreach ($property['gallery_images'] as $image) {
    if ($image['is_primary']) {
        $main_image = $image['image_path'];
    }
    $thumbnail_images[] = $image['image_path'];
}

// If no primary image is set, use the first image from the gallery
if (empty($main_image) && !empty($thumbnail_images)) {
    $main_image = $thumbnail_images[0];
}

?>
<section class="property-gallery mb-4">
    <div class="container-fluid p-0">
        <?php if (!empty($main_image)) : ?>
            <div class="main-image-container mb-3">
                <img src="<?php echo e(SITE_URL . '/' . $main_image); ?>" alt="<?php echo e($property['title']); ?> - Main Image" class="img-fluid w-100 property-main-image" style="max-height: 600px; object-fit: cover;">
            </div>
        <?php endif; ?>

        <?php if (count($thumbnail_images) > 1) : ?>
            <div class="thumbnail-slider-container">
                <div class="swiper property-thumbnail-swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($thumbnail_images as $thumb_path) : ?>
                            <div class="swiper-slide">
                                <img src="<?php echo e(SITE_URL . '/' . $thumb_path); ?>" alt="<?php echo e($property['title']); ?> - Thumbnail" class="img-fluid property-thumbnail-image" style="height: 120px; object-fit: cover; cursor: pointer;" data-full-src="<?php echo e(SITE_URL . '/' . $thumb_path); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-next text-white"></div>
                    <div class="swiper-button-prev text-white"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swiper !== 'undefined') {
        new Swiper('.property-thumbnail-swiper', {
            loop: false,
            spaceBetween: 10,
            slidesPerView: 3,
            freeMode: true,
            watchSlidesProgress: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 4,
                    spaceBetween: 15,
                },
                992: {
                    slidesPerView: 5,
                    spaceBetween: 20,
                },
                1200: {
                    slidesPerView: 6,
                    spaceBetween: 20,
                }
            }
        });

        const mainImageElement = document.querySelector('.property-main-image');
        const thumbnails = document.querySelectorAll('.property-thumbnail-image');

        if (mainImageElement && thumbnails.length > 0) {
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    mainImageElement.src = this.dataset.fullSrc;
                    // Optional: Add active class to thumbnail
                    thumbnails.forEach(t => t.classList.remove('active-thumbnail'));
                    this.classList.add('active-thumbnail');
                });
            });
        }
    } else {
        console.warn('Swiper library not loaded. Gallery thumbnails will not be interactive.');
    }
});
</script>

<style>
.property-main-image {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.property-thumbnail-image {
    border-radius: 6px;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.property-thumbnail-image:hover, .property-thumbnail-image.active-thumbnail {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,123,255,0.5);
    border: 2px solid var(--bs-primary);
}
.thumbnail-slider-container .swiper-button-next, 
.thumbnail-slider-container .swiper-button-prev {
    color: var(--bs-primary) !important; /* Ensure visibility */
    background-color: rgba(255, 255, 255, 0.7);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
}
.thumbnail-slider-container .swiper-button-next::after,
.thumbnail-slider-container .swiper-button-prev::after {
    font-size: 16px;
}
</style>
