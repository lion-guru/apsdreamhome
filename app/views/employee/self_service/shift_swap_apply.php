<?php
/**
 * Request Shift Swap
 */

$page_title = $page_title ?? 'Request Shift Swap';
$colleagues = $colleagues ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-exchange-alt me-2"></i><?= htmlspecialchars($page_title) ?></h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Request a shift swap with a colleague. Minimum 48 hours notice required. Max 4 swaps per month.</p>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/shift-swaps/request" id="requestSwapForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <!-- Date -->
                        <div class="row mb-3">
                            <label for="swap_date" class="col-sm-3 col-form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="swap_date" name="swap_date" value="<?= date('Y-m-d', strtotime('+2 days')) ?>" required min="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                                <div class="form-text">Minimum 48 hours notice required.</div>
                            </div>
                        </div>

                        <!-- Colleague -->
                        <div class="row mb-3">
                            <label for="target_employee_id" class="col-sm-3 col-form-label fw-bold">Colleague <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-select" id="target_employee_id" name="target_employee_id" required>
                                    <option value="">Select colleague</option>
                                    <?php foreach ($colleagues as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['name'] ?? '') ?> - <?= htmlspecialchars($c['designation'] ?? '') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Select a colleague from your department.</div>
                            </div>
                        </div>

                        <!-- My Shift -->
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold">My Shift <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label small">Type</label>
                                        <select class="form-select" name="my_shift_type" required>
                                            <option value="day">Day Shift</option>
                                            <option value="night">Night Shift</option>
                                            <option value="evening">Evening Shift</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Start Time</label>
                                        <input type="time" class="form-control" name="my_start_time" value="09:00" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">End Time</label>
                                        <input type="time" class="form-control" name="my_end_time" value="18:00" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Colleague's Shift -->
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-bold">Colleague's Shift <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label small">Type</label>
                                        <select class="form-select" name="colleague_shift_type" required>
                                            <option value="day">Day Shift</option>
                                            <option value="night">Night Shift</option>
                                            <option value="evening">Evening Shift</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Start Time</label>
                                        <input type="time" class="form-control" name="colleague_start_time" value="09:00" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">End Time</label>
                                        <input type="time" class="form-control" name="colleague_end_time" value="18:00" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="row mb-3">
                            <label for="reason" class="col-sm-3 col-form-label fw-bold">Reason <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Explain why you need this shift swap..."></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary btn-lg me-2">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Request
                                </button>
                                <a href="<?= BASE_URL ?>/employee/self-service/shift-swaps" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>