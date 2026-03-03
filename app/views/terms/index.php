@extends('layouts.base')
@section('title', $page_title ?? 'Terms of Service - APS Dream Home')
@section('content')

<div class="container-fluid py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold text-primary">Terms of Service</h1>
                    <p class="lead text-muted">{{ $page_description ?? 'Please read these terms carefully before using our real estate services.' }}</p>
                    <p class="text-muted small">Last Updated: {{ $last_updated ?? date('F d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="alert alert-warning mb-4">
                            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Important Notice</h5>
                            <p class="mb-0">By accessing and using APS Dream Home services, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.</p>
                        </div>

                        @foreach($sections ?? [] as $section)
                        <div class="mb-5">
                            <h3 class="h4 text-primary mb-3">{{ $section['title'] }}</h3>
                            <p class="text-muted mb-3">{{ $section['content'] }}</p>
                            
                            @if(isset($section['items']) && !empty($section['items']))
                            <ul class="list-unstyled">
                                @foreach($section['items'] as $item)
                                <li class="mb-2">
                                    <i class="fas fa-chevron-right text-primary me-2"></i>
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
                            <h5 class="alert-heading"><i class="fas fa-gavel me-2"></i>Legal Information</h5>
                            <p class="mb-0">These Terms of Service constitute a legally binding agreement between you and APS Dream Homes Pvt Ltd. Registration No: U70109UP2022PTC163047</p>
                        </div>

                        <div class="alert alert-success mt-4">
                            <h5 class="alert-heading"><i class="fas fa-phone me-2"></i>Need Clarification?</h5>
                            <p class="mb-0">If you have any questions about these Terms of Service, please don't hesitate to contact our legal team:</p>
                            <ul class="mb-0">
                                <li>Email: <a href="mailto:legal@apsdreamhomes.com">legal@apsdreamhomes.com</a></li>
                                <li>Phone: <a href="tel:+91-9277121101">+91-9277121101</a></li>
                                <li>Address: 1st floor singhariya chauraha, Kunraghat, Gorakhpur, UP - 273008</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="text-primary mb-3"><i class="fas fa-history me-2"></i>Terms History</h4>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Current Version</h6>
                                    <p class="text-muted small mb-0">Last Updated: {{ $last_updated ?? date('F d, Y') }}</p>
                                    <p class="mb-0">Added sections on digital services and user responsibilities</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-secondary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Previous Version</h6>
                                    <p class="text-muted small mb-0">Updated: {{ date('F d, Y', strtotime('-6 months')) }}</p>
                                    <p class="mb-0">Initial terms of service for APS Dream Home platform</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ url('/contact') }}" class="btn btn-primary me-2">
                        <i class="fas fa-envelope me-2"></i>Contact Legal Team
                    </a>
                    <a href="{{ url('/privacy') }}" class="btn btn-outline-info me-2">
                        <i class="fas fa-shield-alt me-2"></i>Privacy Policy
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

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #2c3e50;
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
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -17px;
    }
}
</style>

@endsection
