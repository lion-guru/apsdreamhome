<?php
$$page_title = 'Premium Properties - APS Dream Home';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-5">
    <!-- Search & Filter Header -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="glass-card p-4 p-md-5 text-center">
                <h1 class="display-5 fw-bold text-white mb-3">Discover Premium Properties</h1>
                <p class="lead text-white-50 mb-4">Explore exclusive residential and commercial opportunities in Uttar Pradesh's prime locations.</p>
                
                <form class="row g-3 justify-content-center">
                    <div class="col-md-5">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-transparent border-end-0 text-white-50"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-transparent border-start-0 text-white" placeholder="Search by location, project name...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-lg bg-transparent text-white">
                            <option value="" class="bg-dark">Property Type</option>
                            <option value="residential" class="bg-dark">Residential</option>
                            <option value="commercial" class="bg-dark">Commercial</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100 px-4">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Listings Grid -->
    <div class="row g-4">
        <?php foreach ($properties ?? [] as $property): ?>
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="glass-card h-100 property-card overflow-hidden">
                <div class="position-relative">
                    <img src="<?php echo $property['image']; ?>" class="w-100" style="height: 240px; object-fit: cover;" alt="<?php echo $property['title']; ?>">
                    <div class="position-absolute top-0 start-0 p-3">
                        <span class="badge bg-primary glass-blur px-3 py-2">
                            <?php echo $property['featured'] ? 'Featured' : 'Premium'; ?>
                        </span>
                    </div>
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark">
                        <h4 class="text-white h5 mb-0">₹<?php echo number_format($property['price']); ?></h4>
                    </div>
                </div>
                
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="text-white mb-0"><?php echo $property['title']; ?></h5>
                        <button class="btn btn-link p-0 text-white-50"><i class="bi bi-heart"></i></button>
                    </div>
                    <p class="text-white-50 small mb-3"><i class="bi bi-geo-alt me-1"></i><?php echo $property['location']; ?></p>
                    
                    <div class="d-flex gap-3 mb-4 text-white-50 small">
                        <span><i class="bi bi-door-open me-1"></i><?php echo $property['bedrooms']; ?> BHK</span>
                        <span><i class="bi bi-rulers me-1"></i><?php echo $property['area']; ?> sq.ft</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-<?php echo $property['status'] == 'ready-to-move' ? 'success' : 'warning'; ?> small fw-bold uppercase">
                            ● <?php echo ucfirst(str_replace('-', ' ', $property['status'])); ?>
                        </span>
                        <a href="/properties/<?php echo $property['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4">View Details</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .property-card:hover {
        transform: translateY(-10px);
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }

    .glass-blur {
        backdrop-filter: blur(8px);
        background: rgba(41, 98, 255, 0.5) !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .bg-gradient-dark {
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
    }

    .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--primary-color);
        box-shadow: none;
        color: #fff;
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }
</style>
