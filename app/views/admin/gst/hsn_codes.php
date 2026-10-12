<?php
$base = rtrim(BASE_URL, '/');
$csrf = $this->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-tags me-2 text-success"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= $base ?>/admin/gst/invoices/create" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Invoices</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add New HSN/SAC Code</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="hsnForm" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-3">
                    <label class="form-label">HSN/SAC Code <span class="text-danger">*</span></label>
                    <input type="text" name="hsn_code" class="form-control" required placeholder="e.g., 9954">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text" name="description" class="form-control" required placeholder="e.g., Construction Services - Residential">
                </div>
                <div class="col-md-2">
                    <label class="form-label">GST Rate % <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="gst_rate" class="form-control" value="0" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cess Rate %</label>
                    <input type="number" step="0.01" name="cess_rate" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit/UOM</label>
                    <input type="text" name="unit" class="form-control" placeholder="SQFT, PCS, KG">
                </div>
                <div class="col-md-2">
                    <label class="form-label">CGST %</label>
                    <input type="number" step="0.01" name="cgst_rate" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">SGST %</label>
                    <input type="number" step="0.01" name="sgst_rate" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">IGST %</label>
                    <input type="number" step="0.01" name="igst_rate" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_service" id="isService">
                        <label class="form-check-label" for="isService">Service (SAC)</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Add HSN Code</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>HSN/SAC Master (<?= count($codes) ?> codes)</h5>
            <div class="input-group" style="max-width:300px">
                <input type="text" class="form-control form-control-sm" id="hsnSearch" placeholder="Search HSN/Description...">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="hsnTable">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                            <th>GST%</th>
                            <th>CGST%</th>
                            <th>SGST%</th>
                            <th>IGST%</th>
                            <th>Cess%</th>
                            <th>Unit</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($codes)): ?>
                            <tr><td colspan="11" class="text-center py-4 text-muted">No HSN codes defined</td></tr>
                        <?php else: ?>
                            <?php foreach ($codes as $h): ?>
                                <tr data-code="<?= htmlspecialchars($h['hsn_code']) ?>" data-desc="<?= htmlspecialchars($h['description']) ?>">
                                    <td><code><?= htmlspecialchars($h['hsn_code']) ?></code></td>
                                    <td><?= htmlspecialchars($h['description']) ?></td>
                                    <td><span class="badge bg-info"><?= $h['gst_rate'] ?>%</span></td>
                                    <td><?= $h['cgst_rate'] ?>%</td>
                                    <td><?= $h['sgst_rate'] ?>%</td>
                                    <td><?= $h['igst_rate'] ?>%</td>
                                    <td><?= $h['cess_rate'] ?>%</td>
                                    <td><?= htmlspecialchars($h['unit'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= $h['is_service'] ? 'primary' : 'secondary' ?>"><?= $h['is_service'] ? 'Service' : 'Goods' ?></span></td>
                                    <td><span class="badge bg-<?= $h['is_active'] ? 'success' : 'secondary' ?>"><?= $h['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary edit-hsn" data-id="<?= $h['id'] ?>" data-code="<?= htmlspecialchars($h['hsn_code']) ?>" data-desc="<?= htmlspecialchars($h['description']) ?>" data-gst="<?= $h['gst_rate'] ?>" data-cgst="<?= $h['cgst_rate'] ?>" data-sgst="<?= $h['sgst_rate'] ?>" data-igst="<?= $h['igst_rate'] ?>" data-cess="<?= $h['cess_rate'] ?>" data-unit="<?= htmlspecialchars($h['unit'] ?? '') ?>" data-service="<?= $h['is_service'] ?>" data-active="<?= $h['is_active'] ?>"><i class="fas fa-edit"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editHsnModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editHsnForm" class="row g-3 p-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="hsn_id" id="editHsnId">
                    <div class="col-md-6">
                        <label class="form-label">HSN/SAC Code</label>
                        <input type="text" name="hsn_code" class="form-control" id="editHsnCode" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" id="editHsnDesc" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">GST Rate %</label>
                        <input type="number" step="0.01" name="gst_rate" class="form-control" id="editGstRate">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cess Rate %</label>
                        <input type="number" step="0.01" name="cess_rate" class="form-control" id="editCessRate">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" class="form-control" id="editUnit">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">CGST %</label>
                        <input type="number" step="0.01" name="cgst_rate" class="form-control" id="editCgstRate">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SGST %</label>
                        <input type="number" step="0.01" name="sgst_rate" class="form-control" id="editSgstRate">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">IGST %</label>
                        <input type="number" step="0.01" name="igst_rate" class="form-control" id="editIgstRate">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_service" id="editIsService">
                            <label class="form-check-label" for="editIsService">Service</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive">
                            <label class="form-check-label" for="editIsActive">Active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.edit-hsn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('editHsnId').value = btn.dataset.id;
        document.getElementById('editHsnCode').value = btn.dataset.code;
        document.getElementById('editHsnDesc').value = btn.dataset.desc;
        document.getElementById('editGstRate').value = btn.dataset.gst;
        document.getElementById('editCessRate').value = btn.dataset.cess;
        document.getElementById('editUnit').value = btn.dataset.unit;
        document.getElementById('editCgstRate').value = btn.dataset.cgst;
        document.getElementById('editSgstRate').value = btn.dataset.sgst;
        document.getElementById('editIgstRate').value = btn.dataset.igst;
        document.getElementById('editIsService').checked = btn.dataset.service == '1';
        document.getElementById('editIsActive').checked = btn.dataset.active == '1';
        document.getElementById('editHsnForm').action = '<?= $base ?>/admin/gst/hsn-code/update/' + btn.dataset.id;
        new bootstrap.Modal(document.getElementById('editHsnModal')).show();
    });
});

document.getElementById('hsnSearch').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#hsnTable tbody tr').forEach(row => {
        const code = (row.dataset.code || '').toLowerCase();
        const desc = (row.dataset.desc || '').toLowerCase();
        row.style.display = (code.includes(term) || desc.includes(term)) ? '' : 'none';
    });
});
</script>
</body>
</html>