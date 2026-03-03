@extends('layouts.base')
@section('title', $page_title ?? 'Contact Us - APS Dream Home')
@section('content')

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Contact Us</h1>
                <p class="lead mb-4">{{ $page_description ?? 'Get in touch with APS Dream Home for all your real estate needs. Visit our office or call us to find your dream property.' }}</p>
                <div class="d-flex gap-3">
                    <a href="{{ url('/properties') }}" class="btn btn-light btn-lg">Browse Properties</a>
                    <a href="tel:+917007444842" class="btn btn-outline-light btn-lg">Call Now</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <i class="fas fa-building fa-5x mb-3"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-primary mb-3">
                            <i class="fas fa-phone-alt fa-3x"></i>
                        </div>
                        <h5 class="card-title">Call Us</h5>
                        @foreach($contact_info['phone_numbers'] ?? [] as $phone)
                            <p class="card-text"><a href="tel:{{ $phone }}" class="text-decoration-none">{{ $phone }}</a></p>
                        @endforeach
                        <p class="text-muted small">{{ $contact_info['working_hours']['weekdays'] ?? '' }}</p>
                        <p class="text-muted small">{{ $contact_info['working_hours']['sunday'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-primary mb-3">
                            <i class="fas fa-envelope fa-3x"></i>
                        </div>
                        <h5 class="card-title">Email Us</h5>
                        @foreach($contact_info['email_addresses'] ?? [] as $email)
                            <p class="card-text"><a href="mailto:{{ $email }}" class="text-decoration-none">{{ $email }}</a></p>
                        @endforeach
                        <p class="text-muted small">We respond within 24 hours</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-primary mb-3">
                            <i class="fas fa-map-marker-alt fa-3x"></i>
                        </div>
                        <h5 class="card-title">Visit Us</h5>
                        <p class="card-text">{{ $contact_info['office_address'] ?? '' }}</p>
                        <p class="text-muted small">Gorakhpur, Uttar Pradesh</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Office Locations Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Our Office Locations</h2>
            <p class="lead text-muted">Visit our offices in Gorakhpur and Lucknow</p>
        </div>
        
        <div class="row">
            @foreach($office_locations ?? [] as $location)
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary">{{ $location['name'] }}</h5>
                        <p class="card-text">
                            <strong>Address:</strong><br>
                            {{ $location['address'] }}<br>
                            {{ $location['city'] }}, {{ $location['state'] }} - {{ $location['pincode'] }}<br><br>
                            <strong>Phone:</strong> <a href="tel:{{ $location['phone'] }}">{{ $location['phone'] }}</a><br>
                            <strong>Email:</strong> <a href="mailto:{{ $location['email'] }}">{{ $location['email'] }}</a>
                        </p>
                        <div class="ratio ratio-16x9">
                            <iframe src="{{ $location['map_embed'] }}" 
                                    style="border:0; width: 100%; height: 300px;" 
                                    allowfullscreen="" 
                                    loading="lazy">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="card-title text-primary mb-4">Send Us a Message</h3>
                        <form action="{{ url('/contact') }}" method="POST">
                            @foreach($contact_form['fields'] ?? [] as $field_name => $field_config)
                            <div class="mb-3">
                                <label for="{{ $field_name }}" class="form-label">{{ $field_config['label'] }} 
                                    @if($field_config['required'] ?? false)<span class="text-danger">*</span>@endif
                                </label>
                                
                                @if($field_config['type'] === 'select')
                                <select class="form-select" id="{{ $field_name }}" name="{{ $field_name }}" 
                                        @if($field_config['required'] ?? false) required @endif>
                                    <option value="">Choose...</option>
                                    @foreach($field_config['options'] ?? [] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                
                                @elseif($field_config['type'] === 'textarea')
                                <textarea class="form-control" id="{{ $field_name }}" name="{{ $field_name }}" rows="4"
                                          @if($field_config['required'] ?? false) required @endif></textarea>
                                
                                @else
                                <input type="{{ $field_config['type'] }}" class="form-control" 
                                       id="{{ $field_name }}" name="{{ $field_name }}"
                                       @if($field_config['required'] ?? false) required @endif>
                                @endif
                            </div>
                            @endforeach
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="card-title text-primary mb-4">Frequently Asked Questions</h3>
                        <div class="accordion" id="faqAccordion">
                            @foreach($faq_items ?? [] as $index => $faq)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $index }}">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                                        {{ $faq['question'] }}
                                    </button>
                                </h2>
                                <div id="collapse{{ $index }}" class="accordion-collapse collapse" 
                                     data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        {{ $faq['answer'] }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Find Your Dream Home?</h2>
            <p class="lead mb-4">Our expert team is here to help you every step of the way</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ url('/properties') }}" class="btn btn-light btn-lg">Browse Properties</a>
                <a href="tel:+917007444842" class="btn btn-outline-light btn-lg">Call Us Now</a>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #0d6efd;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0,0,0,.125);
}

.ratio iframe {
    border-radius: 0.375rem;
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
// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});

// Clear validation on input
document.querySelectorAll('input, select, textarea').forEach(field => {
    field.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>
@endpush

@endsection