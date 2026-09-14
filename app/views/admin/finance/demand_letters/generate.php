<?php $page_title = 'Generate Demand Letter'; ?>
<?php include __DIR__ . '/../../layouts/admin.php'; ?>

<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>Generate Demand Letter</h2>
            <a href="<?= BASE_URL ?>/admin/finance/demand-letters" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Demand Letters
            </a>
        </div>

        <div class="alert alert-info d-flex align-items-start mb-4">
            <i class="fas fa-info-circle me-2 mt-1"></i>
            <div>
                Select a booking and installment to generate a demand letter. The PDF will be created automatically and can be sent via WhatsApp.
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>/admin/finance/demand-letters/generate">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="mb-3">
                        <label for="booking_id" class="form-label">Booking <span class="text-danger">*</span></label>
                        <select name="booking_id" id="booking_id" class="form-select" required>
                            <option value="">Select Booking</option>
                            <?php foreach (($bookings ?? []) as $b): ?>
                                <option value="<?= (int)$b['id'] ?>">
                                    <?= htmlspecialchars($b['booking_number'] ?? '') ?> - <?= htmlspecialchars($b['customer_name'] ?? '') ?> (<?= number_format((float)($b['total_amount'] ?? 0), 0, '.', ',') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="installment-wrapper" style="display:none;">
                        <label for="installment_id" class="form-label">Installment <span class="text-danger">*</span></label>
                        <select name="installment_id" id="installment_id" class="form-select" required>
                            <option value="">Select Installment</option>
                            <?php foreach (($bookings ?? []) as $b): ?>
                                <?php foreach (($b['installments'] ?? []) as $inst): ?>
                                    <option value="<?= (int)($inst['id'] ?? 0) ?>" data-booking-id="<?= (int)$b['id'] ?>">
                                        Installment #<?= htmlspecialchars($inst['installment_number'] ?? $inst['id'] ?? '') ?> - <?= number_format((float)($inst['amount'] ?? 0), 0, '.', ',') ?> (Due: <?= htmlspecialchars($inst['due_date'] ?? '') ?>) - <?= htmlspecialchars($inst['status'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-pdf me-1"></i>Generate Demand Letter
                        </button>
                        <a href="<?= BASE_URL ?>/admin/finance/demand-letters" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('booking_id').addEventListener('change', function() {
    var bookingId = this.value;
    var wrapper = document.getElementById('installment-wrapper');
    var instSelect = document.getElementById('installment_id');
    var options = instSelect.querySelectorAll('option[data-booking-id]');

    for (var i = 0; i < options.length; i++) {
        options[i].style.display = (bookingId && options[i].getAttribute('data-booking-id') === bookingId) ? '' : 'none';
    }

    instSelect.value = '';
    wrapper.style.display = bookingId ? '' : 'none';
});
</script>
