<?php
$page_title = $page_title ?? 'Upload Document for OCR';
$doc_types = $doc_types ?? [];
$doc_type_labels = $doc_type_labels ?? [];
?>

<style>
.ocr-page{background:#0f172a;min-height:100vh;color:#e2e8f0;padding:0 0 40px}
.ocr-header{background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);border-bottom:1px solid #334155;padding:28px 0 24px}
.ocr-card{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:28px}
.ocr-label{color:#94a3b8;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;display:block}
.ocr-input,.ocr-select{width:100%;background:#0f172a;border:1px solid #334155;color:#e2e8f0;border-radius:10px;padding:12px 16px;font-size:14px;outline:none;transition:border-color .2s}
.ocr-input:focus,.ocr-select:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.ocr-upload-zone{border:2px dashed #334155;border-radius:14px;padding:48px 24px;text-align:center;cursor:pointer;transition:all .3s;position:relative}
.ocr-upload-zone:hover,.ocr-upload-zone.dragover{border-color:#3b82f6;background:rgba(59,130,246,.05)}
.ocr-upload-zone.has-file{border-color:#10b981;background:rgba(16,185,129,.05)}
.ocr-upload-icon{width:72px;height:72px;border-radius:50%;background:rgba(59,130,246,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:#60a5fa}
.ocr-upload-text{color:#94a3b8;font-size:14px;margin-bottom:8px}
.ocr-upload-hint{color:#64748b;font-size:12px}
.ocr-btn{border-radius:10px;padding:12px 24px;font-weight:600;font-size:14px;border:none;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.ocr-btn:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(0,0,0,.3)}
.ocr-btn-primary{background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff}
.ocr-btn-outline{background:transparent;border:1px solid #475569;color:#94a3b8}
.ocr-file-input{display:none}
.ocr-file-name{color:#34d399;font-weight:600;font-size:14px;margin-top:12px}
.ocr-type-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px}
.ocr-type-option{position:relative}
.ocr-type-option input{position:absolute;opacity:0;pointer-events:none}
.ocr-type-option label{display:block;background:#0f172a;border:2px solid #334155;border-radius:10px;padding:14px 12px;text-align:center;cursor:pointer;transition:all .2s;font-size:12px;color:#94a3b8}
.ocr-type-option label i{display:block;font-size:20px;margin-bottom:6px;color:#64748b;transition:color .2s}
.ocr-type-option input:checked + label{border-color:#3b82f6;background:rgba(59,130,246,.1);color:#60a5fa}
.ocr-type-option input:checked + label i{color:#3b82f6}
.ocr-type-option label:hover{border-color:#475569}
</style>

<div class="ocr-page">
    <div class="ocr-header">
        <div class="container-fluid px-4" class="style-84072">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-white"><i class="fas fa-cloud-upload-alt me-2"></i>Upload Document</h4>
                    <p class="mb-0" class="style-29848">Upload a document for OCR field extraction and verification</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/ocr" class="ocr-btn ocr-btn-outline"><i class="fas fa-arrow-left"></i>Back</a>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4" class="style-86238">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="ocr-card">
                    <form method="POST" action="<?= BASE_URL ?>/admin/ocr/store" enctype="multipart/form-data" id="ocrUploadForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

                        <label class="ocr-label">Document Type</label>
                        <div class="ocr-type-grid mb-4">
                            <?php
                            $typeIcons = [
                                'aadhaar' => 'fas fa-id-card',
                                'pan' => 'fas fa-credit-card',
                                'passport' => 'fas fa-passport',
                                'driving_license' => 'fas fa-car',
                                'cheque' => 'fas fa-money-check-alt',
                                'invoice' => 'fas fa-file-invoice-dollar',
                                'contract' => 'fas fa-file-contract',
                            ];
                            foreach ($doc_types as $i => $dt): ?>
                                <div class="ocr-type-option">
                                    <input type="radio" name="document_type" id="doctype_<?= $dt ?>" value="<?= $dt ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                    <label for="doctype_<?= $dt ?>">
                                        <i class="<?= $typeIcons[$dt] ?? 'fas fa-file' ?>"></i>
                                        <?= $doc_type_labels[$dt] ?? $dt ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <label class="ocr-label">Upload File</label>
                        <div class="ocr-upload-zone" id="uploadZone">
                            <input type="file" name="document_file" id="documentFile" class="ocr-file-input" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="ocr-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div class="ocr-upload-text">Drag & drop your file here or <span class="style-50045">browse</span></div>
                            <div class="ocr-upload-hint">JPG, PNG, or PDF — Max 10MB</div>
                            <div class="ocr-file-name" id="fileName"></div>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <button type="submit" class="ocr-btn ocr-btn-primary" id="submitBtn" disabled>
                                <i class="fas fa-upload"></i>Upload & Process
                            </button>
                            <a href="<?= BASE_URL ?>/admin/ocr" class="ocr-btn ocr-btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const zone = document.getElementById('uploadZone');
const fileInput = document.getElementById('documentFile');
const fileName = document.getElementById('fileName');
const submitBtn = document.getElementById('submitBtn');

zone.addEventListener('click', () => fileInput.click());
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handleFile(e.dataTransfer.files[0]);
    }
});
fileInput.addEventListener('change', () => { if (fileInput.files.length) handleFile(fileInput.files[0]); });

function handleFile(file) {
    fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    zone.classList.add('has-file');
    submitBtn.disabled = false;
}
</script>
