<?php
/**
 * Mobile-Responsive Table Component
 * Automatically converts tables to card layout on mobile devices
 */

// Default values
$tableClass = $tableClass ?? 'table-bordered';
$headerClass = $headerClass ?? 'table-light';
?>

<div class="mobile-table-container">
    <!-- Desktop Table View -->
    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table <?php echo htmlspecialchars($tableClass); ?>">
                <?php if(isset($headers)): ?>
                    <thead class="<?php echo htmlspecialchars($headerClass); ?>">
                        <tr>
                            <?php foreach($headers as $header): ?>
                                <th <?php echo isset($header['width']) ? 'style="width: ' . htmlspecialchars($header['width']) . '"' : ''; ?>>
                                    <?php echo htmlspecialchars($header['title']); ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                <?php endif; ?>
                <tbody>
                    <?php if(isset($tableRows)): echo $tableRows; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="d-md-none">
        <div class="mobile-cards">
            <?php if(isset($mobileCards)): echo $mobileCards; endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if(isset($pagination)): ?>
        <div class="d-flex justify-content-center mt-4">
            <?php echo $pagination->appends(request()->query())->links(); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Mobile Table Styles -->
<style>
@media (max-width: 768px) {
    .mobile-cards {
        display: block;
    }

    .mobile-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        padding: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: box-shadow 0.15s ease-in-out;
    }

    .mobile-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .mobile-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .mobile-card-title {
        font-weight: 600;
        font-size: 1rem;
        color: #495057;
        margin: 0;
        flex-grow: 1;
    }

    .mobile-card-status {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .mobile-card-status.present,
    .mobile-card-status.active,
    .mobile-card-status.approved,
    .mobile-card-status.completed {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .mobile-card-status.absent,
    .mobile-card-status.inactive,
    .mobile-card-status.rejected,
    .mobile-card-status.cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }

    .mobile-card-status.pending,
    .mobile-card-status.in_progress {
        background-color: #fff3cd;
        color: #856404;
    }

    .mobile-card-status.late,
    .mobile-card-status.overdue {
        background-color: #ffeaa7;
        color: #d63031;
    }

    .mobile-card-body {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    .mobile-card-field {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.25rem 0;
    }

    .mobile-card-field.border-bottom {
        border-bottom: 1px solid #f8f9fa;
    }

    .mobile-card-field:last-child {
        border-bottom: none;
    }

    .mobile-card-label {
        font-weight: 500;
        color: #6c757d;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0;
    }

    .mobile-card-value {
        font-weight: 400;
        color: #495057;
        text-align: right;
        flex-grow: 1;
    }

    .mobile-card-value.text-truncate {
        max-width: 60%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mobile-card-actions {
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #e9ecef;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .mobile-card-actions .btn {
        flex: 1;
        min-width: 80px;
        font-size: 0.875rem;
        padding: 0.5rem;
    }

    /* Two-column layout for some fields */
    .mobile-card-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .mobile-card-grid-2 .mobile-card-field {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .mobile-card-grid-2 .mobile-card-value {
        text-align: left;
        width: 100%;
    }

    /* Compact mode for dense data */
    .mobile-card-compact .mobile-card-body {
        gap: 0.25rem;
    }

    .mobile-card-compact .mobile-card-field {
        padding: 0.125rem 0;
    }

    /* Enhanced touch targets */
    .mobile-card-actions .btn {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}
</style>
