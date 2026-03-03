
<!-- Lazy Loading Component -->

<script>
// Lazy Loading Service
class LazyLoadingService {
    constructor() {
        this.observer = null;
        this.loadedImages = new Set();
        this.init();
    }
    
    init() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.1
            });
        }
        
        this.observeImages();
    }
    
    observeImages() {
        const images = document.querySelectorAll('img[data-src]');
        
        images.forEach(img => {
            if (this.observer) {
                this.observer.observe(img);
            } else {
                // Fallback for browsers without IntersectionObserver
                this.loadImage(img);
            }
        });
    }
    
    loadImage(img) {
        const src = img.getAttribute('data-src');
        const srcset = img.getAttribute('data-srcset');
        
        if (src && !this.loadedImages.has(img)) {
            img.src = src;
            this.loadedImages.add(img);
            
            if (srcset) {
                img.srcset = srcset;
            }
            
            img.removeAttribute('data-src');
            img.removeAttribute('data-srcset');
            
            img.classList.add('loaded');
            
            // Remove from observer
            if (this.observer) {
                this.observer.unobserve(img);
            }
        }
    }
    
    // Lazy load components
    observeComponents() {
        const components = document.querySelectorAll('[data-lazy-component]');
        
        components.forEach(component => {
            if (this.observer) {
                this.observer.observe(component);
            } else {
                this.loadComponent(component);
            }
        });
    }
    
    loadComponent(component) {
        const componentName = component.getAttribute('data-lazy-component');
        const componentUrl = `/components/${componentName}.php`;
        
        fetch(componentUrl)
            .then(response => response.text())
            .then(html => {
                component.innerHTML = html;
                component.classList.add('loaded');
                component.removeAttribute('data-lazy-component');
                
                // Remove from observer
                if (this.observer) {
                    this.observer.unobserve(component);
                }
            })
            .catch(error => {
                console.error('Failed to load component:', error);
            });
    }
}

// Initialize lazy loading
document.addEventListener('DOMContentLoaded', () => {
    const lazyLoading = new LazyLoadingService();
    
    // Observe components after initial load
    setTimeout(() => {
        lazyLoading.observeComponents();
    }, 1000);
});

// Progressive Image Loading
class ProgressiveImageLoader {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupProgressiveImages();
    }
    
    setupProgressiveImages() {
        const progressiveImages = document.querySelectorAll('.progressive-image');
        
        progressiveImages.forEach(container => {
            const placeholder = container.querySelector('.placeholder');
            const mainImage = container.querySelector('img[data-src]');
            
            if (placeholder && mainImage) {
                // Load main image when placeholder is loaded
                placeholder.addEventListener('load', () => {
                    this.loadMainImage(mainImage);
                });
                
                // Fallback if placeholder fails
                placeholder.addEventListener('error', () => {
                    this.loadMainImage(mainImage);
                });
            }
        });
    }
    
    loadMainImage(img) {
        const src = img.getAttribute('data-src');
        
        if (src) {
            img.src = src;
            img.classList.add('fade-in');
            
            img.addEventListener('load', () => {
                img.classList.add('loaded');
            });
        }
    }
}

// Initialize progressive image loading
document.addEventListener('DOMContentLoaded', () => {
    new ProgressiveImageLoader();
});
</script>

<style>
/* Lazy Loading Styles */
img[data-src] {
    background-color: #f0f0f0;
    transition: opacity 0.3s ease;
}

img.loaded {
    opacity: 1;
}

img.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Progressive Image Styles */
.progressive-image {
    position: relative;
    overflow: hidden;
}

.progressive-image .placeholder {
    filter: blur(5px);
    transform: scale(1.1);
    transition: all 0.3s ease;
}

.progressive-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.progressive-image img.loaded {
    opacity: 1;
}

.progressive-image .placeholder.hidden {
    opacity: 0;
}

/* Skeleton Loading */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Lazy Component Styles */
[data-lazy-component] {
    min-height: 200px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

[data-lazy-component]:before {
    content: "Loading...";
}

[data-lazy-component].loaded {
    min-height: auto;
    background: transparent;
    color: inherit;
}

[data-lazy-component].loaded:before {
    content: "";
}
</style>

<!-- Progressive Image Component Template -->
<div class="progressive-image">
    <img class="placeholder" src="<?= $placeholderImage ?>" alt="<?= $alt ?>">
    <img data-src="<?= $fullImage ?>" alt="<?= $alt ?>" loading="lazy">
</div>

<!-- Lazy Loading Component Template -->
<div data-lazy-component="<?= $componentName ?>">
    <!-- Component will be loaded here -->
</div>

<!-- Skeleton Loading Template -->
<div class="skeleton" style="width: <?= $width ?>; height: <?= $height ?>; border-radius: <?= $borderRadius ?>;">
</div>
