<?php require_once 'app/views/layouts/associate_header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/associate/dashboard">डैशबोर्ड</a></li>
                    <li class="breadcrumb-item active" aria-current="page">पेआउट मैनेजमेंट</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Payout Overview -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">अवेलेबल बैलेंस</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹<?= number_format($available_balance) ?>
                            </div>
                            <small class="text-muted">विद्ड्रॉ करने के लिए</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">मिनिमम पेआउट</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹<?= number_format($minimum_payout) ?>
                            </div>
                            <small class="text-muted">न्यूनतम राशि</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">पेंडिंग रिक्वेस्ट्स</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($payout_history) ?>
                            </div>
                            <small class="text-muted">प्रोसेसिंग में</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payout Request Form -->
    <?php if ($available_balance >= $minimum_payout): ?>
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-money-bill-wave mr-2"></i>पेआउट रिक्वेस्ट करें
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/associate/request-payout" id="payoutForm">
                        <div class="form-group mb-3">
                            <label for="amount" class="form-label">विद्ड्रॉ अमाउंट <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₹</span>
                                </div>
                                <input type="number"
                                       class="form-control"
                                       id="amount"
                                       name="amount"
                                       min="<?= $minimum_payout ?>"
                                       max="<?= $available_balance ?>"
                                       step="100"
                                       required>
                            </div>
                            <small class="form-text text-muted">
                                मिनिमम: ₹<?= number_format($minimum_payout) ?> |
                                मैक्सिमम: ₹<?= number_format($available_balance) ?>
                            </small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="payment_method" class="form-label">पेमेंट मेथड <span class="text-danger">*</span></label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="">चुनें</option>
                                <option value="bank_transfer">बैंक ट्रांसफर</option>
                                <option value="upi">UPI</option>
                                <option value="paytm">Paytm</option>
                                <option value="phonepe">PhonePe</option>
                                <option value="google_pay">Google Pay</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="account_details" class="form-label">अकाउंट डिटेल्स <span class="text-danger">*</span></label>
                            <textarea class="form-control"
                                      id="account_details"
                                      name="account_details"
                                      rows="3"
                                      placeholder="अपना अकाउंट नंबर, UPI ID, या अन्य जरूरी डिटेल्स लिखें"
                                      required></textarea>
                            <small class="form-text text-muted">
                                उदाहरण: Bank Account: 1234567890, IFSC: ABCD1234<br>
                                UPI: yourname@upi
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            पेआउट रिक्वेस्ट सबमिट करने के बाद, हम इसे 24-48 घंटों में प्रोसेस करेंगे।
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-paper-plane mr-2"></i>पेआउट रिक्वेस्ट सबमिट करें
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calculator mr-2"></i>पेआउट कैल्कुलेटर
                    </h6>
                </div>
                <div class="card-body">
                    <div class="payout-calculator">
                        <div class="form-group mb-3">
                            <label class="form-label">एंटर अमाउंट</label>
                            <input type="number" class="form-control" id="calcAmount" placeholder="अमाउंट डालें">
                        </div>

                        <div class="calculation-results mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>रिक्वेस्टेड अमाउंट:</span>
                                <span id="requestedAmount">₹0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>प्रोसेसिंग फीस (2%):</span>
                                <span id="processingFee">₹0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>TDS डिडक्शन (5%):</span>
                                <span id="tdsDeduction">₹0</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between font-weight-bold">
                                <span>आपको मिलेगा:</span>
                                <span id="finalAmount" class="text-success">₹0</span>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3">
                            <small>
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                नोट: प्रोसेसिंग फीस और TDS डिडक्शन लागू हो सकते हैं
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payout History -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-2"></i>पेआउट हिस्ट्री
                    </h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-filter mr-1"></i>फिल्टर
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" onclick="filterPayouts('all')">सभी</a>
                            <a class="dropdown-item" href="#" onclick="filterPayouts('pending')">पेंडिंग</a>
                            <a class="dropdown-item" href="#" onclick="filterPayouts('completed')">कंप्लीटेड</a>
                            <a class="dropdown-item" href="#" onclick="filterPayouts('rejected')">रिजेक्टेड</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($payout_history)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>रिक्वेस्ट डेट</th>
                                        <th>अमाउंट</th>
                                        <th>पेमेंट मेथड</th>
                                        <th>स्टेटस</th>
                                        <th>प्रोसेसिंग डेट</th>
                                        <th>एक्शन</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payout_history as $payout): ?>
                                        <tr>
                                            <td>
                                                <?= date('d M Y', strtotime($payout['request_date'])) ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?= date('h:i A', strtotime($payout['request_date'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold">₹<?= number_format($payout['amount']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= ucfirst(str_replace('_', ' ', $payout['payment_method'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $payout['status'];
                                                $statusClass = 'warning';
                                                $statusIcon = 'clock';

                                                if ($status === 'completed') {
                                                    $statusClass = 'success';
                                                    $statusIcon = 'check';
                                                } elseif ($status === 'rejected') {
                                                    $statusClass = 'danger';
                                                    $statusIcon = 'times';
                                                }
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <i class="fas fa-<?= $statusIcon ?> mr-1"></i>
                                                    <?= ucfirst($status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($payout['payout_date']): ?>
                                                    <?= date('d M Y', strtotime($payout['payout_date'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($status === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                                            onclick="cancelPayout(<?= $payout['id'] ?>)">
                                                        <i class="fas fa-times mr-1"></i>कैंसल
                                                    </button>
                                                <?php elseif ($status === 'completed'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success"
                                                            onclick="downloadReceipt(<?= $payout['id'] ?>)">
                                                        <i class="fas fa-download mr-1"></i>रिसीप्ट
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">कोई पेआउट हिस्ट्री नहीं मिली</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payout Guidelines -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>पेआउट गाइडलाइंस
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">✅ पेआउट के लिए कंडीशन्स</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success mr-2"></i>मिनिमम ₹<?= number_format($minimum_payout) ?> बैलेंस होना चाहिए</li>
                                <li><i class="fas fa-check text-success mr-2"></i>KYC वेरिफिकेशन कंप्लीट होना चाहिए</li>
                                <li><i class="fas fa-check text-success mr-2"></i>बैंक डिटेल्स अपडेटेड होने चाहिए</li>
                                <li><i class="fas fa-check text-success mr-2"></i>कोई पेंडिंग KYC डॉक्यूमेंट नहीं होना चाहिए</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-warning">⚠️ इम्पॉर्टेंट नोट्स</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-circle text-warning mr-2"></i>प्रोसेसिंग टाइम: 24-48 घंटे</li>
                                <li><i class="fas fa-circle text-warning mr-2"></i>TDS डिडक्शन लागू हो सकता है</li>
                                <li><i class="fas fa-circle text-warning mr-2"></i>रिजेक्टेड रिक्वेस्ट्स का रीफंड नहीं होता</li>
                                <li><i class="fas fa-circle text-warning mr-2"></i>सपोर्ट टीम से कन्फर्मेशन के बाद ही पेमेंट</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payout Calculator
    document.getElementById('calcAmount').addEventListener('input', function() {
        var amount = parseFloat(this.value) || 0;
        var processingFee = amount * 0.02; // 2% processing fee
        var tdsDeduction = amount * 0.05; // 5% TDS
        var finalAmount = amount - processingFee - tdsDeduction;

        document.getElementById('requestedAmount').textContent = '₹' + amount.toLocaleString();
        document.getElementById('processingFee').textContent = '₹' + processingFee.toFixed(2);
        document.getElementById('tdsDeduction').textContent = '₹' + tdsDeduction.toFixed(2);
        document.getElementById('finalAmount').textContent = '₹' + finalAmount.toFixed(2);
    });

    // Auto-fill amount in payout form
    document.querySelector('[data-target="maxAmount"]').addEventListener('click', function() {
        document.getElementById('amount').value = <?= $available_balance ?>;
        document.getElementById('calcAmount').value = <?= $available_balance ?>;
        document.getElementById('calcAmount').dispatchEvent(new Event('input'));
    });
});

function filterPayouts(status) {
    // This would implement filtering logic
    console.log('Filtering payouts by:', status);
}

function cancelPayout(payoutId) {
    if (confirm('क्या आप वाकई इस पेआउट रिक्वेस्ट को कैंसल करना चाहते हैं?')) {
        // This would implement cancel logic
        console.log('Cancelling payout:', payoutId);
    }
}

function downloadReceipt(payoutId) {
    // This would download receipt
    console.log('Downloading receipt for payout:', payoutId);
}

// Form validation
document.getElementById('payoutForm').addEventListener('submit', function(e) {
    var amount = parseFloat(document.getElementById('amount').value);
    var minAmount = <?= $minimum_payout ?>;
    var maxAmount = <?= $available_balance ?>;

    if (amount < minAmount) {
        alert('मिनिमम पेआउट अमाउंट ₹' + minAmount + ' होना चाहिए');
        e.preventDefault();
        return false;
    }

    if (amount > maxAmount) {
        alert('अवेलेबल बैलेंस से ज्यादा अमाउंट नहीं डाल सकते');
        e.preventDefault();
        return false;
    }

    return true;
});
</script>

<style>
.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e3e6f0;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-block {
    border-radius: 8px;
    padding: 0.75rem 2rem;
    font-weight: 600;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.8em;
}

.calculation-results {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.payout-calculator {
    max-height: 400px;
}

.alert {
    border-radius: 8px;
}

.input-group-text {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.text-success {
    color: #28a745 !important;
}

.text-info {
    color: #17a2b8 !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.btn-outline-success:hover,
.btn-outline-warning:hover,
.btn-outline-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

@media print {
    .btn, .card-header .btn-group {
        display: none !important;
    }
}
</style>

<?php require_once 'app/views/layouts/associate_footer.php'; ?>
