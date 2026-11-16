<?php
/**
 * Shared flash renderer.
 * Consumes both $_SESSION flash families:
 *   - legacy  : flash_success / flash_error / flash_info
 *   - current : success / error / info (BaseController trait HasFlashMessages::setFlash)
 * One consumer only - whoever includes this consumes the keys.
 */
?>
<div class="flash-container px-3 pt-3" id="aps-flash-region" role="status" aria-live="polite">
<?php foreach (['success'=>'alert-success','error'=>'alert-danger','info'=>'alert-info','flash_success'=>'alert-success','flash_error'=>'alert-danger','flash_info'=>'alert-info'] as $key => $cls): ?>
    <?php if (!empty($_SESSION[$key])): ?>
    <div class="alert <?php echo $cls; ?> alert-dismissible fade show my-2" role="alert">
        <i class="fas <?php echo $cls === 'alert-success' ? 'fa-check-circle' : ($cls === 'alert-info' ? 'fa-info-circle' : 'fa-exclamation-circle'); ?> me-2"></i>
        <?php echo htmlspecialchars($_SESSION[$key] ?? '', ENT_QUOTES, 'UTF-8'); unset($_SESSION[$key]); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss"></button>
    </div>
    <?php endif; ?>
<?php endforeach; ?>
</div>
