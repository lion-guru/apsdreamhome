<?php
$page_title = $page_title ?? 'OCR Document Pipeline';
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'processing' => 0, 'completed' => 0, 'failed' => 0, 'valid' => 0, 'invalid' => 0];
$documents = $documents ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$status = $status ?? '';
$doctype = $doctype ?? '';
$search = $search ?? '';
$doc_types = $doc_types ?? [];
$doc_type_labels = $doc_type_labels ?? [];
?>

<style>
.ocr-page{background:#0f172a;min-height:100vh;color:#e2e8f0;padding:0 0 40px}
.ocr-header{background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);border-bottom:1px solid #334155;padding:28px 0 24px;position:relative;overflow:hidden}
.ocr-header::before{content:'';position:absolute;top:-50%;right:-5%;width:350px;height:350px;background:radial-gradient(circle,rgba(59,130,246,.15) 0%,transparent 70%);border-radius:50%}
.ocr-header::after{content:'';position:absolute;bottom:-40%;left:15%;width:250px;height:250px;background:radial-gradient(circle,rgba(16,185,129,.1) 0%,transparent 70%);border-radius:50%}
.ocr-stat-card{background:#1e293b;border:1px solid #334155;border-radius:14px;padding:20px;text-align:center;transition:transform .2s,box-shadow .2s}
.ocr-stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.3)}
.ocr-stat-icon{width:48px;height:48px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:10px}
.ocr-stat-value{font-size:28px;font-weight:800;margin:0}
.ocr-stat-label{font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin:4px 0 0}
.ocr-card{background:#1e293b;border:1px solid #334155;border-radius:14px;overflow:hidden}
.ocr-card-header{background:#1e293b;padding:16px 20px;border-bottom:1px solid #334155;display:flex;justify-content:space-between;align-items:center}
.ocr-card-header h6{color:#e2e8f0;font-weight:700;margin:0;font-size:14px}
.ocr-table{width:100%;border-collapse:collapse}
.ocr-table th{background:#0f172a;color:#94a3b8;font-size:11px;text-transform:uppercase;letter-spacing:1px;padding:10px 14px;text-align:left;border-bottom:1px solid #334155;font-weight:600}
.ocr-table td{padding:12px 14px;border-bottom:1px solid #1e293b;color:#e2e8f0;font-size:13px;vertical-align:middle}
.ocr-table tr:hover td{background:rgba(59,130,246,.05)}
.ocr-table tr:last-child td{border-bottom:none}
.ocr-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.3px}
.ocr-badge-pending{background:rgba(251,191,36,.15);color:#fbbf24}
.ocr-badge-processing{background:rgba(59,130,246,.15);color:#60a5fa}
.ocr-badge-completed{background:rgba(16,185,129,.15);color:#34d399}
.ocr-badge-failed{background:rgba(239,68,68,.15);color:#f87171}
.ocr-badge-valid{background:rgba(16,185,129,.15);color:#34d399}
.ocr-badge-invalid{background:rgba(239,68,68,.15);color:#f87171}
.ocr-badge-identity{background:rgba(99,102,241,.15);color:#a78bfa}
.ocr-badge-financial{background:rgba(245,158,11,.15);color:#fbbf24}
.ocr-badge-legal{background:rgba(16,185,129,.15);color:#34d399}
.ocr-badge-other{background:rgba(148,163,184,.15);color:#94a3b8}
.ocr-filter-bar{background:#1e293b;border:1px solid #334155;border-radius:10px;padding:12px 16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:20px}
.ocr-filter-bar select,.ocr-filter-bar input{background:#0f172a;border:1px solid #334155;color:#e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;transition:border-color .2s}
.ocr-filter-bar select:focus,.ocr-filter-bar input:focus{border-color:#3b82f6}
.ocr-btn{border-radius:10px;padding:9px 18px;font-weight:600;font-size:13px;border:none;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.ocr-btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.3)}
.ocr-btn-primary{background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff}
.ocr-btn-success{background:linear-gradient(135deg,#10b981,#059669);color:#fff}
.ocr-btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff}
.ocr-btn-outline{background:transparent;border:1px solid #475569;color:#94a3b8}
.ocr-btn-outline:hover{border-color:#3b82f6;color:#60a5fa}
.ocr-empty{text-align:center;padding:60px 20px;color:#64748b}
.ocr-empty i{font-size:48px;margin-bottom:16px;opacity:.5}
.ocr-pagination{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:20px}
.ocr-pagination a,.ocr-pagination span{background:#1e293b;border:1px solid #334155;color:#94a3b8;padding:6px 12px;border-radius:8px;font-size:12px;text-decoration:none;transition:all .2s}
.ocr-pagination a:hover{border-color:#3b82f6;color:#60a5fa}
.ocr-pagination .active{background:#3b82f6;border-color:#3b82f6;color:#fff}
.ocr-filename{max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block}
</style>

<div class="ocr-page">
    <div class="ocr-header">
        <div class="container-fluid px-4" class="style-84072">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 fw-bold text-white"><i class="fas fa-eye me-2"></i>OCR Document Pipeline</h4>
                    <p class="mb-0" class="style-29848">Upload, extract, and verify document fields automatically</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/admin/ocr/upload" class="ocr-btn ocr-btn-primary"><i class="fas fa-cloud-upload-alt"></i>Upload Document</a>
                    <a href="<?= BASE_URL ?>/admin/ocr/templates" class="ocr-btn ocr-btn-outline"><i class="fas fa-cog"></i>Templates</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4" class="style-71772">
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-2">
                <div class="ocr-stat-card">
                    <div class="ocr-stat-icon" class="style-78178"><i class="fas fa-file-alt"></i></div>
                    <div class="ocr-stat-value" class="style-61987"><?= $stats['total'] ?></div>
                    <div class="ocr-stat-label">Total</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="ocr-stat-card">
                    <div class="ocr-stat-icon" class="style-83626"><i class="fas fa-clock"></i></div>
                    <div class="ocr-stat-value" class="style-81434"><?= $stats['pending'] ?></div>
                    <div class="ocr-stat-label">Pending</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="ocr-stat-card">
                    <div class="ocr-stat-icon" class="style-51774"><i class="fas fa-cog fa-spin"></i></div>
                    <div class="ocr-stat-value" class="style-45299"><?= $stats['processing'] ?></div>
                    <div class="ocr-stat-label">Processing</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="ocr-stat-card">
                    <div class="ocr-stat-icon" class="style-97604"><i class="fas fa-check-circle"></i></div>
                    <div class="ocr-stat-value" class="style-49307"><?= $stats['completed'] ?></div>
                    <div class="ocr-stat-label">Completed</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="ocr-stat-card">
                    <div class="ocr-stat-icon" class="style-97604"><i class="fas fa-shield-alt"></i></div>
                    <div class="ocr-stat-value" class="style-49307"><?= $stats['valid'] ?></div>
                    <div class="ocr-stat-label">Verified</div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="ocr-stat-card">
                    <div class="ocr-stat-icon" class="style-19876"><i class="fas fa-times-circle"></i></div>
                    <div class="ocr-stat-value" class="style-37569"><?= $stats['failed'] + $stats['invalid'] ?></div>
                    <div class="ocr-stat-label">Failed / Invalid</div>
                </div>
            </div>
        </div>

        <form class="ocr-filter-bar" method="GET" action="<?= BASE_URL ?>/admin/ocr">
            <input type="text" name="q" placeholder="Search filename..." value="<?= htmlspecialchars($search) ?>" class="style-55638">
            <select name="status">
                <option value="">All Status</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
            </select>
            <select name="doctype">
                <option value="">All Types</option>
                <?php foreach ($doc_types as $dt): ?>
                    <option value="<?= $dt ?>" <?= $doctype === $dt ? 'selected' : '' ?>><?= $doc_type_labels[$dt] ?? $dt ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="ocr-btn ocr-btn-outline" class="style-81125"><i class="fas fa-search"></i>Filter</button>
            <?php if ($search || $status || $doctype): ?>
                <a href="<?= BASE_URL ?>/admin/ocr" class="ocr-btn ocr-btn-outline" class="style-81125"><i class="fas fa-times"></i>Clear</a>
            <?php endif; ?>
        </form>

        <div class="ocr-card">
            <div class="ocr-card-header">
                <h6><i class="fas fa-list me-1"></i>Documents (<?= number_format($total) ?>)</h6>
            </div>
            <?php if (empty($documents)): ?>
                <div class="ocr-empty">
                    <i class="fas fa-file-upload d-block"></i>
                    <h6 class="mb-2">No documents found</h6>
                    <p class="mb-3" class="style-87981">Upload your first document to start OCR extraction</p>
                    <a href="<?= BASE_URL ?>/admin/ocr/upload" class="ocr-btn ocr-btn-primary"><i class="fas fa-cloud-upload-alt me-1"></i>Upload Document</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="ocr-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>File</th>
                                <th>Type</th>
                                <th>OCR Status</th>
                                <th>Confidence</th>
                                <th>Validation</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td class="style-17256">#<?= $doc['id'] ?></td>
                                    <td>
                                        <span class="ocr-filename" title="<?= htmlspecialchars($doc['original_name'] ?? '') ?>">
                                            <i class="fas fa-file me-1" class="style-74529"></i><?= htmlspecialchars($doc['original_name'] ?? $doc['file_name'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td><span class="ocr-badge ocr-badge-identity"><?= $doc_type_labels[$doc['document_type']] ?? $doc['document_type'] ?? '?' ?></span></td>
                                    <td>
                                        <?php
                                        $sClass = 'ocr-badge-' . ($doc['ocr_status'] ?? 'pending');
                                        ?>
                                        <span class="ocr-badge <?= $sClass ?>"><?= ucfirst($doc['ocr_status'] ?? 'pending') ?></span>
                                    </td>
                                    <td>
                                        <?php $conf = (float)($doc['confidence_score'] ?? 0); ?>
                                        <span class="style-30876">
                                            <?= number_format($conf * 100, 0) ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php $vClass = 'ocr-badge-' . ($doc['validation_status'] ?? 'pending'); ?>
                                        <span class="ocr-badge <?= $vClass ?>"><?= ucfirst($doc['validation_status'] ?? 'pending') ?></span>
                                    </td>
                                    <td class="style-17256"><?= date('d M Y, h:i A', strtotime($doc['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="<?= BASE_URL ?>/admin/ocr/detail/<?= $doc['id'] ?>" class="ocr-btn ocr-btn-outline" class="style-18377"><i class="fas fa-eye"></i></a>
                                            <?php if (($doc['ocr_status'] ?? '') === 'pending'): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/ocr/process/<?= $doc['id'] ?>" class="style-71727">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                                    <button type="submit" class="ocr-btn ocr-btn-success" class="style-18377"><i class="fas fa-play"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/ocr/delete/<?= $doc['id'] ?>" class="style-71727" onsubmit="return confirm('Delete this document permanently?')">
                                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                                                <button type="submit" class="ocr-btn ocr-btn-danger" class="style-18377"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="ocr-pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&doctype=<?= urlencode($doctype) ?>&q=<?= urlencode($search) ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>
                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&doctype=<?= urlencode($doctype) ?>&q=<?= urlencode($search) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&doctype=<?= urlencode($doctype) ?>&q=<?= urlencode($search) ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
