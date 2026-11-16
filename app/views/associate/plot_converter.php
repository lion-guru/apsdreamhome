<?php
/**
 * Associate Portal - Plot Size Converter
 */
$page_title = $page_title ?? 'Plot Size Converter - Associate Portal';
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
                    <li class="breadcrumb-item active" aria-current="page">Plot Size Converter</li>
                </ol>
            </nav>
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-ruler-combined me-2 text-primary"></i> Plot & Land Area Converter</h2>
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
                    <h5 class="mb-0 text-primary"><i class="fas fa-exchange-alt me-2"></i> Area Conversion</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= $base ?>/associate/tools/plot-converter">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Value to Convert</label>
                            <input type="number" name="value" class="form-control" placeholder="e.g. 1000" 
                                   value="<?= htmlspecialchars($_POST['value'] ?? '1000') ?>" required min="0.001" step="any">
                        </div>

                        <?php
                        $units = [
                            'sqft' => 'Square Feet (Sq. Ft)',
                            'sqyd' => 'Square Yards (Gaj / Sq. Yd)',
                            'sqm'  => 'Square Meters (Sq. M)',
                            'acre' => 'Acre',
                            'hectare' => 'Hectare',
                            'bigha' => 'Bigha (UP Standard)',
                            'biswa' => 'Biswa',
                        ];
                        $fromUnit = $_POST['from_unit'] ?? 'sqft';
                        $toUnit = $_POST['to_unit'] ?? 'sqyd';
                        ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold">From Unit</label>
                            <select name="from_unit" class="form-select">
                                <?php foreach ($units as $key => $lbl): ?>
                                    <option value="<?= $key ?>" <?= $key === $fromUnit ? 'selected' : '' ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">To Unit</label>
                            <select name="to_unit" class="form-select">
                                <?php foreach ($units as $key => $lbl): ?>
                                    <option value="<?= $key ?>" <?= $key === $toUnit ? 'selected' : '' ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-sync-alt me-2"></i> Convert Area
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Result -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-success"><i class="fas fa-check-circle me-2"></i> Conversion Result</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <?php if ($result): ?>
                        <div class="text-center mb-4">
                            <span class="text-muted d-block mb-1">Converted Value</span>
                            <h2 class="display-5 fw-bold text-success"><?= number_format($result['output'], 4) ?></h2>
                            <span class="badge bg-primary px-3 py-2 mt-2"><?= htmlspecialchars($units[$result['to']] ?? $result['to']) ?></span>
                        </div>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Original Input</span>
                                <span class="fw-bold"><?= number_format($result['input'], 2) ?> <?= htmlspecialchars($units[$result['from']] ?? $result['from']) ?></span>
                            </li>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-vector-square fa-3x mb-3 text-secondary"></i>
                            <p>Enter an area value, choose source and target units, and click Convert Area to calculate.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
