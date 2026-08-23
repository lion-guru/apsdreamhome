<?php
$page_title = $page_title ?? 'Document Detail — OCR';
$doc = $doc ?? [];
$fields = $fields ?? [];
$structured_data = $structured_data ?? [];
$doc_type_label = $doc_type_label ?? 'Unknown';
$doc_status = $doc['ocr_status'] ?? 'pending';
$validation = $doc['validation_status'] ?? 'pending';
$conf = (float)($doc['confidence_score'] ?? 0);
?>

<style>
.ocr-page{background:#0f172a;min-height:100vh;color:#e2e8f0;padding:0 0 40px}
.ocr-header{background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);border-bottom:1px solid #334155;padding:28px 0 24px}
.ocr-card{background:#1e293b;border:1px solid #334155;border-radius:14px;overflow:hidden}
.ocr-card-body{padding:24px}
.ocr-card-header{background:#0f172a;padding:16px 20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center}
.ocr-card-header h6{color:#e2e8f0;font-weight:700;margin:0;font-size:14px}
.ocr-badge{display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
.ocr-badge-pending{background:rgba(251,191,36,.15);color:#fbbf24}
.ocr-badge-processing{background:rgba(59,130,246,.15);color:#60a5fa}
.ocr-badge-completed{background:rgba(16,185,129,.15);color:#34d399}
.ocr-badge-failed{background:rgba(239,68,68,.15);color:#f87171}
.ocr-badge-valid{background:rgba(16,185,129,.15);color:#34d399}
.ocr-badge-invalid{background:rgba(239,68,68,.15);color:#f87171}
.ocr-field-row{display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid #334155}
.ocr-field-row:last-child{border-bottom:none}
.ocr-field-name{color:#94a3b8;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.6px}
.ocr-field-value{color:#e2e8f0;font-size:14px;font-weight:500;max-width:60%}
.ocr-field-empty{color:#475569;font-style:italic}
.ocr-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.ocr-info-item{background:#0f172a;border-radius:10px;padding:14px 16px}
.ocr-info-item label{color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.8px;display:block;margin-bottom:4px}
.ocr-info-item span{color:#e2e8f0;font-size:14px;font-weight:600}
.ocr-btn{border-radius:10px;padding:10px 20px;font-weight:600;font-size:13px;border:none;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.ocr-btn:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(0,0,0,.3)}
.ocr-btn-primary{background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff}
.ocr-btn-success{background:linear-gradient(135deg,#10b981,#059669);color:#fff}
.ocr-btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff}
.ocr-btn-outline{background:transparent;border:1px solid #475569;color:#94a3b8}
.ocr-btn-warning{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff}
.ocr-confidence-bar{height:8px;border-radius:4px;background:#0f172a;overflow:hidden;margin-top:6px}
.ocr-confidence-fill{height:100%;border-radius:4px;transition:width .5s}
.ocr-reject-form{background:#0f172a;border:1px solid #334155;border-radius:10px;padding:16px;margin-top:12px;display:none}
.ocr-reject-form.show{display:block}
.ocr-textarea{width:100%;background:#1e293b;border:1px solid #334155;color:#e2e8f0;border-radius:8px;padding:10px 14px;font-size:13px;resize:vertical;min-height:70px;outline:none}
.ocr-textarea:focus{border-color:#ef4444}
.ocr-raw-text{background:#0f172a;border:1px solid #334155;border-radius:10px;padding:16px;font-family:'Courier New',monospace;font-size:12px;color:#94a3b8;white-space:pre-wrap;max-height:300px;overflow-y:auto;line-height:1.6}
</style>

<div class="ocr-page">
    <div class="ocr-header">
        <div class="container-fluid px-4 style-84072">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 fw-bold text-white"><i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($doc['original_name'] ?? 'Document') ?></h4>
                    <p class="mb-0 style-29848"><?= $doc_type_label ?> &middot; #<?= $doc['id'] ?? 0 ?> &middot; <?= date('d M Y, h:i A', strtotime($doc['created_at'] ?? 'now')) ?></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/admin/ocr" class="ocr-btn ocr-btn-outline"><i class="fas fa-arrow-left"></i>Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 style-86238">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="ocr-card mb-4">
                    <div class="ocr-card-header">
                        <h6><i class="fas fa-info-circle me-1"></i>Document Info</h6>
                        <div class="d-flex gap-2">
                            <?php if ($doc_status === 'pending'): ?>
                                <form method="POST" action="<?= BASE_URL ?>/admin/ocr/process/<?= $doc['id'] ?>" class="style-71727">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                    <button type="submit" class="ocr-btn ocr-btn-warning"><i class="fas fa-play me-1"></i>Run OCR</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ocr-card-body">
                        <div class="ocr-info-grid mb-3">
                            <div class="ocr-info-item">
                                <label>Status</label>
                                <span><span class="ocr-badge ocr-badge-<?= $doc_status ?>"><?= ucfirst($doc_status) ?></span></span>
                            </div>
                            <div class="ocr-info-item">
                                <label>Validation</label>
                                <span><span class="ocr-badge ocr-badge-<?= $validation ?>"><?= ucfirst($validation) ?></span></span>
                            </div>
                            <div class="ocr-info-item">
                                <label>Confidence</label>
                                <span class="style-70911"><?= number_format($conf * 100, 0) ?>%</span>
                                <div class="ocr-confidence-bar">
                                    <div class="ocr-confidence-fill style-21270"></div>
                                </div>
                            </div>
                            <div class="ocr-info-item">
                                <label>File Type</label>
                                <span><?= $doc['mime_type'] ?? 'Unknown' ?></span>
                            </div>
                            <div class="ocr-info-item">
                                <label>File Size</label>
                                <span><?= $doc['file_size'] ? number_format($doc['file_size'] / 1024, 1) . ' KB' : 'N/A' ?></span>
                            </div>
                            <div class="ocr-info-item">
                                <label>Uploaded</label>
                                <span><?= date('d M Y, h:i A', strtotime($doc['created_at'] ?? 'now')) ?></span>
                            </div>
                        </div>

                        <?php if (!empty($doc['error_message'])): ?>
                            <div class="style-49565">
                                <i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($doc['error_message'] ?? '') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($doc['rejection_reason'])): ?>
                            <div class="style-49565">
                                <i class="fas fa-ban me-1"></i><strong>Rejection Reason:</strong> <?= htmlspecialchars($doc['rejection_reason'] ?? '') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($fields) || !empty($structured_data)): ?>
                    <div class="ocr-card mb-4">
                        <div class="ocr-card-header">
                            <h6><i class="fas fa-database me-1"></i>Extracted Fields</h6>
                        </div>
                        <div class="ocr-card-body">
                            <?php
                            $displayFields = !empty($structured_data) ? $structured_data : [];
                            if (empty($displayFields) && !empty($fields)) {
                                foreach ($fields as $f) {
                                    $displayFields[$f['field_name']] = $f['field_value'];
                                }
                            }
                            if (!empty($displayFields)):
                                foreach ($displayFields as $fName => $fVal): ?>
                                    <div class="ocr-field-row">
                                        <div class="ocr-field-name"><?= ucwords(str_replace('_', ' ', $fName)) ?></div>
                                        <div class="ocr-field-value <?= empty($fVal) ? 'ocr-field-empty' : '' ?>">
                                            <?= $fVal !== null && $fVal !== '' ? htmlspecialchars($fVal ?? '') : 'Not detected' ?>
                                        </div>
                                    </div>
                                <?php endforeach;
                            else: ?>
                                <p class="style-67067">No fields extracted yet. Run OCR processing first.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($doc['extracted_text'])): ?>
                    <div class="ocr-card">
                        <div class="ocr-card-header">
                            <h6><i class="fas fa-align-left me-1"></i>Raw Extracted Text</h6>
                        </div>
                        <div class="ocr-card-body">
                            <div class="ocr-raw-text"><?= htmlspecialchars($doc['extracted_text'] ?? '') ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="ocr-card mb-4">
                    <div class="ocr-card-header">
                        <h6><i class="fas fa-image me-1"></i>Preview</h6>
                    </div>
                    <div class="ocr-card-body style-88083">
                        <?php if (!empty($doc['file_path'])): ?>
                            <?php if (strpos($doc['mime_type'] ?? '', 'image') !== false): ?>
                                <img src="<?= BASE_URL . $doc['file_path'] ?>" alt="Document" class="style-44476">
                            <?php else: ?>
                                <div class="style-4209">
                                    <i class="fas fa-file-pdf style-19932"></i>
                                    <p class="style-40870">PDF Document</p>
                                    <a href="<?= BASE_URL . $doc['file_path'] ?>" target="_blank" class="ocr-btn ocr-btn-outline mt-2 style-86354"><i class="fas fa-external-link-alt me-1"></i>Open PDF</a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="style-4209"><i class="fas fa-image style-29812"></i><p class="style-87981">No preview available</p></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ocr-card mb-4">
                    <div class="ocr-card-header">
                        <h6><i class="fas fa-check-double me-1"></i>Verification</h6>
                    </div>
                    <div class="ocr-card-body">
                        <?php if ($validation === 'valid'): ?>
                            <div class="style-41883">
                                <i class="fas fa-shield-alt style-50363"></i>
                                <p class="style-91466">Verified & Approved</p>
                            </div>
                        <?php elseif ($validation === 'invalid'): ?>
                            <div class="style-41883">
                                <i class="fas fa-ban style-95217"></i>
                                <p class="style-20478">Rejected</p>
                            </div>
                        <?php else: ?>
                            <p class="style-93017">Review extracted fields and verify the document.</p>
                            <form method="POST" action="<?= BASE_URL ?>/admin/ocr/approve/<?= $doc['id'] ?>" class="style-57864">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                <button type="submit" class="ocr-btn ocr-btn-success style-16158"><i class="fas fa-check me-1"></i>Approve & Verify</button>
                            </form>
                            <button type="button" class="ocr-btn ocr-btn-danger style-16158" onclick="document.getElementById('rejectSection').classList.toggle('show')"><i class="fas fa-times me-1"></i>Reject</button>
                            <div id="rejectSection" class="ocr-reject-form">
                                <form method="POST" action="<?= BASE_URL ?>/admin/ocr/reject/<?= $doc['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                    <label class="style-60951">Rejection Reason</label>
                                    <textarea name="rejection_reason" class="ocr-textarea" placeholder="Enter reason for rejection..." required></textarea>
                                    <button type="submit" class="ocr-btn ocr-btn-danger mt-2 style-86354"><i class="fas fa-times me-1"></i>Confirm Rejection</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ocr-card">
                    <div class="ocr-card-header">
                        <h6><i class="fas fa-cog me-1"></i>Actions</h6>
                    </div>
                    <div class="ocr-card-body d-flex flex-column gap-2">
                        <form method="POST" action="<?= BASE_URL ?>/admin/ocr/delete/<?= $doc['id'] ?>" data-aps-confirm="Delete this document permanently? This cannot be undone.">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                            <button type="submit" class="ocr-btn ocr-btn-danger style-16158"><i class="fas fa-trash me-1"></i>Delete Document</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
