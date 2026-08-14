<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay) ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay) ?>'); ?>
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i><?= __('booking_pay_token') ?></h4>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if ($flashMessage = $_SESSION['flash_message'] ?? null): ?>
                        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
                            <?= htmlspecialchars($flashMessage) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><?= __('booking_summary') ?></h5>
                            <table class="table table-sm">
                                <tr><th><?= __('booking_number') ?> #</th><td>#<?= $booking['id'] ?></td></tr>
                                <tr><th><?= __('booking_plot') ?></th><td>#<?= htmlspecialchars($booking['plot_number'] ?? '') ?> - <?= htmlspecialchars($booking['colony_name'] ?? '') ?></td></tr>
                                <tr><th><?= __('booking_total_price') ?></th><td><strong>&#8377;<?= number_format((float)$booking['total_amount'], 2) ?></strong></td></tr>
                                <tr><th><?= __('booking_token_required') ?></th><td><strong class="text-primary">&#8377;<?= number_format($requiredToken, 2) ?></strong></td></tr>
                                <tr><th><?= __('booking_already_paid') ?></th><td>&#8377;<?= number_format((float)$booking['amount'], 2) ?></td></tr>
                                <tr><th><?= __('booking_token_due') ?></th><td><span class="text-danger">&#8377;<?= number_format($tokenDue, 2) ?></span></td></tr>
                            </table>
                            <div class="progress mb-3" class="style-76750">
                                <div class="progress-bar bg-success" class="style-28108"><?= $tokenPercent ?>%</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5><?= __('booking_payment_details') ?></h5>
                            <form method="POST" action="<?= BASE_URL ?>/booking/pay/<?= $booking['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="mb-3">
                                    <label class="form-label"><?= __('booking_amount') ?> (&#8377;)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">&#8377;</span>
                                        <input type="number" step="0.01" name="amount" id="payAmount" class="form-control form-control-lg" 
                                            value="<?= number_format($tokenDue > 0 ? $tokenDue : $requiredToken, 2) ?>" 
                                            min="1" max="<?= $requiredToken ?>" required>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('payAmount').value='<?= number_format($requiredToken / 2, 2) ?>'">50% (&#8377;<?= number_format($requiredToken / 2) ?>)</button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="document.getElementById('payAmount').value='<?= number_format($requiredToken, 2) ?>'">Full Token (&#8377;<?= number_format($requiredToken) ?>)</button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __('booking_payment_mode') ?></label>
                                    <select name="mode" class="form-select" required>
                                        <option value="online"><?= __('payment_mode_online') ?></option>
                                        <option value="bank_transfer"><?= __('payment_mode_bank') ?></option>
                                        <option value="cash"><?= __('payment_mode_cash') ?></option>
                                        <option value="cheque"><?= __('payment_mode_cheque') ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?= __('booking_transaction_ref') ?></label>
                                    <input type="text" name="reference" class="form-control" placeholder="<?= __('booking_transaction_placeholder') ?>">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        <?= __('booking_terms_agree') ?>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-check-circle me-2"></i>Pay &#8377;<span id="payDisplay"><?= number_format($tokenDue > 0 ? $tokenDue : $requiredToken) ?></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <hr>
                    <h6><?= __('booking_payment_instructions') ?></h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light mb-2">
                                <div class="card-body py-2">
                                    <small><strong><?= __('payment_bank_transfer') ?>:</strong><br>
                                    Account: APS Dream Home<br>
                                    IFSC: Check with office<br>
                                    Account: Contact support for details</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-2">
                                <div class="card-body py-2">
                                    <small><strong><?= __('payment_office_visit') ?>:</strong><br>
                                    <?= __('payment_office_desc') ?><br>
                                    <?= __('common_call') ?> <?= htmlspecialchars($phoneDisplay) ?> <?= __('common_for_details') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('payAmount').addEventListener('input', function() {
    document.getElementById('payDisplay').textContent = parseInt(this.value || 0).toLocaleString('en-IN');
});
</script>