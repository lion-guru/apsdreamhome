@extends('layouts.base')

@section('title', $page_title ?? 'Resale Properties - APS Dream Home')

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">Resale Properties</h1>
                    <p class="lead mb-4">{{ $page_description ?? 'Find verified resale properties with transparent pricing and clear documentation.' }}</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ url('/contact') }}" class="btn btn-light btn-lg">Contact Us</a>
                        <a href="{{ url('/properties') }}" class="btn btn-outline-light btn-lg">All Properties</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="{{ asset('images/resale-hero.jpg') }}" alt="Resale Properties" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-primary fw-bold">{{ $resale_stats['total_properties'] ?? 150 }}+</h3>
                    <p class="text-muted mb-0">Total Properties</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-success fw-bold">{{ $resale_stats['avg_savings'] ?? 12 }}%</h3>
                    <p class="text-muted mb-0">Average Savings</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-info fw-bold">{{ $resale_stats['verified_properties'] ?? 145 }}+</h3>
                    <p class="text-muted mb-0">Verified Properties</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-warning fw-bold">{{ $resale_stats['happy_customers'] ?? 2800 }}+</h3>
                    <p class="text-muted mb-0">Happy Customers</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Why Choose Our Resale Properties?</h2>
            <p class="lead text-muted">Get the best value with our verified and transparent resale property deals</p>
        </div>
        
        <div class="row g-4">
            @foreach($resale_benefits ?? [] as $benefit)
            <div class="col-md-6 col-lg-3">
                <div class="benefit-card text-center p-4 h-100">
                    <div class="benefit-icon mb-3">
                        <i class="fas {{ $benefit['icon'] }} fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">{{ $benefit['title'] }}</h5>
                    <p class="text-muted">{{ $benefit['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Properties Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Featured Resale Properties</h2>
            <p class="lead text-muted">Handpicked properties with best value and verified documentation</p>
        </div>
        
        <div class="row g-4">
            @foreach($resale_properties ?? [] as $property)
            <div class="col-lg-4 col-md-6">
                <div class="property-card card h-100 shadow-sm">
                    <div class="position-relative">
                        <img src="{{ asset('images/properties/' . ($property['images'][0] ?? 'default.jpg')) }}" 
                             alt="{{ $property['title'] }}" 
                             class="card-img-top property-image">
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-success">Resale</span>
                        </div>
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-danger">Save {{ round((($property['original_price'] - $property['price']) / $property['original_price']) * 100) }}%</span>
                        </div>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <div class="mb-3">
                            <h5 class="card-title fw-bold">{{ $property['title'] }}</h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt"></i> {{ $property['location'] }}
                            </p>
                            <div class="property-specs d-flex gap-3 flex-wrap mb-3">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-bed"></i> {{ $property['bedrooms'] }} Beds
                                </span>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-bath"></i> {{ $property['bathrooms'] }} Baths
                                </span>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-ruler-combined"></i> {{ $property['area'] }} Sq.ft
                                </span>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-clock"></i> {{ $property['age'] }} Years
                                </span>
                            </div>
                        </div>
                        
                        <div class="price-section mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <h4 class="text-primary fw-bold mb-0">₹{{ number_format($property['price']) }}</h4>
                                <small class="text-muted text-decoration-line-through">₹{{ number_format($property['original_price']) }}</small>
                            </div>
                        </div>
                        
                        <p class="text-muted small">{{ $property['description'] }}</p>
                        
                        <div class="features-section mb-3">
                            @foreach(array_slice($property['features'] ?? [], 0, 3) as $feature)
                            <span class="badge bg-secondary me-1">{{ $feature }}</span>
                            @endforeach
                        </div>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-eye"></i> {{ $property['views'] ?? 0 }} views
                                </small>
                                <a href="{{ url('/properties/' . $property['id']) }}" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-5">
            <a href="{{ url('/properties') }}" class="btn btn-outline-primary btn-lg">View All Properties</a>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Resale Property Process</h2>
            <p class="lead text-muted">Simple and transparent process for buying resale properties</p>
        </div>
        
        <div class="row g-4">
            @foreach($process_steps ?? [] as $step)
            <div class="col-md-6 col-lg-3">
                <div class="process-step text-center">
                    <div class="step-number mb-3">
                        <div class="step-circle bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <span class="fw-bold">{{ $step['step'] }}</span>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-3">{{ $step['title'] }}</h5>
                    <p class="text-muted">{{ $step['description'] }}</p>
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
                <h2 class="display-5 fw-bold mb-3">Ready to Find Your Dream Resale Property?</h2>
                <p class="lead mb-0">Contact our experts to get personalized assistance in finding the perfect resale property for you.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg me-2">Contact Us</a>
                <a href="tel:+917007444842" class="btn btn-outline-light btn-lg">Call Now</a>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.property-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.property-image {
    height: 200px;
    object-fit: cover;
}

.benefit-card {
    transition: transform 0.3s ease;
}

.benefit-card:hover {
    transform: translateY(-3px);
}

.step-circle {
    font-size: 1.2rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

@media (max-width: 768px) {
    .hero-section .display-4 {
        font-size: 2.5rem;
    }
    
    .property-image {
        height: 150px;
    }
}
</style>
@endsection
