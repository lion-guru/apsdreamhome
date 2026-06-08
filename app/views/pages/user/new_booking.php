<?php
$page_title = $page_title ?? 'Book a Plot';
$current_page = 'bookings';
$user = $user ?? [];
$colonies = $colonies ?? [];
$plots = $plots ?? [];
$selectedColony = $selected_colony ?? 0;

$selectedPlotData = null;
if (!empty($_GET['plot_id']) && !empty($plots)) {
    foreach ($plots as $p) {
        if ((int)$p['id'] === (int)$_GET['plot_id']) {
            $selectedPlotData = $p;
            break;
        }
    }
}
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-map-marked-alt me-2"></i>Book a Plot</h2>
            <p>Select a colony, choose your preferred plot, and complete your booking in minutes.</p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>My Bookings
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2 <?= $selectedColony > 0 ? 'text-success' : 'text-primary' ?>">
                <span class="badge rounded-pill bg-<?= $selectedColony > 0 ? 'success' : 'primary' ?> d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">1</span>
                <span class="fw-semibold">Select Colony</span>
            </div>
            <i class="fas fa-chevron-right text-muted"></i>
            <div class="d-flex align-items-center gap-2 <?= !empty($_GET['plot_id']) ? 'text-success' : 'text-muted' ?>">
                <span class="badge rounded-pill bg-<?= !empty($_GET['plot_id']) ? 'success' : 'secondary' ?> d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">2</span>
                <span class="fw-semibold">Choose Plot</span>
            </div>
            <i class="fas fa-chevron-right text-muted"></i>
            <div class="d-flex align-items-center gap-2 text-muted">
                <span class="badge rounded-pill bg-secondary d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">3</span>
                <span class="fw-semibold">Confirm</span>
            </div>
        </div>
    </div>
</div>

<?php if (empty($colonies)): ?>
<div class="aps-cp-card">
    <div class="aps-cp-card-body">
        <div class="aps-cp-empty">
            <div class="aps-cp-empty-icon"><i class="fas fa-map-marked-alt"></i></div>
            <h5>No colonies available</h5>
            <p>There are no active colonies with available plots at the moment. Please check back later.</p>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary"><i class="fas fa-search me-2"></i>Browse Properties</a>
        </div>
    </div>
</div>
<?php else: ?>

<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-building text-primary me-2"></i>Select Colony</h5>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3">
            <?php foreach ($colonies as $colony): ?>
            <div class="col-md-6 col-lg-3">
                <a href="<?= BASE_URL ?>/user/bookings/new?colony_id=<?= (int)$colony['id'] ?>"
                   class="text-decoration-none">
                    <div class="aps-cp-card h-100 <?= (int)$colony['id'] === $selectedColony ? 'border-primary shadow' : '' ?>"
                         style="<?= (int)$colony['id'] === $selectedColony ? 'border: 2px solid #4f46e5;' : 'border: 1px solid var(--aps-cp-border);' ?>">
                        <div class="position-relative">
                            <?php if (!empty($colony['image_path'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($colony['image_path']) ?>"
                                     alt="<?= htmlspecialchars($colony['name']) ?>"
                                     class="w-100" style="height:120px; object-fit:cover; border-radius: 12px 12px 0 0;">
                            <?php else: ?>
                                <div class="w-100 d-flex align-items-center justify-content-center"
                                     style="height:120px; background: linear-gradient(135deg, #ede9fe, #dbeafe); border-radius: 12px 12px 0 0;">
                                    <i class="fas fa-building fa-2x text-primary opacity-50"></i>
                                </div>
                            <?php endif; ?>
                            <span class="badge bg-success position-absolute top-0 end-0 m-2">
                                <?= (int)($colony['available_plots'] ?? 0) ?> available
                            </span>
                        </div>
                        <div class="p-3">
                            <h6 class="mb-1 text-dark fw-bold"><?= htmlspecialchars($colony['name']) ?></h6>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars($colony['district_name'] ?? 'Location') ?>
                            </p>
                            <p class="mb-1 small">
                                <strong>Starting from ₹<?= number_format((float)($colony['starting_price'] ?? 0)) ?></strong>
                            </p>
                            <p class="text-muted small mb-0">
                                <?= (int)($colony['total_plots'] ?? 0) ?> total plots &middot; <?= (int)($colony['available_plots'] ?? 0) ?> open
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($selectedColony > 0): ?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-th-large text-success me-2"></i>Available Plots
            <?php if (!empty($plots)): ?>
                <small class="text-muted fw-normal ms-2">(<?= count($plots) ?> plots found)</small>
            <?php endif; ?>
        </h5>
    </div>
    <div class="aps-cp-card-body">
        <?php if (empty($plots)): ?>
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-th-large"></i></div>
                <h5>No available plots</h5>
                <p>All plots in this colony are currently booked or sold. Please select another colony.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="aps-cp-table">
                    <thead>
                        <tr>
                            <th>Plot No</th>
                            <th>Block</th>
                            <th>Area (sqft)</th>
                            <th>Dimensions</th>
                            <th>Facing</th>
                            <th>Price</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plots as $plot): ?>
                        <tr id="plot-row-<?= (int)$plot['id'] ?>">
                            <td><strong><?= htmlspecialchars($plot['plot_number']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($plot['block']) ?></span></td>
                            <td><?= number_format((float)$plot['area_sqft']) ?></td>
                            <td>
                                <?php if (!empty($plot['dimension_label'])): ?>
                                    <?= htmlspecialchars($plot['dimension_label']) ?>
                                <?php elseif (!empty($plot['width_ft']) && !empty($plot['length_ft'])): ?>
                                    <?= number_format((float)$plot['width_ft'], 0) ?> x <?= number_format((float)$plot['length_ft'], 0) ?> ft
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($plot['facing'] ?? '-') ?></td>
                            <td><strong>₹<?= number_format((float)$plot['total_price']) ?></strong></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-primary aps-select-plot"
                                        data-plot-id="<?= (int)$plot['id'] ?>"
                                        data-plot-no="<?= htmlspecialchars($plot['plot_number']) ?>"
                                        data-block="<?= htmlspecialchars($plot['block']) ?>"
                                        data-area="<?= number_format((float)$plot['area_sqft']) ?>"
                                        data-dims="<?= htmlspecialchars($plot['dimension_label'] ?: ($plot['width_ft'] . 'x' . $plot['length_ft'])) ?>"
                                        data-price="<?= number_format((float)$plot['total_price']) ?>"
                                        data-colony="<?= htmlspecialchars($plot['colony_name'] ?? '') ?>"
                                        data-facing="<?= htmlspecialchars($plot['facing'] ?? '-') ?>">
                                    <i class="fas fa-check me-1"></i>Book Now
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; border: none;">
                <h5 class="modal-title" id="bookingModalLabel">
                    <i class="fas fa-file-contract me-2"></i>Confirm Your Booking
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <h6 class="text-muted small mb-2">PLOT DETAILS</h6>
                            <p class="mb-1"><strong id="modal-colony"></strong></p>
                            <p class="mb-1">Plot: <strong id="modal-plot"></strong> (Block <span id="modal-block"></span>)</p>
                            <p class="mb-1">Area: <span id="modal-area"></span> sqft</p>
                            <p class="mb-1">Dimensions: <span id="modal-dims"></span></p>
                            <p class="mb-0">Facing: <span id="modal-facing"></span></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <h6 class="text-muted small mb-2">BOOKING SUMMARY</h6>
                            <p class="mb-1">Token Amount: <strong>₹25,000</strong></p>
                            <p class="mb-1">Total Price: <strong id="modal-price"></strong></p>
                            <p class="mb-0">Status: <span class="badge bg-primary">Token Paid</span></p>
                        </div>
                    </div>
                </div>

                <form id="bookingForm" method="POST" action="<?= BASE_URL ?>/user/bookings/create">
                    <input type="hidden" name="csrf_token" value="">
                    <input type="hidden" name="plot_id" id="modal-plot-id" value="">
                    <input type="hidden" name="notes" id="modal-notes" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea class="form-control" id="bookingNotes" rows="2" placeholder="Any special requirements or notes..."></textarea>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>
                        By confirming, you agree to pay the token amount of <strong>₹25,000</strong> towards the booking.
                        The remaining amount can be paid via EMI or lump sum as per your payment plan.
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="confirmBookingBtn">
                    <i class="fas fa-check me-2"></i>Confirm Booking
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-body p-5">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10" style="width:80px;height:80px;">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-2">Booking Confirmed!</h4>
                <p class="text-muted mb-3">Your plot has been successfully booked.</p>
                <div class="bg-light rounded-3 p-3 mb-4 text-start">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Booking Number:</span>
                        <strong id="success-booking-number"></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Plot:</span>
                        <strong id="success-plot"></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Colony:</span>
                        <strong id="success-colony"></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Amount:</span>
                        <strong id="success-amount"></strong>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <a id="success-confirmation-link" href="#" class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i>View Confirmation
                    </a>
                    <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>My Bookings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var baseUrl = '<?= BASE_URL ?>';

    document.querySelectorAll('.aps-select-plot').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('modal-plot-id').value = this.dataset.plotId;
            document.getElementById('modal-plot').textContent = this.dataset.plotNo;
            document.getElementById('modal-block').textContent = this.dataset.block;
            document.getElementById('modal-area').textContent = this.dataset.area;
            document.getElementById('modal-dims').textContent = this.dataset.dims;
            document.getElementById('modal-price').textContent = this.dataset.price;
            document.getElementById('modal-colony').textContent = this.dataset.colony;
            document.getElementById('modal-facing').textContent = this.dataset.facing;
            document.getElementById('modal-notes').value = '';

            var modal = new bootstrap.Modal(document.getElementById('bookingModal'));
            modal.show();
        });
    });

    document.getElementById('confirmBookingBtn').addEventListener('click', function() {
        var btn = this;
        var plotId = document.getElementById('modal-plot-id').value;
        if (!plotId) { alert('Please select a plot first.'); return; }

        document.getElementById('modal-notes').value = document.getElementById('bookingNotes').value;

        var tokenInput = document.querySelector('#bookingForm input[name="csrf_token"]');
        var metaToken = document.querySelector('meta[name="csrf-token"]');
        if (tokenInput && metaToken) { tokenInput.value = metaToken.content; }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

        var formData = new FormData(document.getElementById('bookingForm'));

        fetch(baseUrl + '/user/bookings/create', {
            method: 'POST',
            body: formData
        })
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();

                document.getElementById('success-booking-number').textContent = data.booking_number;
                document.getElementById('success-plot').textContent = data.plot;
                document.getElementById('success-colony').textContent = data.colony;
                document.getElementById('success-amount').textContent = '₹' + Number(data.total_amount).toLocaleString('en-IN');
                document.getElementById('success-confirmation-link').href = baseUrl + '/user/bookings/' + data.booking_id + '/confirmation';

                var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            } else {
                alert(data.error || 'Booking failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Booking';
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('An error occurred. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Booking';
        });
    });
});
</script>
