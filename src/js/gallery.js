document.addEventListener('DOMContentLoaded', () => {
    const gallery = document.querySelector('.gallery');
    const lightbox = document.getElementById('gallery-lightbox');
    const lightboxImage = document.querySelector('.lightbox-image');
    let currentImageIndex = 0;
    let galleryImages = [];

    // Initialize Intersection Observer for lazy loading
    const lazyLoadObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.remove('lazy-load');
                    lazyLoadObserver.unobserve(img);
                }
            }
        });
    });

    // Function to load gallery images
    function loadGalleryImages() {
        galleryImages = Array.from(gallery.getElementsByTagName('img'));
        galleryImages.forEach(img => {
            // Set up lazy loading
            img.classList.add('lazy-load');
            if (!img.dataset.src) {
                img.dataset.src = img.src;
                img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
            }
            lazyLoadObserver.observe(img);

            // Add click event for lightbox
            img.addEventListener('click', () => {
                currentImageIndex = galleryImages.indexOf(img);
                openLightbox(img.dataset.src || img.src);
            });
        });
    }

    // Lightbox functions
    function openLightbox(imageSrc) {
        lightboxImage.src = imageSrc;
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setupLightboxControls();
    }

    function closeLightbox() {
        lightbox.style.display = 'none';
        document.body.style.overflow = '';
    }

    function navigateGallery(direction) {
        currentImageIndex = (currentImageIndex + direction + galleryImages.length) % galleryImages.length;
        const newImage = galleryImages[currentImageIndex];
        lightboxImage.src = newImage.dataset.src || newImage.src;
    }

    function setupLightboxControls() {
        // Close button
        document.querySelector('.lightbox-close').onclick = closeLightbox;

        // Navigation buttons
        document.querySelector('.lightbox-prev').onclick = () => navigateGallery(-1);
        document.querySelector('.lightbox-next').onclick = () => navigateGallery(1);

        // Keyboard navigation
        document.addEventListener('keydown', handleKeyPress);
    }

    function handleKeyPress(e) {
        if (lightbox.style.display === 'flex') {
            switch(e.key) {
                case 'Escape':
                    closeLightbox();
                    break;
                case 'ArrowLeft':
                    navigateGallery(-1);
                    break;
                case 'ArrowRight':
                    navigateGallery(1);
                    break;
            }
        }
    }

    // Click outside to close
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });

    // Initialize gallery
    loadGalleryImages();
});