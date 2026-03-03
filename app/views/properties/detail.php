@extends('layouts.base')
@section('title', $title ?? 'Property Details - APS Dream Home')
@section('content')

<div class="container-fluid py-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/properties') }}">Properties</a></li>
                <li class="breadcrumb-item active">{{ $property->title }}</li>
            </ol>
        </nav>

        <!-- Property Details Section -->
        <div class="row">
            <!-- Property Images -->
            <div class="col-lg-8">
                <div class="property-gallery">
                    <img src="{{ $property->image_path }}" alt="{{ $property->title }}" class="img-fluid rounded mb-3" style="width: 100%; height: 400px; object-fit: cover;">
                    
                    <!-- Thumbnail Gallery -->
                    <div class="row">
                        <div class="col-3">
                            <img src="{{ $property->image_path }}" alt="Thumbnail 1" class="img-fluid rounded thumbnail-img" style="width: 100%; height: 80px; object-fit: cover; cursor: pointer;">
                        </div>
                        <div class="col-3">
                            <img src="{{ $property->image_path }}" alt="Thumbnail 2" class="img-fluid rounded thumbnail-img" style="width: 100%; height: 80px; object-fit: cover; cursor: pointer;">
                        </div>
                        <div class="col-3">
                            <img src="{{ $property->image_path }}" alt="Thumbnail 3" class="img-fluid rounded thumbnail-img" style="width: 100%; height: 80px; object-fit: cover; cursor: pointer;">
                        </div>
                        <div class="col-3">
                            <img src="{{ $property->image_path }}" alt="Thumbnail 4" class="img-fluid rounded thumbnail-img" style="width: 100%; height: 80px; object-fit: cover; cursor: pointer;">
                        </div>
                    </div>
                </div>

                <!-- Property Description -->
                <div class="mt-4">
                    <h3>Description</h3>
                    <p class="text-muted">Experience luxury living in this stunning {{ $property->type ?? 'property' }} located in the prime area of {{ $property->location }}. This exceptional property offers modern amenities, spacious interiors, and unparalleled comfort.</p>
                    
                    <h4 class="mt-4">Features & Amenities</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Spacious Living Room</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Modern Kitchen</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Parking Space</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>24/7 Security</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Power Backup</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Water Supply</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Garden Area</li>
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Gym Access</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Information & Contact -->
            <div class="col-lg-4">
                <!-- Price Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="text-primary">₹{{ number_format($property->price) }}</h4>
                        <p class="text-muted mb-3">{{ $property->title }}</p>
                        
                        <!-- Property Specifications -->
                        <div class="row mb-3">
                            <div class="col-6 text-center">
                                <i class="bi bi-door-open fs-4 text-primary"></i>
                                <p class="mb-0">{{ $property->bedrooms }} Beds</p>
                            </div>
                            <div class="col-6 text-center">
                                <i class="bi bi-droplet fs-4 text-primary"></i>
                                <p class="mb-0">{{ $property->bathrooms }} Baths</p>
                            </div>
                        </div>
                        
                        <div class="text-center mb-3">
                            <i class="bi bi-rulers fs-4 text-primary"></i>
                            <p class="mb-0">{{ $property->area }} Sq.ft</p>
                        </div>

                        <!-- Contact Buttons -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary">
                                <i class="bi bi-telephone me-2"></i>Contact Agent
                            </button>
                            <button class="btn btn-outline-primary">
                                <i class="bi bi-calendar-check me-2"></i>Schedule Visit
                            </button>
                            <button class="btn btn-outline-secondary">
                                <i class="bi bi-heart me-2"></i>Save Property
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Location</h5>
                        <p class="text-muted"><i class="bi bi-geo-alt me-2"></i>{{ $property->location }}</p>
                        
                        <!-- Map Placeholder -->
                        <div class="bg-light rounded" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-center">
                                <i class="bi bi-map fs-1 text-muted"></i>
                                <p class="text-muted mb-0">Map View</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Agent Information -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Property Agent</h5>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Rajesh Kumar</h6>
                                <p class="text-muted mb-0">Senior Property Consultant</p>
                                <small class="text-muted">+91-7007444842</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Properties -->
        @if(isset($related_properties) && count($related_properties) > 0)
        <div class="mt-5">
            <h3>Related Properties</h3>
            <div class="row">
                @foreach($related_properties as $related)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <img src="{{ $related->image_path }}" class="card-img-top" alt="{{ $related->title }}" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">{{ $related->title }}</h6>
                            <p class="text-muted small">{{ $related->location }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary fw-bold">₹{{ number_format($related->price) }}</span>
                                <a href="{{ url('/properties/' . $related->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.thumbnail-img:hover {
    border: 2px solid #0d6efd;
    opacity: 0.8;
}
</style>

@endsection
