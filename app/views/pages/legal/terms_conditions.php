<?php
require_once __DIR__ . '/../../../Helpers/TranslationHelper.php';

$page_title = __('terms_conditions_title', [], 'Terms and Conditions - APS Dream Home');
$page_description = __('terms_conditions_meta_desc', [], 'Terms and conditions for using APS Dream Home real estate services');
$active_page = 'terms';

ob_start();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <h1 class="display-5 fw-bold text-primary"><?php echo __('tc_heading', [], 'Terms and Conditions'); ?></h1>
                        <p class="lead text-muted"><?php echo __('last_updated_on', [], 'Last updated: '); ?><?php echo date('F j, Y'); ?></p>
                    </div>

                    <div class="terms-content">
                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s1_title', [], '1. Acceptance of Terms'); ?></h3>
                            <p><?php echo __('tc_s1_desc', [], 'By accessing and using APS Dream Home services, you accept and agree to be bound by the terms and provision of this agreement. These Terms and Conditions apply to all users of the service.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s2_title', [], '2. Services Description'); ?></h3>
                            <p><?php echo __('tc_s2_desc', [], 'APS Dream Home provides a comprehensive real estate platform including:'); ?></p>
                            <ul>
                                <li><?php echo __('tc_s2_1', [], 'Property listing and search services'); ?></li>
                                <li><?php echo __('tc_s2_2', [], 'Real estate agent connections'); ?></li>
                                <li><?php echo __('tc_s2_3', [], 'Property viewing arrangements'); ?></li>
                                <li><?php echo __('tc_s2_4', [], 'Real estate consultation services'); ?></li>
                                <li><?php echo __('tc_s2_5', [], 'Digital property documentation'); ?></li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s3_title', [], '3. User Responsibilities'); ?></h3>
                            <p><?php echo __('tc_s3_desc', [], 'As a user of APS Dream Home, you agree to:'); ?></p>
                            <ul>
                                <li><?php echo __('tc_s3_1', [], 'Provide accurate and truthful information'); ?></li>
                                <li><?php echo __('tc_s3_2', [], 'Maintain confidentiality of your account credentials'); ?></li>
                                <li><?php echo __('tc_s3_3', [], 'Use the service for legitimate real estate purposes only'); ?></li>
                                <li><?php echo __('tc_s3_4', [], 'Respect the rights of other users and property owners'); ?></li>
                                <li><?php echo __('tc_s3_5', [], 'Comply with all applicable laws and regulations'); ?></li>
                            </ul>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s4_title', [], '4. Privacy and Data Protection'); ?></h3>
                            <p><?php echo __('tc_s4_desc', [], 'Your privacy is important to us. Our collection and use of personal information is governed by our Privacy Policy, which forms part of these Terms and Conditions.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s5_title', [], '5. Property Information'); ?></h3>
                            <p><?php echo __('tc_s5_desc', [], 'While we strive to provide accurate property information, APS Dream Home makes no warranties or representations about the completeness, accuracy, reliability, suitability, or availability of the property listings.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s6_title', [], '6. Agent and Associate Services'); ?></h3>
                            <p><?php echo __('tc_s6_desc', [], 'Our network of real estate users and users are independent contractors. APS Dream Home acts as a platform connecting users with qualified professionals but is not responsible for the actions or representations of individual users.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s7_title', [], '7. Payment Terms'); ?></h3>
                            <p><?php echo __('tc_s7_desc', [], 'For paid services, you agree to provide current, complete, and accurate payment information. You authorize us to charge the agreed-upon fees to your chosen payment method.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s8_title', [], '8. Intellectual Property'); ?></h3>
                            <p><?php echo __('tc_s8_desc', [], 'All content, trademarks, service marks, logos, and other intellectual property on the APS Dream Home platform are owned by or licensed to APS Dream Home and are protected by applicable intellectual property laws.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s9_title', [], '9. Limitation of Liability'); ?></h3>
                            <p><?php echo __('tc_s9_desc', [], 'APS Dream Home shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or related to your use of the service.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s10_title', [], '10. Termination'); ?></h3>
                            <p><?php echo __('tc_s10_desc', [], 'We may terminate or suspend your account and bar access to the service immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s11_title', [], '11. Changes to Terms'); ?></h3>
                            <p><?php echo __('tc_s11_desc', [], 'We reserve the right to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days notice prior to any new terms taking effect.'); ?></p>
                        </section>

                        <section class="mb-5">
                            <h3 class="h4 fw-bold mb-3"><?php echo __('tc_s12_title', [], '12. Contact Information'); ?></h3>
                            <p><?php echo __('tc_s12_desc', [], 'If you have any questions about these Terms and Conditions, please contact us at:'); ?></p>
                            <div class="contact-info bg-light p-3 rounded">
                                <p><strong><?php echo __('email', [], 'Email:'); ?></strong> legal@apsdreamhome.com</p>
                                <p><strong><?php echo __('phone', [], 'Phone:'); ?></strong> +91-XXXXXXXXXX</p>
                                <p><strong><?php echo __('address', [], 'Address:'); ?></strong> <?php echo __('business_address', [], '[Your Business Address]'); ?></p>
                            </div>
                        </section>
                    </div>

                    <div class="text-center mt-5">
                        <a href="<?php echo BASE_URL; ?>/privacy" class="btn btn-outline-primary me-3">
                            <i class="fas fa-shield-alt me-2"></i><?php echo __('privacy_policy', [], 'Privacy Policy'); ?>
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
    .terms-content { max-width: 800px; margin: 0 auto; line-height: 1.8; }
    .terms-content section { background: #f8f9fa; padding: 2rem; border-radius: 10px; border-left: 4px solid #007bff; }
    .terms-content h3 { color: #007bff; }
    .terms-content ul { padding-left: 1.5rem; }
    .terms-content li { margin-bottom: 0.5rem; }
    .contact-info { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important; }
    @media (max-width: 768px) {
        .terms-content section { padding: 1.5rem; }
        .display-5 { font-size: 2rem; }
    }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/base.php';
echo $content;
?>
