<?php
/**
 * Financial Contact Page - APS Dream Homes
 * Contact form for financial services inquiries
 */

try {
    $services = $services ?? [];
    $faqs = $faqs ?? [];
} catch (\Exception $e) {
    error_log('Financial contact page database error: ' . $e->getMessage());
    $services = [];
    $faqs = [];
}
?>
<!-- Hero Section -->
<section class="hero-premium pt-5 pb-5">
    <div class="container premium-reveal fade-up position-relative z-2">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <span class="capsule-badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 mb-3 px-3 py-2"><i class="fas fa-coins me-1"></i> Financial Contact</span>
                <h1 class="display-4 fw-bold text-white mb-4">Get Expert Financial Advice</h1>
                <p class="lead text-white-50 mb-4">
                    Our financial advisors are ready to help you with home loans, plot loans, construction finance, and more.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/financial-services">Financial Services</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
        </ol>
    </div>
</nav>

<!-- Contact Form Section -->
<section id="contact-form" class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="display-5 fw-bold mb-3">Send Us Your Inquiry</h2>
                    <p class="lead text-muted">
                        Fill out the form below and our financial team will get back to you within 24 hours.
                    </p>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="card border-0 shadow-lg" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body p-5">
                        <form id="financialContactForm" method="POST" action="<?= BASE_URL ?>/financial-services/contact">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Your Name *</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email *</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone *</label>
                                    <input type="tel" name="phone" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Service Interested *</label>
                                    <select name="service" class="form-select" required>
                                        <option value="">Select a Service</option>
                                        <?php foreach ($services as $svc): ?>
                                            <option value="<?= htmlspecialchars($svc['slug'] ?? $svc['title']) ?>"><?= htmlspecialchars($svc['title'] ?? '') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Your Message</label>
                                    <textarea name="message" class="form-control" rows="4" placeholder="Tell us about your requirement..."></textarea>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Inquiry
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Why Choose APS Dream Home Financial Services?</h2>
                <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                    We partner with top banks and financial institutions to bring you the best rates and seamless service.
                </p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="feature-card text-center p-4 h-100 bg-white rounded-4 shadow-sm border-0">
                    <div class="icon-wrap bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto">
                        <i class="fas fa-handshake fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Free Consultation</h5>
                    <p class="text-muted mb-0">No-obligation financial assessment with our experts</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card text-center p-4 h-100 bg-white rounded-4 shadow-sm border-0">
                    <div class="icon-wrap bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Compare Bank Rates</h5>
                    <p class="text-muted mb-0">Access to 20+ banks for the lowest interest rates</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card text-center p-4 h-100 bg-white rounded-4 shadow-sm border-0">
                    <div class="icon-wrap bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Documentation Support</h5>
                    <p class="text-muted mb-0">Complete paperwork assistance from application to disbursement</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card text-center p-4 h-100 bg-white rounded-4 shadow-sm border-0">
                    <div class="icon-wrap bg-info bg-opacity-10 text-info d-inline-flex align-items-center justify-content-center rounded-circle mb-3 mx-auto">
                        <i class="fas fa-truck fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Doorstep Service</h5>
                    <p class="text-muted mb-0">We come to you for document collection and verification</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Current Bank Rates -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" data-aos="fade-up">Current Bank Interest Rates</h2>
                <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
                    Indicative rates as of <?= date('F Y') ?>. Actual rates may vary based on profile and loan amount.
                </p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="table-responsive" data-aos="fade-up">
                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Bank / Financial Institution</th>
                                <th class="text-center">Home Loan</th>
                                <th class="text-center">Plot Loan</th>
                                <th class="text-center">Construction Loan</th>
                                <th class="text-center">Balance Transfer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold"><i class="fas fa-bank me-2 text-primary"></i>State Bank of India (SBI)</td>
                                <td class="text-center">8.50% - 9.00%</td>
                                <td class="text-center">9.00% - 9.50%</td>
                                <td class="text-center">8.75% - 9.25%</td>
                                <td class="text-center">8.50% - 9.00%</td>
                            </tr>
                            <tr>
                                <td class="fw-bold"><i class="fas fa-bank me-2 text-primary"></i>HDFC Bank</td>
                                <td class="text-center">8.55% - 9.10%</td>
                                <td class="text-center">9.05% - 9.60%</td>
                                <td class="text-center">8.80% - 9.35%</td>
                                <td class="text-center">8.55% - 9.10%</td>
                            </tr>
                            <tr>
                                <td class="fw-bold"><i class="fas fa-bank me-2 text-primary"></i>ICICI Bank</td>
                                <td class="text-center">8.55% - 9.10%</td>
                                <td class="text-center">9.05% - 9.60%</td>
                                <td class="text-center">8.80% - 9.35%</td>
                                <td class="text-center">8.55% - 9.10%</td>
                            </tr>
                            <tr>
                                <td class="fw-bold"><i class="fas fa-bank me-2 text-primary"></i>Punjab National Bank</td>
                                <td class="text-center">8.50% - 9.00%</td>
                                <td class="text-center">9.00% - 9.50%</td>
                                <td class="text-center">8.75% - 9.25%</td>
                                <td class="text-center">8.50% - 9.00%</td>
                            </tr>
                            <tr>
                                <td class="fw-bold"><i class="fas fa-bank me-2 text-primary"></i>Axis Bank</td>
                                <td class="text-center">8.60% - 9.15%</td>
                                <td class="text-center">9.10% - 9.65%</td>
                                <td class="text-center">8.85% - 9.40%</td>
                                <td class="text-center">8.60% - 9.15%</td>
                            </tr>
                            <tr>
                                <td class="fw-bold"><i class="fas fa-bank me-2 text-primary"></i>Bank of Baroda</td>
                                <td class="text-center">8.50% - 9.05%</td>
                                <td class="text-center">9.00% - 9.55%</td>
                                <td class="text-center">8.75% - 9.30%</td>
                                <td class="text-center">8.50% - 9.05%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.feature-card {
    transition: all 0.3s ease;
}
.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}
.feature-card .icon-wrap {
    width: 80px;
    height: 80px;
    font-size: 1.5rem;
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
            showToast('success', 'Inquiry submitted successfully! We will contact you soon.');
            form.reset();
        } else {
            showToast('error', result.message || 'Something went wrong. Please try again.');
        }
    } catch (error) {
        showToast('error', 'Something went wrong. Please try again.');
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