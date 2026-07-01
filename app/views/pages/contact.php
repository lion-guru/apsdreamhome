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
<section class="position-relative overflow-hidden" style="padding: 60px 0 50px;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#0d9488 100%)">
    <div style="position:absolute;inset:0;background:url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\")"></div>
    <div class="container position-relative" style="z-index:2">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-5 display-lg-4 fw-bold mb-3" style="color:#fff"><?php echo __('get_in_touch'); ?></h1>
                <p class="lead mb-4" style="font-size:1.1rem;color:rgba(255,255,255,0.8)"><?php echo __('contact_subtitle'); ?></p>
                <div class="d-flex flex-wrap gap-3 animate-fade-in-delay-2">
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $sc('contact_phone', '+919277121112')) ?>" class="btn btn-light btn-lg shadow-sm">
                        <i class="fas fa-phone-alt me-2"></i><?php echo __('call_now'); ?>
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')) ?>" class="btn btn-success btn-lg shadow-sm" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i><?php echo __('whatsapp'); ?>
                    </a>
                </div>
                <!-- Quick contact chips for mobile -->
                <div class="d-flex d-lg-none flex-wrap gap-2 mt-3">
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $sc('contact_phone', '+919277121112')) ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="fas fa-phone me-1"></i> Call Now
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')) ?>" class="btn btn-outline-success btn-sm rounded-pill" target="_blank">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </a>
                    <a href="#contactForm" class="btn btn-outline-secondary btn-sm rounded-pill">
                        <i class="fas fa-envelope me-1"></i> Send Message
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-lg border-0 glass-card-premium" style="background: rgba(255, 255, 255, 0.9) !important; color: #333;">
                    <div class="card-header bg-primary text-white text-center py-3" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important; border-bottom: none;">
                        <h4 class="mb-0 fw-bold text-white"><i class="fas fa-envelope me-2 text-white"></i><?php echo __('send_us_message'); ?></h4>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <form action="<?php echo BASE_URL; ?>/contact" method="POST" id="contactForm" data-aps-ajax="true">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            
                            <!-- UTM Tracking inputs -->
                            <input type="hidden" name="utm_source" value="">
                            <input type="hidden" name="utm_medium" value="">
                            <input type="hidden" name="utm_campaign" value="">
                            <input type="hidden" name="utm_term" value="">
                            <input type="hidden" name="utm_content" value="">

                            <div class="aps-form-field mb-3">
                                <label for="name" class="form-label fw-bold mb-1"><?php echo __('your_name'); ?> *</label>
                                <input type="text" name="name" id="name" class="form-control form-control-lg animate-focus" placeholder="<?php echo __('your_name_placeholder'); ?>" required style="border-radius: 10px;">
                                <div class="aps-field-error" role="alert"></div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-3 aps-form-field">
                                    <label for="email" class="form-label fw-bold mb-1"><?php echo __('email'); ?> *</label>
                                    <input type="email" name="email" id="email" class="form-control form-control-lg animate-focus" placeholder="your@email.com" required style="border-radius: 10px;">
                                    <div class="aps-field-error" role="alert"></div>
                                </div>
                                <div class="col-md-6 mb-3 aps-form-field">
                                    <label for="phone" class="form-label fw-bold mb-1"><?php echo __('phone'); ?> *</label>
                                    <input type="tel" name="phone" id="phone" class="form-control form-control-lg animate-focus" placeholder="+91 XXXXXXXXXX" required style="border-radius: 10px;">
                                    <div class="aps-field-error" role="alert"></div>
                                </div>
                            </div>
                            <div class="aps-form-field mb-3">
                                <label for="subject" class="form-label fw-bold mb-1"><?php echo __('subject'); ?> *</label>
                                <select name="subject" id="subject" class="form-select form-select-lg animate-focus" required style="border-radius: 10px;">
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
                                <label for="message" class="form-label fw-bold mb-1"><?php echo __('message_label'); ?> *</label>
                                <textarea name="message" id="message" class="form-control form-control-lg animate-focus" rows="4" placeholder="<?php echo __('message_placeholder_contact'); ?>" required style="border-radius: 10px;"></textarea>
                                <div class="aps-field-error" role="alert"></div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-hover" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border: none; border-radius: 10px; padding: 14px;">
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

<!-- Floating WhatsApp Button for Mobile -->
<style>
.contact-bottom{padding:80px 0;background:#f8fafc}
.contact-bottom .section-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(13,148,136,0.08);color:#0d9488;font-size:0.75rem;font-weight:700;padding:6px 16px;border-radius:50px;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:16px}
.contact-bottom .section-title{font-size:1.8rem;font-weight:800;color:#1e293b;letter-spacing:-0.5px}

.faq-glass{background:rgba(255,255,255,0.92);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.6);border-radius:20px;box-shadow:0 8px 32px rgba(0,0,0,0.06);overflow:hidden}
.faq-glass .accordion-item{border:none;border-bottom:1px solid #f1f5f9}
.faq-glass .accordion-item:last-child{border-bottom:none}
.faq-glass .accordion-button{font-weight:700;color:#1e293b;font-size:0.92rem;padding:18px 24px;transition:all 0.2s}
.faq-glass .accordion-button:not(.collapsed){background:rgba(13,148,136,0.04);color:#0d9488}
.faq-glass .accordion-button::after{width:20px;height:20px;background-size:14px;transition:all 0.3s}
.faq-glass .accordion-button:not(.collapsed)::after{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%230d9488'%3E%3Cpath d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E")}
.faq-glass .accordion-body{color:#475569;font-size:0.88rem;line-height:1.7;padding:4px 24px 20px}

.office-card{background:#fff;border-radius:20px;overflow:hidden;border:none;box-shadow:0 8px 32px rgba(0,0,0,0.06);position:relative}
.office-card .office-header{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#0d9488 100%);padding:24px;color:#fff;position:relative;overflow:hidden}
.office-card .office-header::before{content:'';position:absolute;top:-50%;right:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,0.08) 0%,transparent 60%)}
.office-card .office-header h3{font-size:1.1rem;font-weight:700;margin:0;position:relative;z-index:1}
.office-card .office-body{padding:24px}
.office-card .office-body h4{font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:10px}
.office-card .office-body address{color:#475569;font-size:0.85rem;line-height:1.8}
.office-card .office-body address a{color:#0d9488;text-decoration:none;font-weight:600;transition:color 0.2s}
.office-card .office-body address a:hover{color:#0f766e}
.office-card .office-map{border-radius:12px;overflow:hidden;margin-top:16px;border:2px solid #f1f5f9}
.office-card .office-map iframe{width:100%;height:200px;border:0;display:block}

.contact-float-wa{position:fixed;bottom:80px;right:20px;z-index:1050;width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#25d366,#128c7e);color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(37,211,102,0.4);transition:all 0.3s;text-decoration:none}
.contact-float-wa:hover{transform:scale(1.1);box-shadow:0 12px 32px rgba(37,211,102,0.5);color:#fff}
</style>

<section class="contact-bottom">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="section-badge"><i class="fas fa-question-circle"></i> FAQ</div>
                <h2 class="section-title mb-4"><?php echo __('faq_title'); ?></h2>
                <div class="faq-glass">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1">
                                    <i class="fas fa-building me-2" style="color:#0d9488"></i><?= __('contact_faq1_q', null, 'What types of properties do you offer?') ?>
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
                                    <i class="fas fa-calendar-check me-2" style="color:#0d9488"></i><?= __('contact_faq2_q', null, 'How can I schedule a property visit?') ?>
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
                                    <i class="fas fa-university me-2" style="color:#0d9488"></i><?= __('contact_faq3_q', null, 'Do you provide home loan assistance?') ?>
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
                                    <i class="fas fa-shield-alt me-2" style="color:#0d9488"></i><?= __('contact_faq4_q', null, 'Are your properties legally verified?') ?>
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
            </div>
            <div class="col-lg-5">
                <div class="section-badge"><i class="fas fa-map-marker-alt"></i> Visit Us</div>
                <h2 class="section-title mb-4"><?php echo __('office_locations'); ?></h2>
                <div class="office-card scroll-reveal">
                    <div class="office-header">
                        <h3><i class="fas fa-building me-2"></i><?= __('contact_office_hq', null, 'Head Office - Gorakhpur') ?></h3>
                    </div>
                    <div class="office-body">
                        <address class="mb-0">
                            <strong><i class="fas fa-map-pin me-1" style="color:#0d9488"></i> <?= __('contact_office_addr1', null, '1st floor, Singhariya Chauraha, Kunraghat, Deoria Road') ?></strong><br>
                            <?= __('contact_office_addr2', null, 'Gorakhpur, UP - 273008') ?><br><br>
                            <i class="fas fa-phone me-1" style="color:#0d9488"></i> <?= __('phone_lbl') ?>: <a href="tel:<?= preg_replace('/[^0-9+]/', '', $sc('contact_phone', '+919277121112')) ?>"><?= htmlspecialchars($sc('contact_phone', '+91 92771 21112')) ?></a><?php if ($sc('contact_phone_2')): ?> / <a href="tel:<?= preg_replace('/[^0-9+]/', '', $sc('contact_phone_2')) ?>"><?= htmlspecialchars($sc('contact_phone_2')) ?></a><?php endif; ?><br>
                            <i class="fas fa-envelope me-1" style="color:#0d9488"></i> <?= __('email_lbl') ?>: <a href="mailto:<?= htmlspecialchars($sc('contact_email', 'info@apsdreamhome.com')) ?>"><?= htmlspecialchars($sc('contact_email', 'info@apsdreamhome.com')) ?></a>
                        </address>
                        <div class="office-map">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.991144111075!2d83.30122467380973!3d26.840233976690463!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399149002e8a386b%3A0x907b565a09c02435!2sSuryoday%20Colony%20developed%20by%20APS%20Dream%20Homes!5e0!3m2!1sen!2sin!4v1775289074035!5m2!1sen!2sin"
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

<a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')) ?>" 
   class="d-lg-none contact-float-wa"
   target="_blank" aria-label="Chat on WhatsApp">
    <i class="fab fa-whatsapp fa-2x"></i>
</a>
