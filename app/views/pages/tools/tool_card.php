<?php

/**
 * Renders a single tool card for the Tools Hub.
 * Expects a $tool array with keys: url, gradient, icon, title_key, title_default, desc_key, desc_default.
 */
?>
<div class="col-md-6 col-lg-4">
    <a href="<?php echo e($tool['url']); ?>" class="text-decoration-none">
        <div class="card border-0 shadow h-100 style-67912">
            <div class="card-body text-white text-center p-4 d-flex flex-column justify-content-center">
                <i class="fas <?php echo e($tool['icon']); ?> fa-3x mb-3"></i>
                <h5 class="fw-bold"><?php echo __($tool['title_key'], [], $tool['title_default']); ?></h5>
                <p class="small mb-0 text-white-50"><?php echo __($tool['desc_key'], [], $tool['desc_default']); ?></p>
            </div>
        </div>
    </a>
</div>