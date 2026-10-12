<?php
/**
 * Admin SRO Registry Appointment Detail & Execution Desk
 */
$page_title = $page_title ?? "Registry Token #{$appointment['appointment_token']}";
$appointment = $appointment ?? [];
?>

<div class="container-fluid py-4">
    <!-- Back & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/admin/registry-appointments" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Registry Desk
        </a>
        <div class="d-flex gap-2">
            <a href="/admin/registry-appointments/<?= $appointment['id'] ?>/slip" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-print me-1"></i>Print SRO Gatepass & Checklist
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-danger text-uppercase font-monospace fs-6 mb-2"><?= htmlspecialchars($appointment['appointment_token']) ?></span>
                            <h4 class="fw-bold mb-1 text-dark">Sub-Registrar Office (SRO) Appointment</h4>
                            <p class="text-muted mb-0">
                                <i class="fas fa-landmark text-primary me-1"></i><?= htmlspecialchars($appointment['sro_office']) ?>
                            </p>
                        </div>
                        <div class="text-end">
                            <?php if ($appointment['status'] === 'completed'): ?>
                                <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i>Deed Executed</span>
                            <?php elseif ($appointment['status'] === 'slot_confirmed'): ?>
                                <span class="badge bg-primary fs-6"><i class="fas fa-calendar-check me-1"></i>Slot Confirmed</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark fs-6"><i class="fas fa-clock me-1"></i>Pending Confirmation</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Property & Booking Specs -->
                    <div class="row g-3 p-3 bg-light rounded-3 small text-muted mb-3">
                        <div class="col-md-4 col-6">
                            <span class="d-block fw-semibold text-dark">Township / Colony</span>
                            <?= htmlspecialchars($appointment['colony_name']) ?>
                        </div>
                        <div class="col-md-4 col-6">
                            <span class="d-block fw-semibold text-dark">Plot Number & Area</span>
                            Plot #<?= htmlspecialchars($appointment['plot_number']) ?> (<?= htmlspecialchars($appointment['area_sqft'] ?? 'N/A') ?> sq.ft)
                        </div>
                        <div class="col-md-4 col-6">
                            <span class="d-block fw-semibold text-dark">Booking Consideration</span>
                            ₹<?= number_format((float)$appointment['total_plot_value'], 2) ?>
                        </div>
                        <div class="col-md-4 col-6">
                            <span class="d-block fw-semibold text-dark">Buyer Name</span>
                            <?= htmlspecialchars($appointment['customer_name']) ?>
                        </div>
                        <div class="col-md-4 col-6">
                            <span class="d-block fw-semibold text-dark">Buyer Contact</span>
                            <a href="tel:<?= htmlspecialchars($appointment['customer_phone']) ?>" class="text-decoration-none">
                                <i class="fas fa-phone-alt text-success me-1"></i><?= htmlspecialchars($appointment['customer_phone']) ?>
                            </a>
                        </div>
                        <div class="col-md-4 col-6">
                            <span class="d-block fw-semibold text-dark">Buyer Email</span>
                            <?= htmlspecialchars($appointment['customer_email'] ?: 'N/A') ?>
                        </div>
                    </div>

                    <!-- Dates & Slot Info -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <span class="text-muted small d-block">Preferred Date 1</span>
                                <strong class="fs-6 text-dark"><?= date('d M Y', strtotime($appointment['preferred_date_1'])) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <span class="text-muted small d-block">Preferred Date 2</span>
                                <strong class="fs-6 text-dark"><?= !empty($appointment['preferred_date_2']) ? date('d M Y', strtotime($appointment['preferred_date_2'])) : 'None' ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-primary bg-opacity-10 border-primary">
                                <span class="text-primary small d-block fw-semibold">Confirmed Slot Date</span>
                                <strong class="fs-5 text-primary">
                                    <?= !empty($appointment['confirmed_date']) ? date('d M Y', strtotime($appointment['confirmed_date'])) : 'Pending Assignment' ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Witnesses & Statutory Verification -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-user-friends text-info me-2"></i>Mandatory Witnesses for Registry (SRO Requirement)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <span class="badge bg-secondary mb-2">Witness 1</span>
                                <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($appointment['witness_1_name'] ?: 'Not Provided') ?></h6>
                                <p class="small text-muted mb-1"><i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($appointment['witness_1_phone'] ?: 'N/A') ?></p>
                                <p class="small text-muted mb-0"><i class="fas fa-id-card me-1"></i>Aadhaar: <?= htmlspecialchars($appointment['witness_1_aadhaar'] ?: 'N/A') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <span class="badge bg-secondary mb-2">Witness 2</span>
                                <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($appointment['witness_2_name'] ?: 'Not Provided') ?></h6>
                                <p class="small text-muted mb-1"><i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($appointment['witness_2_phone'] ?: 'N/A') ?></p>
                                <p class="small text-muted mb-0"><i class="fas fa-id-card me-1"></i>Aadhaar: <?= htmlspecialchars($appointment['witness_2_aadhaar'] ?: 'N/A') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deed Registration Result if Completed -->
            <?php if (!empty($appointment['deed_registration_number'])): ?>
                <div class="card border-0 shadow-sm border-start border-4 border-success">
                    <div class="card-body">
                        <h5 class="fw-bold text-success mb-2"><i class="fas fa-certificate me-2"></i>Registered Sale Deed Information</h5>
                        <p class="mb-1"><strong>Official SRO Deed / Bahi Reference:</strong> <span class="font-monospace fw-bold fs-6"><?= htmlspecialchars($appointment['deed_registration_number']) ?></span></p>
                        <?php if (!empty($appointment['registered_deed_file'])): ?>
                            <a href="<?= htmlspecialchars($appointment['registered_deed_file']) ?>" target="_blank" class="btn btn-outline-success btn-sm mt-2">
                                <i class="fas fa-file-pdf me-1"></i>Download Scanned Registered Sale Deed PDF
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar / Actions -->
        <div class="col-lg-4">
            <!-- Stamp Duty Card -->
            <div class="card border-0 shadow-sm mb-4 bg-white">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-calculator text-primary me-2"></i>Stamp Duty & Govt Fees</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">UP Stamp Duty:</span>
                        <strong class="text-dark">₹<?= number_format((float)$appointment['stamp_duty_amount'], 2) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">SRO Registration Fee:</span>
                        <strong class="text-dark">₹<?= number_format((float)$appointment['reg_fee_amount'], 2) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 fw-bold text-success fs-6">
                        <span>Total Govt Outlay:</span>
                        <span>₹<?= number_format((float)$appointment['stamp_duty_amount'] + (float)$appointment['reg_fee_amount'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Confirmation Action Box -->
            <?php if ($appointment['status'] === 'requested'): ?>
                <div class="card border-0 shadow-sm mb-4 border-top border-4 border-primary">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-check-circle text-primary me-2"></i>Confirm SRO Date Slot</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/admin/registry-appointments/<?= $appointment['id'] ?>/confirm">
                            <?= \App\Core\Security::csrfField() ?>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Confirmed SRO Date <span class="text-danger">*</span></label>
                                <input type="date" name="confirmed_date" class="form-control" value="<?= $appointment['preferred_date_1'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Assigned Legal Executive</label>
                                <input type="text" name="assigned_executive_name" class="form-control" placeholder="e.g. Adv. Rajesh Tripathi" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Executive Phone</label>
                                <input type="text" name="assigned_executive_phone" class="form-control" placeholder="e.g. 9839000000" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Instructions to Buyer</label>
                                <textarea name="notes" rows="2" class="form-control" placeholder="Report at Tehsil Sadar Room 14 at 11:00 AM with original Aadhaar cards..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-check me-1"></i>Confirm Appointment Slot
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Complete Execution Box -->
            <?php if ($appointment['status'] === 'slot_confirmed'): ?>
                <div class="card border-0 shadow-sm mb-4 border-top border-4 border-success">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-stamp text-success me-2"></i>Record Registry Deed Completion</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/admin/registry-appointments/<?= $appointment['id'] ?>/complete" enctype="multipart/form-data">
                            <?= \App\Core\Security::csrfField() ?>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Official Deed / Bahi # <span class="text-danger">*</span></label>
                                <input type="text" name="deed_registration_number" class="form-control" placeholder="e.g. 2026/04/SADAR/8942" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Upload Scanned Deed PDF</label>
                                <input type="file" name="registered_deed_file" class="form-control" accept=".pdf,image/*">
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check-double me-1"></i>Mark Registry Completed
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
