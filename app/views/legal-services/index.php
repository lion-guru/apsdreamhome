@extends('layouts.base')
@section('title', $page_title ?? 'Legal Services - APS Dream Home')
@section('content')

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">{{ $page_title ?? 'Legal Services' }}</h1>
                <p class="lead mb-4">{{ $page_description ?? 'Expert legal assistance for all your real estate needs.' }}</p>
                <div class="d-flex gap-3">
                    <a href="{{ url('/contact') }}" class="btn btn-light btn-lg">Consult Our Experts</a>
                    <a href="tel:+917007444842" class="btn btn-outline-light btn-lg">Call Now</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="{{ asset('images/legal-services-hero.jpg') }}" alt="Legal Services" class="img-fluid rounded-3 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3">
                <h3 class="text-primary fw-bold">{{ $legal_stats['cases_handled'] ?? '500+' }}</h3>
                <p class="text-muted">Cases Handled</p>
            </div>
            <div class="col-md-3">
                <h3 class="text-primary fw-bold">{{ $legal_stats['successful_registrations'] ?? '450+' }}</h3>
                <p class="text-muted">Successful Registrations</p>
            </div>
            <div class="col-md-3">
                <h3 class="text-primary fw-bold">{{ $legal_stats['years_experience'] ?? '15+' }}</h3>
                <p class="text-muted">Years Experience</p>
            </div>
            <div class="col-md-3">
                <h3 class="text-primary fw-bold">{{ $legal_stats['client_satisfaction'] ?? '98%' }}</h3>
                <p class="text-muted">Client Satisfaction</p>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Our Legal Services</h2>
            <p class="lead text-muted">Comprehensive legal solutions for all your real estate needs</p>
        </div>
        
        <div class="row g-4">
            @foreach($legal_services ?? [] as $service)
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                                    <i class="fas {{ $service['icon'] ?? 'fa-balance-scale' }} fa-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="card-title fw-bold">{{ $service['title'] }}</h4>
                                <p class="text-muted">{{ $service['description'] }}</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold text-primary">Key Features:</h6>
                            <ul class="list-unstyled">
                                @foreach($service['features'] ?? [] as $feature)
                                <li class="mb-1"><i class="fas fa-check text-success me-2"></i>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div>
                                <small class="text-muted">Process Time:</small>
                                <span class="fw-semibold">{{ $service['process_time'] ?? 'Varies' }}</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">Starting from:</small>
                                <div class="fw-bold text-primary">{{ $service['price'] ?? 'Contact for pricing' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Our Legal Process</h2>
            <p class="lead text-muted">Simple and transparent legal procedures</p>
        </div>
        
        <div class="row">
            @foreach($process_steps ?? [] as $step)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <span class="fw-bold fs-4">{{ $step['step'] }}</span>
                    </div>
                    <h5 class="fw-bold">{{ $step['title'] }}</h5>
                    <p class="text-muted">{{ $step['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Why Choose Our Legal Services?</h2>
            <p class="lead text-muted">We stand out with our commitment to excellence</p>
        </div>
        
        <div class="row g-4">
            @foreach($why_choose_us ?? [] as $reason)
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas {{ $reason['icon'] ?? 'fa-star' }} fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">{{ $reason['title'] }}</h5>
                    <p class="text-muted">{{ $reason['description'] }}</p>
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
            <h2 class="display-5 fw-bold">Client Testimonials</h2>
            <p class="lead text-muted">What our clients say about our legal services</p>
        </div>
        
        <div class="row g-4">
            @foreach($testimonials ?? [] as $testimonial)
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
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
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $testimonial['name'] }}</h6>
                                <small class="text-muted">{{ $testimonial['service'] }}</small>
                            </div>
                        </div>
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
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3">Need Legal Assistance for Your Property?</h2>
                <p class="lead mb-0">Our expert legal team is ready to help you with all your real estate legal needs.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg me-2">Get Legal Consultation</a>
                <a href="tel:+917007444842" class="btn btn-outline-light btn-lg">Call Now</a>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
.hero-section {
    background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
}
.card {
    transition: transform 0.2s ease-in-out;
}
.card:hover {
    transform: translateY(-5px);
}
.bg-primary.bg-opacity-10 {
    background-color: rgba(13, 110, 253, 0.1) !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling
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
});
</script>
@endpush

@endsection