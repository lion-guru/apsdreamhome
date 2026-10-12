<?php
/**
 * Customer Registry Appointment Booking Form
 */
$page_title = $page_title ?? 'Book SRO Registry Date Slot';
$booking = $booking ?? [];
$plotValue = (float)($booking['total_plot_value'] ?? 0);
?>

<div class="container py-4" style="max-width: 800px;">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="/customer/registry-appointments" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Registry Desk
        </a>
    </div>

    <!-- Booking Summary Card -->
    <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-white text-primary text-uppercase mb-1">Plot Details</span>
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($booking['colony_name']) ?> — Plot #<?= htmlspecialchars($booking['plot_number']) ?></h4>
                    <p class="mb-0 opacity-75 small">Area: <?= htmlspecialchars($booking['area_sqft'] ?? 'N/A') ?> sq.ft | Location: <?= htmlspecialchars($booking['colony_location'] ?? 'Gorakhpur') ?></p>
                </div>
                <div class="text-end">
                    <span class="small d-block opacity-75">Plot Valuation</span>
                    <h3 class="fw-bold mb-0">₹<?= number_format($plotValue, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-gavel text-danger me-2"></i>Schedule Sub-Registrar Office (SRO) Appointment</h5>
            <p class="text-muted small mb-4">Please submit your preferred dates, jurisdiction SRO office, and 2 witness details for deed preparation.</p>

            <form method="POST" action="/customer/registry-appointments/book">
                <?= \App\Core\Security::csrfField() ?>
                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                <!-- Dates & SRO Selection -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Preferred Registry Date 1 <span class="text-danger">*</span></label>
                        <input type="date" name="preferred_date_1" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Alternate Preferred Date 2</label>
                        <input type="date" name="preferred_date_2" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Sub-Registrar Office Jurisdiction <span class="text-danger">*</span></label>
                        <select name="sro_office" class="form-select" required>
                            <option value="Tehsil Sadar Gorakhpur">Tehsil Sadar Gorakhpur (Near Civil Court)</option>
                            <option value="Tehsil Chauri Chaura">Tehsil Chauri Chaura (Gorakhpur)</option>
                            <option value="Tehsil Sahjanwa">Tehsil Sahjanwa (Gorakhpur)</option>
                            <option value="Tehsil Campierganj">Tehsil Campierganj (Gorakhpur)</option>
                            <option value="Tehsil Bansgaon">Tehsil Bansgaon (Gorakhpur)</option>
                        </select>
                    </div>
                </div>

                <!-- Gender & Stamp Duty Live Preview -->
                <div class="p-3 border rounded-3 bg-light mb-4">
                    <label class="form-label small fw-bold d-block text-dark">Buyer Ownership Category (UP Stamp Duty Rate)</label>
                    <div class="d-flex gap-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="buyer_gender" id="genderMale" value="male" checked onchange="updateStampCalc()">
                            <label class="form-check-label" for="genderMale">Male (7.0%)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="buyer_gender" id="genderFemale" value="female" onchange="updateStampCalc()">
                            <label class="form-check-label" for="genderFemale">Female (6.0% concession)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="buyer_gender" id="genderJoint" value="joint" onchange="updateStampCalc()">
                            <label class="form-check-label" for="genderJoint">Joint (6.5%)</label>
                        </div>
                    </div>
                    <div class="row g-2 text-muted small pt-2 border-top">
                        <div class="col-6">Estimated Stamp Duty: <strong class="text-dark" id="calcStamp">₹0.00</strong></div>
                        <div class="col-6">Registration Fee (1% max ₹20k): <strong class="text-dark" id="calcReg">₹0.00</strong></div>
                    </div>
                </div>

                <!-- 2 Witnesses Details -->
                <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-users text-primary me-2"></i>Witnesses Details (SRO Requirement)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6 border-end">
                        <span class="badge bg-secondary mb-2">Witness 1</span>
                        <div class="mb-2">
                            <input type="text" name="witness_1_name" class="form-control form-control-sm" placeholder="Full Name" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="witness_1_phone" class="form-control form-control-sm" placeholder="Mobile Number" required>
                        </div>
                        <div>
                            <input type="text" name="witness_1_aadhaar" class="form-control form-control-sm" placeholder="Aadhaar (Last 4 Digits or Full)" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <span class="badge bg-secondary mb-2">Witness 2</span>
                        <div class="mb-2">
                            <input type="text" name="witness_2_name" class="form-control form-control-sm" placeholder="Full Name" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="witness_2_phone" class="form-control form-control-sm" placeholder="Mobile Number" required>
                        </div>
                        <div>
                            <input type="text" name="witness_2_aadhaar" class="form-control form-control-sm" placeholder="Aadhaar (Last 4 Digits or Full)" required>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Special Requests / Notes</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Any specific requirements regarding timing, advocate assistance, etc..."></textarea>
                </div>

                <button type="submit" class="btn btn-danger btn-lg w-100">
                    <i class="fas fa-calendar-check me-2"></i>Submit Registry Appointment Request
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const val = <?= json_encode($plotValue) ?>;
function updateStampCalc() {
    let rate = 7.0;
    if (document.getElementById('genderFemale').checked) rate = 6.0;
    if (document.getElementById('genderJoint').checked) rate = 6.5;

    const stamp = (val * rate) / 100;
    const reg = Math.min(20000, (val * 1.0) / 100);

    document.getElementById('calcStamp').innerText = '₹' + stamp.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('calcReg').innerText = '₹' + reg.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
document.addEventListener('DOMContentLoaded', updateStampCalc);
</script>
