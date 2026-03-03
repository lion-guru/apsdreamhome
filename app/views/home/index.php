@extends('layouts.base')
@section('title', $page_title ?? 'Welcome to APS Dream Home')
@section('content')

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">{{ $page_title ?? 'APS Dream Home' }}</h1>
                <p class="lead mb-4">{{ $page_description ?? 'Trusted Real Estate Partner in Gorakhpur, Lucknow & across Uttar Pradesh' }}</p>
                <div class="d-flex gap-3">
                    <a href="{{ url('/properties') }}" class="btn btn-light btn-lg">Explore Properties</a>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <i class="fas fa-home fa-5x mb-3"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            @foreach($hero_stats ?? [] as $key => $stat)
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <h2 class="display-4 fw-bold text-primary">{{ $stat }}</h2>
                    <p class="text-muted text-capitalize">{{ str_replace('_', ' ', $key) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Property Types Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Browse by Property Type</h2>
            <p class="lead text-muted">Find your perfect property from our wide range of options</p>
        </div>
        
        <div class="row">
            @foreach($property_types ?? [] as $type)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 text-center property-type-card">
                    <div class="card-body">
                        <div class="text-primary mb-3">
                            <i class="fas {{ $type['icon'] }} fa-3x"></i>
                        </div>
                        <h5 class="card-title">{{ $type['name'] }}</h5>
                        <p class="text-muted">{{ $type['count'] }} Properties</p>
                        <a href="{{ url('/properties?type=' . strtolower($type['name'])) }}" class="btn btn-outline-primary">View All</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Properties Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Featured Properties</h2>
            <p class="lead text-muted">Handpicked properties that offer exceptional value</p>
        </div>
        
        <div class="row">
            @foreach($featured_properties ?? [] as $property)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 property-card">
                    @if($property['featured'] ?? false)
                    <div class="position-relative">
                        <span class="badge bg-primary position-absolute top-0 end-0 m-2">Featured</span>
                    </div>
                    @endif
                    <img src="{{ asset($property['image'] ?? 'images/property-placeholder.jpg') }}" 
                         class="card-img-top" alt="{{ $property['title'] }}" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">{{ $property['title'] }}</h5>
                        <p class="text-muted mb-2"><i class="fas fa-map-marker-alt"></i> {{ $property['location'] }}</p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="text-primary mb-0">{{ $property['price'] }}</h4>
                            <span class="badge bg-secondary">{{ ucfirst($property['type']) }}</span>
                        </div>
                        <div class="property-features mb-3">
                            @if($property['bedrooms'] > 0)
                            <span class="me-3"><i class="fas fa-bed"></i> {{ $property['bedrooms'] }} Beds</span>
                            @endif
                            @if($property['bathrooms'] > 0)
                            <span class="me-3"><i class="fas fa-bath"></i> {{ $property['bathrooms'] }} Baths</span>
                            @endif
                            <span><i class="fas fa-ruler-combined"></i> {{ $property['area'] }}</span>
                        </div>
                        <a href="{{ url('/properties/' . $property['id']) }}" class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ url('/properties?featured=1') }}" class="btn btn-outline-primary btn-lg">View All Featured Properties</a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Why Choose APS Dream Home?</h2>
            <p class="lead text-muted">We stand out from the competition with our commitment to excellence</p>
        </div>
        
        <div class="row">
            @foreach($why_choose_us ?? [] as $reason)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body">
                        <div class="text-primary mb-3">
                            <i class="fas {{ $reason['icon'] }} fa-3x"></i>
                        </div>
                        <h5 class="card-title">{{ $reason['title'] }}</h5>
                        <p class="card-text">{{ $reason['description'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">What Our Clients Say</h2>
            <p class="lead text-muted">Real experiences from satisfied customers</p>
        </div>
        
        <div class="row">
            @foreach($testimonials ?? [] as $testimonial)
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= ($testimonial['rating'] ?? 5))
                                <i class="fas fa-star text-warning"></i>
                                @else
                                <i class="far fa-star text-warning"></i>
                                @endif
                            @endfor
                        </div>
                        <blockquote class="blockquote mb-3">
                            <p class="mb-0">{{ $testimonial['content'] }}</p>
                        </blockquote>
                        <footer class="blockquote-footer">
                            <strong>{{ $testimonial['name'] }}</strong> - {{ $testimonial['property'] }}
                        </footer>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Find Your Dream Home?</h2>
            <p class="lead mb-4">Join thousands of happy families who found their perfect home with APS Dream Home</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ url('/properties') }}" class="btn btn-light btn-lg">Browse Properties</a>
                <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg">Contact Us</a>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-item {
    transition: transform 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
}

.property-type-card,
.property-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.property-type-card:hover,
.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.property-features {
    font-size: 0.9rem;
    color: #6c757d;
}

.card-img-top {
    transition: transform 0.3s ease;
}

.property-card:hover .card-img-top {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .hero-section .display-4 {
        font-size: 2.5rem;
    }
    
    .hero-section .btn-lg {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
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

// Add animation to stats on scroll
const observerOptions = {
    threshold: 0.5,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
        }
    });
}, observerOptions);

document.querySelectorAll('.stat-item').forEach(item => {
    observer.observe(item);
});

// CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
</script>
@endpush

@endsection