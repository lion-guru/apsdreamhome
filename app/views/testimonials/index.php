@extends('layouts.base')

@section('title', $page_title ?? 'Testimonials - APS Dream Home')

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">Customer Testimonials</h1>
                    <p class="lead mb-4">{{ $page_description ?? 'Read what our satisfied customers have to say about their experience with APS Dream Home. Real stories from real homeowners.' }}</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#customer-reviews" class="btn btn-light btn-lg">Read Reviews</a>
                        <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg">Share Your Story</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="{{ asset('images/testimonials-hero.jpg') }}" alt="Customer Testimonials" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Statistics -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-primary fw-bold">{{ $testimonials_stats['total_reviews'] ?? 2500 }}+</h3>
                    <p class="text-muted mb-0">Total Reviews</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-warning fw-bold">{{ $testimonials_stats['average_rating'] ?? 4.8 }}/5</h3>
                    <p class="text-muted mb-0">Average Rating</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-success fw-bold">{{ $testimonials_stats['satisfied_customers'] ?? 2000 }}+</h3>
                    <p class="text-muted mb-0">Happy Customers</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-info fw-bold">{{ $testimonials_stats['years_of_service'] ?? 8 }}+</h3>
                    <p class="text-muted mb-0">Years of Service</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Rating Distribution -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Customer Satisfaction</h2>
                <p class="lead text-muted mb-4">Our commitment to excellence is reflected in our customer ratings</p>
                
                <div class="rating-bars">
                    @foreach($rating_distribution ?? [] as $stars => $count)
                    @php
                    $starLabel = str_replace('_', ' ', $stars);
                    $percentage = ($count / ($testimonials_stats['total_reviews'] ?? 2500)) * 100;
                    @endphp
                    <div class="rating-bar mb-3">
                        <div class="d-flex align-items-center">
                            <div class="rating-label me-3" style="width: 80px;">
                                <span class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                    @if($i <= (int)str_replace('_star', '', $stars))
                                    <i class="fas fa-star"></i>
                                    @else
                                    <i class="far fa-star"></i>
                                    @endif
                                    @endfor
                                </span>
                            </div>
                            <div class="progress flex-grow-1 me-3" style="height: 25px;">
                                <div class="progress-bar bg-warning" style="width: {{ $percentage }}%;">
                                    <span class="text-dark fw-bold">{{ $count }}</span>
                                </div>
                            </div>
                            <div class="rating-percentage" style="width: 60px;">
                                <small class="text-muted">{{ number_format($percentage, 1) }}%</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <div class="rating-circle">
                        <div class="circle-progress">
                            <div class="circle-text">
                                <h1 class="display-1 fw-bold text-warning mb-0">{{ $testimonials_stats['average_rating'] ?? 4.8 }}</h1>
                                <div class="stars">
                                    @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($testimonials_stats['average_rating'] ?? 4.8))
                                    <i class="fas fa-star text-warning"></i>
                                    @else
                                    <i class="far fa-star text-warning"></i>
                                    @endif
                                    @endfor
                                </div>
                                <p class="text-muted">Average Rating</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Video Testimonials -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Video Testimonials</h2>
            <p class="lead text-muted">Watch our customers share their experiences with APS Dream Home</p>
        </div>
        
        <div class="row g-4">
            @foreach($video_testimonials ?? [] as $video)
            <div class="col-md-4">
                <div class="video-testimonial-card">
                    <div class="video-thumbnail position-relative">
                        <img src="{{ asset('images/' . ($video['thumbnail'] ?? 'testimonials/default-video.jpg')) }}" 
                             alt="{{ $video['customer_name'] }}" 
                             class="img-fluid rounded-3">
                        <div class="play-overlay position-absolute top-50 start-50 translate-middle">
                            <button class="btn btn-primary btn-lg rounded-circle" onclick="playVideo('{{ $video['video_url'] }}');">
                                <i class="fas fa-play"></i>
                            </button>
                        </div>
                        <div class="video-duration position-absolute bottom-0 end-0 m-2">
                            <span class="badge bg-dark">{{ $video['duration'] }}</span>
                        </div>
                    </div>
                    <div class="video-info mt-3">
                        <h5 class="fw-bold mb-1">{{ $video['customer_name'] }}</h5>
                        <p class="text-muted mb-2">{{ $video['property'] }}</p>
                        <div class="video-stats">
                            <small class="text-muted">
                                <i class="fas fa-eye"></i> {{ number_format($video['views']) }} views
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Customer Reviews -->
<section id="customer-reviews" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Customer Reviews</h2>
            <p class="lead text-muted">Real experiences from our valued customers</p>
        </div>
        
        <div class="row g-4">
            @foreach($customer_testimonials ?? [] as $testimonial)
            <div class="col-lg-6">
                <div class="testimonial-card card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="testimonial-header mb-3">
                            <div class="d-flex align-items-center">
                                <div class="customer-avatar me-3">
                                    <img src="{{ asset('images/' . ($testimonial['image'] ?? 'testimonials/default.jpg')) }}" 
                                         alt="{{ $testimonial['name'] }}" 
                                         class="rounded-circle"
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                </div>
                                <div class="customer-info flex-grow-1">
                                    <h5 class="fw-bold mb-1">{{ $testimonial['name'] }}</h5>
                                    <p class="text-muted mb-1">{{ $testimonial['property'] }}</p>
                                    <div class="rating mb-1">
                                        @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $testimonial['rating'])
                                        <i class="fas fa-star text-warning"></i>
                                        @else
                                        <i class="far fa-star text-warning"></i>
                                        @endif
                                        @endfor
                                    </div>
                                </div>
                                <div class="review-date">
                                    <small class="text-muted">{{ date('M d, Y', strtotime($testimonial['review_date'])) }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="testimonial-content mb-3">
                            <p class="text-muted">{{ $testimonial['testimonial'] }}</p>
                        </div>
                        
                        <div class="testimonial-footer">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> {{ $testimonial['location'] }}
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-home"></i> {{ $testimonial['property_type'] }}
                                    </small>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> Customer for {{ $testimonial['experience_years'] }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-5">
            <button class="btn btn-outline-primary btn-lg" onclick="loadMoreTestimonials()">
                Load More Reviews
            </button>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Top Rated Properties</h2>
            <p class="lead text-muted">Properties with the highest customer satisfaction</p>
        </div>
        
        <div class="row g-4">
            @foreach($featured_properties ?? [] as $property)
            <div class="col-md-4">
                <div class="property-card card h-100 shadow-sm">
                    <div class="property-image">
                        <img src="{{ asset('images/' . ($property['image'] ?? 'properties/default.jpg')) }}" 
                             alt="{{ $property['name'] }}" 
                             class="card-img-top">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold">{{ $property['name'] }}</h5>
                        <div class="property-rating mb-2">
                            <div class="rating">
                                @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($property['average_rating']))
                                <i class="fas fa-star text-warning"></i>
                                @else
                                <i class="far fa-star text-warning"></i>
                                @endif
                                @endfor
                            </div>
                            <span class="text-muted ms-2">{{ $property['average_rating'] }} ({{ $property['total_reviews'] }} reviews)</span>
                        </div>
                        <a href="{{ url('/properties') }}" class="btn btn-primary btn-sm">View Properties</a>
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
                <h2 class="display-5 fw-bold mb-3">Share Your Experience</h2>
                <p class="lead mb-0">Have you worked with APS Dream Home? We'd love to hear about your experience and help others make informed decisions.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg me-2">Share Story</a>
                <a href="{{ url('/properties') }}" class="btn btn-outline-light btn-lg">Browse Properties</a>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.rating-bar {
    transition: transform 0.3s ease;
}

.rating-bar:hover {
    transform: translateX(5px);
}

.rating-circle {
    max-width: 300px;
    margin: 0 auto;
}

.circle-progress {
    position: relative;
    width: 250px;
    height: 250px;
    border-radius: 50%;
    background: conic-gradient(#ffc107 0deg {{ ($testimonials_stats['average_rating'] ?? 4.8) * 72 }}deg, #e9ecef 0deg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.circle-text {
    background: white;
    border-radius: 50%;
    width: 200px;
    height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.video-testimonial-card {
    transition: transform 0.3s ease;
}

.video-testimonial-card:hover {
    transform: translateY(-5px);
}

.video-thumbnail {
    position: relative;
    overflow: hidden;
    border-radius: 12px;
}

.play-overlay button {
    width: 60px;
    height: 60px;
    border: 3px solid white;
    background: rgba(255, 255, 255, 0.9);
    color: #667eea;
    transition: all 0.3s ease;
}

.play-overlay button:hover {
    background: white;
    transform: scale(1.1);
}

.testimonial-card {
    transition: transform 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-3px);
}

.property-card {
    transition: transform 0.3s ease;
}

.property-card:hover {
    transform: translateY(-3px);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

@media (max-width: 768px) {
    .hero-section .display-4 {
        font-size: 2.5rem;
    }
    
    .circle-progress {
        width: 200px;
        height: 200px;
    }
    
    .circle-text {
        width: 160px;
        height: 160px;
    }
    
    .circle-text h1 {
        font-size: 2.5rem !important;
    }
}
</style>

<script>
function playVideo(videoUrl) {
    // Create modal for video playback
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.setAttribute('tabindex', '-1');
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="ratio ratio-16x9">
                        <video controls class="w-100">
                            <source src="${videoUrl}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.addEventListener('hidden.bs.modal', () => {
        modal.remove();
    });
}

function loadMoreTestimonials() {
    // Simulate loading more testimonials
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        
        // Show success message
        const alert = document.createElement('div');
        alert.className = 'alert alert-info alert-dismissible fade show mt-3';
        alert.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            More testimonials will be available soon. Thank you for your interest!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        button.parentNode.appendChild(alert);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }, 1500);
}

document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll to customer reviews
    document.querySelectorAll('a[href="#customer-reviews"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add animation to cards on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all cards
    document.querySelectorAll('.testimonial-card, .video-testimonial-card, .property-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>
@endsection
