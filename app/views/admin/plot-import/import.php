<?php
$pageTitle = $page_title ?? 'Bulk Import/Export Plots';
$colonies = $colonies ?? [];
$importErrors = $_SESSION['import_errors'] ?? [];
unset($_SESSION['import_errors']);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Bulk Import/Export Plots</h1>
        <div>
            <a href="/admin/plots/import-history" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-history me-1"></i> Import History
            </a>
        </div>
    </div>

    <?php if ($this->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $this->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $this->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->hasFlash('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <?= $this->getFlash('warning') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-file-import me-2"></i>Import Plots from CSV</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/plots/import" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="mb-3">
                            <label for="colony_id" class="form-label">Colony (Optional)</label>
                            <select name="colony_id" id="colony_id" class="form-select">
                                <option value="">-- All Colonies (specify colony_id in CSV) --</option>
                                <?php foreach ($colonies as $colony): ?>
                                    <option value="<?= $colony['id'] ?>"><?= htmlspecialchars($colony['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">If selected, all plots will be assigned to this colony regardless of CSV colony_id.</div>
                        </div>

                        <div class="mb-3">
                            <label for="import_file" class="form-label">CSV File</label>
                            <input type="file" name="import_file" id="import_file" class="form-control" accept=".csv" required>
                            <div class="form-text">Upload a CSV file with plot data. Maximum file size: 10MB.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i> Import CSV
                        </button>
                        <a href="/admin/plots/template" class="btn btn-outline-success">
                            <i class="fas fa-download me-1"></i> Download Template
                        </a>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>CSV Format Instructions</h5>
                </div>
                <div class="card-body">
                    <p>Your CSV file must include the following columns:</p>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Column</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><code>plot_number</code></td><td>Yes</td><td>Unique plot identifier</td></tr>
                            <tr><td><code>colony_id</code></td><td>Yes</td><td>Colony ID (numeric)</td></tr>
                            <tr><td><code>width_ft</code></td><td>No</td><td>Width in feet</td></tr>
                            <tr><td><code>length_ft</code></td><td>No</td><td>Length in feet</td></tr>
                            <tr><td><code>total_price</code></td><td>No</td><td>Total price in INR</td></tr>
                            <tr><td><code>status</code></td><td>No</td><td>available, booked, sold, hold, reserved, under_construction</td></tr>
                            <tr><td><code>facing</code></td><td>No</td><td>e.g., north, south, east, west</td></tr>
                            <tr><td><code>corner_plot</code></td><td>No</td><td>0 or 1</td></tr>
                            <tr><td><code>park_facing</code></td><td>No</td><td>0 or 1</td></tr>
                            <tr><td><code>road_width_ft</code></td><td>No</td><td>Road width in feet</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-file-export me-2"></i>Export Plots to CSV</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="/admin/plots/export">
                        <div class="mb-3">
                            <label for="export_colony_id" class="form-label">Filter by Colony (Optional)</label>
                            <select name="colony_id" id="export_colony_id" class="form-select">
                                <option value="">-- All Colonies --</option>
                                <?php foreach ($colonies as $colony): ?>
                                    <option value="<?= $colony['id'] ?>"><?= htmlspecialchars($colony['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download me-1"></i> Export CSV
                        </button>
                    </form>
                </div>
            </div>

            <?php if (!empty($importErrors)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Import Errors</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($importErrors as $error): ?>
                                <li class="text-danger"><i class="fas fa-times-circle me-1"></i> <?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-lightbulb me-2"></i>Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Download the template first to see the expected format</li>
                        <li>Plot numbers must be unique within a colony</li>
                        <li>Existing plots (same plot_number + colony_id) will be skipped</li>
                        <li>Status must be one of: available, booked, sold, hold, reserved, under_construction</li>
                        <li>Large imports are processed in batches of 100 rows</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
