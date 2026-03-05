<?php
$layout = 'layouts/base';
$page_title = $page_title ?? 'Associate Registration - APS Dream Home';
$page_description = $page_description ?? 'Register as a Property Associate';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold">Associate Registration</h2>
                            <p class="text-muted">Join our team as a Property Associate</p>
                        </div>

                        <form action="<?php echo BASE_URL; ?>/associate/register" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="experience" class="form-label">Years of Experience</label>
                                    <select class="form-control" id="experience" name="experience">
                                        <option value="">Select Experience</option>
                                        <option value="0-1">0-1 Year</option>
                                        <option value="1-3">1-3 Years</option>
                                        <option value="3-5">3-5 Years</option>
                                        <option value="5+">5+ Years</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="commission_rate" class="form-label">Expected Commission Rate (%)</label>
                                    <input type="number" class="form-control" id="commission_rate" name="commission_rate" min="1" max="10" step="0.5" placeholder="2.5">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="about" class="form-label">About Yourself</label>
                                <textarea class="form-control" id="about" name="about" rows="3" placeholder="Tell us about your experience in real estate..."></textarea>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">I agree to the terms and conditions *</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">Register as Associate</button>
                            
                            <div class="text-center">
                                <a href="<?php echo BASE_URL; ?>/login" class="text-decoration-none">Already have an account? Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>