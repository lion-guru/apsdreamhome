<?php
/**
 * Mobile-Optimized Dashboard Card Component
 * Responsive card component that adapts to mobile screens
 */

// Define component variables if not provided
$card_class = $card_class ?? '';
$icon = $icon ?? null;
$action = $action ?? null;
$title = $title ?? 'Card Title';
$action_url = $action_url ?? '#';
$action_text = $action_text ?? 'View';
?>

<div class="dashboard-card <?php echo htmlspecialchars($card_class); ?>">
    <div class="card shadow h-100">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <?php if($icon): ?>
                    <i class="bi bi-<?php echo htmlspecialchars($icon); ?> me-1"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($title); ?>
            </h6>
            <?php if($action): ?>
                <a href="<?php echo htmlspecialchars($action_url); ?>" class="btn btn-sm btn-outline-primary">
                    <?php if(isset($action_icon)): ?>
                        <i class="bi bi-<?php echo htmlspecialchars($action_icon); ?> me-1"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($action_text); ?>
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php echo $card_content ?? ''; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .dashboard-card {
        margin-bottom: 1rem;
    }

    .dashboard-card .card-header {
        padding: 1rem !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem;
    }

    .dashboard-card .card-header h6 {
        margin-bottom: 0 !important;
        text-align: center;
    }

    .dashboard-card .card-header .btn {
        align-self: center;
    }

    .dashboard-card .card-body {
        padding: 1rem !important;
    }

    /* Statistics cards grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    /* Activity feed mobile optimization */
    .activity-item {
        padding: 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
        background: #fff;
    }

    .activity-meta {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    /* Quick actions mobile */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }

    .quick-actions .btn {
        padding: 0.75rem;
        font-size: 0.875rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        min-height: 70px;
    }

    .quick-actions .btn i {
        font-size: 1.25rem;
    }
}
</style>
