<!-- Hero Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-3">Photo Gallery</h1>
                <p class="lead mb-0">Explore our project galleries and property photos</p>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="py-5">
    <div class="container">
        <!-- Filter Buttons -->
        <div class="text-center mb-5">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-primary active" data-filter="all">All</button>
                <button type="button" class="btn btn-outline-primary" data-filter="residential">Residential</button>
                <button type="button" class="btn btn-outline-primary" data-filter="commercial">Commercial</button>
                <button type="button" class="btn btn-outline-primary" data-filter="projects">Projects</button>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="row g-4">
            <div class="col-md-4 gallery-item" data-category="residential">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="position-relative" style="height: 250px; background: linear-gradient(135deg, #667eea, #764ba2);">
                        <div class="d-flex align-items-center justify-content-center h-100 text-white">
                            <div class="text-center">
                                <i class="fas fa-home fa-3x mb-2"></i>
                                <p class="mb-0">Suyoday Colony</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">Suyoday Colony - Premium Plots</h6>
                        <p class="text-muted small mb-0">Gorakhpur, Uttar Pradesh</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 gallery-item" data-category="residential">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="position-relative" style="height: 250px; background: linear-gradient(135deg, #11998e, #38ef7d);">
                        <div class="d-flex align-items-center justify-content-center h-100 text-white">
                            <div class="text-center">
                                <i class="fas fa-building fa-3x mb-2"></i>
                                <p class="mb-0">Raghunat Nagri</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">Raghunat Nagri - Integrated Township</h6>
                        <p class="text-muted small mb-0">Gorakhpur, Uttar Pradesh</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 gallery-item" data-category="residential">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="position-relative" style="height: 250px; background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <div class="d-flex align-items-center justify-content-center h-100 text-white">
                            <div class="text-center">
                                <i class="fas fa-landmark fa-3x mb-2"></i>
                                <p class="mb-0">Braj Radha Nagri</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">Braj Radha Nagri - Affordable Plots</h6>
                        <p class="text-muted small mb-0">Gorakhpur, Uttar Pradesh</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 gallery-item" data-category="projects">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="position-relative" style="height: 250px; background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <div class="d-flex align-items-center justify-content-center h-100 text-white">
                            <div class="text-center">
                                <i class="fas fa-city fa-3x mb-2"></i>
                                <p class="mb-0">Budh Bihar Colony</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">Budh Bihar Colony - Township</h6>
                        <p class="text-muted small mb-0">Kushinagar, Uttar Pradesh</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 gallery-item" data-category="residential">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="position-relative" style="height: 250px; background: linear-gradient(135deg, #fa709a, #fee140);">
                        <div class="d-flex align-items-center justify-content-center h-100 text-white">
                            <div class="text-center">
                                <i class="fas fa-hotel fa-3x mb-2"></i>
                                <p class="mb-0">Awadhpuri</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">Awadhpuri - Premium Project</h6>
                        <p class="text-muted small mb-0">Lucknow, Uttar Pradesh</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 gallery-item" data-category="commercial">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="position-relative" style="height: 250px; background: linear-gradient(135deg, #a18cd1, #fbc2eb);">
                        <div class="d-flex align-items-center justify-content-center h-100 text-white">
                            <div class="text-center">
                                <i class="fas fa-store fa-3x mb-2"></i>
                                <p class="mb-0">Commercial Complex</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">Commercial Complex - Prime Location</h6>
                        <p class="text-muted small mb-0">Gorakhpur, Uttar Pradesh</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h3 class="mb-4">Want to See More?</h3>
        <p class="text-muted mb-4">Contact us for site visits and detailed project galleries</p>
        <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-primary btn-lg">
            <i class="fas fa-phone me-2"></i>Contact Us
        </a>
    </div>
</section>

<script>
// Gallery Filter
document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active', 'btn-primary'));
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.add('btn-outline-primary'));
        this.classList.add('active', 'btn-primary');
        this.classList.remove('btn-outline-primary');
        
        const filter = this.dataset.filter;
        document.querySelectorAll('.gallery-item').forEach(item => {
            if (filter === 'all' || item.dataset.category === filter) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>
