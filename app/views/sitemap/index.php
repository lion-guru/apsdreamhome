@extends('layouts.base')
@section('title', $page_title ?? 'Sitemap - APS Dream Home')
@section('content')

<div class="container-fluid py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold text-primary">Sitemap</h1>
                    <p class="lead text-muted">{{ $page_description ?? 'Navigate through all pages and sections of APS Dream Home website.' }}</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-primary">{{ count($main_pages ?? []) }} Main Pages</span>
                        <span class="badge bg-success">{{ count($property_pages ?? []) }} Property Pages</span>
                        <span class="badge bg-info">{{ count($legal_pages ?? []) }} Legal Pages</span>
                        <span class="badge bg-warning">{{ count($user_pages ?? []) }} User Pages</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Main Pages -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-home me-2"></i>Main Pages</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @foreach($main_pages ?? [] as $page)
                            <div class="col-md-6 mb-4">
                                <div class="sitemap-item">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-box bg-primary text-white me-3">
                                            <i class="fas fa-{{ $page['title'] == 'Home' ? 'home' : ($page['title'] == 'Properties' ? 'building' : ($page['title'] == 'Projects' ? 'hammer' : ($page['title'] == 'About Us' ? 'info-circle' : 'phone'))) }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2">
                                                <a href="{{ url($page['url']) }}" class="text-primary text-decoration-none">
                                                    {{ $page['title'] }}
                                                </a>
                                            </h5>
                                            <p class="text-muted small mb-2">{{ $page['description'] }}</p>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark me-2">{{ $page['url'] }}</span>
                                                <a href="{{ url($page['url']) }}" class="btn btn-sm btn-outline-primary">
                                                    Visit <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Property Pages -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-building me-2"></i>Property Pages</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @foreach($property_pages ?? [] as $page)
                            <div class="col-md-6 mb-4">
                                <div class="sitemap-item">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-box bg-success text-white me-3">
                                            <i class="fas fa-{{ $page['title'] == 'Resale Properties' ? 'sync' : ($page['title'] == 'Gallery' ? 'images' : ($page['title'] == 'Blog' ? 'blog' : ($page['title'] == 'Careers' ? 'briefcase' : ($page['title'] == 'Our Team' ? 'users' : ($page['title'] == 'Testimonials' ? 'quote-left' : 'question-circle')))) }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2">
                                                <a href="{{ url($page['url']) }}" class="text-success text-decoration-none">
                                                    {{ $page['title'] }}
                                                </a>
                                            </h5>
                                            <p class="text-muted small mb-2">{{ $page['description'] }}</p>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark me-2">{{ $page['url'] }}</span>
                                                <a href="{{ url($page['url']) }}" class="btn btn-sm btn-outline-success">
                                                    Visit <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Legal Pages -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-gavel me-2"></i>Legal Pages</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @foreach($legal_pages ?? [] as $page)
                            <div class="col-md-6 mb-4">
                                <div class="sitemap-item">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-box bg-info text-white me-3">
                                            <i class="fas fa-{{ $page['title'] == 'Privacy Policy' ? 'shield-alt' : ($page['title'] == 'Terms of Service' ? 'file-contract' : 'sitemap') }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2">
                                                <a href="{{ url($page['url']) }}" class="text-info text-decoration-none">
                                                    {{ $page['title'] }}
                                                </a>
                                            </h5>
                                            <p class="text-muted small mb-2">{{ $page['description'] }}</p>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark me-2">{{ $page['url'] }}</span>
                                                <a href="{{ url($page['url']) }}" class="btn btn-sm btn-outline-info">
                                                    Visit <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- User Pages -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>User Pages</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @foreach($user_pages ?? [] as $page)
                            <div class="col-md-6 mb-4">
                                <div class="sitemap-item">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-box bg-warning text-dark me-3">
                                            <i class="fas fa-{{ $page['title'] == 'Login' ? 'sign-in-alt' : ($page['title'] == 'Register' ? 'user-plus' : 'tachometer-alt') }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2">
                                                <a href="{{ url($page['url']) }}" class="text-warning text-decoration-none">
                                                    {{ $page['title'] }}
                                                </a>
                                            </h5>
                                            <p class="text-muted small mb-2">{{ $page['description'] }}</p>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark me-2">{{ $page['url'] }}</span>
                                                <a href="{{ url($page['url']) }}" class="btn btn-sm btn-outline-warning">
                                                    Visit <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Admin Pages -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Admin Pages</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @foreach($admin_pages ?? [] as $page)
                            <div class="col-md-6 mb-4">
                                <div class="sitemap-item">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-box bg-secondary text-white me-3">
                                            <i class="fas fa-cogs"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2">
                                                <a href="{{ url($page['url']) }}" class="text-secondary text-decoration-none">
                                                    {{ $page['title'] }}
                                                </a>
                                            </h5>
                                            <p class="text-muted small mb-2">{{ $page['description'] }}</p>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark me-2">{{ $page['url'] }}</span>
                                                <a href="{{ url($page['url']) }}" class="btn btn-sm btn-outline-secondary">
                                                    Visit <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <h3 class="text-primary mb-1">{{ count($main_pages ?? []) + count($property_pages ?? []) + count($legal_pages ?? []) + count($user_pages ?? []) + count($admin_pages ?? []) }}</h3>
                                    <p class="text-muted mb-0">Total Pages</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <h3 class="text-success mb-1">{{ count($property_pages ?? []) }}</h3>
                                    <p class="text-muted mb-0">Property Pages</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <h3 class="text-info mb-1">{{ count($user_pages ?? []) }}</h3>
                                    <p class="text-muted mb-0">User Pages</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <h3 class="text-warning mb-1">24/7</h3>
                                    <p class="text-muted mb-0">Availability</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ url('/contact') }}" class="btn btn-primary me-2">
                        <i class="fas fa-envelope me-2"></i>Contact Support
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

.card-header {
    border-radius: 15px 15px 0 0 !important;
    border: none;
}

.sitemap-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid;
    border-left-color: inherit;
    transition: all 0.3s ease;
}

.sitemap-item:hover {
    background: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.icon-box {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.badge {
    border-radius: 15px;
    padding: 5px 12px;
}

.btn {
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
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

.stat-box {
    padding: 20px;
    border-radius: 10px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.stat-box:hover {
    background: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.text-primary {
    color: #2c3e50 !important;
}

.text-success {
    color: #27ae60 !important;
}

.text-info {
    color: #3498db !important;
}

.text-warning {
    color: #f39c12 !important;
}

.text-secondary {
    color: #7f8c8d !important;
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
    
    .sitemap-item {
        padding: 15px;
    }
    
    .icon-box {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>

@endsection
