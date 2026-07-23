<?php
/**
 * Financial Services Page - APS Dream Homes
 * Professional financial services for real estate
 */

try {
    $services = $services ?? [];
    $advisors = $advisors ?? [];
    $faqs = $faqs ?? [];
} catch (\Exception $e) {
    error_log('Financial services page database error: ' . $e->getMessage());
    $services = [];
    $advisors = [];
    $faqs = [];
}
?>

<!-- Hero Section -->
<section class="hero-premium pt-5 pb-5">
    <div class="container premium-reveal fade-up position-relative z-2">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <span class="capsule-badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 mb-3 px-3 py-2"><i class="fas fa-coins me-1"></i> Financial Services</span>
                <h1 class="display-4 fw-bold text-white mb-4"><?= __('fs_hero_title') ?></h1>
                <p class="lead text-white-50 mb-4">
                    <?= __('fs_hero_desc') ?>
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="#contact-form" class="btn btn-premium px-4 py-2">
                        <i class="fas fa-coins me-2"></i><?= __('fs_get_advice') ?>
                    </a>
                    <a href="#services" class="btn btn-outline-light btn-lg px-4 py-2">
                        <i class="fas fa-list me-2"></i><?= __('fs_our_services') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/"><?= __('fs_breadcrumb_home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services"><?= __('fs_breadcrumb_services') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= __('fs_breadcrumb_active') ?></li>
        </ol>
    </div>
</nav>

<!-- Services Section -->
<section id="services" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" data-aos="fade-up"><?= __('fs_section_title') ?></h2>
                <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                    <?= __('fs_section_desc') ?>
                </p>
            </div>
        </div>

        <?php if (!empty($services)): ?>
            <div class="row g-4">
                <?php foreach ($services as $index => $service): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                        <div class="service-card h-100 bg-white rounded-4 shadow-sm hover-lift p-4 border border-light">
                            <div class="service-icon mb-4">
                                <div class="icon-wrap bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 60px; height: 60px; font-size: 24px;">
                                    <i class="<?php echo htmlspecialchars($service['icon'] ?? 'fas fa-coins'); ?>"></i>
                                </div>
                            </div>
                            <h3 class="h4 fw-bold mb-3"><?php echo htmlspecialchars($service['title'] ?? 'Financial Service'); ?></h3>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($service['description'] ?? 'Professional financial assistance for your real estate needs.'); ?></p>

                            <?php if (!empty($service['features'])): ?>
                                <?php $features = json_decode($service['features'], true); ?>
                                <?php if (is_array($features) && !empty($features)): ?>
                                    <ul class="service-features">
                                        <?php foreach ($features as $feature): ?>
                                            <li>
                                                <i class="fas fa-check text-success"></i>
                                                <?php echo htmlspecialchars($feature); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <?php if (!empty($service['interest_rate'])): ?>
                                    <span class="capsule-badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 me-2 mb-2">
                                        <i class="fas fa-percentage me-1"></i>From <?= number_format($service['interest_rate'], 2) ?>% p.a.
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($service['max_amount']) && $service['max_amount'] > 0): ?>
                                    <span class="capsule-badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 me-2 mb-2">
                                        <i class="fas fa-rupee-sign me-1"></i>Up to ₹<?= number_format($service['max_amount'] / 10000000, 1) ?> Cr
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($service['tenure_months']) && $service['tenure_months'] > 0): ?>
                                    <span class="badge bg-info text-dark">
                                        <i class="fas fa-calendar me-1"></i>Up to <?= $service['tenure_months'] ?> months
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <a href="#contact-form" class="btn btn-outline-primary w-100 mt-3">
                                <?= __('fs_enquire_now') ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-coins fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted"><?= __('fs_coming_soon') ?></h3>
                    <p class="text-muted mb-4">
                        <?= __('fs_coming_soon_desc') ?>
                    </p>
                    <a href="<?= BASE_URL ?>/contact" class="btn btn-primary"><?= __('fs_contact_us') ?></a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Advisors Section -->
<?php if (!empty($advisors)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" data-aos="fade-up"><?= __('fs_advisors_title') ?></h2>
                <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                    <?= __('fs_advisors_desc') ?>
                </p>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($advisors as $index => $advisor): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div class="advisor-card h-100">
                        <?php if (!empty($advisor['photo'])): ?>
                            <img src="<?= htmlspecialchars($advisor['photo']) ?>" alt="<?= htmlspecialchars($advisor['name']) ?>" class="advisor-photo">
                        <?php else: ?>
                            <div class="advisor-photo-placeholder">
                                <i class="fas fa-user-tie fa-3x text-primary"></i>
                            </div>
                        <?php endif; ?>
                        <div class="advisor-info">
                            <h4 class="fw-bold"><?= htmlspecialchars($advisor['name']) ?></h4>
                            <p class="text-primary mb-1"><?= htmlspecialchars($advisor['title'] ?? '') ?></p>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($advisor['experience'] ?? '') ?>
                            </p>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($advisor['specialization'] ?? '') ?></p>
                            <div class="advisor-contact">
                                <?php if (!empty($advisor['phone'])): ?>
                                    <a href="tel:<?= htmlspecialchars($advisor['phone']) ?>" class="text-decoration-none text-muted">
                                        <i class="fas fa-phone me-1"></i> Call
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($advisor['email'])): ?>
                                    <a href="mailto:<?= htmlspecialchars($advisor['email']) ?>" class="text-decoration-none text-muted ms-3">
                                        <i class="fas fa-envelope me-1"></i> Email
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FAQs Section -->
<?php if (!empty($faqs)): ?>
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" data-aos="fade-up"><?= __('fs_faq_title') ?></h2>
                <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                    <?= __('fs_faq_desc') ?>
                </p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="financialFaqs">
                    <?php 
                    $categories = [];
                    foreach ($faqs as $faq) {
                        $cat = $faq['category'] ?? 'General';
                        $categories[$cat][] = $faq;
                    }
                    $catIndex = 0;
                    foreach ($categories as $category => $catFaqs): 
                        $catIndex++;
                    ?>
                        <div class="accordion-item border-0 shadow-sm mb-3" data-aos="fade-up">
                            <h2 class="accordion-header" id="heading<?= $catIndex ?>">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $catIndex ?>" aria-expanded="false" aria-controls="collapse<?= $catIndex ?>">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>
                                    <?= htmlspecialchars($category) ?>
                                    <span class="badge bg-primary ms-2"><?= count($catFaqs) ?></span>
                                </button>
                            </h2>
                            <div id="collapse<?= $catIndex ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $catIndex ?>" data-bs-parent="#financialFaqs">
                                <div class="accordion-body">
                                    <?php foreach ($catFaqs as $faqIndex => $faq): ?>
                                        <div class="accordion-item border-0 shadow-sm mb-2">
                                            <h2 class="accordion-header" id="faqHeading<?= $catIndex . $faqIndex ?>">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?= $catIndex . $faqIndex ?>" aria-expanded="false" aria-controls="faqCollapse<?= $catIndex . $faqIndex ?>">
                                                    <?= htmlspecialchars($faq['question']) ?>
                                                </button>
                                            </h2>
                                            <div id="faqCollapse<?= $catIndex . $faqIndex ?>" class="accordion-collapse collapse" aria-labelledby="faqHeading<?= $catIndex . $faqIndex ?>" data-bs-parent="#financialFaqs">
                                                <div class="accordion-body">
                                                    <?= htmlspecialchars($faq['answer']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Contact Form Section -->
<section id="contact-form" class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="display-5 fw-bold mb-3"><?= __('fs_contact_title') ?></h2>
                    <p class="lead"><?= __('fs_contact_desc') ?></p>
                </div>
                
                <div class="card bg-dark border-0 shadow-lg" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body p-5">
                        <form id="financialContactForm" method="POST" action="<?= BASE_URL ?>/financial-services/contact">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold"><?= __('fs_name') ?></label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold"><?= __('fs_email') ?></label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold"><?= __('fs_phone') ?></label>
                                    <input type="tel" name="phone" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold"><?= __('fs_service_interested') ?></label>
                                    <select name="service" class="form-select" required>
                                        <option value=""><?= __('fs_select_service') ?></option>
                                        <?php foreach ($services as $svc): ?>
                                            <option value="<?= htmlspecialchars($svc['slug'] ?? $svc['title']) ?>"><?= htmlspecialchars($svc['title']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold"><?= __('fs_message') ?></label>
                                    <textarea name="message" class="form-control" rows="4" placeholder="<?= __('fs_message_placeholder') ?>"></textarea>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning btn-lg fw-bold">
                                    <i class="fas fa-paper-plane me-2"></i><?= __('fs_submit_enquiry') ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.financial-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 100%);
    color: white;
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}
.financial-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(245,158,11,0.15) 0%, transparent 70%);
    border-radius: 50%;
}
.service-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 32px;
    transition: all 0.3s ease;
    height: 100%;
}
.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    border-color: #f59e0b;
}
.service-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #1e3a8a, #3730a3);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: white;
    font-size: 1.5rem;
}
.service-features {
    list-style: none;
    padding: 0;
    margin: 0 0 20px;
}
.service-features li {
    padding: 8px 0;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
}
.service-features li:last-child {
    border-bottom: none;
}
.service-features li i {
    width: 24px;
}
.advisor-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    text-align: center;
}
.advisor-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}
.advisor-photo {
    width: 100%;
    height: 200px;
    object-fit: cover;
}
.advisor-photo-placeholder {
    height: 200px;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: center;
}
.advisor-info {
    padding: 24px;
}
.advisor-contact a {
    font-size: 0.875rem;
}
.accordion-button {
    background: white;
    border: 1px solid #e2e8f0 !important;
    border-radius: 12px !important;
    margin-bottom: 8px;
    font-weight: 600;
}
.accordion-button:not(.collapsed) {
    background: #fef3c7;
    color: #1e3a8a;
    box-shadow: none;
}
.accordion-body {
    color: #475569;
    line-height: 1.7;
}
#financialContactForm .form-control,
#financialContactForm .form-select {
    background: #1e293b;
    border: 1px solid #334155;
    color: white;
}
#financialContactForm .form-control:focus,
#financialContactForm .form-select:focus {
    background: #1e293b;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245,158,11,0.2);
    color: white;
}
#financialContactForm .form-label {
    color: #f1f5f9;
}
</style>

<script>
document.getElementById('financialContactForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', '<?= __('fs_enquiry_submitted') ?>');
            form.reset();
        } else {
            showToast('error', result.message || '<?= __('fs_enquiry_failed') ?>');
        }
    } catch (error) {
        showToast('error', '<?= __('fs_enquiry_error') ?>');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}
</script>