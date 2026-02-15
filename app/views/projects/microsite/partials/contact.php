<?php
$defaultSubject = $cta['enquiry']['default_subject'] ?? 'Project enquiry';
$projectCode = $cta['enquiry']['project_code'] ?? '';
$enquiryEndpoint = $cta['enquiry']['endpoint'] ?? '/api/enquiry';
$phone = $cta['phone'] ?? '';
$email = $cta['email'] ?? '';
$whatsapp = $cta['whatsapp'] ?? '';
?>
<section class="microsite-section py-5" id="microsite-enquiry">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h2 class="section-title">Plan Your Visit</h2>
                <p class="section-subtitle">Share your details and our sales team will reach out with availability, pricing, and site tour slots.</p>
                <ul class="list-unstyled microsite-contact-list">
                    <?php if (!empty($phone)): ?>
                    <li><i class="fa-solid fa-phone me-2"></i> <a href="tel:<?php echo h($phone); ?>"><?php echo h($phone); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($email)): ?>
                    <li><i class="fa-solid fa-envelope me-2"></i> <a href="mailto:<?php echo h($email); ?>"><?php echo h($email); ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($whatsapp)): ?>
                    <li><i class="fa-brands fa-whatsapp me-2 text-success"></i> <a href="<?php echo h($whatsapp); ?>" target="_blank" rel="noopener">Chat on WhatsApp</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="microsite-enquiry-form" data-endpoint="<?php echo h($enquiryEndpoint); ?>">
                            <input type="hidden" name="project_code" value="<?php echo h($projectCode); ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control" rows="4" placeholder="<?php echo h($defaultSubject); ?>" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <span class="submit-text">Submit Enquiry</span>
                                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="alert alert-success d-none mt-3" id="microsite-enquiry-success">
                            Thank you! Our team will contact you shortly.
                        </div>
                        <div class="alert alert-danger d-none mt-3" id="microsite-enquiry-error">
                            Something went wrong. Please try again later.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
