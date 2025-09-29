<!-- Testimonial Submission Form -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h3 class="h4 mb-2">Share Your Experience</h3>
                        <p class="text-muted">We'd love to hear about your experience with APS Dream Home</p>
                    </div>
                    
                    <form id="testimonialForm" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Please enter your name</div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Your Rating <span class="text-danger">*</span></label>
                                <div class="rating-input mb-2">
                                    <input type="radio" id="star5" name="rating" value="5" class="d-none" required>
                                    <label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star4" name="rating" value="4" class="d-none">
                                    <label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star3" name="rating" value="3" class="d-none">
                                    <label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star2" name="rating" value="2" class="d-none">
                                    <label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star1" name="rating" value="1" class="d-none">
                                    <label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                                </div>
                                <div class="invalid-feedback">Please select a rating</div>
                            </div>
                            <div class="col-12">
                                <label for="testimonial" class="form-label">Your Testimonial <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="testimonial" name="testimonial" rows="4" required></textarea>
                                <div class="invalid-feedback">Please share your experience with us</div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="consent" name="consent" required>
                                    <label class="form-check-label small text-muted" for="consent">
                                        I agree to have my testimonial and name displayed on the website
                                    </label>
                                    <div class="invalid-feedback">You must agree to share your testimonial</div>
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary px-4">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    Submit Testimonial
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Success Message (initially hidden) -->
                    <div id="testimonialSuccess" class="text-center d-none">
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            Thank you for your testimonial! It's under review and will be published soon.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Testimonial Submission Form -->
