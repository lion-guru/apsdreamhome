<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-success text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">मेरी बुकिंग्स</h4>
                            <p class="card-text mb-0">आपकी सभी प्रॉपर्टी बुकिंग्स और साइट विजिट्स की स्थिति यहाँ देखें।</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="/customer/properties" class="btn btn-light">
                                <i class="fas fa-plus mr-1"></i> नई बुकिंग करें
                            </a>
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
                    <form action="/customer/bookings" method="GET" class="row">
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">स्टेटस</label>
                            <select name="status" class="form-control">
                                <option value="">सभी स्टेटस</option>
                                <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>पेंडिंग</option>
                                <option value="confirmed" <?= ($filters['status'] ?? '') == 'confirmed' ? 'selected' : '' ?>>कन्फर्म्ड</option>
                                <option value="cancelled" <?= ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>कैंसिल्ड</option>
                                <option value="completed" <?= ($filters['status'] ?? '') == 'completed' ? 'selected' : '' ?>>पूरा हुआ</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">डेट (से)</label>
                            <input type="date" name="date_from" class="form-control" value="<?= h($filters['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">डेट (तक)</label>
                            <input type="date" name="date_to" class="form-control" value="<?= h($filters['date_to'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">फिल्टर करें</button>
                            <a href="/customer/bookings" class="btn btn-secondary">रीसेट</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="bg-light">
                                <tr>
                                    <th>बुकिंग ID</th>
                                    <th>प्रॉपर्टी</th>
                                    <th>डेट</th>
                                    <th>टाइम</th>
                                    <th>अमाउंट</th>
                                    <th>स्टेटस</th>
                                    <th>एक्शन</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($bookings)): ?>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td class="font-weight-bold text-primary">#<?= $booking['id'] ?></td>
                                            <td>
                                                <div class="font-weight-bold"><?= h($booking['property_title']) ?></div>
                                                <small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i> <?= h($booking['city']) ?></small>
                                            </td>
                                            <td><?= date('d M Y', strtotime($booking['booking_date'])) ?></td>
                                            <td><?= date('h:i A', strtotime($booking['booking_time'])) ?></td>
                                            <td class="font-weight-bold text-success">₹<?= number_format($booking['amount'] ?? 0) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                $statusText = 'पेंडिंग';
                                                switch ($booking['status']) {
                                                    case 'confirmed': $statusClass = 'success'; $statusText = 'कन्फर्म्ड'; break;
                                                    case 'cancelled': $statusClass = 'danger'; $statusText = 'कैंसिल्ड'; break;
                                                    case 'completed': $statusClass = 'info'; $statusText = 'पूरा हुआ'; break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                            <td>
                                                <a href="/customer/booking/<?= $booking['id'] ?>" class="btn btn-sm btn-outline-primary mr-1" title="देखें">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($booking['status'] == 'pending'): ?>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="cancelBooking(<?= $booking['id'] ?>)" title="कैंसिल करें">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">कोई बुकिंग नहीं मिली।</td>
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

<script>
function cancelBooking(bookingId) {
    if (confirm('क्या आप वाकई इस बुकिंग को कैंसिल करना चाहते हैं?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/customer/cancel-booking/' + bookingId;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
