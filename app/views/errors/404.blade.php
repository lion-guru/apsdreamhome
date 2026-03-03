@extends('layouts.base')

@section('title', 'Page Not Found - APS Dream Home')

@section('content')
<div class="container mt-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="error-page text-center">
                <h1 class="display-1 text-primary mb-4">404</h1>
                <h2 class="mb-3">Page Not Found</h2>
                <p class="lead text-muted mb-4">The page you are looking for does not exist.</p>
                <a href="{{ url('/') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-home me-2"></i>Go Home
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.error-page {
    padding: 60px 0;
}

.error-page .display-1 {
    font-size: 8rem;
    font-weight: bold;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.error-page h2 {
    color: #333;
    font-weight: 600;
}

.error-page .btn-primary {
    padding: 12px 30px;
    font-size: 1.1rem;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.error-page .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
</style>
@endpush
@endsection
