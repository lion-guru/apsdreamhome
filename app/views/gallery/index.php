@extends('layouts.base')

@section('title', $page_title ?? 'Gallery - APS Dream Home')

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">Gallery</h1>
                    <p class="lead mb-4">{{ $page_description ?? 'Explore our completed projects, property showcases, and construction quality through our extensive gallery.' }}</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ url('/contact') }}" class="btn btn-light btn-lg">Schedule Visit</a>
                        <a href="{{ url('/properties') }}" class="btn btn-outline-light btn-lg">View Properties</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="{{ asset('images/gallery-hero.jpg') }}" alt="Gallery" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Statistics -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-primary fw-bold">{{ $gallery_stats['total_images'] ?? 249 }}+</h3>
                    <p class="text-muted mb-0">Total Images</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-success fw-bold">{{ $gallery_stats['total_categories'] ?? 6 }}</h3>
                    <p class="text-muted mb-0">Categories</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-info fw-bold">{{ $gallery_stats['completed_projects'] ?? 45 }}+</h3>
                    <p class="text-muted mb-0">Completed Projects</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-warning fw-bold">{{ $gallery_stats['happy_customers'] ?? 2800 }}+</h3>
                    <p class="text-muted mb-0">Happy Customers</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Categories -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Browse by Category</h2>
            <p class="lead text-muted">Explore our extensive collection organized by categories</p>
        </div>
        
        <div class="row g-4">
            @foreach($gallery_categories ?? [] as $category)
            <div class="col-lg-4 col-md-6">
                <div class="category-card card h-100 shadow-sm">
                    <div class="position-relative">
                        <img src="{{ asset('images/' . ($category['cover_image'] ?? 'gallery/default-cover.jpg')) }}" 
                             alt="{{ $category['name'] }}" 
                             class="card-img-top category-image">
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-primary">{{ $category['image_count'] }} Images</span>
                        </div>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold">{{ $category['name'] }}</h5>
                        <p class="text-muted flex-grow-1">{{ $category['description'] }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $category['image_count'] }} images</small>
                            <a href="#" class="btn btn-primary btn-sm">View Gallery</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Images -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Featured Images</h2>
            <p class="lead text-muted">Handpicked showcase of our best work</p>
        </div>
        
        <div class="row g-4" id="gallery-grid">
            @foreach($featured_images ?? [] as $image)
            <div class="col-lg-4 col-md-6 gallery-item">
                <div class="gallery-card card shadow-sm">
                    <div class="gallery-image-container">
                        <img src="{{ asset('images/' . ($image['thumbnail'] ?? 'gallery/thumbnails/default.jpg')) }}" 
                             alt="{{ $image['title'] }}" 
                             class="gallery-image"
                             data-full-image="{{ asset('images/' . $image['image']) }}"
                             data-title="{{ $image['title'] }}"
                             data-description="{{ $image['description'] }}">
                        <div class="gallery-overlay">
                            <div class="overlay-content">
                                <h6 class="text-white mb-2">{{ $image['title'] }}</h6>
                                <p class="text-white-50 small mb-0">{{ $image['category'] }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-3">
                        <h6 class="card-title mb-1">{{ $image['title'] }}</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $image['date'] }}</small>
                            <small class="text-muted">
                                <i class="fas fa-eye"></i> {{ $image['views'] }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-5">
            <button class="btn btn-outline-primary btn-lg" id="load-more-btn">Load More Images</button>
        </div>
    </div>
</section>

<!-- Recent Updates -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Recent Updates</h2>
            <p class="lead text-muted">Latest additions to our gallery</p>
        </div>
        
        <div class="row g-4">
            @foreach($recent_updates ?? [] as $update)
            <div class="col-md-4">
                <div class="update-card card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="update-icon me-3">
                                <i class="fas fa-images fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="card-title mb-1">{{ $update['title'] }}</h6>
                                <small class="text-muted">{{ $update['date'] }}</small>
                            </div>
                        </div>
                        <p class="text-muted mb-2">{{ $update['images_added'] }} new images added</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-secondary">{{ $update['category'] }}</span>
                            <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-3">Want to See Our Properties in Person?</h2>
                <p class="lead mb-0">Schedule a site visit and experience the quality and craftsmanship of our projects firsthand.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg me-2">Schedule Visit</a>
                <a href="tel:+917007444842" class="btn btn-outline-light btn-lg">Call Now</a>
            </div>
        </div>
    </div>
</section>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="modalImageTitle">Image Title</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="text-center">
                    <img id="modalImage" src="" alt="" class="img-fluid">
                    <div class="p-3">
                        <p id="modalImageDescription" class="text-white mb-0"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.category-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.category-image {
    height: 200px;
    object-fit: cover;
}

.gallery-card {
    transition: transform 0.3s ease;
}

.gallery-card:hover {
    transform: translateY(-3px);
}

.gallery-image-container {
    position: relative;
    overflow: hidden;
}

.gallery-image {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: transform 0.3s ease;
    cursor: pointer;
}

.gallery-image:hover {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
    display: flex;
    align-items: flex-end;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-image-container:hover .gallery-overlay {
    opacity: 1;
}

.overlay-content {
    padding: 1rem;
    width: 100%;
}

.update-card {
    transition: transform 0.3s ease;
}

.update-card:hover {
    transform: translateY(-2px);
}

.update-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 50%;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

#imageModal .modal-content {
    border: none;
}

#imageModal .modal-image {
    max-height: 80vh;
    object-fit: contain;
}

@media (max-width: 768px) {
    .hero-section .display-4 {
        font-size: 2.5rem;
    }
    
    .gallery-image {
        height: 200px;
    }
    
    .category-image {
        height: 150px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gallery image modal
    const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
    
    document.querySelectorAll('.gallery-image').forEach(function(image) {
        image.addEventListener('click', function() {
            const fullImage = this.getAttribute('data-full-image');
            const title = this.getAttribute('data-title');
            const description = this.getAttribute('data-description');
            
            document.getElementById('modalImage').src = fullImage;
            document.getElementById('modalImageTitle').textContent = title;
            document.getElementById('modalImageDescription').textContent = description;
            
            imageModal.show();
        });
    });
    
    // Load more functionality (simulation)
    document.getElementById('load-more-btn').addEventListener('click', function() {
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
        this.disabled = true;
        
        setTimeout(() => {
            this.innerHTML = 'Load More Images';
            this.disabled = false;
            
            // Show toast notification
            const toast = document.createElement('div');
            toast.className = 'toast position-fixed bottom-0 end-0 m-3';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">Gallery</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    More images will be available soon!
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => toast.remove(), 3000);
        }, 1000);
    });
});
</script>
@endsection
