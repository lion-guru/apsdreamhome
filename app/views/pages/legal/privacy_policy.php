<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page variables
$page_title = 'Privacy Policy - APS Dream Home';
$page_description = 'Privacy policy for APS Dream Home real estate services and data protection';
$active_page = 'privacy';

// Content for base layout
ob_start();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <h1 class="display-5 fw-bold text-primary">Privacy Policy</h1>
                        <p class="lead text-muted">Last updated: <?php echo date('F j, Y'); ?></p>
                    </div>

                    <div class="privacy-content">
                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">1. Information We Collect</h3>
                            <p>APS Dream Home collects various types of information to provide and improve our services:</p>
                            <div class="info-types mt-3">
                                <div class="info-type mb-3">
                                    <h5 class="fw-bold text-primary">Personal Information</h5>
                                    <ul>
                                        <li>Name, email address, phone number</li>
                                        <li>Physical address and location data</li>
                                        <li>Professional information and role</li>
                                        <li>Communication preferences</li>
                                    </ul>
                                </div>
                                <div class="info-type mb-3">
                                    <h5 class="fw-bold text-primary">Property Preferences</h5>
                                    <ul>
                                        <li>Property type preferences</li>
                                        <li>Budget and financing information</li>
                                        <li>Location preferences</li>
                                        <li>Property search history</li>
                                    </ul>
                                </div>
                                <div class="info-type mb-3">
                                    <h5 class="fw-bold text-primary">Usage Data</h5>
                                    <ul>
                                        <li>Pages visited and time spent</li>
                                        <li>Search queries and filters used</li>
                                        <li>Device and browser information</li>
                                        <li>IP address and location data</li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">2. How We Use Your Information</h3>
                            <p>We use the collected information for various purposes:</p>
                            <ul>
                                <li><strong>Service Provision:</strong> To provide and maintain our real estate services</li>
                                <li><strong>Personalization:</strong> To tailor property recommendations and user experience</li>
                                <li><strong>Communication:</strong> To respond to inquiries and send relevant updates</li>
                                <li><strong>Matching:</strong> To connect users with suitable properties and agents</li>
                                <li><strong>Analytics:</strong> To analyze usage patterns and improve our services</li>
                                <li><strong>Legal Compliance:</strong> To comply with legal obligations and protect our rights</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">3. Information Sharing</h3>
                            <p>We may share your information under specific circumstances:</p>
                            <div class="sharing-scenarios mt-3">
                                <div class="scenario mb-3">
                                    <h5 class="fw-bold text-success">With Property Agents</h5>
                                    <p>When you engage with our network of real estate professionals, we share relevant information to facilitate property transactions.</p>
                                </div>
                                <div class="scenario mb-3">
                                    <h5 class="fw-bold text-success">With Service Providers</h5>
                                    <p>We may share information with third-party service providers who assist in operating our platform (payment processors, analytics services, etc.).</p>
                                </div>
                                <div class="scenario mb-3">
                                    <h5 class="fw-bold text-warning">Legal Requirements</h5>
                                    <p>We may disclose information if required by law or to protect our rights, property, or safety.</p>
                                </div>
                            </div>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">4. Data Security</h3>
                            <p>We implement appropriate security measures to protect your information:</p>
                            <div class="security-measures mt-3 bg-light p-3 rounded">
                                <ul>
                                    <li><i class="fas fa-lock text-primary me-2"></i>256-bit SSL encryption for data transmission</li>
                                    <li><i class="fas fa-shield-alt text-primary me-2"></i>Secure password hashing and authentication</li>
                                    <li><i class="fas fa-database text-primary me-2"></i>Regular security audits and updates</li>
                                    <li><i class="fas fa-user-shield text-primary me-2"></i>Access controls and employee training</li>
                                    <li><i class="fas fa-backup text-primary me-2"></i>Regular data backups and recovery systems</li>
                                </ul>
                            </div>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">5. Your Rights and Choices</h3>
                            <p>You have the following rights regarding your personal information:</p>
                            <div class="user-rights mt-3">
                                <div class="right mb-3">
                                    <h5 class="fw-bold text-info">🔍 Access and Review</h5>
                                    <p>You can request access to and review the personal information we hold about you.</p>
                                </div>
                                <div class="right mb-3">
                                    <h5 class="fw-bold text-info">✏️ Correction and Updates</h5>
                                    <p>You can correct or update inaccurate or incomplete personal information.</p>
                                </div>
                                <div class="right mb-3">
                                    <h5 class="fw-bold text-info">🗑️ Deletion</h5>
                                    <p>You can request deletion of your personal information, subject to legal obligations.</p>
                                </div>
                                <div class="right mb-3">
                                    <h5 class="fw-bold text-info">📊 Portability</h5>
                                    <p>You can request a copy of your data in a structured, machine-readable format.</p>
                                </div>
                                <div class="right mb-3">
                                    <h5 class="fw-bold text-info">⚙️ Preferences</h5>
                                    <p>You can manage communication preferences and privacy settings.</p>
                                </div>
                            </div>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">6. Cookies and Tracking</h3>
                            <p>We use cookies and similar technologies to enhance your experience:</p>
                            <ul>
                                <li><strong>Essential Cookies:</strong> Required for basic site functionality</li>
                                <li><strong>Performance Cookies:</strong> Help us understand how our site is used</li>
                                <li><strong>Functional Cookies:</strong> Remember your preferences and settings</li>
                                <li><strong>Marketing Cookies:</strong> Used to deliver relevant advertisements</li>
                            </ul>
                            <p class="mt-3">You can control cookie settings through your browser preferences.</p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">7. Data Retention</h3>
                            <p>We retain personal information for as long as necessary to:</p>
                            <ul>
                                <li>Fulfill the purposes for which it was collected</li>
                                <li>Comply with legal obligations</li>
                                <li>Resolve disputes and enforce our agreements</li>
                                <li>Fulfill legitimate business interests</li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">8. International Data Transfers</h3>
                            <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place for such transfers.</p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">9. Children's Privacy</h3>
                            <p>Our services are not intended for individuals under 18 years of age. We do not knowingly collect personal information from children.</p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">10. Changes to This Policy</h3>
                            <p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date.</p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3">11. Contact Us</h3>
                            <p>If you have any questions about this Privacy Policy or wish to exercise your rights, please contact us:</p>
                            <div class="contact-info bg-light p-3 rounded">
                                <p><strong>Email:</strong> privacy@apsdreamhome.com</p>
                                <p><strong>Phone:</strong> +91-XXXXXXXXXX</p>
                                <p><strong>Address:</strong> [Your Business Address]</p>
                                <p><strong>Data Protection Officer:</strong> dpo@apsdreamhome.com</p>
                            </div>
                        </section>
                    </div>

                    <div class="text-center mt-5">
                        <a href="<?php echo BASE_URL; ?>/terms" class="btn btn-outline-primary me-3">
                            <i class="fas fa-file-contract me-2"></i>Terms & Conditions
                        </a>
                        <a href="<?php echo BASE_URL; ?>/" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .privacy-content {
        max-width: 800px;
        margin: 0 auto;
        line-height: 1.8;
    }

    .privacy-content section {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 10px;
        border-left: 4px solid #28a745;
    }

    .privacy-content h3 {
        color: #28a745;
    }

    .info-types,
    .sharing-scenarios,
    .user-rights {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .info-type h5,
    .scenario h5,
    .right h5 {
        margin-bottom: 0.5rem;
    }

    .privacy-content ul {
        padding-left: 1.5rem;
    }

    .privacy-content li {
        margin-bottom: 0.5rem;
    }

    .contact-info {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }

    @media (max-width: 768px) {
        .privacy-content section {
            padding: 1.5rem;
        }

        .display-5 {
            font-size: 2rem;
        }

        .info-types,
        .sharing-scenarios,
        .user-rights {
            padding: 1rem;
        }
    }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/base.php';
echo $content;
?>