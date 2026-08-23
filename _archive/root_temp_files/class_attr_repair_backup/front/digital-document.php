ï»¿<?php
/** @var array $booking */
/** @var array $document */
$base = BASE_URL ?? '/apsdreamhome';
?>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/booking/digital/<?= urlencode($booking['booking_number']) ?>">Booking <?= htmlspecialchars($booking['booking_number'] ?? '') ?></a></li>
                    <li class="breadcrumb-item active">Document</li>
                </ol>
            </nav>
            <h2 class="mb-1"><?= htmlspecialchars($document['title'] ?? '') ?></h2>
            <p class="text-muted">Document #: <?= htmlspecialchars($document['document_number'] ?? '') ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Document Content</h5>
                    <?php if (($document['status'] ?? '') === 'signed' && ($document['signed_at'] ?? null)): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>Signed on <?= date('d M Y H:i', strtotime($document['signed_at'])) ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-clock me-1"></i>Pending Signature
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body" class="style-18847">
                    <div class="document-content" class="style-80284">
                        <?= nl2br(htmlspecialchars($document['content'] ?? '')) ?>
                    </div>
                </div>
                <?php if (($document['status'] ?? '') !== 'signed'): ?>
                <div class="card-footer bg-light">
                    <form id="signForm" action="<?= BASE_URL ?>/booking/digital/<?= urlencode($booking['booking_number']) ?>/document/<?= $document['id'] ?>/sign" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Digital Signature</label>
                            <div class="border rounded p-3" class="style-36355">
                                <canvas id="signatureCanvas" width="600" height="100" class="style-76084"></canvas>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSignature()">Clear</button>
                                    <span class="ms-2 text-muted small">Draw your signature above</span>
                                </div>
                            </div>
                            <input type="hidden" name="signature_data" id="signatureData">
                            <input type="hidden" name="signature_type" value="digital">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="videoConsentCheck" name="video_consent" value="1">
                            <label class="form-check-label" for="videoConsentCheck">
                                I have recorded video consent accepting all terms (optional but recommended)
                            </label>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary" id="signBtn" disabled>
                                <i class="fas fa-pen me-1"></i>Sign Document
                            </button>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <div class="card-footer bg-light">
                    <div class="d-flex gap-2">
                        <a href="/booking/digital/<?= urlencode($booking['booking_number']) ?>/download/<?= $document['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i>Download Signed PDF
                        </a>
                        <a href="/booking/digital/<?= urlencode($booking['booking_number']) ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Booking
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm sticky-top" class="style-76854">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Document Info</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted small">Number</dt>
                        <dd class="col-sm-8 fw-bold"><?= htmlspecialchars($document['document_number'] ?? '') ?></dd>
                        
                        <dt class="col-sm-4 text-muted small">Category</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($document['category_name'] ?? 'General') ?></dd>
                        
                        <dt class="col-sm-4 text-muted small">Status</dt>
                        <dd class="col-sm-8">
                            <?php if (($document['status'] ?? '') === 'signed'): ?>
                                <span class="badge bg-success">Signed</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-sm-4 text-muted small">Created</dt>
                        <dd class="col-sm-8"><?= date('d M Y H:i', strtotime($document['created_at'] ?? date('Y-m-d'))) ?></dd>
                        
                        <?php if (($document['status'] ?? '') === 'signed' && ($document['signed_at'] ?? null)): ?>
                        <dt class="col-sm-4 text-muted small">Signed</dt>
                        <dd class="col-sm-8"><?= date('d M Y H:i', strtotime($document['signed_at'])) ?></dd>
                        
                        <dt class="col-sm-4 text-muted small">Signed By</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($document['signed_by_name'] ?? $booking['customer_name'] ?? '') ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const canvas = document.getElementById('signatureCanvas');
const ctx = canvas ? canvas.getContext('2d') : null;
let drawing = false;
let signatureData = '';

if (canvas && ctx) {
    // Setup canvas
    ctx.strokeStyle = '#2c3e50';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    function getCoords(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function startDrawing(e) {
        drawing = true;
        const coords = getCoords(e);
        ctx.beginPath();
        ctx.moveTo(coords.x, coords.y);
    }

    function draw(e) {
        if (!drawing) return;
        e.preventDefault();
        const coords = getCoords(e);
        ctx.lineTo(coords.x, coords.y);
        ctx.stroke();
    }

    function stopDrawing() {
        drawing = false;
        // Save signature as base64
        signatureData = canvas.toDataURL('image/png');
        document.getElementById('signatureData').value = signatureData;
        document.getElementById('signBtn').disabled = false;
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    canvas.addEventListener('touchstart', startDrawing, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDrawing);

    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        signatureData = '';
        document.getElementById('signatureData').value = '';
        document.getElementById('signBtn').disabled = true;
    }

    window.clearSignature = clearSignature;

    // Form validation
    document.getElementById('signForm')?.addEventListener('submit', function(e) {
        if (!signatureData) {
            e.preventDefault();
            alert('Please draw your signature first');
        }
    });
}
</script>