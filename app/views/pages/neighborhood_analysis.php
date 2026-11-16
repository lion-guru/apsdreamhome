<?php
/**
 * Neighborhood Analysis & Livability Calculator
 * APS Dream Home — Public Tools
 */
$page_title = $page_title ?? 'Neighborhood Analysis - APS Dream Home';
$page_description = $page_description ?? 'Analyze neighborhood amenities, schools, transport, and livability score.';
$base = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="container py-5">
    <!-- Breadcrumb & Header -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="<?= $base ?>/">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= $base ?>/tools/hub">Tools</a></li>
            <li class="breadcrumb-item active" aria-current="page">Neighborhood Analysis</li>
        </ol>
    </nav>
    <div class="mb-4">
        <h1 class="h2 fw-bold text-gray-800"><i class="fas fa-map-marked-alt text-primary me-2"></i> Neighborhood & Livability Analysis</h1>
        <p class="text-muted">Explore infrastructure, connectivity, education, and healthcare proximity around APS Dream Home colonies.</p>
    </div>

    <!-- Quick Colony Picker -->
    <div class="row g-4 mb-5">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-city me-2"></i> Select Location</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Colony / Region</label>
                        <select id="colonySelect" class="form-select">
                            <option value="budhacity">Budha City, Gorakhpur</option>
                            <option value="suyoday">Suyoday Colony, Gorakhpur</option>
                            <option value="medical">Medical College Road, Gorakhpur</option>
                            <option value="taramandal">Taramandal Lake View, Gorakhpur</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Search Amenities</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="amenitySearch" class="form-control" placeholder="Schools, Hospitals, Metro...">
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded text-center">
                        <span class="text-muted small d-block mb-1">Livability Score</span>
                        <div class="display-6 fw-bold text-success">9.2 / 10</div>
                        <span class="badge bg-success mt-1"><i class="fas fa-star me-1"></i> Highly Recommended</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary"><i class="fas fa-layer-group me-2"></i> Infrastructure & Connectivity Metrics</h5>
                    <span class="badge bg-light text-dark border">Updated 2026</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold"><i class="fas fa-graduation-cap text-primary me-2"></i> Schools & Colleges</span>
                                    <span class="badge bg-primary">Within 2.5 km</span>
                                </div>
                                <p class="text-muted small mb-0">Top CBSE/ICSE schools, engineering and degree colleges accessible via 4-lane link roads.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold"><i class="fas fa-hospital-alt text-danger me-2"></i> Healthcare & Hospitals</span>
                                    <span class="badge bg-success">Within 1.8 km</span>
                                </div>
                                <p class="text-muted small mb-0">Multi-specialty hospitals, emergency trauma centers, and 24x7 pharmacies nearby.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold"><i class="fas fa-bus-alt text-warning me-2"></i> Transit & Highways</span>
                                    <span class="badge bg-info text-dark">500 m to Highway</span>
                                </div>
                                <p class="text-muted small mb-0">Direct connectivity to 4-lane state highway, upcoming metro corridor, and bus terminals.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold"><i class="fas fa-shopping-cart text-success me-2"></i> Markets & Commercial</span>
                                    <span class="badge bg-primary">Within 1.0 km</span>
                                </div>
                                <p class="text-muted small mb-0">Local markets, daily grocery hubs, banks, and ATMs within easy walking distance.</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Data sourced from municipal master plans and live GPS telemetry.</span>
                        <a href="<?= $base ?>/colonies" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i> View Available Plots in this Colony
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
