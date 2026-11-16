<?php
/**
 * Associate Portal - Stamp Duty Calculator
 */
$page_title = $page_title ?? 'Stamp Duty Calculator - Associate Portal';
$result = $result ?? null;
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/tools">Tools</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Stamp Duty Calculator</li>
                </ol>
            </nav>
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-stamp me-2 text-primary"></i> Stamp Duty & Registration Calculator</h2>
        </div>
        <a href="<?= $base ?>/associate/tools" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Tools
        </a>
    </div>

    <div class="row">
        <!-- Form -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-file-contract me-2"></i> Property & Registration Details</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= $base ?>/associate/tools/stamp-duty">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Property Value (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="property_value" class="form-control" placeholder="e.g. 5000000" 
                                       value="<?= htmlspecialchars($_POST['property_value'] ?? '5000000') ?>" required min="50000" step="10000">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">State</label>
                            <select name="state" class="form-select">
                                <?php
                                $states = ['Uttar Pradesh', 'Maharashtra', 'Delhi', 'Karnataka', 'Tamil Nadu', 'Gujarat', 'Rajasthan'];
                                $selectedState = $_POST['state'] ?? 'Uttar Pradesh';
                                foreach ($states as $st): ?>
                                    <option value="<?= $st ?>" <?= $st === $selectedState ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Buyer Gender</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderMale" value="male" 
                                           <?= ($_POST['gender'] ?? 'male') === 'male' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="genderMale">Male</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="female"
                                           <?= ($_POST['gender'] ?? '') === 'female' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="genderFemale">Female (Rebate Applicable)</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-calculator me-2"></i> Calculate Stamp Duty
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Result -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-success"><i class="fas fa-receipt me-2"></i> Estimated Charges</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <?php if ($result): ?>
                        <div class="text-center mb-4">
                            <span class="text-muted d-block mb-1">Total Registration Cost</span>
                            <h2 class="display-5 fw-bold text-success">₹<?= number_format($result['total'], 2) ?></h2>
                        </div>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Property Value</span>
                                <span class="fw-bold">₹<?= number_format($result['property_value'], 2) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Stamp Duty Rate</span>
                                <span class="fw-bold"><?= htmlspecialchars((string)$result['rate']) ?>%</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Stamp Duty Amount</span>
                                <span class="fw-bold text-primary">₹<?= number_format($result['stamp_duty'], 2) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Registration Fee (1%)</span>
                                <span class="fw-bold text-info">₹<?= number_format($result['registration_fee'], 2) ?></span>
                            </li>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-stamp fa-3x mb-3 text-secondary"></i>
                            <p>Select property value, state, and buyer gender to estimate the applicable stamp duty and registration fees.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
