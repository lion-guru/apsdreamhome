<?php $page_title = $page_title ?? __('pen_emi_penalty'); $page_heading = $page_heading ?? __('pen_emi_penalty'); $summary = $summary ?? []; ?>
<style>
.badge-risk-grace {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    display: inline-block;
}
.badge-risk-mild {
    background-color: #fef9c3;
    color: #854d0e;
    border: 1px solid #fef08a;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    display: inline-block;
}
.badge-risk-moderate {
    background-color: #ffedd5;
    color: #c2410c;
    border: 1px solid #fed7aa;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    display: inline-block;
}
.badge-risk-high {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.badge-risk-high.pulse-danger::after {
    content: '';
    width: 6px;
    height: 6px;
    background-color: #dc2626;
    border-radius: 50%;
    display: inline-block;
    animation: pulse-red-dot 1.5s infinite;
}

@keyframes pulse-red-dot {
    0% {
        transform: scale(0.9);
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
    }
    70% {
        transform: scale(1.1);
        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0);
    }
    100% {
        transform: scale(0.9);
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
    }
}
</style>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i><?= __('pen_emi_penalty') ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i><?= __('pen_back_cash_book') ?></a>
    </div>

    <p class="text-muted mb-4"><?= __('pen_description') ?></p>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?= __('pen_overdue_installments') ?></div>
                    <div class="aps-cp-stat-value text-danger"><?= (int)($summary['total_overdue_count'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?= __('pen_total_overdue_amount') ?></div>
                    <div class="aps-cp-stat-value text-danger">â‚¹<?= number_format((float)($summary['total_overdue_amount'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?= __('pen_accrued_penalties') ?></div>
                    <div class="aps-cp-stat-value text-warning">â‚¹<?= number_format((float)($summary['total_accrued_penalties'] ?? 0), 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?= __('pen_worst_overdue_days') ?></div>
                    <div class="aps-cp-stat-value text-danger"><?= (int)($summary['worst_overdue_days'] ?? 0) ?>d</div>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-play-circle me-1"></i><?= __('pen_apply_now') ?></span>
        </div>
        <div class="aps-cp-card-body">
            <p class="mb-2"><?= __('pen_apply_description') ?></p>
            <form id="penaltyForm" method="post" action="<?= BASE_URL ?>/admin/finance/penalties/apply">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            </form>
            <button id="applyPenaltiesBtn" class="btn btn-warning" onclick="applyPenalties()">
                <i class="fas fa-calculator me-1"></i><?= __('pen_apply_now') ?>
            </button>
            <div id="penaltyResult" class="mt-3" class="style-2248"></div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-list me-1"></i><?= __('pen_overdue_installments') ?></span>
        </div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= __('pen_booking') ?></th>
                        <th><?= __('pen_plot') ?></th>
                        <th><?= __('pen_customer') ?></th>
                        <th><?= __('pen_installment_no') ?></th>
                        <th><?= __('pen_due_date') ?></th>
                        <th class="text-center"><?= __('pen_days_overdue') ?></th>
                        <th class="text-end"><?= __('pen_amount') ?></th>
                        <th class="text-end"><?= __('pen_accrued_penalty') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php $items = $summary['overdue_installments'] ?? []; if (empty($items)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><?= __('pen_no_overdue') ?></td></tr>
                <?php else: foreach ($items as $item): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($item['booking_number'] ?? 'BK-' . $item['booking_id']) ?></code></td>
                        <td><?= htmlspecialchars($item['plot_number'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($item['customer_name'] ?? '-') ?></td>
                        <td class="text-center">#<?= (int)$item['installment_no'] ?></td>
                        <td><?= htmlspecialchars($item['due_date'] ?? '') ?></td>
                        <td class="text-center">
                            <?php
                            $days = (int)$item['days_overdue'];
                            $cls = 'badge-risk-high pulse-danger';
                            if ($days <= 5) $cls = 'badge-risk-grace';
                            elseif ($days <= 15) $cls = 'badge-risk-mild';
                            elseif ($days <= 30) $cls = 'badge-risk-moderate';
                            ?>
                            <span class="<?= $cls ?>"><?= $days ?> <?= __('pen_days') ?></span>
                        </td>
                        <td class="text-end">â‚¹<?= number_format((float)$item['amount'], 2) ?></td>
                        <td class="text-end fw-bold text-warning">â‚¹<?= number_format((float)$item['accrued_penalty'], 2) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<script>
function applyPenalties() {
    var btn = document.getElementById('applyPenaltiesBtn');
    var resultDiv = document.getElementById('penaltyResult');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i><?= __('pen_processing') ?>';

    var token = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = token ? token.getAttribute('content') : '';
    if (!csrfToken) {
        var input = document.querySelector('#penaltyForm input[name="csrf_token"]');
        if (!input) input = document.querySelector('input[name="csrf_token"]');
        csrfToken = input ? input.value : '';
    }

    fetch('<?= BASE_URL ?>/admin/finance/penalties/apply', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': csrfToken
        },
        body: 'csrf_token=' + encodeURIComponent(csrfToken)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        resultDiv.style.display = 'block';
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-1"></i>' +
                '<?= __('pen_applied') ?> <strong>' + data.penalties_applied + '</strong> <?= __('pen_penalties_totaling') ?> <strong>â‚¹' +
                Number(data.total_penalty).toLocaleString('en-IN', {minimumFractionDigits:2}) + '</strong>. ' +
                '<?= __('pen_will_reload') ?></div>';
            setTimeout(function() { location.reload(); }, 2000);
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-1"></i><?= __('pen_error') ?> ' +
                (data.error || 'Unknown error') + '</div>';
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-calculator me-1"></i><?= __('pen_apply_now') ?>';
    })
    .catch(function(err) {
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-1"></i><?= __('pen_request_failed') ?> ' + err.message + '</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-calculator me-1"></i><?= __('pen_apply_now') ?>';
    });
}
</script>
