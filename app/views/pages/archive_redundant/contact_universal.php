<?php
// Enhanced Contact.php using Universal Template System
// All functionality from old contact.php preserved and enhanced

require_once __DIR__ . '/includes/enhanced_universal_template.php';

// Get database connection and data (preserved from old contact.php)
require_once 'includes/config.php';
require_once 'includes/db_connection.php';
$conn = getMysqliConnection();

// Get agents for contact page (same query as old contact.php)
$agents = [];
try {
    $query = "SELECT * FROM users WHERE role = 'agent' AND status = 'active' ORDER BY first_name LIMIT 4";
    $result = $conn->query($query);
    if ($result) {
        $agents = $result->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log('Error fetching agents: ' . $e->getMessage());
}

// Company info (same as old contact.php)
$company_info = [
    'phone' => '+91-9000000001',
    'email' => 'info@apsdreamhome.com',
    'address' => 'Gorakhpur, Uttar Pradesh, India'
];

// Handle form submission (identical to old contact.php)
$form_message = '';
$form_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $contact_type = trim($_POST['contact_type'] ?? 'general');

    // Basic validation (same as old contact.php)
    if (empty($name) || empty($email) || empty($message)) {
        $form_message = 'Please fill in all required fields.';
        $form_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_message = 'Please enter a valid email address.';
        $form_type = 'danger';
    } else {
        try {
            // Insert contact message into database (same as old contact.php)
            $query = "INSERT INTO contact_messages (name, email, phone, subject, message, contact_type, status, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($query);
            $stmt->execute([$name, $email, $phone, $subject, $message, $contact_type]);

            $form_message = 'Thank you for contacting us! We will get back to you within 24 hours.';
            $form_type = 'success';

            // Clear form data (same as old contact.php)
            $_POST = [];
        } catch (Exception $e) {
            error_log('Error saving contact message: ' . $e->getMessage());
            $form_message = 'Sorry, there was an error sending your message. Please try again later.';
            $form_type = 'danger';
        }
    }
}

// Build the complete content with all old contact.php features
$content = "
<!-- Contact Hero Section (Identical to old contact.php) -->
<section class='py-5 bg-primary text-white' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);'>
    <div class='container'>
        <div class='row align-items-center'>
            <div class='col-lg-6'>
                <h1 class='display-4 fw-bold mb-4'>Get In Touch</h1>
                <p class='lead mb-4'>Have questions about a property or need expert advice? We're here to help you every step of the way.</p>
                <div class='row g-3'>
                    <div class='col-md-6'>
                        <div class='d-flex align-items-center'>
                            <i class='fas fa-phone fa-2x text-warning me-3'></i>
                            <div>
                                <h6 class='mb-0'>Call Us</h6>
                                <p class='mb-0'>" . htmlspecialchars($company_info['phone'] ?? '+91-9000000001') . "</p>
                            </div>
                        </div>
                    </div>
                    <div class='col-md-6'>
                        <div class='d-flex align-items-center'>
                            <i class='fas fa-envelope fa-2x text-warning me-3'></i>
                            <div>
                                <h6 class='mb-0'>Email Us</h6>
                                <p class='mb-0'>" . htmlspecialchars($company_info['email'] ?? 'info@apsdreamhome.com') . "</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-lg-6'>
                <div class='text-center'>
                    <i class='fas fa-comments fa-5x text-warning mb-4'></i>
                    <h3 class='h2 mb-3'>We'd Love to Hear From You</h3>
                    <p class='mb-0'>Send us a message and we'll respond as soon as possible.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information Section (Identical to old contact.php) -->
<section class='py-5'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h2 class='display-5 fw-bold mb-3'>Contact Information</h2>
                <p class='lead text-muted'>Multiple ways to reach us</p>
            </div>
        </div>

        <div class='row g-4'>
            <div class='col-lg-4 col-md-6' data-aos='fade-up'>
                <div class='card h-100 border-0 shadow-sm text-center'>
                    <div class='card-body p-4'>
                        <div class='mb-3'>
                            <i class='fas fa-map-marker-alt fa-3x text-primary'></i>
                        </div>
                        <h5 class='card-title fw-bold mb-3'>Visit Our Office</h5>
                        <p class='card-text text-muted'>
                            " . htmlspecialchars($company_info['address'] ?? 'Gorakhpur, Uttar Pradesh, India') . "
                        </p>
                        <p class='card-text text-muted small'>
                            <strong>Business Hours:</strong><br>
                            Monday - Saturday: 9:00 AM - 6:00 PM<br>
                            Sunday: By Appointment Only
                        </p>
                    </div>
                </div>
            </div>

            <div class='col-lg-4 col-md-6' data-aos='fade-up' data-aos-delay='100'>
                <div class='card h-100 border-0 shadow-sm text-center'>
                    <div class='card-body p-4'>
                        <div class='mb-3'>
                            <i class='fas fa-phone fa-3x text-success'></i>
                        </div>
                        <h5 class='card-title fw-bold mb-3'>Call Us</h5>
                        <p class='card-text'>
                            <a href='tel:" . htmlspecialchars($company_info['phone'] ?? '+91-9000000001') . "'
                               class='text-decoration-none text-success fw-bold'>
                                " . htmlspecialchars($company_info['phone'] ?? '+91-9000000001') . "
                            </a>
                        </p>
                        <p class='card-text text-muted small'>
                            Our phone lines are open during business hours. For urgent matters, call us anytime.
                        </p>
                    </div>
                </div>
            </div>

            <div class='col-lg-4 col-md-6' data-aos='fade-up' data-aos-delay='200'>
                <div class='card h-100 border-0 shadow-sm text-center'>
                    <div class='card-body p-4'>
                        <div class='mb-3'>
                            <i class='fas fa-envelope fa-3x text-info'></i>
                        </div>
                        <h5 class='card-title fw-bold mb-3'>Email Us</h5>
                        <p class='card-text'>
                            <a href='mailto:" . htmlspecialchars($company_info['email'] ?? 'info@apsdreamhome.com') . "'
                               class='text-decoration-none text-info fw-bold'>
                                " . htmlspecialchars($company_info['email'] ?? 'info@apsdreamhome.com') . "
                            </a>
                        </p>
                        <p class='card-text text-muted small'>
                            Send us an email and we'll get back to you within 24 hours.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section (Identical to old contact.php) -->
<section class='py-5 bg-light'>
    <div class='container'>
        <div class='row justify-content-center'>
            <div class='col-lg-8'>
                <div class='card shadow-sm border-0'>
                    <div class='card-body p-5'>
                        <div class='text-center mb-5'>
                            <h2 class='display-6 fw-bold mb-3'>Send Us a Message</h2>
                            <p class='lead text-muted'>Fill out the form below and we'll get back to you shortly</p>
                        </div>";

                        if (!empty($form_message)) {
                            $content .= "
                        <div class='alert alert-" . ($form_type === 'success' ? 'success' : 'danger') . " alert-dismissible fade show' role='alert'>
                            " . htmlspecialchars($form_message) . "
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
                        }

                        $content .= "
                        <form method='POST' action='contact.php' id='contactForm'>
                            <div class='row g-4'>
                                <div class='col-md-6'>
                                    <label for='name' class='form-label fw-bold'>Full Name *</label>
                                    <input type='text' class='form-control form-control-lg' id='name' name='name'
                                           value='" . htmlspecialchars($_POST['name'] ?? '') . "' required>
                                </div>

                                <div class='col-md-6'>
                                    <label for='email' class='form-label fw-bold'>Email Address *</label>
                                    <input type='email' class='form-control form-control-lg' id='email' name='email'
                                           value='" . htmlspecialchars($_POST['email'] ?? '') . "' required>
                                </div>

                                <div class='col-md-6'>
                                    <label for='phone' class='form-label fw-bold'>Phone Number</label>
                                    <input type='tel' class='form-control form-control-lg' id='phone' name='phone'
                                           value='" . htmlspecialchars($_POST['phone'] ?? '') . "'>
                                </div>

                                <div class='col-md-6'>
                                    <label for='contact_type' class='form-label fw-bold'>Contact Type</label>
                                    <select class='form-select form-select-lg' id='contact_type' name='contact_type'>
                                        <option value='general' " . (($_POST['contact_type'] ?? '') === 'general' ? 'selected' : '') . ">General Inquiry</option>
                                        <option value='property' " . (($_POST['contact_type'] ?? '') === 'property' ? 'selected' : '') . ">Property Inquiry</option>
                                        <option value='support' " . (($_POST['contact_type'] ?? '') === 'support' ? 'selected' : '') . ">Technical Support</option>
                                        <option value='feedback' " . (($_POST['contact_type'] ?? '') === 'feedback' ? 'selected' : '') . ">Feedback</option>
                                        <option value='partnership' " . (($_POST['contact_type'] ?? '') === 'partnership' ? 'selected' : '') . ">Partnership</option>
                                    </select>
                                </div>

                                <div class='col-12'>
                                    <label for='subject' class='form-label fw-bold'>Subject *</label>
                                    <input type='text' class='form-control form-control-lg' id='subject' name='subject'
                                           value='" . htmlspecialchars($_POST['subject'] ?? '') . "' required>
                                </div>

                                <div class='col-12'>
                                    <label for='message' class='form-label fw-bold'>Message *</label>
                                    <textarea class='form-control form-control-lg' id='message' name='message'
                                              rows='6' required>" . htmlspecialchars($_POST['message'] ?? '') . "</textarea>
                                </div>

                                <div class='col-12'>
                                    <div class='d-grid'>
                                        <button type='submit' class='btn btn-primary btn-lg'>
                                            <i class='fas fa-paper-plane me-2'></i>Send Message
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Agents Section (Identical to old contact.php) -->";
if (!empty($agents)) {
    $content .= "
<section class='py-5'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h2 class='display-5 fw-bold mb-3'>Meet Our Expert Agents</h2>
                <p class='lead text-muted'>Get personalized assistance from our experienced team</p>
            </div>
        </div>

        <div class='row g-4'>";
            foreach ($agents as $agent) {
                $content .= "
            <div class='col-lg-6' data-aos='fade-up'>
                <div class='card h-100 border-0 shadow-sm'>
                    <div class='card-body p-4'>
                        <div class='row align-items-center'>
                            <div class='col-md-4 text-center'>
                                <img src='assets/images/user-placeholder.jpg'
                                     alt='" . htmlspecialchars(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? '')) . "'
                                     class='rounded-circle mb-3' width='100' height='100'>
                            </div>
                            <div class='col-md-8'>
                                <h5 class='card-title fw-bold mb-2'>
                                    " . htmlspecialchars(($agent['first_name'] ?? '') . ' ' . ($agent['last_name'] ?? '')) . "
                                </h5>
                                <p class='text-muted mb-3'>Senior Real Estate Agent</p>
                                <div class='d-flex gap-2 mb-3'>
                                    <a href='tel:" . htmlspecialchars($agent['phone'] ?? '') . "'
                                       class='btn btn-outline-primary btn-sm'>
                                        <i class='fas fa-phone me-1'></i>Call
                                    </a>
                                    <a href='mailto:" . htmlspecialchars($agent['email'] ?? '') . "'
                                       class='btn btn-outline-primary btn-sm'>
                                        <i class='fas fa-envelope me-1'></i>Email
                                    </a>
                                </div>
                                <p class='card-text text-muted small'>
                                    Specializes in residential properties and has helped over 500+ families find their dream homes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
            }
            $content .= "
        </div>
    </div>
</section>";
}
$content .= "

<!-- Map Section (Identical to old contact.php) -->
<section class='py-5 bg-light'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h2 class='display-5 fw-bold mb-3'>Find Us</h2>
                <p class='lead text-muted'>Visit our office or explore our service areas</p>
            </div>
        </div>

        <div class='row g-4'>
            <div class='col-lg-8'>
                <div class='card border-0 shadow-sm'>
                    <div class='card-body p-0'>
                        <!-- Placeholder for map (same as old contact.php) -->
                        <div style='height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;'>
                            <div class='text-center'>
                                <i class='fas fa-map-marked-alt fa-4x mb-3'></i>
                                <h4>Interactive Map</h4>
                                <p class='mb-0'>Our office location and service areas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-lg-4'>
                <div class='card border-0 shadow-sm h-100'>
                    <div class='card-body p-4'>
                        <h5 class='card-title fw-bold mb-4'>Service Areas</h5>
                        <div class='list-group list-group-flush'>
                            <div class='list-group-item border-0 px-0 py-2'>
                                <i class='fas fa-map-marker-alt text-primary me-2'></i>
                                <strong>Gorakhpur</strong> - Main Office
                            </div>
                            <div class='list-group-item border-0 px-0 py-2'>
                                <i class='fas fa-map-marker-alt text-success me-2'></i>
                                <strong>Lucknow</strong> - Branch Office
                            </div>
                            <div class='list-group-item border-0 px-0 py-2'>
                                <i class='fas fa-map-marker-alt text-info me-2'></i>
                                <strong>Varanasi</strong> - Service Area
                            </div>
                            <div class='list-group-item border-0 px-0 py-2'>
                                <i class='fas fa-map-marker-alt text-warning me-2'></i>
                                <strong>Allahabad</strong> - Service Area
                            </div>
                            <div class='list-group-item border-0 px-0 py-2'>
                                <i class='fas fa-map-marker-alt text-secondary me-2'></i>
                                <strong>Other Cities</strong> - On Request
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section (Identical to old contact.php) -->
<section class='py-5'>
    <div class='container'>
        <div class='row'>
            <div class='col-12 text-center mb-5'>
                <h2 class='display-5 fw-bold mb-3'>Frequently Asked Questions</h2>
                <p class='lead text-muted'>Quick answers to common questions</p>
            </div>
        </div>

        <div class='row justify-content-center'>
            <div class='col-lg-8'>
                <div class='accordion' id='contactFAQ'>
                    <div class='accordion-item border-0 shadow-sm mb-3'>
                        <h2 class='accordion-header'>
                            <button class='accordion-button' type='button' data-bs-toggle='collapse' data-bs-target='#faq1'>
                                How quickly can I expect a response?
                            </button>
                        </h2>
                        <div id='faq1' class='accordion-collapse collapse show' data-bs-parent='#contactFAQ'>
                            <div class='accordion-body'>
                                We typically respond to all inquiries within 24 hours during business days. For urgent matters, please call us directly.
                            </div>
                        </div>
                    </div>

                    <div class='accordion-item border-0 shadow-sm mb-3'>
                        <h2 class='accordion-header'>
                            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#faq2'>
                                Do you charge any fees for consultations?
                            </button>
                        </h2>
                        <div id='faq2' class='accordion-collapse collapse' data-bs-parent='#contactFAQ'>
                            <div class='accordion-body'>
                                Initial consultations and property viewings are completely free. We only charge fees when you decide to proceed with a purchase or rental agreement.
                            </div>
                        </div>
                    </div>

                    <div class='accordion-item border-0 shadow-sm mb-3'>
                        <h2 class='accordion-header'>
                            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#faq3'>
                                Can you help with property financing?
                            </button>
                        </h2>
                        <div id='faq3' class='accordion-collapse collapse' data-bs-parent='#contactFAQ'>
                            <div class='accordion-body'>
                                Yes, we work with multiple banks and financial institutions to help you secure the best financing options for your property purchase.
                            </div>
                        </div>
                    </div>

                    <div class='accordion-item border-0 shadow-sm mb-3'>
                        <h2 class='accordion-header'>
                            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#faq4'>
                                What documents do I need to buy a property?
                            </button>
                        </h2>
                        <div id='faq4' class='accordion-collapse collapse' data-bs-parent='#contactFAQ'>
                            <div class='accordion-body'>
                                You'll need ID proof, address proof, income proof, and bank statements. Our legal team will guide you through the complete documentation process.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>";

// Add all JavaScript functionality from old contact.php (preserved)
$template->addJS("
document.addEventListener('DOMContentLoaded', function() {
    // Form validation (from old contact.php)
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();

            if (!name || !email || !message) {
                e.preventDefault();
                showToast('Please fill in all required fields.', 'danger');
                return;
            }

            if (!isValidEmail(email)) {
                e.preventDefault();
                showToast('Please enter a valid email address.', 'danger');
                return;
            }

            // Show loading state (from old contact.php)
            const submitBtn = contactForm.querySelector('button[type=\"submit\"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Sending...';
            submitBtn.disabled = true;
        });
    }

    // Email validation (from old contact.php)
    function isValidEmail(email) {
        const emailRegex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
        return emailRegex.test(email);
    }

    // Toast notification function (from old contact.php)
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-\${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ` + message + `
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    // Initialize AOS if available (from old contact.php)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }
});
");

// Add custom CSS for accordion and hover effects (from old contact.php)
$template->addCSS('
.accordion-button {
    font-weight: 600;
    color: var(--primary-color);
}

.accordion-button:not(.collapsed) {
    background-color: var(--primary-color);
    color: white;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

.list-group-item:hover {
    background-color: var(--light-bg);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
');

// Render the complete page with all old contact.php functionality preserved
page($content, 'Contact Us - APS Dream Home');
?>
