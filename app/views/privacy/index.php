@extends('layouts.base')
@section('title', $page_title ?? 'Privacy Policy - APS Dream Home')
@section('content')

<div class="container-fluid py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold text-primary">Privacy Policy</h1>
                    <p class="lead text-muted">{{ $page_description ?? 'Your privacy is important to us. This policy explains how we collect, use, and protect your information.' }}</p>
                    <p class="text-muted small">Last Updated: {{ $last_updated ?? date('F d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        @foreach($sections ?? [] as $section)
                        <div class="mb-5">
                            <h3 class="h4 text-primary mb-3">{{ $section['title'] }}</h3>
                            <p class="text-muted mb-3">{{ $section['content'] }}</p>
                            
                            @if(isset($section['items']) && !empty($section['items']))
                            <ul class="list-unstyled">
                                @foreach($section['items'] as $item)
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span>{{ $item }}</span>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        
                        @if(!$loop->last)
                        <hr class="my-4">
                        @endif
                        @endforeach

                        <div class="alert alert-info mt-4">
                            <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Contact Us</h5>
                            <p class="mb-0">If you have any questions about this Privacy Policy, please contact us at:</p>
                            <ul class="mb-0">
                                <li>Email: <a href="mailto:privacy@apsdreamhomes.com">privacy@apsdreamhomes.com</a></li>
                                <li>Phone: <a href="tel:+91-9277121101">+91-9277121101</a></li>
                                <li>Address: 1st floor singhariya chauraha, Kunraghat, Gorakhpur, UP - 273008</li>
                            </ul>
                        </div>

                        <div class="alert alert-success mt-4">
                            <h5 class="alert-heading"><i class="fas fa-shield-alt me-2"></i>Our Commitment</h5>
                            <p class="mb-0">We are committed to protecting your privacy and ensuring the security of your personal information. This policy will be updated as needed to reflect changes in our practices or applicable laws.</p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ url('/contact') }}" class="btn btn-primary me-2">
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.list-unstyled li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.list-unstyled li:last-child {
    border-bottom: none;
}

.text-primary {
    color: #2c3e50 !important;
}

.alert {
    border: none;
    border-radius: 10px;
}

.btn {
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.display-4 {
    font-weight: 700;
    color: #2c3e50;
}

.lead {
    font-size: 1.2rem;
    max-width: 800px;
    margin: 0 auto;
}

h3.h4 {
    color: #2c3e50;
    font-weight: 600;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2rem;
    }
    
    .lead {
        font-size: 1rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
}
</style>

@endsection
