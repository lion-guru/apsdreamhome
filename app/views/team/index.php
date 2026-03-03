@extends('layouts.base')

@section('title', $page_title ?? 'Our Team - APS Dream Home')

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">Our Team</h1>
                    <p class="lead mb-4">{{ $page_description ?? 'Meet the dedicated professionals behind APS Dream Home. Our experienced team is committed to delivering excellence in real estate services.' }}</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#leadership" class="btn btn-light btn-lg">Meet Leadership</a>
                        <a href="{{ url('/careers') }}" class="btn btn-outline-light btn-lg">Join Our Team</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="{{ asset('images/team-hero.jpg') }}" alt="Our Team" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Statistics -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-primary fw-bold">{{ $team_stats['total_members'] ?? 150 }}+</h3>
                    <p class="text-muted mb-0">Team Members</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-success fw-bold">{{ $team_stats['years_experience'] ?? 100 }}+</h3>
                    <p class="text-muted mb-0">Years Experience</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-info fw-bold">{{ $team_stats['projects_completed'] ?? 500 }}+</h3>
                    <p class="text-muted mb-0">Projects Completed</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-warning fw-bold">{{ $team_stats['happy_customers'] ?? 2000 }}+</h3>
                    <p class="text-muted mb-0">Happy Customers</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Values -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Our Core Values</h2>
            <p class="lead text-muted">The principles that guide our work and define our culture</p>
        </div>
        
        <div class="row g-4">
            @foreach($company_values ?? [] as $value)
            <div class="col-md-6 col-lg-3">
                <div class="value-card text-center p-4 h-100">
                    <div class="value-icon mb-3">
                        <i class="fas {{ $value['icon'] }} fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-3">{{ $value['title'] }}</h5>
                    <p class="text-muted">{{ $value['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Leadership Team -->
<section id="leadership" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Leadership Team</h2>
            <p class="lead text-muted">Meet the visionary leaders driving APS Dream Home's success</p>
        </div>
        
        <div class="row g-4">
            @foreach($leadership_team ?? [] as $leader)
            <div class="col-lg-6">
                <div class="leader-card card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="leader-image text-center mb-3 mb-md-0">
                                    <img src="{{ asset('images/' . ($leader['image'] ?? 'team/default.jpg')) }}" 
                                         alt="{{ $leader['name'] }}" 
                                         class="rounded-circle img-fluid"
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <h4 class="fw-bold mb-1">{{ $leader['name'] }}</h4>
                                <h5 class="text-primary mb-3">{{ $leader['position'] }}</h5>
                                
                                <p class="text-muted mb-3">{{ $leader['bio'] }}</p>
                                
                                <div class="leader-details mb-3">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-graduation-cap"></i> {{ $leader['education'] }}
                                            </small>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-briefcase"></i> {{ $leader['experience'] }} Experience
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="achievements mb-3">
                                    <h6 class="fw-bold mb-2">Key Achievements:</h6>
                                    <ul class="small text-muted mb-0">
                                        @foreach($leader['achievements'] as $achievement)
                                        <li>{{ $achievement }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <div class="leader-contact">
                                    <div class="d-flex gap-3">
                                        <a href="mailto:{{ $leader['email'] }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-envelope"></i> Email
                                        </a>
                                        <a href="tel:{{ $leader['phone'] }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-phone"></i> Call
                                        </a>
                                        @if(isset($leader['linkedin']))
                                        <a href="{{ $leader['linkedin'] }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fab fa-linkedin"></i> LinkedIn
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Department Heads -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Department Heads</h2>
            <p class="lead text-muted">Expert professionals leading our key departments</p>
        </div>
        
        <div class="row g-4">
            @foreach($department_heads ?? [] as $head)
            <div class="col-md-6 col-lg-3">
                <div class="department-head-card card h-100 shadow-sm text-center">
                    <div class="card-body">
                        <div class="head-image mb-3">
                            <img src="{{ asset('images/' . ($head['image'] ?? 'team/default.jpg')) }}" 
                                 alt="{{ $head['name'] }}" 
                                 class="rounded-circle img-fluid"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        </div>
                        <h5 class="fw-bold mb-2">{{ $head['name'] }}</h5>
                        <h6 class="text-primary mb-3">{{ $head['position'] }}</h6>
                        
                        <p class="text-muted small mb-3">{{ $head['bio'] }}</p>
                        
                        <div class="head-details mb-3">
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-graduation-cap"></i> {{ $head['education'] }}
                                </small>
                            </div>
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-briefcase"></i> {{ $head['experience'] }}
                                </small>
                            </div>
                        </div>
                        
                        <div class="head-contact">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="mailto:{{ $head['email'] }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-envelope"></i>
                                </a>
                                <a href="tel:{{ $head['phone'] }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-phone"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Join Our Team -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-4">Join Our Growing Team</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-briefcase fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-0">{{ $join_team_info['current_openings'] }} Current Openings</h6>
                                <small>{{ $join_team_info['hiring_process'] }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-chart-line fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-0">Growth Opportunities</h6>
                                <small>{{ $join_team_info['growth_opportunities'] }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-heart fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-0">Work Culture</h6>
                                <small>{{ $join_team_info['work_culture'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ url('/careers') }}" class="btn btn-light btn-lg me-2">View Careers</a>
                <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg">Contact HR</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center">
            <h2 class="display-5 fw-bold mb-3">Ready to Work With Our Expert Team?</h2>
            <p class="lead mb-4">Connect with our professionals to find your dream property or explore partnership opportunities.</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ url('/properties') }}" class="btn btn-primary btn-lg">Browse Properties</a>
                <a href="{{ url('/contact') }}" class="btn btn-outline-primary btn-lg">Get in Touch</a>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.value-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.leader-card {
    transition: transform 0.3s ease;
}

.leader-card:hover {
    transform: translateY(-3px);
}

.department-head-card {
    transition: transform 0.3s ease;
}

.department-head-card:hover {
    transform: translateY(-3px);
}

.leader-image img,
.head-image img {
    transition: transform 0.3s ease;
}

.leader-card:hover .leader-image img,
.department-head-card:hover .head-image img {
    transform: scale(1.05);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

@media (max-width: 768px) {
    .hero-section .display-4 {
        font-size: 2.5rem;
    }
    
    .leader-image img,
    .head-image img {
        width: 100px !important;
        height: 100px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll to leadership section
    document.querySelectorAll('a[href="#leadership"]').forEach(anchor => {
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
    document.querySelectorAll('.leader-card, .department-head-card, .value-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>
@endsection
