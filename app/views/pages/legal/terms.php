<?php
// app/views/pages/terms-of-service.php
?>

<!-- Hero Section -->
<section class="tos-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2"><?php echo __('tos_title', [], 'Terms of Service'); ?></h1>
        <p class="lead mb-0"><?php echo __('tos_subtitle', [], 'Please read our terms carefully before using our services'); ?></p>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <?php if (isset($crumb['url'])): ?>
                        <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= $crumb['title'] ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>
</div>

<?php if (!empty($pageContent)): ?>
<section class="py-4 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="cms-content p-4"><?php echo $pageContent; ?></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section-padding py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden" data-aos="fade-up">
                    <div class="card-body p-4 p-md-5">
                        <div class="tos-content">
                            <h4 class="fw-bold mb-3 text-primary"><?php echo __('tos_s1_title', [], '1. Acceptance of Terms'); ?></h4>
                            <p class="text-muted mb-4"><?php echo __('tos_s1_desc', [], 'By accessing and using APS Dream Home services, you accept and agree to be bound by the terms and provision of this agreement.'); ?></p>

                            <h4 class="fw-bold mb-3 text-primary"><?php echo __('tos_s2_title', [], '2. Use License'); ?></h4>
                            <p class="text-muted mb-4"><?php echo __('tos_s2_desc', [], 'Permission is granted to temporarily use APS Dream Home services for personal, non-commercial transitory viewing only.'); ?></p>

                            <h4 class="fw-bold mb-3 text-primary"><?php echo __('tos_s3_title', [], '3. Disclaimer'); ?></h4>
                            <p class="text-muted mb-4"><?php echo __('tos_s3_desc', [], 'The materials on APS Dream Home website are provided on an \'as is\' basis. APS Dream Home makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.'); ?></p>

                            <h4 class="fw-bold mb-3 text-primary"><?php echo __('tos_s4_title', [], '4. Limitations'); ?></h4>
                            <p class="text-muted mb-4"><?php echo __('tos_s4_desc', [], 'In no event shall APS Dream Home or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on APS Dream Home website.'); ?></p>

                            <h4 class="fw-bold mb-3 text-primary"><?php echo __('tos_s5_title', [], '5. Revisions and Errata'); ?></h4>
                            <p class="text-muted mb-4"><?php echo __('tos_s5_desc', [], 'The materials appearing on APS Dream Home website could include technical, typographical, or photographic errors. APS Dream Home does not warrant that any of the materials on its website are accurate, complete, or current.'); ?></p>

                            <h4 class="fw-bold mb-3 text-primary"><?php echo __('tos_s6_title', [], '6. Contact Information'); ?></h4>
                            <p class="text-muted mb-0"><?php echo __('tos_s6_desc', [], 'If you have any questions about these Terms of Service, please contact us at '); ?><a href="mailto:info@apsdreamhome.com" class="text-primary fw-bold">info@apsdreamhome.com</a></p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="100">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm">
                        <i class="fas fa-home me-2"></i> <?php echo __('back_to_home', [], 'Back to Home'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
