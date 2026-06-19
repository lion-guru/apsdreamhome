<?php $page_title = $page_title ?? 'EMI Penalties'; $page_heading = $page_heading ?? 'EMI Penalty Engine'; $summary = $summary ?? []; ?>
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
        <h2 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>EMI Penalty Engine</h2>
        <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Cash Book</a>
    </div>

    <p class="text-muted mb-4">18% flat per annum (0.0493%/day) after 5-day grace period. Penalties accrue daily on overdue installments.</p>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Overdue Installments</div>
                    <div class="aps-cp-stat-value text-danger"><?= (int)($summary['total_overdue_count'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Total Overdue Amount</div>
                    <div class="aps-cp-stat-value text-danger">₹<?= number_format((float)($summary['total_overdue_amount'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Accrued Penalties</div>
                    <div class="aps-cp-stat-value text-warning">₹<?= number_format((float)($summary['total_accrued_penalties'] ?? 0), 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Worst Overdue (Days)</div>
                    <div class="aps-cp-stat-value text-danger"><?= (int)($summary['worst_overdue_days'] ?? 0) ?>d</div>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-play-circle me-1"></i>Apply Penalties Now</span>
        </div>
        <div class="aps-cp-card-body">
            <p class="mb-2">Click below to calculate and apply daily penalties to all overdue installments past the 5-day grace period.</p>
            <form id="penaltyForm" method="post" action="<?= BASE_URL ?>/admin/finance/penalties/apply">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            </form>
            <button id="applyPenaltiesBtn" class="btn btn-warning" onclick="applyPenalties()">
                <i class="fas fa-calculator me-1"></i>Apply Penalties Now
            </button>
            <div id="penaltyResult" class="mt-3" style="display:none;"></div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-list me-1"></i>Overdue Installments</span>
        </div>
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking</th>
                        <th>Plot</th>
                        <th>Customer</th>
                        <th>Installment #</th>
                        <th>Due Date</th>
                        <th class="text-center">Days Overdue</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Accrued Penalty</th>
                    </tr>
                </thead>
                <tbody>
                <?php $items = $summary['overdue_installments'] ?? []; if (empty($items)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No overdue installments past grace period</td></tr>
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
                            <span class="<?= $cls ?>"><?= $days ?> days</span>
                        </td>
                        <td class="text-end">₹<?= number_format((float)$item['amount'], 2) ?></td>
                        <td class="text-end fw-bold text-warning">₹<?= number_format((float)$item['accrued_penalty'], 2) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function applyPenalties() {
    var btn = document.getElementById('applyPenaltiesBtn');
    var resultDiv = document.getElementById('penaltyResult');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';

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
                'Applied <strong>' + data.penalties_applied + '</strong> penalties totaling <strong>₹' +
                Number(data.total_penalty).toLocaleString('en-IN', {minimumFractionDigits:2}) + '</strong>. ' +
                'Page will reload in 2 seconds.</div>';
            setTimeout(function() { location.reload(); }, 2000);
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-1"></i>Error: ' +
                (data.error || 'Unknown error') + '</div>';
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-calculator me-1"></i>Apply Penalties Now';
    })
    .catch(function(err) {
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-1"></i>Request failed: ' + err.message + '</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-calculator me-1"></i>Apply Penalties Now';
    });
}
</script>
