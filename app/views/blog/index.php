@extends('layouts.base')

@section('title', $page_title ?? 'Blog - APS Dream Home')

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">Blog</h1>
                    <p class="lead mb-4">{{ $page_description ?? 'Stay updated with the latest real estate trends, property tips, and market insights from our expert team.' }}</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ url('/contact') }}" class="btn btn-light btn-lg">Get Expert Advice</a>
                        <a href="{{ url('/properties') }}" class="btn btn-outline-light btn-lg">Browse Properties</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="{{ asset('images/blog-hero.jpg') }}" alt="Blog" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Statistics -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-primary fw-bold">{{ $blog_stats['total_posts'] ?? 91 }}+</h3>
                    <p class="text-muted mb-0">Articles</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-success fw-bold">{{ $blog_stats['total_categories'] ?? 5 }}</h3>
                    <p class="text-muted mb-0">Categories</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-info fw-bold">{{ $blog_stats['total_authors'] ?? 8 }}</h3>
                    <p class="text-muted mb-0">Expert Authors</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-warning fw-bold">{{ number_format($blog_stats['total_views'] ?? 45670) }}+</h3>
                    <p class="text-muted mb-0">Total Readers</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Browse by Category</h2>
            <p class="lead text-muted">Explore articles organized by topics that matter to you</p>
        </div>
        
        <div class="row g-4">
            @foreach($blog_categories ?? [] as $category)
            <div class="col-lg-4 col-md-6">
                <div class="category-card card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="category-icon mb-3">
                            <i class="fas fa-folder fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title fw-bold">{{ $category['name'] }}</h5>
                        <p class="text-muted small">{{ $category['description'] }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">{{ $category['post_count'] }} Articles</span>
                            <a href="#" class="btn btn-outline-primary btn-sm">View All</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Posts -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Featured Articles</h2>
            <p class="lead text-muted">Handpicked articles with valuable insights for homebuyers and investors</p>
        </div>
        
        <div class="row g-4">
            @foreach($featured_posts ?? [] as $post)
            <div class="col-lg-4">
                <article class="featured-post card h-100 shadow-sm">
                    <div class="position-relative">
                        <img src="{{ asset('images/' . ($post['featured_image'] ?? 'blog/default-featured.jpg')) }}" 
                             alt="{{ $post['title'] }}" 
                             class="card-img-top post-image">
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-primary">{{ $post['category'] }}</span>
                        </div>
                        @if($post['featured'] ?? false)
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-warning">
                                <i class="fas fa-star"></i> Featured
                            </span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <div class="post-meta mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> {{ date('M d, Y', strtotime($post['published_date'])) }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-clock"></i> {{ $post['reading_time'] }}
                            </small>
                        </div>
                        
                        <h5 class="card-title fw-bold mb-3">
                            <a href="#" class="text-decoration-none text-dark">{{ $post['title'] }}</a>
                        </h5>
                        
                        <p class="text-muted flex-grow-1">{{ $post['excerpt'] }}</p>
                        
                        <div class="author-info d-flex align-items-center mb-3">
                            <img src="{{ asset('images/' . ($post['author']['avatar'] ?? 'authors/default.jpg')) }}" 
                                 alt="{{ $post['author']['name'] }}" 
                                 class="rounded-circle me-2" 
                                 style="width: 32px; height: 32px; object-fit: cover;">
                            <div>
                                <small class="text-dark fw-semibold">{{ $post['author']['name'] }}</small>
                                <br>
                                <small class="text-muted">{{ $post['author']['role'] }}</small>
                            </div>
                        </div>
                        
                        <div class="post-stats d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-3">
                                <small class="text-muted">
                                    <i class="fas fa-eye"></i> {{ number_format($post['views']) }}
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-heart"></i> {{ $post['likes'] }}
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-comment"></i> {{ $post['comments'] }}
                                </small>
                            </div>
                            <a href="#" class="btn btn-primary btn-sm">Read More</a>
                        </div>
                    </div>
                </article>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-5">
            <a href="#" class="btn btn-outline-primary btn-lg">View All Articles</a>
        </div>
    </div>
</section>

<!-- Recent Posts -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Recent Articles</h2>
            <p class="lead text-muted">Latest insights and updates from our expert team</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="recent-posts">
                    @foreach($recent_posts ?? [] as $post)
                    <article class="recent-post d-flex gap-4 mb-4 pb-4 border-bottom">
                        <div class="recent-post-image flex-shrink-0">
                            <img src="{{ asset('images/' . ($post['thumbnail'] ?? 'blog/thumbnails/default.jpg')) }}" 
                                 alt="{{ $post['title'] }}" 
                                 class="rounded-3"
                                 style="width: 200px; height: 150px; object-fit: cover;">
                        </div>
                        
                        <div class="recent-post-content flex-grow-1">
                            <div class="post-meta mb-2">
                                <small class="text-muted">
                                    <span class="badge bg-secondary me-2">{{ $post['category'] }}</span>
                                    <i class="fas fa-calendar"></i> {{ date('M d, Y', strtotime($post['published_date'])) }}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock"></i> {{ $post['reading_time'] }}
                                </small>
                            </div>
                            
                            <h5 class="fw-bold mb-2">
                                <a href="#" class="text-decoration-none text-dark">{{ $post['title'] }}</a>
                            </h5>
                            
                            <p class="text-muted mb-3">{{ $post['excerpt'] }}</p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-3">
                                        <i class="fas fa-user"></i> {{ $post['author'] }}
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-eye"></i> {{ number_format($post['views']) }} views
                                    </small>
                                </div>
                                <a href="#" class="btn btn-outline-primary btn-sm">Read More</a>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                
                <div class="text-center mt-4">
                    <button class="btn btn-outline-primary" id="load-more-posts">Load More Articles</button>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Popular Tags -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Popular Tags</h5>
                        <div class="tag-cloud">
                            @foreach($popular_tags ?? [] as $tag)
                            <a href="#" class="badge bg-light text-dark text-decoration-none me-2 mb-2 d-inline-block">
                                #{{ $tag['name'] }} ({{ $tag['count'] }})
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Newsletter Subscribe -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Subscribe to Newsletter</h5>
                        <p class="text-muted small mb-3">Get latest articles and market insights delivered to your inbox</p>
                        <form class="newsletter-form">
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Your email address" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Quick Links</h5>
                        <div class="list-group list-group-flush">
                            <a href="{{ url('/properties') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-home me-2"></i> Browse Properties
                            </a>
                            <a href="{{ url('/projects') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-building me-2"></i> Our Projects
                            </a>
                            <a href="{{ url('/gallery') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-images me-2"></i> Gallery
                            </a>
                            <a href="{{ url('/contact') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-phone me-2"></i> Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-3">Need Expert Real Estate Advice?</h2>
                <p class="lead mb-0">Our team of experienced advisors is here to help you make informed decisions about your property investments.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg me-2">Consult Experts</a>
                <a href="tel:+917007444842" class="btn btn-outline-light btn-lg">Call Now</a>
            </div>
        </div>
    </div>
</section>

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

.post-image {
    height: 200px;
    object-fit: cover;
}

.featured-post {
    transition: transform 0.3s ease;
}

.featured-post:hover {
    transform: translateY(-3px);
}

.recent-post-image img {
    transition: transform 0.3s ease;
}

.recent-post:hover .recent-post-image img {
    transform: scale(1.05);
}

.tag-cloud .badge {
    transition: all 0.3s ease;
}

.tag-cloud .badge:hover {
    background-color: #667eea !important;
    color: white !important;
    transform: translateY(-2px);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

@media (max-width: 768px) {
    .hero-section .display-4 {
        font-size: 2.5rem;
    }
    
    .recent-post {
        flex-direction: column;
    }
    
    .recent-post-image {
        width: 100% !important;
    }
    
    .recent-post-image img {
        width: 100% !important;
        height: 200px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Newsletter form submission
    document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'toast position-fixed bottom-0 end-0 m-3';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">Newsletter</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Successfully subscribed with email: ${email}
            </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        setTimeout(() => toast.remove(), 3000);
        this.reset();
    });
    
    // Load more posts functionality
    document.getElementById('load-more-posts').addEventListener('click', function() {
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
        this.disabled = true;
        
        setTimeout(() => {
            this.innerHTML = 'Load More Articles';
            this.disabled = false;
            
            // Show toast notification
            const toast = document.createElement('div');
            toast.className = 'toast position-fixed bottom-0 end-0 m-3';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">Blog</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    More articles will be available soon!
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
