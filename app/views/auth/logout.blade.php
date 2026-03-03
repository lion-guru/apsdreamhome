@extends('layouts.base')

@section('title', 'Logged Out - APS Dream Home')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-sign-out-alt fa-4x text-success"></i>
                    </div>
                    <h2 class="mb-3">Successfully Logged Out</h2>
                    <p class="text-muted mb-4">You have been successfully logged out of the admin panel.</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ url('/admin/login') }}" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login Again
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Go Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    max-width: 500px;
    margin: 0 auto;
    border-radius: 15px;
}

.fa-sign-out-alt {
    animation: fadeOut 2s ease-in-out;
}

@keyframes fadeOut {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.1); }
    100% { opacity: 1; transform: scale(1); }
}

.btn {
    padding: 10px 25px;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
}
</style>
@endpush
@endsection
