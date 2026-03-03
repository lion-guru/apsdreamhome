@extends('layouts.base')

@section('title', $page_title ?? 'Our Services - APS Dream Home')

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">Our Services</h1>
                    <p class="lead mb-4">{{ $page_description ?? 'Discover our comprehensive range of real estate services designed to help you find your perfect property or sell your current one with ease.' }}</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ url('/contact') }}" class="btn btn-light btn-lg">Get Consultation</a>
                        <a href="{{ url('/properties') }}" class="btn btn-outline-light btn-lg">Browse Properties</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-stats">
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="stat-card text-center">
                                <h3 class="fw-bold">{{ $services_stats['properties_sold'] ?? 500 }}+</h3>
                                <p class="mb-0">Properties Sold</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card text-center">
                                <h3 class="fw-bold">{{ $services_stats['happy_clients'] ?? 1000 }}+</h3>
                                <p class="mb-0">Happy Clients</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card text-center">
                                <h3 class="fw-bold">{{ $services_stats['years_experience'] ?? 15 }}+</h3>
                                <p class="mb-0">Years Experience</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card text-center">
                                <h3 class="fw-bold">{{ $services_stats['projects_completed'] ?? 50 }}+</h3>
                                <p class="mb-0">Projects Completed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Services -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Our Core Services</h2>
            <p class="lead text-muted">Comprehensive real estate solutions tailored to your needs</p>
        </div>
        
        <div class="row g-4">
            @foreach($main_services ?? [] as $service)
            <div class="col-lg-6">
                <div class="service-card h-100">
                    <div class="service-header">
                        <div class="service-icon">
                            <i class="{{ $service['icon'] }} fa-2x"></i>
                        </div>
                        <h3 class="service-title">{{ $service['title'] }}</h3>
                    </div>
                    <div class="service-body">
                        <p class="service-description">{{ $service['description'] }}</p>
                        
                        <div class="service-features">
                            <h5>Key Features:</h5>
                            <ul class="list-unstyled">
                                @foreach($service['features'] ?? [] as $feature)
                                <li><i class="fas fa-check text-success me-2"></i>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="service-process">
                            <h5>Our Process:</h5>
                            <ol class="process-steps">
                                @foreach($service['process_steps'] ?? [] as $step)
                                <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Specialized Services -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Specialized Services</h2>
            <p class="lead text-muted">Additional services to meet your specific requirements</p>
        </div>
        
        <div class="row g-4">
            @foreach($specialized_services ?? [] as $service)
            <div class="col-md-6 col-lg-3">
                <div class="specialized-service-card text-center">
                    <div class="specialized-icon">
                        <i class="{{ $service['icon'] }} fa-3x"></i>
                    </div>
                    <h4 class="mt-3">{{ $service['title'] }}</h4>
                    <p class="text-muted">{{ $service['description'] }}</p>
                    <div class="specialized-features">
                        @foreach($service['features'] ?? [] as $feature)
                        <span class="badge bg-primary me-1 mb-1">{{ $feature }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Why Choose APS Dream Home?</h2>
            <p class="lead text-muted">We stand out from the competition with our commitment to excellence</p>
        </div>
        
        <div class="row g-4">
            @foreach($why_choose_us ?? [] as $reason)
            <div class="col-md-6 col-lg-4">
                <div class="why-card text-center">
                    <div class="why-icon">
                        <i class="{{ $reason['icon'] }} fa-2x"></i>
                    </div>
                    <h4 class="mt-3">{{ $reason['title'] }}</h4>
                    <p class="text-muted">{{ $reason['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Client Testimonials -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">What Our Clients Say</h2>
            <p class="lead text-muted">Real experiences from satisfied customers</p>
        </div>
        
        <div class="row g-4">
            @foreach($testimonials ?? [] as $testimonial)
            <div class="col-md-6 col-lg-4">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= ($testimonial['rating'] ?? 5))
                                <i class="fas fa-star text-warning"></i>
                            @else
                                <i class="far fa-star text-warning"></i>
                            @endif
                        @endfor
                    </div>
                    <blockquote class="testimonial-content">
                        "{{ $testimonial['content'] }}"
                    </blockquote>
                    <div class="testimonial-author">
                        <strong>{{ $testimonial['name'] }}</strong>
                        <small class="text-muted d-block">{{ $testimonial['property'] }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-4">Ready to Experience Our Services?</h2>
        <p class="lead mb-4">Get in touch with our expert team and let us help you with your real estate needs.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ url('/contact') }}" class="btn btn-light btn-lg">Contact Us</a>
            <a href="tel:+917007444842" class="btn btn-outline-light btn-lg">Call Now</a>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
.service-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.service-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
}

.service-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 1rem;
}

.service-title {
    margin: 0;
    color: #2c3e50;
}

.service-features, .service-process {
    margin-top: 1.5rem;
}

.service-features h5, .service-process h5 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.process-steps {
    padding-left: 1.5rem;
}

.process-steps li {
    margin-bottom: 0.5rem;
    color: #6c757d;
}

.specialized-service-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    height: 100%;
}

.specialized-service-card:hover {
    transform: translateY(-5px);
}

.specialized-icon {
    color: #667eea;
    margin-bottom: 1rem;
}

.specialized-features .badge {
    font-size: 0.75rem;
}

.why-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    height: 100%;
}

.why-card:hover {
    transform: translateY(-5px);
}

.why-icon {
    color: #667eea;
    margin-bottom: 1rem;
}

.testimonial-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    height: 100%;
}

.testimonial-rating {
    margin-bottom: 1rem;
}

.testimonial-content {
    font-style: italic;
    margin-bottom: 1.5rem;
    color: #6c757d;
}

.testimonial-author strong {
    color: #2c3e50;
}

.stat-card {
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 1rem;
    backdrop-filter: blur(10px);
}

.hero-stats .stat-card h3 {
    color: white;
    margin: 0;
}

.hero-stats .stat-card p {
    color: rgba(255,255,255,0.8);
    margin: 0;
}
</style>
@endpush
