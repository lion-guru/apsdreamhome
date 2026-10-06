<?php
/**
 * Impersonation banner (shared partial).
 * Shown on portal layouts while an admin is using "Login as user".
 * Explains why the menu looks different and offers a one-click return.
 * Safe to include from any layout: renders nothing when not impersonating.
 */
$__impFrom = $_SESSION['impersonated_from'] ?? null;
if (!empty($__impFrom)):
    $__impBase = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
?>
<div style="background:#0d6efd;color:#fff;padding:8px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:14px;position:sticky;top:0;z-index:1050;">
    <span>
        <i class="fas fa-user-secret"></i>
        Admin <strong><?= htmlspecialchars($__impFrom['admin_name'] ?? '') ?></strong>
        viewing as <strong><?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['user_name'] ?? 'user') ?></strong>
        (<?= htmlspecialchars($_SESSION['role'] ?? '') ?>)
    </span>
    <a href="<?= $__impBase ?>/admin/users/stop-impersonation" class="btn btn-sm btn-light">
        <i class="fas fa-undo me-1"></i>Back to Admin
    </a>
</div>
<?php endif; ?>
