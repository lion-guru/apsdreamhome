<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">मेरे पेमेंट्स</h4>
                            <p class="card-text mb-0">आपकी सभी ट्रांजेक्शन हिस्ट्री और पेमेंट रसीदें यहाँ उपलब्ध हैं।</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="h4 mb-0 font-weight-bold">कुल खर्च: ₹<?= number_format(array_sum(array_column($payments, 'amount'))) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form action="/customer/payments" method="GET" class="row">
                        <div class="col-md-2 mb-3">
                            <label class="small font-weight-bold">स्टेटस</label>
                            <select name="status" class="form-control">
                                <option value="">सभी</option>
                                <option value="success" <?= ($filters['status'] ?? '') == 'success' ? 'selected' : '' ?>>सफल</option>
                                <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>पेंडिंग</option>
                                <option value="failed" <?= ($filters['status'] ?? '') == 'failed' ? 'selected' : '' ?>>विफल</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">पेमेंट मेथड</label>
                            <select name="payment_method" class="form-control">
                                <option value="">सभी</option>
                                <option value="online" <?= ($filters['payment_method'] ?? '') == 'online' ? 'selected' : '' ?>>ऑनलाइन</option>
                                <option value="cash" <?= ($filters['payment_method'] ?? '') == 'cash' ? 'selected' : '' ?>>कैश</option>
                                <option value="cheque" <?= ($filters['payment_method'] ?? '') == 'cheque' ? 'selected' : '' ?>>चेक</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="small font-weight-bold">डेट (से)</label>
                            <input type="date" name="date_from" class="form-control" value="<?= h($filters['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="small font-weight-bold">डेट (तक)</label>
                            <input type="date" name="date_to" class="form-control" value="<?= h($filters['date_to'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">फिल्टर</button>
                            <a href="/customer/payments" class="btn btn-secondary">रीसेट</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="bg-light">
                                <tr>
                                    <th>ट्रांजेक्शन ID</th>
                                    <th>विवरण</th>
                                    <th>डेट</th>
                                    <th>मेथड</th>
                                    <th>अमाउंट</th>
                                    <th>स्टेटस</th>
                                    <th>रसीद</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($payments)): ?>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td class="font-weight-bold text-gray-800"><?= $payment['transaction_id'] ?></td>
                                            <td>
                                                <div class="font-weight-bold"><?= h($payment['property_title'] ?? 'Booking Payment') ?></div>
                                                <small class="text-muted">बुकिंग #<?= $payment['booking_id'] ?></small>
                                            </td>
                                            <td><?= date('d M Y, h:i A', strtotime($payment['payment_date'])) ?></td>
                                            <td>
                                                <span class="text-capitalize"><?= $payment['payment_method'] ?></span>
                                            </td>
                                            <td class="font-weight-bold text-success">₹<?= number_format($payment['amount']) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                $statusText = 'पेंडिंग';
                                                switch ($payment['status']) {
                                                    case 'success': $statusClass = 'success'; $statusText = 'सफल'; break;
                                                    case 'failed': $statusClass = 'danger'; $statusText = 'विफल'; break;
                                                    case 'refunded': $statusClass = 'warning'; $statusText = 'रिफंडेड'; break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($payment['status'] == 'success'): ?>
                                                    <a href="/customer/payment/receipt/<?= $payment['id'] ?>" class="btn btn-sm btn-info" target="_blank">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">कोई पेमेंट रिकॉर्ड नहीं मिला।</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
