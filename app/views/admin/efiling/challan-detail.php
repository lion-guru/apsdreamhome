<?php
$page_title = $page_title ?? 'Challan Detail';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-money-check me-2 text-warning"></i><?= htmlspecialchars($page_title) ?></h4>
        <span class="text-muted">Form 281 Challan</span>
    </div>
    <a href="<?= BASE_URL ?>/admin/efiling/tds/challans" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Challans</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body aps-cp-card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Challan Number</label>
                <div class="fw-bold"><?= htmlspecialchars($challan['challan_number'] ?? '-') ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">BSR Code</label>
                <div class="fw-bold"><?= htmlspecialchars($challan['bsr_code'] ?? '-') ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">TDS Section</label>
                <div><span class="badge bg-secondary fs-6"><?= htmlspecialchars($challan['tds_section'] ?? $challan['section'] ?? '-') ?></span></div>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Assessment Year</label>
                <div class="fw-bold"><?= htmlspecialchars($challan['assessment_year'] ?? '-') ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Financial Year</label>
                <div class="fw-bold"><?= htmlspecialchars($challan['financial_year'] ?? '-') ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Quarter</label>
                <div class="fw-bold">Q<?= $challan['quarter'] ?? '-' ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Deposit Date</label>
                <div class="fw-bold"><?= $challan['deposit_date'] ? date('d M Y', strtotime($challan['deposit_date'])) : '-' ?></div>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Deposited Via</label>
                <div><span class="badge bg-<?= ($challan['deposited_via'] ?? '') === 'online' ? 'primary' : 'info' ?>"><?= ucfirst($challan['deposited_via'] ?? '-') ?></span></div>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Status</label>
                <div><span class="badge bg-<?= ($challan['status'] ?? '') === 'deposited' ? 'success' : (($challan['status'] ?? '') === 'failed' ? 'danger' : 'secondary') ?>"><?= ucfirst($challan['status'] ?? '-') ?></span></div>
            </div>
        </div>

        <hr>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">TDS Amount</label>
                <div class="fs-4 fw-bold text-primary">?<?= number_format($challan['tds_amount'] ?? $challan['total_with_charges'] ?? 0, 0) ?></div>
            </div>
            <?php if (($challan['interest_amount'] ?? 0) > 0): ?>
            <div class="col-md-3">
                <label class="form-label small text-muted">Interest</label>
                <div class="fs-5 fw-bold text-warning">?<?= number_format($challan['interest_amount'], 0) ?></div>
            </div>
            <?php endif; ?>
            <?php if (($challan['penalty_amount'] ?? 0) > 0): ?>
            <div class="col-md-3">
                <label class="form-label small text-muted">Penalty</label>
                <div class="fs-5 fw-bold text-danger">?<?= number_format($challan['penalty_amount'], 0) ?></div>
            </div>
            <?php endif; ?>
            <?php if (($challan['late_fee'] ?? 0) > 0): ?>
            <div class="col-md-3">
                <label class="form-label small text-muted">Late Fee</label>
                <div class="fs-5 fw-bold text-danger">?<?= number_format($challan['late_fee'], 0) ?></div>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <label class="form-label small text-muted">Total Deposited</label>
                <div class="fs-4 fw-bold text-success">?<?= number_format($challan['total_with_charges'] ?? $challan['tds_amount'] ?? 0, 0) ?></div>
            </div>
        </div>

        <?php if (!empty($challan['remarks'])): ?>
            <hr>
            <div>
                <label class="form-label small text-muted">Remarks</label>
                <div class="small"><?= nl2br(htmlspecialchars($challan['remarks'])) ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>

