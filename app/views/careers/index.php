@extends('layouts.base')

@section('title', $page_title ?? 'Careers - APS Dream Home')

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">Careers</h1>
                    <p class="lead mb-4">{{ $page_description ?? 'Join our growing team and build a rewarding career in real estate. Explore current job openings and internship opportunities.' }}</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#current-openings" class="btn btn-light btn-lg">View Openings</a>
                        <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg">Contact HR</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="{{ asset('images/careers-hero.jpg') }}" alt="Careers" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Career Statistics -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-primary fw-bold">{{ $career_stats['total_employees'] ?? 150 }}+</h3>
                    <p class="text-muted mb-0">Employees</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-success fw-bold">{{ $career_stats['new_hires_this_year'] ?? 45 }}</h3>
                    <p class="text-muted mb-0">New Hires This Year</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-info fw-bold">{{ $career_stats['employee_satisfaction'] ?? 92 }}%</h3>
                    <p class="text-muted mb-0">Employee Satisfaction</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-item">
                    <h3 class="text-warning fw-bold">{{ $career_stats['internal_promotions'] ?? 28 }}</h3>
                    <p class="text-muted mb-0">Internal Promotions</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Culture -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">{{ $company_culture['title'] ?? 'Why Work With APS Dream Home?' }}</h2>
            <p class="lead text-muted">{{ $company_culture['description'] ?? 'Join a team that values innovation, integrity, and excellence.' }}</p>
        </div>
        
        <div class="row g-4">
            @foreach($company_culture['values'] ?? [] as $value)
            <div class="col-md-6 col-lg-3">
                <div class="culture-card text-center p-4 h-100">
                    <div class="culture-icon mb-3">
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

<!-- Current Openings -->
<section id="current-openings" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Current Openings</h2>
            <p class="lead text-muted">Explore exciting career opportunities with APS Dream Home</p>
        </div>
        
        <div class="row g-4">
            @foreach($current_openings ?? [] as $job)
            <div class="col-lg-6">
                <div class="job-card card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title fw-bold">{{ $job['title'] }}</h5>
                                <div class="job-meta d-flex gap-3 flex-wrap mb-2">
                                    <span class="badge bg-primary">{{ $job['department'] }}</span>
                                    <span class="badge bg-secondary">{{ $job['type'] }}</span>
                                    @if($job['urgent'] ?? false)
                                    <span class="badge bg-danger">Urgent</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <h6 class="text-primary fw-bold">{{ $job['vacancies'] }} Vacancies</h6>
                            </div>
                        </div>
                        
                        <div class="job-details mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> {{ $job['location'] }}
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-briefcase"></i> {{ $job['experience'] }}
                                    </small>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">
                                        <i class="fas fa-rupee-sign"></i> {{ $job['salary'] }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-muted mb-3">{{ $job['description'] }}</p>
                        
                        <div class="job-deadline mb-3">
                            <small class="text-danger">
                                <i class="fas fa-clock"></i> Deadline: {{ date('M d, Y', strtotime($job['deadline'])) }}
                            </small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#job-details-{{ $job['id'] }}">
                                    View Details
                                </button>
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="applyForJob({{ $job['id'] }})">
                                Apply Now
                            </button>
                        </div>
                        
                        <div class="collapse mt-3" id="job-details-{{ $job['id'] }}">
                            <div class="border-top pt-3">
                                <h6 class="fw-bold mb-2">Responsibilities:</h6>
                                <ul class="small text-muted mb-3">
                                    @foreach($job['responsibilities'] as $responsibility)
                                    <li>{{ $responsibility }}</li>
                                    @endforeach
                                </ul>
                                
                                <h6 class="fw-bold mb-2">Requirements:</h6>
                                <ul class="small text-muted">
                                    @foreach($job['requirements'] as $requirement)
                                    <li>{{ $requirement }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Internship Programs -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Internship Programs</h2>
            <p class="lead text-muted">Kickstart your career with our comprehensive internship opportunities</p>
        </div>
        
        <div class="row g-4">
            @foreach($internship_programs ?? [] as $internship)
            <div class="col-md-4">
                <div class="internship-card card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="internship-icon mb-3">
                            <i class="fas fa-user-graduate fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title fw-bold">{{ $internship['title'] }}</h5>
                        <p class="text-muted">{{ $internship['description'] }}</p>
                        <div class="internship-details">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> {{ $internship['duration'] }}
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-rupee-sign"></i> {{ $internship['stipend'] }}
                                </small>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary btn-sm w-100">Apply for Internship</button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Employee Benefits</h2>
            <p class="lead text-muted">Comprehensive benefits package to support your well-being and growth</p>
        </div>
        
        <div class="row g-4">
            @foreach($benefits ?? [] as $benefit)
            <div class="col-md-6 col-lg-4">
                <div class="benefit-card d-flex align-items-start p-3">
                    <div class="benefit-icon me-3">
                        <i class="fas {{ $benefit['icon'] }} fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">{{ $benefit['title'] }}</h6>
                        <p class="text-muted small mb-0">{{ $benefit['description'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Recruitment Process -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Recruitment Process</h2>
            <p class="lead text-muted">Our transparent and efficient hiring process</p>
        </div>
        
        <div class="row g-4">
            @foreach($recruitment_process ?? [] as $step)
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

<!-- Employee Testimonials -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">What Our Employees Say</h2>
            <p class="lead text-muted">Hear from our team members about their experience at APS Dream Home</p>
        </div>
        
        <div class="row g-4">
            @foreach($testimonials ?? [] as $testimonial)
            <div class="col-md-4">
                <div class="testimonial-card card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="testimonial-content mb-3">
                            <i class="fas fa-quote-left text-primary mb-2"></i>
                            <p class="text-muted">{{ $testimonial['quote'] }}</p>
                        </div>
                        <div class="testimonial-author d-flex align-items-center">
                            <div class="author-avatar me-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="fw-bold">{{ substr($testimonial['name'], 0, 1) }}</span>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $testimonial['name'] }}</h6>
                                <small class="text-muted">{{ $testimonial['position'] }}</small>
                                <br>
                                <small class="text-primary">{{ $testimonial['duration'] }}</small>
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
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-3">Ready to Join Our Team?</h2>
                <p class="lead mb-0">Take the first step towards a rewarding career with APS Dream Home. Submit your application today!</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="mailto:hr@apsdreamhomes.com" class="btn btn-light btn-lg me-2">Email Resume</a>
                <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg">Contact HR</a>
            </div>
        </div>
    </div>
</section>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.culture-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.culture-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.job-card {
    transition: transform 0.3s ease;
}

.job-card:hover {
    transform: translateY(-3px);
}

.internship-card {
    transition: transform 0.3s ease;
}

.internship-card:hover {
    transform: translateY(-3px);
}

.benefit-card {
    transition: transform 0.3s ease;
}

.benefit-card:hover {
    transform: translateY(-2px);
}

.step-circle {
    font-size: 1.2rem;
}

.testimonial-card {
    transition: transform 0.3s ease;
}

.testimonial-card:hover {
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
function applyForJob(jobId) {
    // Show application modal or redirect to application form
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.setAttribute('tabindex', '-1');
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Job Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Thank you for your interest in this position!</p>
                    <p>Please send your resume to <strong>hr@apsdreamhomes.com</strong> with the job ID: <strong>#\${jobId}</strong></p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Our HR team will review your application and contact you within 3-5 business days.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="mailto:hr@apsdreamhomes.com?subject=Job Application - ID \${jobId}" class="btn btn-primary">Send Email</a>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.addEventListener('hidden.bs.modal', () => {
        modal.remove();
    });
}
</script>
@endsection
