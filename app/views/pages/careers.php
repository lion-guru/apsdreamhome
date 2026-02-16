<?php
// app/views/pages/careers.php
// Data passed from PageController::careers()
// Available variables: $careers, $page_title
?>

<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 80px 0;
    }

    .job-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease;
        margin-bottom: 30px;
        border-left: 5px solid #667eea;
        height: 100%;
    }

    .job-card:hover {
        transform: translateY(-5px);
    }

    .benefit-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        margin-bottom: 30px;
        height: 100%;
    }

    .benefit-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(45deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin: 0 auto 20px;
        font-size: 1.5rem;
    }

    .cta-section {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 60px 0;
        text-align: center;
    }

    .culture-section {
        padding: 80px 0;
        background: #f8f9fa;
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold mb-4">Join Our Team</h1>
                <p class="lead mb-4">
                    Be part of a dynamic team that's shaping the future of real estate in Eastern UP.
                    Grow your career with APS Dream Homes Pvt Ltd.
                </p>
                <a href="#jobs" class="btn btn-light btn-lg">
                    <i class="fas fa-search me-2"></i>View Open Positions
                </a>
            </div>
        </div>
    </div>
</section>


<!-- Why Join Us -->
<section class="culture-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Why Join APS Dream Homes?</h2>
            <p class="lead text-muted">Discover what makes us a great place to work</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h5>Growth Opportunities</h5>
                    <p class="text-muted">Continuous learning and career advancement opportunities in a growing company</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5>Collaborative Culture</h5>
                    <p class="text-muted">Work with talented professionals in a supportive and inclusive environment</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h5>Work-Life Balance</h5>
                    <p class="text-muted">Flexible working hours and policies that support your personal life</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h5>Competitive Compensation</h5>
                    <p class="text-muted">Attractive salary packages with performance-based incentives and benefits</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h5>Employee Wellness</h5>
                    <p class="text-muted">Comprehensive health benefits and wellness programs for our team members</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h5>Meaningful Work</h5>
                    <p class="text-muted">Contribute to building dream homes and communities that matter</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Current Openings -->
<section class="py-5" id="jobs">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Current Openings</h2>
            <p class="lead text-muted">Join our growing team and make a difference</p>
        </div>

        <div class="row">
            <?php if (isset($careers) && count($careers) > 0): ?>
                <?php foreach ($careers as $career): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="job-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($career->title); ?></h5>
                                    <p class="text-primary mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($career->location ?? 'Gorakhpur, UP'); ?>
                                    </p>
                                </div>
                                <span class="badge bg-success"><?php echo htmlspecialchars($career->type ?? 'Full Time'); ?></span>
                            </div>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars(substr($career->description ?? '', 0, 150)) . '...'; ?></p>

                            <!-- Tags/Badges if available -->
                            <div class="mb-3">
                                <?php if (isset($career->department)): ?>
                                    <span class="badge bg-light text-dark me-2"><?php echo htmlspecialchars($career->department); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-muted">
                                    <?php if (isset($career->experience)): ?>
                                        Experience: <?php echo htmlspecialchars($career->experience); ?>
                                    <?php endif; ?>
                                </small>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#applyModal" onclick="setJobTitle('<?php echo addslashes($career->title); ?>')">
                                    Apply Now
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <h4>No current openings</h4>
                        <p>We don't have any specific openings right now, but we're always looking for talent. Feel free to send your resume!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Application Modal -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply for <span id="jobTitle">Position</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="jobApplicationForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position Applied For</label>
                            <input type="text" class="form-control" name="position" id="applicationPosition" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cover Letter</label>
                        <textarea class="form-control" name="cover_letter" rows="4" placeholder="Tell us why you're interested in this position..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resume/CV *</label>
                        <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx" required>
                        <small class="text-muted">Upload PDF, DOC, or DOCX files only (Max 5MB)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitApplication()">Submit Application</button>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="mb-4">Don't See a Position That Fits?</h2>
                <p class="lead mb-4">
                    We're always looking for talented individuals. Send us your resume and we'll keep you in mind for future opportunities.
                </p>
                <a href="/contact" class="btn btn-light btn-lg">
                    <i class="fas fa-envelope me-2"></i>Get In Touch
                </a>
            </div>
        </div>
    </div>
</section>

<?php include '../app/views/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function setJobTitle(title) {
        document.getElementById('jobTitle').textContent = title;
        document.getElementById('applicationPosition').value = title;
    }

    function submitApplication() {
        const form = document.getElementById('jobApplicationForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);

        // Here you would typically submit to your backend
        // TODO: Implement actual form submission endpoint
        alert('Application submitted successfully! We will review your application and get back to you soon.');

        // Close modal and reset form
        const modal = bootstrap.Modal.getInstance(document.getElementById('applyModal'));
        modal.hide();
        form.reset();
    }
</script>
</body>

</html>