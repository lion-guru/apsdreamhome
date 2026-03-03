<!-- Booking & Site Visit Page -->
<section class="booking-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">Book Your Dream Property</h1>
        <p class="lead mb-0">Schedule a site visit or start your booking process today</p>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (isset($crumb['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= $crumb['title'] ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<section class="section-padding py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden" data-aos="fade-up">
                    <div class="card-body p-4 p-md-5">
                        <div id="booking-message"></div>
                        
                        <form id="bookingForm" action="<?= BASE_URL ?>property/booking" method="POST" class="needs-validation" novalidate>
                            <?= csrf_field() ?>
                            
                            <?php if (isset($property)): ?>
                                <div class="selected-property-info mb-4 p-3 bg-light rounded-3 d-flex align-items-center">
                                    <div class="property-img me-3">
                                        <img src="<?= !empty($property['main_image']) ? BASE_URL . 'public/uploads/property/' . $property['main_image'] : get_asset_url('default-property.jpg', 'images') ?>" alt="<?= h($property['title']) ?>" class="rounded-3" style="width: 80px; height: 60px; object-fit: cover;">
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?= h($property['title']) ?></h6>
                                        <p class="mb-0 text-muted small"><i class="fas fa-map-marker-alt me-1"></i> <?= h($property['location']) ?></p>
                                        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                                    </div>
                                </div>
                            <?php elseif (isset($project)): ?>
                                <div class="selected-property-info mb-4 p-3 bg-light rounded-3 d-flex align-items-center">
                                    <div class="property-img me-3">
                                        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab" alt="<?= h($project['name']) ?>" class="rounded-3" style="width: 80px; height: 60px; object-fit: cover;">
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?= h($project['name']) ?></h6>
                                        <p class="mb-0 text-muted small"><i class="fas fa-map-marker-alt me-1"></i> <?= h($project['location']) ?></p>
                                        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-4">
                                    <label for="property_id" class="form-label fw-bold text-secondary">Select Property <span class="text-danger">*</span></label>
                                    <select name="property_id" id="property_id" class="form-select form-select-lg rounded-3" required>
                                        <option value="">-- Choose a Property --</option>
                                        <?php if (isset($properties)): foreach($properties as $prop): ?>
                                            <option value="<?= $prop['id'] ?>"><?= h($prop['title']) ?> - <?= h($prop['location']) ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a property.</div>
                                </div>
                            <?php endif; ?>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label fw-bold text-secondary">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" id="full_name" class="form-control form-control-lg rounded-3" placeholder="Enter your full name" required>
                                    <div class="invalid-feedback">Please enter your name.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="mobile" class="form-label fw-bold text-secondary">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="mobile" id="mobile" class="form-control form-control-lg rounded-3" placeholder="10-digit mobile number" pattern="[0-9]{10}" required>
                                    <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                                </div>
                                <div class="col-md-12">
                                    <label for="email" class="form-label fw-bold text-secondary">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control form-control-lg rounded-3" placeholder="yourname@example.com" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <label class="form-label fw-bold text-secondary">What would you like to do? <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-3 mt-2">
                                        <div class="form-check custom-radio-card">
                                            <input class="form-check-input d-none" type="radio" name="booking_type" id="type_visit" value="site_visit" checked>
                                            <label class="form-check-label py-3 px-4 border rounded-3 text-center cursor-pointer d-block" for="type_visit">
                                                <i class="fas fa-calendar-check d-block mb-2 fs-4"></i>
                                                Schedule Site Visit
                                            </label>
                                        </div>
                                        <div class="form-check custom-radio-card">
                                            <input class="form-check-input d-none" type="radio" name="booking_type" id="type_book" value="direct_booking">
                                            <label class="form-check-label py-3 px-4 border rounded-3 text-center cursor-pointer d-block" for="type_book">
                                                <i class="fas fa-key d-block mb-2 fs-4"></i>
                                                Book This Property
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-4">
                                    <label for="visit_date" class="form-label fw-bold text-secondary">Preferred Date</label>
                                    <input type="date" name="visit_date" id="visit_date" class="form-control form-control-lg rounded-3" min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-6 mt-4">
                                    <label for="visit_time" class="form-label fw-bold text-secondary">Preferred Time</label>
                                    <select name="visit_time" id="visit_time" class="form-select form-select-lg rounded-3">
                                        <option value="">Select time</option>
                                        <option value="morning">Morning (9 AM - 12 PM)</option>
                                        <option value="afternoon">Afternoon (12 PM - 3 PM)</option>
                                        <option value="evening">Evening (3 PM - 6 PM)</option>
                                    </select>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <label for="special_requirements" class="form-label fw-bold text-secondary">Any Special Requirements or Questions?</label>
                                    <textarea name="special_requirements" id="special_requirements" class="form-control rounded-3" rows="3" placeholder="Tell us more about your requirements..."></textarea>
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="financing_needed" id="financing_needed">
                                        <label class="form-check-label text-muted" for="financing_needed">
                                            I am interested in home loan / financing assistance
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12 mt-5">
                                    <button type="submit" id="submitBookingBtn" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm">
                                        Confirm Request <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.custom-radio-card .form-check-label {
    min-width: 160px;
    transition: all 0.3s ease;
    border-color: #dee2e6;
}
.custom-radio-card .form-check-input:checked + .form-check-label {
    background-color: var(--bs-primary-bg-subtle);
    border-color: var(--bs-primary);
    color: var(--bs-primary);
}
.custom-radio-card .form-check-label:hover {
    border-color: var(--bs-primary);
    background-color: #f8f9fa;
}
.cursor-pointer { cursor: pointer; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('bookingForm');
    const submitBtn = document.getElementById('submitBookingBtn');
    const messageDiv = document.getElementById('booking-message');

    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                    bookingForm.reset();
                    bookingForm.classList.remove('was-validated');
                    window.scrollTo({ top: messageDiv.offsetTop - 100, behavior: 'smooth' });
                } else {
                    let errorMsg = data.message || 'Something went wrong. Please try again.';
                    if (data.errors) {
                        errorMsg = data.errors.join('<br>');
                    }
                    messageDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> ${errorMsg}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> An error occurred. Please try again later.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Confirm Request <i class="fas fa-arrow-right ms-2"></i>';
            });
        });
    }
});
</script>