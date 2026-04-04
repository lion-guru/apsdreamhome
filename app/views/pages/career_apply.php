<?php
/**
 * APS Dream Home - Career Application Page
 */

$page_title = "Career Application - APS Dream Home";
$description = "Apply for exciting career opportunities at APS Dream Home. Join our team of real estate professionals.";
?>

<!-- Hero Section -->
<section class="careers-hero bg-dark text-white py-5 mb-0 position-relative overflow-hidden">
    <div class="container py-5 mt-4 text-center" data-aos="fade-up">
        <h1 class="display-3 fw-bold mb-3">Career Application</h1>
        <p class="lead opacity-75 mx-auto">Take the first step towards a rewarding career in real estate</p>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>careers">Careers</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Apply</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-dark">Ready to Join Our Team?</h2>
                            <p class="text-muted">Fill out the form below and we'll get back to you soon.</p>
                        </div>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['success']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <form action="<?= BASE_URL ?>careers/apply" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-semibold">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-semibold">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-semibold">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="position" class="form-label fw-semibold">Position Applied For *</label>
                                    <select class="form-select" id="position" name="position" required>
                                        <option value="">Select Position</option>
                                        <option value="Sales Executive">Sales Executive</option>
                                        <option value="Marketing Manager">Marketing Manager</option>
                                        <option value="Business Development">Business Development</option>
                                        <option value="Customer Relations">Customer Relations</option>
                                        <option value="Digital Marketing">Digital Marketing</option>
                                        <option value="Office Administrator">Office Administrator</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="experience" class="form-label fw-semibold">Years of Experience</label>
                                <input type="text" class="form-control" id="experience" name="experience" placeholder="e.g., 2-3 years">
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label fw-semibold">Cover Letter / Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" placeholder="Tell us why you're interested in this position..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="resume" class="form-label fw-semibold">Resume/CV *</label>
                                <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                <div class="form-text">Accepted formats: PDF, DOC, DOCX (Max size: 5MB)</div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card border-0 shadow-sm rounded-4 mt-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Other Ways to Apply</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-envelope text-primary me-3"></i>
                                    <div>
                                        <strong>Email:</strong><br>
                                        <a href="mailto:hr@apsdreamhome.com">hr@apsdreamhome.com</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-phone text-primary me-3"></i>
                                    <div>
                                        <strong>Phone:</strong><br>
                                        <a href="tel:+919876543210">+91 92771 21112</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-map-marker-alt text-primary me-3"></i>
                            <div>
                                <strong>Office:</strong><br>
                                APS Dream Home, Kaushambi, Ghaziabad, Uttar Pradesh
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .careers-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        padding-bottom: 100px !important;
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
</style>
