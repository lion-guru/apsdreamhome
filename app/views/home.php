<?php $this->layout('layouts/main', ['title' => $title ?? 'APS Dream Home']); ?>

<div class="hero-section">
    <div class="container">
        <h1>Welcome to APS Dream Home</h1>
        <p>Find your perfect property with us.</p>
        <a href="/properties" class="btn btn-primary">View Properties</a>
    </div>
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Buy Property</h5>
                    <p class="card-text">Explore our wide range of properties for sale.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sell Property</h5>
                    <p class="card-text">List your property with us for the best deals.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Rent Property</h5>
                    <p class="card-text">Find the perfect rental property for your needs.</p>
                </div>
            </div>
        </div>
    </div>
</div>
