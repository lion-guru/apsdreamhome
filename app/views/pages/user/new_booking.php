<?php
$page_title = $page_title ?? __('user_new_booking_title', 'Book a Plot');
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
            <h2><i class="fas fa-map-marked-alt me-2"></i><?= __('user_new_booking_heading', 'Book a Plot') ?></h2>
            <p><?= __('user_new_booking_subtitle', 'Select a colony, choose your preferred plot, and complete your booking in minutes.') ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i><?= __('user_new_booking_my_bookings', 'My Bookings') ?>
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2 <?= $selectedColony > 0 ? 'text-success' : 'text-primary' ?>">
                <span class="badge rounded-pill bg-<?= $selectedColony > 0 ? 'success' : 'primary' ?> d-inline-flex align-items-center justify-content-center style-6984">1</span>
                <span class="fw-semibold"><?= __('user_new_booking_step_select_colony', 'Select Colony') ?></span>
            </div>
            <i class="fas fa-chevron-right text-muted"></i>
            <div class="d-flex align-items-center gap-2 <?= !empty($_GET['plot_id']) ? 'text-success' : 'text-muted' ?>">
                <span class="badge rounded-pill bg-<?= !empty($_GET['plot_id']) ? 'success' : 'secondary' ?> d-inline-flex align-items-center justify-content-center style-6984">2</span>
                <span class="fw-semibold"><?= __('user_new_booking_step_choose_plot', 'Choose Plot') ?></span>
            </div>
            <i class="fas fa-chevron-right text-muted"></i>
            <div class="d-flex align-items-center gap-2 text-muted">
                <span class="badge rounded-pill bg-secondary d-inline-flex align-items-center justify-content-center style-6984">3</span>
                <span class="fw-semibold"><?= __('user_new_booking_step_confirm', 'Confirm') ?></span>
            </div>
        </div>
    </div>
</div>

<?php if (empty($colonies)): ?>
<div class="aps-cp-card">
    <div class="aps-cp-card-body">
        <div class="aps-cp-empty">
            <div class="aps-cp-empty-icon"><i class="fas fa-map-marked-alt"></i></div>
            <h5><?= __('user_new_booking_no_colonies_heading', 'No colonies available') ?></h5>
            <p><?= __('user_new_booking_no_colonies_desc', 'There are no active colonies with available plots at the moment. Please check back later.') ?></p>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary"><i class="fas fa-search me-2"></i><?= __('user_new_booking_browse_properties', 'Browse Properties') ?></a>
        </div>
    </div>
</div>
<?php else: ?>

<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-building text-primary me-2"></i><?= __('user_new_booking_select_colony', 'Select Colony') ?></h5>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3">
            <?php foreach ($colonies as $colony): ?>
            <div class="col-md-6 col-lg-3">
                <a href="<?= BASE_URL ?>/user/bookings/new?colony_id=<?= (int)$colony['id'] ?>"
                   class="text-decoration-none">
                    <div class="aps-cp-card h-100 <?= (int)$colony['id'] === $selectedColony ? 'border-primary shadow' : '' ?> style-73923">
                        <div class="position-relative">
                            <?php if (!empty($colony['image_path'])): ?>
                                <?php $imgRaw = $colony['image_path'] ?? '';
                                      $imgSrc = (str_starts_with($imgRaw, 'http://') || str_starts_with($imgRaw, 'https://')) ? $imgRaw : BASE_URL . '/' . $imgRaw; ?>
                                <img src="<?= htmlspecialchars($imgSrc ?? '') ?>"
                                     alt="<?= htmlspecialchars($colony['name'] ?? '') ?>"
                                     class="w-100 style-46386">
                            <?php else: ?>
                                <div class="w-100 d-flex align-items-center justify-content-center style-46646">
                                    <i class="fas fa-building fa-2x text-primary opacity-50"></i>
                                </div>
                            <?php endif; ?>
                            <span class="badge bg-success position-absolute top-0 end-0 m-2">
                                <?= (int)($colony['available_plots'] ?? 0) ?> <?= __('user_new_booking_available', 'available') ?>
                            </span>
                        </div>
                        <div class="p-3">
                            <h6 class="mb-1 text-dark fw-bold"><?= htmlspecialchars($colony['name'] ?? '') ?></h6>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars($colony['district_name'] ?? __('user_new_booking_location', 'Location')) ?>
                            </p>
                            <p class="mb-1 small">
                                <strong><?= __('user_new_booking_starting_from', 'Starting from') ?> ₹<?= number_format((float)($colony['starting_price'] ?? 0)) ?></strong>
                            </p>
                            <p class="text-muted small mb-0">
                                <?= (int)($colony['total_plots'] ?? 0) ?> <?= __('user_new_booking_total_plots', 'total plots') ?> &middot; <?= (int)($colony['available_plots'] ?? 0) ?> <?= __('user_new_booking_open', 'open') ?>
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
        <h5><i class="fas fa-th-large text-success me-2"></i><?= __('user_new_booking_available_plots', 'Available Plots') ?>
            <?php if (!empty($plots)): ?>
                <small class="text-muted fw-normal ms-2">(<?= count($plots) ?> <?= __('user_new_booking_plots_found', 'plots found') ?>)</small>
            <?php endif; ?>
        </h5>
    </div>
    <div class="aps-cp-card-body">
        <?php if (empty($plots)): ?>
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-th-large"></i></div>
                <h5><?= __('user_new_booking_no_plots_heading', 'No available plots') ?></h5>
                <p><?= __('user_new_booking_no_plots_desc', 'All plots in this colony are currently booked or sold. Please select another colony.') ?></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="aps-cp-table">
                    <thead>
                        <tr>
                            <th><?= __('user_new_booking_th_plot_no', 'Plot No') ?></th>
                            <th><?= __('user_new_booking_th_block', 'Block') ?></th>
                            <th><?= __('user_new_booking_th_area', 'Area (sqft)') ?></th>
                            <th><?= __('user_new_booking_th_dimensions', 'Dimensions') ?></th>
                            <th><?= __('user_new_booking_th_facing', 'Facing') ?></th>
                            <th><?= __('user_new_booking_th_price', 'Price') ?></th>
                            <th class="text-end"><?= __('user_new_booking_th_action', 'Action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plots as $plot): ?>
                        <tr id="plot-row-<?= (int)$plot['id'] ?>">
                            <td><strong><?= htmlspecialchars($plot['plot_number'] ?? '') ?></strong></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($plot['block'] ?? '') ?></span></td>
                            <td><?= number_format((float)$plot['area_sqft']) ?></td>
                            <td>
                                <?php if (!empty($plot['dimension_label'])): ?>
                                    <?= htmlspecialchars($plot['dimension_label'] ?? '') ?>
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
                                        data-plot-no="<?= htmlspecialchars($plot['plot_number'] ?? '') ?>"
                                        data-block="<?= htmlspecialchars($plot['block'] ?? '') ?>"
                                        data-area="<?= number_format((float)$plot['area_sqft']) ?>"
                                        data-dims="<?= htmlspecialchars($plot['dimension_label'] ?: ($plot['width_ft'] . 'x' . $plot['length_ft'])) ?>"
                                        data-price="<?= number_format((float)$plot['total_price']) ?>"
                                        data-colony="<?= htmlspecialchars($plot['colony_name'] ?? '') ?>"
                                        data-facing="<?= htmlspecialchars($plot['facing'] ?? '-') ?>">
                                    <i class="fas fa-check me-1"></i><?= __('user_new_booking_book_now', 'Book Now') ?>
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
        <div class="modal-content style-337">
            <div class="modal-header style-99001">
                <h5 class="modal-title" id="bookingModalLabel">
                    <i class="fas fa-file-contract me-2"></i><?= __('user_new_booking_modal_title', 'Confirm Your Booking') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="<?= __('user_new_booking_close', 'Close') ?>"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <h6 class="text-muted small mb-2"><?= __('user_new_booking_modal_plot_details', 'PLOT DETAILS') ?></h6>
                            <p class="mb-1"><strong id="modal-colony"></strong></p>
                            <p class="mb-1"><?= __('user_new_booking_modal_plot', 'Plot') ?>: <strong id="modal-plot"></strong> (<?= __('user_new_booking_modal_block', 'Block') ?> <span id="modal-block"></span>)</p>
                            <p class="mb-1"><?= __('user_new_booking_modal_area', 'Area') ?>: <span id="modal-area"></span> sqft</p>
                            <p class="mb-1"><?= __('user_new_booking_modal_dimensions', 'Dimensions') ?>: <span id="modal-dims"></span></p>
                            <p class="mb-0"><?= __('user_new_booking_modal_facing', 'Facing') ?>: <span id="modal-facing"></span></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <h6 class="text-muted small mb-2"><?= __('user_new_booking_modal_booking_summary', 'BOOKING SUMMARY') ?></h6>
                            <p class="mb-1"><?= __('user_new_booking_modal_token_amount', 'Token Amount') ?>: <strong>₹25,000</strong></p>
                            <p class="mb-1"><?= __('user_new_booking_modal_total_price', 'Total Price') ?>: <strong id="modal-price"></strong></p>
                            <p class="mb-0"><?= __('user_new_booking_modal_status', 'Status') ?>: <span class="badge bg-primary"><?= __('user_new_booking_modal_token_paid', 'Token Paid') ?></span></p>
                        </div>
                    </div>
                </div>

                <form id="bookingForm" method="POST" action="<?= BASE_URL ?>/user/bookings/create">
                    <input type="hidden" name="csrf_token" value="">
                    <input type="hidden" name="plot_id" id="modal-plot-id" value="">
                    <input type="hidden" name="notes" id="modal-notes" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?= __('user_new_booking_label_full_name', 'Full Name') ?></label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?= __('user_new_booking_label_phone', 'Phone') ?></label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?= __('user_new_booking_label_email', 'Email') ?></label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold"><?= __('user_new_booking_label_notes', 'Notes') ?> <span class="text-muted fw-normal">(<?= __('user_new_booking_label_optional', 'optional') ?>)</span></label>
                            <textarea class="form-control" id="bookingNotes" rows="2" placeholder="<?= __('user_new_booking_notes_placeholder', 'Any special requirements or notes...') ?>"></textarea>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3 mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>
                        <?= __('user_new_booking_terms', 'By confirming, you agree to pay the token amount of') ?> <strong>₹25,000</strong> <?= __('user_new_booking_terms_towards', 'towards the booking.') ?>
                        <?= __('user_new_booking_terms_emi', 'The remaining amount can be paid via EMI or lump sum as per your payment plan.') ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= __('user_new_booking_cancel', 'Cancel') ?></button>
                <button type="button" class="btn btn-primary px-4" id="confirmBookingBtn">
                    <i class="fas fa-check me-2"></i><?= __('user_new_booking_confirm_booking', 'Confirm Booking') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center border-0 shadow-lg style-90348">
            <div class="modal-body p-5">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 style-61938">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-2"><?= __('user_new_booking_confirmed_heading', 'Booking Confirmed!') ?></h4>
                <p class="text-muted mb-3"><?= __('user_new_booking_confirmed_desc', 'Your plot has been successfully booked.') ?></p>
                <div class="bg-light rounded-3 p-3 mb-4 text-start">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted"><?= __('user_new_booking_success_booking_number', 'Booking Number:') ?></span>
                        <strong id="success-booking-number"></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted"><?= __('user_new_booking_success_plot', 'Plot:') ?></span>
                        <strong id="success-plot"></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted"><?= __('user_new_booking_success_colony', 'Colony:') ?></span>
                        <strong id="success-colony"></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><?= __('user_new_booking_success_amount', 'Amount:') ?></span>
                        <strong id="success-amount"></strong>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <a id="success-confirmation-link" href="#" class="btn btn-primary">
                        <i class="fas fa-eye me-2"></i><?= __('user_new_booking_view_confirmation', 'View Confirmation') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i><?= __('user_new_booking_my_bookings', 'My Bookings') ?>
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
        if (!plotId) { alert('<?= __('user_new_booking_js_select_plot', 'Please select a plot first.') ?>'); return; }

        document.getElementById('modal-notes').value = document.getElementById('bookingNotes').value;

        var tokenInput = document.querySelector('#bookingForm input[name="csrf_token"]');
        var metaToken = document.querySelector('meta[name="csrf-token"]');
        if (tokenInput && metaToken) { tokenInput.value = metaToken.content; }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= __('user_new_booking_js_processing', 'Processing...') ?>';

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
                alert(data.error || '<?= __('user_new_booking_js_booking_failed', 'Booking failed. Please try again.') ?>');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-2"></i><?= __('user_new_booking_confirm_booking', 'Confirm Booking') ?>';
            }
        })
        .catch(function(err) {
            console.error(err);
            alert('<?= __('user_new_booking_js_error', 'An error occurred. Please try again.') ?>');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-2"></i><?= __('user_new_booking_confirm_booking', 'Confirm Booking') ?>';
        });
    });
});
</script>
