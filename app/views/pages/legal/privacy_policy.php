<?php
require_once __DIR__ . '/../../../Helpers/TranslationHelper.php';

$page_title = __('privacy_policy_title', [], 'Privacy Policy - APS Dream Home');
$page_description = __('privacy_policy_meta_desc', [], 'Privacy policy for APS Dream Home real estate services and data protection');
$active_page = 'privacy';

ob_start();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <h1 class="display-5 fw-bold text-primary"><?php echo __('privacy_policy_heading', [], 'Privacy Policy'); ?></h1>
                        <p class="lead text-muted"><?php echo __('last_updated_on', [], 'Last updated: '); ?><?php echo date('F j, Y'); ?></p>
                    </div>

                    <div class="privacy-content">
                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s1_title', [], '1. Information We Collect'); ?></h3>
                            <p><?php echo __('pp_s1_desc', [], 'APS Dream Home collects various types of information to provide and improve our services:'); ?></p>
                            <div class="info-types mt-3">
                                <div class="info-type mb-3">
                                    <h5 class="fw-bold text-primary"><?php echo __('pp_personal_info', [], 'Personal Information'); ?></h5>
                                    <ul>
                                        <li><?php echo __('pp_pi_1', [], 'Name, email address, phone number'); ?></li>
                                        <li><?php echo __('pp_pi_2', [], 'Physical address and location data'); ?></li>
                                        <li><?php echo __('pp_pi_3', [], 'Professional information and role'); ?></li>
                                        <li><?php echo __('pp_pi_4', [], 'Communication preferences'); ?></li>
                                    </ul>
                                </div>
                                <div class="info-type mb-3">
                                    <h5 class="fw-bold text-primary"><?php echo __('pp_property_pref', [], 'Property Preferences'); ?></h5>
                                    <ul>
                                        <li><?php echo __('pp_pp_1', [], 'Property type preferences'); ?></li>
                                        <li><?php echo __('pp_pp_2', [], 'Budget and financing information'); ?></li>
                                        <li><?php echo __('pp_pp_3', [], 'Location preferences'); ?></li>
                                        <li><?php echo __('pp_pp_4', [], 'Property search history'); ?></li>
                                    </ul>
                                </div>
                                <div class="info-type mb-3">
                                    <h5 class="fw-bold text-primary"><?php echo __('pp_usage_data', [], 'Usage Data'); ?></h5>
                                    <ul>
                                        <li><?php echo __('pp_ud_1', [], 'Pages visited and time spent'); ?></li>
                                        <li><?php echo __('pp_ud_2', [], 'Search queries and filters used'); ?></li>
                                        <li><?php echo __('pp_ud_3', [], 'Device and browser information'); ?></li>
                                        <li><?php echo __('pp_ud_4', [], 'IP address and location data'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s2_title', [], '2. How We Use Your Information'); ?></h3>
                            <p><?php echo __('pp_s2_desc', [], 'We use the collected information for various purposes:'); ?></p>
                            <ul>
                                <li><strong><?php echo __('pp_s2_service', [], 'Service Provision:'); ?></strong> <?php echo __('pp_s2_service_desc', [], 'To provide and maintain our real estate services'); ?></li>
                                <li><strong><?php echo __('pp_s2_personalize', [], 'Personalization:'); ?></strong> <?php echo __('pp_s2_personalize_desc', [], 'To tailor property recommendations and user experience'); ?></li>
                                <li><strong><?php echo __('pp_s2_comm', [], 'Communication:'); ?></strong> <?php echo __('pp_s2_comm_desc', [], 'To respond to inquiries and send relevant updates'); ?></li>
                                <li><strong><?php echo __('pp_s2_match', [], 'Matching:'); ?></strong> <?php echo __('pp_s2_match_desc', [], 'To connect users with suitable properties and users'); ?></li>
                                <li><strong><?php echo __('pp_s2_analytics', [], 'Analytics:'); ?></strong> <?php echo __('pp_s2_analytics_desc', [], 'To analyze usage patterns and improve our services'); ?></li>
                                <li><strong><?php echo __('pp_s2_legal', [], 'Legal Compliance:'); ?></strong> <?php echo __('pp_s2_legal_desc', [], 'To comply with legal obligations and protect our rights'); ?></li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s3_title', [], '3. Information Sharing'); ?></h3>
                            <p><?php echo __('pp_s3_desc', [], 'We may share your information under specific circumstances:'); ?></p>
                            <div class="sharing-scenarios mt-3">
                                <div class="scenario mb-3">
                                    <h5 class="fw-bold text-success"><?php echo __('pp_s3_property_users', [], 'With Property Users'); ?></h5>
                                    <p><?php echo __('pp_s3_property_users_desc', [], 'When you engage with our network of real estate professionals, we share relevant information to facilitate property transactions.'); ?></p>
                                </div>
                                <div class="scenario mb-3">
                                    <h5 class="fw-bold text-success"><?php echo __('pp_s3_service_prov', [], 'With Service Providers'); ?></h5>
                                    <p><?php echo __('pp_s3_service_prov_desc', [], 'We may share information with third-party service providers who assist in operating our platform (payment processors, analytics services, etc.).'); ?></p>
                                </div>
                                <div class="scenario mb-3">
                                    <h5 class="fw-bold text-warning"><?php echo __('pp_s3_legal_req', [], 'Legal Requirements'); ?></h5>
                                    <p><?php echo __('pp_s3_legal_req_desc', [], 'We may disclose information if required by law or to protect our rights, property, or safety.'); ?></p>
                                </div>
                            </div>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s4_title', [], '4. Data Security'); ?></h3>
                            <p><?php echo __('pp_s4_desc', [], 'We implement appropriate security measures to protect your information:'); ?></p>
                            <div class="security-measures mt-3 bg-light p-3 rounded">
                                <ul>
                                    <li><i class="fas fa-lock text-primary me-2"></i><?php echo __('pp_s4_ssl', [], '256-bit SSL encryption for data transmission'); ?></li>
                                    <li><i class="fas fa-shield-alt text-primary me-2"></i><?php echo __('pp_s4_password', [], 'Secure password hashing and authentication'); ?></li>
                                    <li><i class="fas fa-database text-primary me-2"></i><?php echo __('pp_s4_audit', [], 'Regular security audits and updates'); ?></li>
                                    <li><i class="fas fa-user-shield text-primary me-2"></i><?php echo __('pp_s4_access', [], 'Access controls and employee training'); ?></li>
                                    <li><i class="fas fa-backup text-primary me-2"></i><?php echo __('pp_s4_backup', [], 'Regular data backups and recovery systems'); ?></li>
                                </ul>
                            </div>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s5_title', [], '5. Your Rights and Choices'); ?></h3>
                            <p><?php echo __('pp_s5_desc', [], 'You have the following rights regarding your personal information:'); ?></p>
                            <div class="user-rights mt-3">
                                <div class="right mb-3">
                                    <h5 class="fw-bold text-info"><?php echo __('pp_s5_access', [], 'Access and Review'); ?></h5>
                                    <p><?php echo __('pp_s5_access_desc', [], 'You can request access to and review the personal information we hold about you.'); ?></p>
                                </div>
                                <div class="right mb-3">
                                    <h5 class="fw-bold text-info"><?php echo __('pp_s5_correction', [], 'Correction and Updates'); ?></h5>
                                    <p><?php echo __('pp_s5_correction_desc', [], 'You can correct or update inaccurate or incomplete personal information.'); ?></p>
                                </div>
                                <div class="right mb-3">
                                    <h5 class="fw-bold text-info"><?php echo __('pp_s5_deletion', [], 'Deletion'); ?></h5>
                                    <p><?php echo __('pp_s5_deletion_desc', [], 'You can request deletion of your personal information, subject to legal obligations.'); ?></p>
                                </div>
                                <div class="right mb-3">
                                    <h5 class="fw-bold text-info"><?php echo __('pp_s5_portability', [], 'Portability'); ?></h5>
                                    <p><?php echo __('pp_s5_portability_desc', [], 'You can request a copy of your data in a structured, machine-readable format.'); ?></p>
                                </div>
                                <div class="right mb-3">
                                    <h5 class="fw-bold text-info"><?php echo __('pp_s5_preferences', [], 'Preferences'); ?></h5>
                                    <p><?php echo __('pp_s5_preferences_desc', [], 'You can manage communication preferences and privacy settings.'); ?></p>
                                </div>
                            </div>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s6_title', [], '6. Cookies and Tracking'); ?></h3>
                            <p><?php echo __('pp_s6_desc', [], 'We use cookies and similar technologies to enhance your experience:'); ?></p>
                            <ul>
                                <li><strong><?php echo __('pp_s6_essential', [], 'Essential Cookies:'); ?></strong> <?php echo __('pp_s6_essential_desc', [], 'Required for basic site functionality'); ?></li>
                                <li><strong><?php echo __('pp_s6_performance', [], 'Performance Cookies:'); ?></strong> <?php echo __('pp_s6_performance_desc', [], 'Help us understand how our site is used'); ?></li>
                                <li><strong><?php echo __('pp_s6_functional', [], 'Functional Cookies:'); ?></strong> <?php echo __('pp_s6_functional_desc', [], 'Remember your preferences and settings'); ?></li>
                                <li><strong><?php echo __('pp_s6_marketing', [], 'Marketing Cookies:'); ?></strong> <?php echo __('pp_s6_marketing_desc', [], 'Used to deliver relevant advertisements'); ?></li>
                            </ul>
                            <p class="mt-3"><?php echo __('pp_s6_control', [], 'You can control cookie settings through your browser preferences.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s7_title', [], '7. Data Retention'); ?></h3>
                            <p><?php echo __('pp_s7_desc', [], 'We retain personal information for as long as necessary to:'); ?></p>
                            <ul>
                                <li><?php echo __('pp_s7_1', [], 'Fulfill the purposes for which it was collected'); ?></li>
                                <li><?php echo __('pp_s7_2', [], 'Comply with legal obligations'); ?></li>
                                <li><?php echo __('pp_s7_3', [], 'Resolve disputes and enforce our agreements'); ?></li>
                                <li><?php echo __('pp_s7_4', [], 'Fulfill legitimate business interests'); ?></li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s8_title', [], '8. International Data Transfers'); ?></h3>
                            <p><?php echo __('pp_s8_desc', [], 'Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place for such transfers.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s9_title', [], '9. Children\'s Privacy'); ?></h3>
                            <p><?php echo __('pp_s9_desc', [], 'Our services are not intended for individuals under 18 years of age. We do not knowingly collect personal information from children.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s10_title', [], '10. Changes to This Policy'); ?></h3>
                            <p><?php echo __('pp_s10_desc', [], 'We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('pp_s11_title', [], '11. Contact Us'); ?></h3>
                            <p><?php echo __('pp_s11_desc', [], 'If you have any questions about this Privacy Policy or wish to exercise your rights, please contact us:'); ?></p>
                            <div class="contact-info bg-light p-3 rounded">
                                <p><strong><?php echo __('email', [], 'Email:'); ?></strong> privacy@apsdreamhome.com</p>
                                <p><strong><?php echo __('phone', [], 'Phone:'); ?></strong> +91-XXXXXXXXXX</p>
                                <p><strong><?php echo __('address', [], 'Address:'); ?></strong> <?php echo __('business_address', [], '[Your Business Address]'); ?></p>
                                <p><strong><?php echo __('dpo', [], 'Data Protection Officer:'); ?></strong> dpo@apsdreamhome.com</p>
                            </div>
                        </section>
                    </div>

                    <div class="text-center mt-5">
                        <a href="<?php echo BASE_URL; ?>/terms" class="btn btn-outline-primary me-3">
                            <i class="fas fa-file-contract me-2"></i><?php echo __('terms_and_conditions', [], 'Terms & Conditions'); ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i><?php echo __('back_to_home', [], 'Back to Home'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .privacy-content { max-width: 800px; margin: 0 auto; line-height: 1.8; }
    .privacy-content section { background: #f8f9fa; padding: 2rem; border-radius: 10px; border-left: 4px solid #28a745; }
    .privacy-content h3 { color: #28a745; }
    .info-types, .sharing-scenarios, .user-rights { background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid #dee2e6; }
    .info-type h5, .scenario h5, .right h5 { margin-bottom: 0.5rem; }
    .privacy-content ul { padding-left: 1.5rem; }
    .privacy-content li { margin-bottom: 0.5rem; }
    .contact-info { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important; }
    @media (max-width: 768px) {
        .privacy-content section { padding: 1.5rem; }
        .display-5 { font-size: 2rem; }
        .info-types, .sharing-scenarios, .user-rights { padding: 1rem; }
    }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/base.php';
echo $content;
?>
