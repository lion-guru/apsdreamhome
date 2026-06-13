<?php
// Contact Page - APS Dream Home - Enhanced UI/UX
$contactSuccess = $contact_success ?? false;
$contactError = $contact_error ?? '';
if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }
?>

<?php if ($contactSuccess): ?>
    <div class="container mt-4">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo __('contact_success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>
<?php if ($contactError): ?>
    <div class="container mt-4">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($contactError); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Hero Section -->
<section class="page-hero position-relative overflow-hidden" style="padding: 80px 0;">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4 animate-fade-in"><?php echo __('get_in_touch'); ?></h1>
                <p class="lead mb-4 animate-fade-in-delay"><?php echo __('contact_subtitle'); ?></p>
                <div class="d-flex flex-wrap gap-3 animate-fade-in-delay-2">
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $sc('contact_phone', '+919277121112')) ?>" class="btn btn-light btn-lg">
                        <i class="fas fa-phone-alt me-2"></i><?php echo __('call_now'); ?>
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')) ?>" class="btn btn-success btn-lg" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i><?php echo __('whatsapp'); ?>
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-lg border-0 glass-card-premium" style="background: rgba(255, 255, 255, 0.9) !important; color: #333;">
                    <div class="card-header bg-primary text-white text-center py-3" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important; border-bottom: none;">
                        <h4 class="mb-0 fw-bold text-white"><i class="fas fa-envelope me-2 text-white"></i><?php echo __('send_us_message'); ?></h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?php echo BASE_URL; ?>/contact" method="POST" id="contactForm" data-aps-ajax="true">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            
                            <!-- UTM Tracking inputs -->
                            <input type="hidden" name="utm_source" value="">
                            <input type="hidden" name="utm_medium" value="">
                            <input type="hidden" name="utm_campaign" value="">
                            <input type="hidden" name="utm_term" value="">
                            <input type="hidden" name="utm_content" value="">

                            <div class="aps-form-field mb-3">
                                <label for="name" class="form-label fw-bold"><?php echo __('your_name'); ?> *</label>
                                <input type="text" name="name" id="name" class="form-control animate-focus" placeholder="<?php echo __('your_name_placeholder'); ?>" required>
                                <div class="aps-field-error" role="alert"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3 aps-form-field">
                                    <label for="email" class="form-label fw-bold"><?php echo __('email'); ?> *</label>
                                    <input type="email" name="email" id="email" class="form-control animate-focus" placeholder="your@email.com" required>
                                    <div class="aps-field-error" role="alert"></div>
                                </div>
                                <div class="col-md-6 mb-3 aps-form-field">
                                    <label for="phone" class="form-label fw-bold"><?php echo __('phone'); ?> *</label>
                                    <input type="tel" name="phone" id="phone" class="form-control animate-focus" placeholder="+91 XXXXXXXXXX" required>
                                    <div class="aps-field-error" role="alert"></div>
                                </div>
                            </div>
                            <div class="aps-form-field mb-3">
                                <label for="subject" class="form-label fw-bold"><?php echo __('subject'); ?> *</label>
                                <select name="subject" id="subject" class="form-select animate-focus" required>
                                    <option value=""><?php echo __('select_subject'); ?></option>
                                    <option value="buy"><?php echo __('subject_buy'); ?></option>
                                    <option value="sell"><?php echo __('subject_sell'); ?></option>
                                    <option value="rent"><?php echo __('subject_rent'); ?></option>
                                    <option value="loan"><?php echo __('subject_loan'); ?></option>
                                    <option value="legal"><?php echo __('subject_legal'); ?></option>
                                    <option value="interior"><?php echo __('subject_interior'); ?></option>
                                    <option value="general"><?php echo __('subject_general'); ?></option>
                                </select>
                                <div class="aps-field-error" role="alert"></div>
                            </div>
                            <div class="aps-form-field mb-3">
                                <label for="message" class="form-label fw-bold"><?php echo __('message_label'); ?> *</label>
                                <textarea name="message" id="message" class="form-control animate-focus" rows="4" placeholder="<?php echo __('message_placeholder_contact'); ?>" required></textarea>
                                <div class="aps-field-error" role="alert"></div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-hover" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                                <i class="fas fa-paper-plane me-2"></i><?php echo __('send_message'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    // Populate UTM parameters
    const params = ['utm_source','utm_medium','utm_campaign','utm_term','utm_content'];
    params.forEach(p => {
        const val = new URLSearchParams(window.location.search).get(p);
        if (val) {
            document.querySelectorAll(`input[name="${p}"]`).forEach(el => el.value = val);
            try { sessionStorage.setItem(p, val); } catch(e) {}
        }
    });

    // AJAX Form submission
    const form = document.getElementById('contactForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Check custom form validation if available
            if (window.APS && typeof window.APS.validateForm === 'function') {
                if (!window.APS.validateForm(form)) {
                    e.preventDefault();
                    return;
                }
            }
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            const formData = new FormData(form);

            if (window.APS && typeof window.APS.fetch === 'function') {
                window.APS.fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData,
                    context: submitBtn
                })
                .then(function(data) {
                    if (data && data.success) {
                        window.APS.toast(data.message || 'Thank you! Your message has been sent successfully.', 'success');
                        form.reset();
                        // Reset validation classes
                        form.querySelectorAll('.aps-form-field').forEach(function(el) {
                            el.classList.remove('aps-has-success', 'aps-has-error');
                        });
                    } else {
                        window.APS.toast(data.message || 'Validation failed. Please check your inputs.', 'error');
                    }
                })
                .catch(function(err) {
                    console.error('Contact AJAX submission failed:', err);
                });
            } else {
                // Fallback direct AJAX submit
                submitBtn.disabled = true;
                const originalBtnHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
                
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                    if (data.success) {
                        alert(data.message);
                        form.reset();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                    console.error('Contact submit error:', err);
                    alert('An error occurred. Please try again.');
                });
            }
        });
    }
})();
</script>

<?php if (!empty($pageContent)): ?>
<section class="py-5 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="cms-content p-4"><?php echo $pageContent; ?></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4"><?php echo __('faq_title'); ?></h2>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1">
                                <?= __('contact_faq1_q', null, 'What types of properties do you offer?') ?>
                            </button>
                        </h2>
                        <div id="faqCollapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?= __('contact_faq1_a', null, 'We offer residential apartments, villas, commercial spaces, and plots in Gorakhpur, Lucknow, and across Uttar Pradesh.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2">
                                <?= __('contact_faq2_q', null, 'How can I schedule a property visit?') ?>
                            </button>
                        </h2>
                        <div id="faqCollapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php
                                    $contactPhone = htmlspecialchars($sc('contact_phone', '+91 92771 21112'));
                                    if ($sc('contact_phone_2')) {
                                        $contactPhone .= ' / ' . htmlspecialchars($sc('contact_phone_2'));
                                    }
                                ?>
                                <?= sprintf(__('contact_faq2_a_dynamic', null, 'You can call us at %s or fill out the contact form. Our team will get back to you to arrange a convenient time.'), $contactPhone) ?>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3">
                                <?= __('contact_faq3_q', null, 'Do you provide home loan assistance?') ?>
                            </button>
                        </h2>
                        <div id="faqCollapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?= __('contact_faq3_a', null, 'Yes, we have partnerships with leading banks and financial institutions to help you with home loan assistance and documentation.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4">
                                <?= __('contact_faq4_q', null, 'Are your properties legally verified?') ?>
                            </button>
                        </h2>
                        <div id="faqCollapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?= __('contact_faq4_a', null, 'Absolutely! All our properties undergo thorough legal verification to ensure they are free from disputes and have clear titles.') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card aps-cp-card">
                    <div class="card-body aps-cp-card-body">
                        <h3 class="card-title"><?php echo __('office_locations'); ?></h3>
                        <div class="office-location">
                            <h4><?= __('contact_office_hq', null, 'Head Office - Gorakhpur') ?></h4>
                            <address>
                                <?= __('contact_office_addr1', null, '1st floor, Singhariya Chauraha, Kunraghat, Deoria Road') ?><br>
                                <?= __('contact_office_addr2', null, 'Gorakhpur, UP - 273008') ?><br>
                                <?= __('phone_lbl') ?>: <?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?><?php if ($sc('contact_phone_2')): ?> / <?= htmlspecialchars($sc('contact_phone_2')) ?><?php endif; ?><br>
                                <?= __('email_lbl') ?>: <?= htmlspecialchars($sc('contact_email', 'info@apsdreamhome.com')) ?>
                            </address>
                        </div>
                        <div class="map-container mt-3">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.991144111075!2d83.30122467380973!3d26.840233976690463!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399149002e8a386b%3A0x907b565a09c02435!2sSuryoday%20Colony%20developed%20by%20APS%20Dream%20Homes!5e0!3m2!1sen!2sin!4v1775289074035!5m2!1sen!2sin"
                                width="100%"
                                height="250"
                                style="border:0; border-radius: 8px;"
                                allowfullscreen
                                loading="lazy">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
