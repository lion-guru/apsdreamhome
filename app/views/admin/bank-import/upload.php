<?php $page_title = $page_title ?? 'Upload Bank Statement'; $page_heading = $page_heading ?? 'Upload Bank Statement'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-upload me-2 text-primary"></i>Upload Bank Statement</h2>
        <a href="<?= BASE_URL ?>/admin/bank-import" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Imports</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-file-csv me-1"></i> Import CSV File</span>
                </div>
                <div class="aps-cp-card-body">
                    <!-- Flash messages -->
                    <?php if (!empty($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($_SESSION['flash_error'] ?? '') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_error']); ?>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/admin/bank-import/process" enctype="multipart/form-data" id="uploadForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Bank Account <span class="text-muted">(optional)</span></label>
                            <select name="bank_account_id" class="form-select">
                                <option value="">— Select Bank Account —</option>
                                <?php foreach (($banks ?? []) as $b): ?>
                                    <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['account_name'] . ' — ' . $b['bank_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Link this import to a specific bank account for easier reconciliation.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">CSV File <span class="text-danger">*</span></label>
                            <div id="dropZone" class="border border-2 border-dashed rounded-3 p-5 text-center" class="style-96836">
                                <div id="dropContent">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <p class="mb-1 fw-semibold">Drag & drop your CSV file here</p>
                                    <p class="text-muted small mb-3">or click to browse</p>
                                    <input type="file" name="csv_file" id="csvFile" accept=".csv,.txt" class="d-none" required>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('csvFile').click()">
                                        <i class="fas fa-folder-open me-1"></i>Select File
                                    </button>
                                </div>
                                <div id="fileInfo" class="d-none">
                                    <i class="fas fa-file-csv fa-3x text-success mb-3"></i>
                                    <p class="mb-1 fw-semibold" id="fileName"></p>
                                    <p class="text-muted small" id="fileSize"></p>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearFile()">
                                        <i class="fas fa-times me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-upload me-1"></i>Import Transactions
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Expected Format Info -->
            <div class="aps-cp-card mt-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-info-circle me-1"></i> Expected CSV Format</span>
                </div>
                <div class="aps-cp-card-body">
                    <p class="mb-3">The CSV file should have headers in the first row. Column detection is automatic for common bank formats.</p>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>Column</th>
                                    <th>Accepted Header Names</th>
                                    <th>Format</th>
                                    <th>Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Date</strong></td>
                                    <td>Date, Transaction Date, Value Date, Txn Date</td>
                                    <td>DD/MM/YYYY, YYYY-MM-DD, DD-MM-YYYY</td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Description</strong></td>
                                    <td>Description, Narration, Particulars, Details</td>
                                    <td>Text</td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Debit</strong></td>
                                    <td>Debit, Dr, Withdrawal, Debit Amount</td>
                                    <td>Number (₹)</td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Credit</strong></td>
                                    <td>Credit, Cr, Deposit, Credit Amount</td>
                                    <td>Number (₹)</td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Balance</strong></td>
                                    <td>Balance, Closing Balance, Running Balance</td>
                                    <td>Number (₹)</td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Cheque/Ref No</strong></td>
                                    <td>Cheque, Cheque No, Reference, Ref No, UTR</td>
                                    <td>Text</td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-lightbulb me-1"></i>
                        <strong>Sample CSV:</strong>
                        <code>Date,Description,Debit,Credit,Balance,Cheque/Ref
01/06/2026,NEFT FROM CUSTOMER ABC,0,50000.00,125000.00,UTR123456
02/06/2026,OFFICE RENT PAYMENT,25000.00,0,100000.00,CHQ789</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const dropZone = document.getElementById('dropZone');
    const csvFile = document.getElementById('csvFile');
    const dropContent = document.getElementById('dropContent');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');

    dropZone.addEventListener('click', () => csvFile.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#0d9488';
        dropZone.style.backgroundColor = '#f0f0ff';
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = '#cbd5e1';
        dropZone.style.backgroundColor = '';
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#cbd5e1';
        dropZone.style.backgroundColor = '';
        if (e.dataTransfer.files.length > 0) {
            csvFile.files = e.dataTransfer.files;
            showFile(e.dataTransfer.files[0]);
        }
    });

    csvFile.addEventListener('change', () => {
        if (csvFile.files.length > 0) {
            showFile(csvFile.files[0]);
        }
    });

    function showFile(file) {
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
        dropContent.classList.add('d-none');
        fileInfo.classList.remove('d-none');
    }

    window.clearFile = function() {
        csvFile.value = '';
        dropContent.classList.remove('d-none');
        fileInfo.classList.add('d-none');
    };

    document.getElementById('uploadForm').addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Importing...';
    });
})();
</script>
