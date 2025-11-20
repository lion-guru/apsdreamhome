<?php
/**
 * Contact Page - APS Dream Homes Pvt Ltd - Enhanced Version
 * Professional contact page with comprehensive information and multiple contact methods
 */
// Define constant to allow database connection
if (!defined('INCLUDED_FROM_MAIN')) {
    define('INCLUDED_FROM_MAIN', true);
} 

// Include database connection
require_once 'includes/db_connection.php';

// Process form submission
$form_submitted = false;
$form_success = false;
$form_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    // Validate form data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        $form_error = "सभी आवश्यक फ़ील्ड भरें";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = "कृपया एक वैध ईमेल पता दर्ज करें";
    } else {
        try {
            // Insert into database
            $sql = "INSERT INTO contact_submissions (name, email, phone, subject, message, submission_date, ip_address) 
                    VALUES (:name, :email, :phone, :subject, :message, NOW(), :ip)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':subject' => $subject ?: 'Website Contact Form',
                ':message' => $message,
                ':ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            // Send notification email to admin
            // Note: Implement email sending functionality here
            
            $form_submitted = true;
            $form_success = true;
        } catch (PDOException $e) {
            $form_submitted = true;
            $form_error = "फॉर्म जमा करने में त्रुटि हुई। कृपया बाद में पुन: प्रयास करें।";
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}

// Set page variables for layout
$page_title = 'Contact Us - APS Dream Homes Pvt Ltd - Real Estate Services in Gorakhpur';
$page_description = 'Contact APS Dream Homes Pvt Ltd for all your real estate needs in Gorakhpur. Call +91-9554000001, visit our office, or fill out our contact form. Professional real estate services with 8+ years of experience.';

try {
    global $pdo;
    $conn = $pdo;

    // Get company settings from database
    $settings_sql = "SELECT setting_name, setting_value FROM site_settings WHERE setting_name IN ('company_name', 'company_phone', 'company_email', 'company_address', 'working_hours')";
    $settings_stmt = $conn->prepare($settings_sql);
    $settings_stmt->execute();
    $settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    $settings = [];
    $error_message = "Unable to load company information.";
}

$company_name = $settings['company_name'] ?? 'APS Dream Homes Pvt Ltd';
$company_phone = $settings['company_phone'] ?? '+91-9554000001';
$company_email = $settings['company_email'] ?? 'info@apsdreamhomes.com';
$company_address = $settings['company_address'] ?? '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008';
$working_hours = $settings['working_hours'] ?? 'Mon-Sat: 9:30 AM - 7:00 PM | Sun: 10:00 AM - 5:00 PM';
$company_phone_link = preg_replace('/[^\d+]/', '', $company_phone);
$whatsapp_link = 'https://wa.me/' . ($company_phone_link ?: '919554000001');

require_once __DIR__ . '/includes/enhanced_universal_template.php';

$template = new EnhancedUniversalTemplate();
$template->setTitle($page_title)
    ->setDescription($page_description)
    ->addMeta('keywords', 'APS Dream Homes contact, Gorakhpur real estate, property enquiry')
    ->addCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css')
    ->addCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css')
    ->addCssFile('https://unpkg.com/aos@2.3.1/dist/aos.css')
    ->addCustomCss(<<<'CSS'
.contact-hero {
    position: relative;
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.92), rgba(59, 130, 246, 0.92)), url('assets/img/contact-hero.jpg') center/cover;
    color: white;
    padding: clamp(5rem, 8vw, 7rem) 0;
    overflow: hidden;
}

.contact-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top left, rgba(255,255,255,0.25), transparent 55%);
}

.contact-hero .hero-content {
    position: relative;
    z-index: 2;
}

.contact-title {
    font-size: clamp(2.5rem, 6vw, 3.5rem);
    font-weight: 800;
    margin-bottom: 1rem;
}

.contact-lead {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.85);
}

.contact-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
}

.contact-info-card {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 18px;
    padding: 1.8rem;
    backdrop-filter: blur(12px);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.contact-info-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
}

.contact-info-icon {
    width: 58px;
    height: 58px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    margin-bottom: 1.2rem;
    background: rgba(255,255,255,0.15);
}

.contact-info-card h4 {
    color: white;
    font-weight: 700;
}

.contact-info-card p,
.contact-info-card a,
.contact-info-card span {
    color: rgba(255,255,255,0.85);
}

.contact-section {
    padding: clamp(4rem, 6vw, 6rem) 0;
    background: #f4f6fb;
}

.contact-wrapper {
    background: white;
    border-radius: 28px;
    box-shadow: 0 40px 80px rgba(15, 23, 42, 0.12);
    overflow: hidden;
}

.contact-form-pane {
    padding: clamp(2.5rem, 4vw, 3.5rem);
}

.contact-details-pane {
    background: linear-gradient(160deg, #1d4ed8 0%, #312e81 100%);
    color: white;
    padding: clamp(2.5rem, 4vw, 3.5rem);
    position: relative;
    overflow: hidden;
}

.contact-details-pane::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.35), transparent 50%);
}

.contact-details-pane > * {
    position: relative;
    z-index: 2;
}

.form-label {
    font-weight: 600;
    color: #1e293b;
}

.form-control,
.form-select {
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.35);
    padding: 0.85rem 1rem;
    font-weight: 500;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: rgba(37, 99, 235, 0.65);
    box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
}

.btn-contact-primary {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    border: none;
    padding: 1rem 2.5rem;
    border-radius: 999px;
    font-weight: 700;
    letter-spacing: 0.5px;
    box-shadow: 0 20px 35px rgba(37, 99, 235, 0.25);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.btn-contact-primary:hover {
    transform: translateY(-4px);
    box-shadow: 0 25px 45px rgba(37, 99, 235, 0.35);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.service-card {
    background: white;
    border-radius: 18px;
    padding: 1.8rem;
    border: 1px solid rgba(148, 163, 184, 0.16);
    box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
}

.service-card .icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: rgba(37, 99, 235, 0.12);
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.map-card {
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.15);
    box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.12);
}

.map-card iframe {
    border: 0;
    width: 100%;
    height: 300px;
}

.faq-accordion .accordion-item {
    border: none;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 16px 24px rgba(15, 23, 42, 0.08);
    margin-bottom: 1.5rem;
}

.faq-accordion .accordion-button {
    font-weight: 600;
    padding: 1.5rem;
}

.faq-accordion .accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(99, 102, 241, 0.12));
    color: #1d4ed8;
}

.cta-banner {
    background: linear-gradient(135deg, #312e81, #1d4ed8);
    color: white;
    border-radius: 24px;
    padding: clamp(2.5rem, 4vw, 3.5rem);
    position: relative;
    overflow: hidden;
}

.cta-banner::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top right, rgba(255,255,255,0.18), transparent 55%);
}

.cta-banner > * {
    position: relative;
    z-index: 2;
}

@media (max-width: 991.98px) {
    .contact-wrapper {
        border-radius: 20px;
    }

    .contact-details-pane {
        margin-top: -1px;
    }
}

@media (max-width: 575.98px) {
    .contact-info-card {
        padding: 1.4rem;
    }

    .service-card {
        padding: 1.4rem;
    }
}
CSS
);

$template->addJS('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', true, false);
$template->addJS('https://unpkg.com/aos@2.3.1/dist/aos.js', true, false);
$template->addCustomJs(<<<'JS'
document.addEventListener('DOMContentLoaded', function() {
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 900, once: true, offset: 80 });
    }

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function (event) {
            if (!contactForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            contactForm.classList.add('was-validated');
        });
    }

    document.querySelectorAll('.hover-elevate').forEach(function(card) {
        card.addEventListener('mouseenter', function () {
            card.classList.add('shadow-lg');
        });
        card.addEventListener('mouseleave', function () {
            card.classList.remove('shadow-lg');
        });
    });
});
JS
);

ob_start();

?>

<section class="contact-hero">
    <div class="container">
        <div class="hero-content text-center" data-aos="fade-up">
            <span class="badge rounded-pill text-bg-light px-3 py-2 fw-semibold mb-3"><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></span>
            <h1 class="contact-title">Let’s Build Your Dream Property Journey</h1>
            <p class="contact-lead mb-4">
                Speak directly with our specialist advisors for property guidance, investment planning, and site visits.<br>
                We respond to every query within 24 hours.
            </p>
            <div class="d-inline-flex align-items-center gap-3 flex-wrap justify-content-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold fs-5"><i class="fas fa-phone text-warning me-2"></i><?php echo htmlspecialchars($company_phone, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <span class="text-white-50 d-none d-md-inline">|</span>
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-clock text-info"></i>
                    <span><?php echo htmlspecialchars($working_hours, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        </div>

        <div class="contact-info-grid mt-5">
            <div class="contact-info-card hover-elevate" data-aos="fade-up" data-aos-delay="0">
                <div class="contact-info-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h4>Call Our Team</h4>
                <p class="mb-3">Speak with a senior consultant for instant property guidance.</p>
                <a href="tel:<?php echo htmlspecialchars($company_phone_link, ENT_QUOTES, 'UTF-8'); ?>" class="fw-semibold text-decoration-none">
                    <?php echo htmlspecialchars($company_phone, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <div class="small mt-2 text-white-50">Available during business hours</div>
            </div>

            <div class="contact-info-card hover-elevate" data-aos="fade-up" data-aos-delay="120">
                <div class="contact-info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h4>Email Support</h4>
                <p class="mb-3">Receive tailored proposals, brochures, and investment plans.</p>
                <a href="mailto:<?php echo htmlspecialchars($company_email, ENT_QUOTES, 'UTF-8'); ?>" class="fw-semibold text-decoration-none">
                    <?php echo htmlspecialchars($company_email, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <div class="small mt-2 text-white-50">Response within 24 hours</div>
            </div>

            <div class="contact-info-card hover-elevate" data-aos="fade-up" data-aos-delay="240">
                <div class="contact-info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h4>Visit Our Studio</h4>
                <p class="mb-3">Experience project walkthroughs and curated property previews.</p>
                <span class="fw-semibold d-block">
                    <?php echo htmlspecialchars($company_address, ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <div class="small mt-2 text-white-50">Walk-in consultations welcome</div>
            </div>

            <div class="contact-info-card hover-elevate" data-aos="fade-up" data-aos-delay="360">
                <div class="contact-info-icon">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h4>WhatsApp Concierge</h4>
                <p class="mb-3">Instant updates, brochures, and confirmation of site visits.</p>
                <a href="<?php echo htmlspecialchars($whatsapp_link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">
                    Chat on WhatsApp
                </a>
                <div class="small mt-2 text-white-50">Quick responses guaranteed</div>
            </div>
        </div>
    </div>
</section>

<section class="contact-section" id="contact">
    <div class="container">
        <div class="row g-0 contact-wrapper">
            <div class="col-lg-7">
                <div class="contact-form-pane h-100">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 fw-semibold">Write to us</span>
                        <span class="text-secondary">We reply within 24 hours</span>
                    </div>

                    <h2 class="fw-bold mb-2">Tell us how we can help</h2>
                    <p class="text-secondary mb-4">Share your property goals, investment plans, or questions and our expert team will reach out with tailored recommendations.</p>

                    <?php if ($form_submitted): ?>
                        <?php if ($form_success): ?>
                            <div class="alert alert-success shadow-sm" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                आपका संदेश सफलतापूर्वक भेज दिया गया है। हमारी टीम जल्द ही आपसे संपर्क करेगी।
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger shadow-sm" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($form_error ?? 'फॉर्म जमा करने में त्रुटि हुई। कृपया बाद में पुनः प्रयास करें।', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <form id="contactForm" method="POST" action="<?php echo BASE_URL; ?>/submit/contact" class="needs-validation" novalidate>
                        <input type="hidden" name="contact_submit" value="1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="Enter your full name">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="your.email@example.com">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required placeholder="Your contact number">
                            </div>
                            <div class="col-md-6">
                                <label for="contact_type" class="form-label">Inquiry Type</label>
                                <select class="form-select" id="contact_type" name="contact_type">
                                    <option value="General Inquiry">General Inquiry</option>
                                    <option value="Property Inquiry">Property Inquiry</option>
                                    <option value="Investment Consultation">Investment Consultation</option>
                                    <option value="Site Visit Request">Site Visit Request</option>
                                    <option value="Legal Documentation">Legal Documentation</option>
                                    <option value="Technical Support">Technical Support</option>
                                    <option value="Feedback">Feedback</option>
                                    <option value="Partnership">Partnership</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="budget" class="form-label">Budget Range</label>
                                <select class="form-select" id="budget" name="budget">
                                    <option value="">Select Budget Range</option>
                                    <option value="Under 25L">Under ₹25 Lakh</option>
                                    <option value="25L-50L">₹25L - ₹50L</option>
                                    <option value="50L-1Cr">₹50L - ₹1Cr</option>
                                    <option value="1Cr-2Cr">₹1Cr - ₹2Cr</option>
                                    <option value="Above 2Cr">Above ₹2Cr</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="property_type" class="form-label">Property Interest</label>
                                <select class="form-select" id="property_type" name="property_type">
                                    <option value="">Select Property Type</option>
                                    <option value="Apartment">Apartment</option>
                                    <option value="Villa">Villa</option>
                                    <option value="Plot">Plot</option>
                                    <option value="Commercial">Commercial</option>
                                    <option value="Farmhouse">Farmhouse</option>
                                    <option value="Row House">Row House</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" required placeholder="Brief description of your inquiry">
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="6" required placeholder="Please share details about your requirement, preferred timelines, and any questions you may have."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        Subscribe me to receive curated property recommendations and market insights
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-contact-primary w-100" name="contact_submit_btn">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="contact-details-pane h-100 text-white" data-aos="fade-left">
                    <div class="mb-4">
                        <h3 class="fw-semibold">Direct Support</h3>
                        <p class="mb-0">Our advisors are available Monday to Sunday for dedicated assistance and personalised walkthroughs.</p>
                    </div>

                    <div class="d-flex gap-3 align-items-start mb-4 pb-4 border-bottom border-white-25">
                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                            <i class="fas fa-headset fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-1">Call Us</h6>
                            <a href="tel:<?php echo htmlspecialchars($company_phone, ENT_QUOTES, 'UTF-8'); ?>" class="fs-5 text-white fw-semibold text-decoration-none">
                                <?php echo htmlspecialchars($company_phone, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <div class="small text-white-50 mt-1">Business Hours: <?php echo htmlspecialchars($working_hours, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 align-items-start mb-4 pb-4 border-bottom border-white-25">
                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                            <i class="fas fa-envelope-open-text fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-1">Email Us</h6>
                            <a href="mailto:<?php echo htmlspecialchars($company_email, ENT_QUOTES, 'UTF-8'); ?>" class="fs-6 text-white fw-semibold text-decoration-none">
                                <?php echo htmlspecialchars($company_email, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <div class="small text-white-50 mt-1">Share documents, requirements, or request brochures anytime.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 align-items-start mb-4 pb-4 border-bottom border-white-25">
                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                            <i class="fas fa-map-marked-alt fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-1">Visit Our Studio</h6>
                            <p class="mb-1 fw-semibold">
                                <?php echo htmlspecialchars($company_address, ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <div class="small text-white-50">Walk-ins welcome • Dedicated parking • Guided site visits</div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 align-items-start">
                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                            <i class="fab fa-whatsapp fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-1">WhatsApp Concierge</h6>
                            <a href="<?php echo htmlspecialchars($whatsapp_link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="fs-6 text-white fw-semibold text-decoration-none">
                                Chat on WhatsApp
                            </a>
                            <div class="small text-white-50 mt-1">Quick responses guaranteed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" id="services">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 fw-semibold">Our Reach</span>
            <h2 class="fw-bold mt-3">We’re building across key growth corridors</h2>
            <p class="lead text-muted mb-0">From premium residential townships to high-yield investment plots, explore the destinations we currently serve.</p>
        </div>

        <div class="services-grid" data-aos="fade-up" data-aos-delay="120">
            <div class="service-card hover-elevate">
                <div class="icon">
                    <i class="fas fa-city"></i>
                </div>
                <h5 class="fw-semibold mb-1">Gorakhpur HQ</h5>
                <p class="text-muted mb-3">Flagship developments with premium amenities and curated investment plots.</p>
                <span class="text-primary fw-semibold">Suryoday Colony • Dream Heights • Royal Greens</span>
            </div>
            <div class="service-card hover-elevate">
                <div class="icon">
                    <i class="fas fa-landmark"></i>
                </div>
                <h5 class="fw-semibold mb-1">Lucknow Expansion</h5>
                <p class="text-muted mb-3">Strategic projects near IT & industrial corridors with strong appreciation potential.</p>
                <span class="text-primary fw-semibold">Shaheed Path • Sultanpur Road • Amar Shaheed Path</span>
            </div>
            <div class="service-card hover-elevate">
                <div class="icon">
                    <i class="fas fa-map-pin"></i>
                </div>
                <h5 class="fw-semibold mb-1">Varanasi & Prayagraj</h5>
                <p class="text-muted mb-3">Curated residential clusters crafted for comfort, connectivity, and growth.</p>
                <span class="text-primary fw-semibold">Sangam City • Smart Riverfront • Heritage Greens</span>
            </div>
            <div class="service-card hover-elevate">
                <div class="icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h5 class="fw-semibold mb-1">Investor Desk</h5>
                <p class="text-muted mb-3">Dedicated support for NRI, HNI, and institutional investors seeking bespoke portfolios.</p>
                <span class="text-primary fw-semibold">Virtual walkthroughs • ROI planning • Legal advisory</span>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="cta-banner" data-aos="fade-up">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-2">Ready for a site visit or virtual walkthrough?</h2>
                    <p class="mb-0">Our relationship managers curate bespoke experiences—whether you prefer an on-site tour or an immersive virtual presentation.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="<?php echo htmlspecialchars($whatsapp_link, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light btn-lg text-primary fw-semibold" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp me-2"></i>Schedule with Concierge
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light" id="map">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 fw-semibold">Visit Us</span>
            <h2 class="fw-bold mt-3">Find our experience studio</h2>
            <p class="lead text-muted mb-0">Plan your visit and explore our curated property showcase with guided walkthroughs.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="map-card" data-aos="zoom-in">
                    <iframe src="https://maps.google.com/maps?q=<?php echo urlencode($company_address); ?>&output=embed" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" id="faq">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 fw-semibold">FAQs</span>
            <h2 class="fw-bold mt-3">Answers to frequent queries</h2>
            <p class="lead text-muted mb-0">Everything you need to know before starting your property journey with us.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="accordion faq-accordion" id="faqAccordion">
                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="0">
                        <h2 class="accordion-header" id="faqHeadingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseOne" aria-expanded="true" aria-controls="faqCollapseOne">
                                How quickly will your team respond to my inquiry?
                            </button>
                        </h2>
                        <div id="faqCollapseOne" class="accordion-collapse collapse show" aria-labelledby="faqHeadingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We respond to all inquiries within 24 hours on business days. For urgent queries, call us directly at <?php echo htmlspecialchars($company_phone, ENT_QUOTES, 'UTF-8'); ?> and our concierge team will assist immediately.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="80">
                        <h2 class="accordion-header" id="faqHeadingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseTwo" aria-expanded="false" aria-controls="faqCollapseTwo">
                                Do you charge consultation fees or site visit charges?
                            </button>
                        </h2>
                        <div id="faqCollapseTwo" class="accordion-collapse collapse" aria-labelledby="faqHeadingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Initial consultations, property recommendations, and site visits are complimentary. Costs are only applicable when you proceed with booking or documentation.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="160">
                        <h2 class="accordion-header" id="faqHeadingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
                                Can you help arrange home loans or financing?
                            </button>
                        </h2>
                        <div id="faqCollapseThree" class="accordion-collapse collapse" aria-labelledby="faqHeadingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolutely. We collaborate with leading nationalised and private banks to provide preferred loan terms and support you with eligibility checks, paperwork, and quick approvals.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="240">
                        <h2 class="accordion-header" id="faqHeadingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseFour" aria-expanded="false" aria-controls="faqCollapseFour">
                                What documents do I need to initiate a booking?
                            </button>
                        </h2>
                        <div id="faqCollapseFour" class="accordion-collapse collapse" aria-labelledby="faqHeadingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Typically, we require identity proof, address proof, income proof, and recent bank statements. Our legal desk shares a personalised checklist based on your profile and chosen property.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="320">
                        <h2 class="accordion-header" id="faqHeadingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseFive" aria-expanded="false" aria-controls="faqCollapseFive">
                                Do you support clients after the sale is completed?
                            </button>
                        </h2>
                        <div id="faqCollapseFive" class="accordion-collapse collapse" aria-labelledby="faqHeadingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we provide end-to-end post-sales support including registration assistance, possession coordination, rental management, interior design consultations, and ongoing customer care.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

page($content, $page_title);
?>