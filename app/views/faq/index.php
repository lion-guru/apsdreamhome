@extends('layouts.base')

@section('title', $page_title ?? 'FAQ - APS Dream Home')

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">Frequently Asked Questions</h1>
                    <p class="lead mb-4">{{ $page_description ?? 'Find answers to frequently asked questions about APS Dream Home, our services, properties, and the real estate buying process.' }}</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#faq-categories" class="btn btn-light btn-lg">Browse FAQs</a>
                        <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg">Ask a Question</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="{{ asset('images/faq-hero.jpg') }}" alt="FAQ" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Search -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="search-box">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-primary"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0" 
                               id="faqSearch" 
                               placeholder="Search for questions...">
                        <button class="btn btn-primary" type="button" onclick="searchFAQs()">
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Categories -->
<section id="faq-categories" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Browse by Category</h2>
            <p class="lead text-muted">Find answers organized by topic</p>
        </div>
        
        <div class="row g-4 mb-5">
            @foreach($faq_categories ?? [] as $category)
            <div class="col-md-6 col-lg-4">
                <div class="category-card card h-100 shadow-sm text-center p-4" onclick="scrollToCategory('{{ $category['id'] }}');" style="cursor: pointer;">
                    <div class="category-icon mb-3">
                        <i class="fas {{ $category['icon'] }} fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-2">{{ $category['name'] }}</h5>
                    <p class="text-muted mb-0">{{ count($category['faqs']) }} Questions</p>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- FAQ Items -->
        <div class="accordion" id="faqAccordion">
            @foreach($faq_categories ?? [] as $category)
            <div class="faq-category mb-5" id="{{ $category['id'] }}">
                <div class="category-header mb-4">
                    <h3 class="h4">
                        <i class="fas {{ $category['icon'] }} text-primary me-2"></i>
                        {{ $category['name'] }}
                    </h3>
                </div>
                
                @foreach($category['faqs'] as $index => $faq)
                <div class="accordion-item mb-3">
                    <h2 class="accordion-header" id="heading-{{ $category['id'] }}-{{ $index }}">
                        <button class="accordion-button collapsed" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse-{{ $category['id'] }}-{{ $index }}"
                                aria-expanded="false"
                                aria-controls="collapse-{{ $category['id'] }}-{{ $index }}">
                            {{ $faq['question'] }}
                        </button>
                    </h2>
                    <div id="collapse-{{ $category['id'] }}-{{ $index }}" 
                         class="accordion-collapse collapse" 
                         aria-labelledby="heading-{{ $category['id'] }}-{{ $index }}"
                         data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p class="mb-0">{{ $faq['answer'] }}</p>
                            <div class="faq-actions mt-3">
                                <button class="btn btn-outline-primary btn-sm" onclick="markHelpful('{{ $category['id'] }}-{{ $index }}');">
                                    <i class="fas fa-thumbs-up me-1"></i> Helpful
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="copyAnswer('{{ $category['id'] }}-{{ $index }}');">
                                    <i class="fas fa-copy me-1"></i> Copy
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="shareAnswer('{{ $category['id'] }}-{{ $index }}');">
                                    <i class="fas fa-share me-1"></i> Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Still Have Questions -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="questions-content">
                    <h2 class="display-5 fw-bold mb-4">Still Have Questions?</h2>
                    <p class="lead mb-4">Can't find the answer you're looking for? Our expert team is here to help you with any questions about properties, buying process, or our services.</p>
                    
                    <div class="contact-options">
                        <div class="contact-option mb-3">
                            <div class="d-flex align-items-center">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-phone-alt fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Call Us</h6>
                                    <p class="mb-0">{{ $contact_info['phone'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-option mb-3">
                            <div class="d-flex align-items-center">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-envelope fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Email Us</h6>
                                    <p class="mb-0">{{ $contact_info['email'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-option mb-3">
                            <div class="d-flex align-items-center">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-clock fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Working Hours</h6>
                                    <p class="mb-0">{{ $contact_info['working_hours'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="contact-form">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Ask Your Question</h4>
                            <form onsubmit="submitQuestion(event)">
                                <div class="mb-3">
                                    <label for="questionName" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="questionName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="questionEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="questionEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="questionCategory" class="form-label">Category</label>
                                    <select class="form-select" id="questionCategory" required>
                                        <option value="">Select Category</option>
                                        @foreach($faq_categories ?? [] as $category)
                                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="questionText" class="form-label">Your Question</label>
                                    <textarea class="form-control" id="questionText" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Submit Question</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Links -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Quick Links</h2>
            <p class="lead text-muted">Helpful resources for your property journey</p>
        </div>
        
        <div class="row g-4">
            @foreach($quick_links ?? [] as $link)
            <div class="col-md-6 col-lg-3">
                <div class="quick-link-card card h-100 shadow-sm text-center p-4">
                    <div class="link-icon mb-3">
                        <i class="fas fa-arrow-right fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-2">{{ $link['title'] }}</h5>
                    <p class="text-muted mb-3">{{ $link['description'] }}</p>
                    <a href="{{ url($link['url']) }}" class="btn btn-outline-primary btn-sm">Learn More</a>
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
                <h2 class="display-5 fw-bold mb-3">Ready to Find Your Dream Property?</h2>
                <p class="lead mb-0">Browse our extensive property collection or get in touch with our expert team for personalized assistance.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ url('/properties') }}" class="btn btn-light btn-lg me-2">Browse Properties</a>
                <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg">Contact Us</a>
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

.search-box .input-group {
    border-radius: 50px;
    overflow: hidden;
}

.search-box .input-group-text {
    border-radius: 50px 0 0 50px;
    border: none;
}

.search-box .form-control {
    border: none;
    border-radius: 0;
}

.search-box .btn {
    border-radius: 0 50px 50px 0;
    padding: 0 20px;
}

.accordion-item {
    border: 1px solid #e9ecef;
    border-radius: 8px !important;
    overflow: hidden;
}

.accordion-button {
    background-color: #f8f9fa;
    font-weight: 500;
}

.accordion-button:not(.collapsed) {
    background-color: #667eea;
    color: white;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: #667eea;
}

.faq-actions {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
}

.contact-option {
    transition: transform 0.3s ease;
}

.contact-option:hover {
    transform: translateX(5px);
}

.quick-link-card {
    transition: transform 0.3s ease;
}

.quick-link-card:hover {
    transform: translateY(-3px);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

@media (max-width: 768px) {
    .hero-section .display-4 {
        font-size: 2.5rem;
    }
}
</style>

<script>
function scrollToCategory(categoryId) {
    const element = document.getElementById(categoryId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

function searchFAQs() {
    const searchTerm = document.getElementById('faqSearch').value.toLowerCase();
    const faqItems = document.querySelectorAll('.accordion-item');
    let foundCount = 0;
    
    faqItems.forEach(item => {
        const question = item.querySelector('.accordion-button').textContent.toLowerCase();
        const answer = item.querySelector('.accordion-body p').textContent.toLowerCase();
        
        if (question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = 'block';
            foundCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show message if no results found
    const existingMessage = document.querySelector('.no-results');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    if (foundCount === 0) {
        const message = document.createElement('div');
        message.className = 'alert alert-info no-results mt-3';
        message.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            No FAQs found matching "${searchTerm}". Try different keywords or 
            <a href="{{ url('/contact') }}" class="alert-link">contact our support team</a>.
        `;
        document.getElementById('faqAccordion').appendChild(message);
    }
}

function markHelpful(id) {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check me-1"></i> Marked Helpful';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 2000);
}

function copyAnswer(id) {
    const answerText = event.target.closest('.accordion-body').querySelector('p').textContent;
    navigator.clipboard.writeText(answerText).then(() => {
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
        
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
    });
}

function shareAnswer(id) {
    const answerText = event.target.closest('.accordion-body').querySelector('p').textContent;
    if (navigator.share) {
        navigator.share({
            title: 'APS Dream Home FAQ',
            text: answerText,
            url: window.location.href
        });
    } else {
        // Fallback to copying to clipboard
        navigator.clipboard.writeText(answerText + '\n\n' + window.location.href).then(() => {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check me-1"></i> Link Copied!';
            
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        });
    }
}

function submitQuestion(event) {
    event.preventDefault();
    
    const formData = {
        name: document.getElementById('questionName').value,
        email: document.getElementById('questionEmail').value,
        category: document.getElementById('questionCategory').value,
        question: document.getElementById('questionText').value
    };
    
    const button = event.target.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
    button.disabled = true;
    
    // Simulate form submission
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-check me-2"></i>Submitted Successfully!';
        
        // Reset form
        event.target.reset();
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 3000);
        
        // Show success message
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show mt-3';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            Thank you for your question! Our team will get back to you within 24 hours.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        event.target.appendChild(alert);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }, 1500);
}

document.addEventListener('DOMContentLoaded', function() {
    // Add search functionality on Enter key
    document.getElementById('faqSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchFAQs();
        }
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
    document.querySelectorAll('.category-card, .quick-link-card, .contact-option').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>
@endsection
