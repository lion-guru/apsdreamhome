<?php
/** @var array $booking */
/** @var array $plot */
/** @var array $documents */
/** @var array $schedule */
/** @var string $booking_token */
$csrf = $_SESSION['csrf_token'] ?? '';
$base = BASE_URL ?? '/apsdreamhome';
?>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="fas fa-file-contract text-primary me-2"></i><?= htmlspecialchars($booking['booking_number'] ?? '') ?></h2>
                    <p class="text-muted mb-0">Complete your booking digitally - review documents, sign agreements, and set up EMI</p>
                </div>
                <span class="badge bg-success fs-6 px-3 py-2">
                    Status: <?= ucfirst(str_replace('_', ' ', $booking['status'] ?? 'pending')) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Booking Summary Card -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Booking Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Booking Number</label>
                            <div class="fw-bold"><?= htmlspecialchars($booking['booking_number'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Booking Date</label>
                            <div class="fw-bold"><?= date('d M Y', strtotime($booking['booking_date'] ?? date('Y-m-d'))) ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Plot</label>
                            <div class="fw-bold"><?= htmlspecialchars($plot['plot_code'] ?? $plot['plot_number'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Colony</label>
                            <div class="fw-bold"><?= htmlspecialchars($plot['colony_name'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Total Plot Value</label>
                            <div class="fw-bold text-primary fs-5">₹<?= number_format((float)($booking['total_plot_value'] ?? 0), 2) ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Agreement Value</label>
                            <div class="fw-bold text-success fs-5">₹<?= number_format((float)($booking['agreement_value'] ?? 0), 2) ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Token Paid</label>
                            <div class="fw-bold">₹<?= number_format((float)($booking['booking_amount'] ?? 0), 2) ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Balance</label>
                            <div class="fw-bold text-danger">₹<?= number_format((float)($booking['agreement_value'] ?? 0) - (float)($booking['booking_amount'] ?? 0), 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Next Steps</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#documents" class="btn btn-outline-primary">
                            <i class="fas fa-file-contract me-2"></i>Review & Sign Documents
                        </a>
                        <a href="#emi" class="btn btn-outline-success">
                            <i class="fas fa-calculator me-2"></i>Setup EMI Schedule
                        </a>
                        <a href="#video-consent" class="btn btn-outline-info">
                            <i class="fas fa-video me-2"></i>Record Video Consent
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legal Documents Section -->
    <div id="documents" class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-file-signature text-primary me-2"></i>Legal Documents for Digital Signature</h5>
        </div>
        <div class="card-body">
            <?php if (empty($documents)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-file-contract fa-2x mb-2"></i>
                    <p>No documents found for this booking. Please contact admin to generate documents.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($documents as $doc): 
                        $signed = $doc['signed_at'] ?? $doc['status'] === 'signed';
                        $statusClass = $signed ? 'success' : 'warning';
                        $statusIcon = $signed ? 'fa-check-circle' : 'fa-clock';
                        $statusText = $signed ? 'Signed' : 'Pending Signature';
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-<?= $statusClass ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0"><?= htmlspecialchars($doc['title'] ?? '') ?></h6>
                                    <span class="badge bg-<?= $statusClass ?>"><i class="fas <?= $statusIcon ?> me-1"></i><?= $statusText ?></span>
                                </div>
                                <p class="card-text text-muted small"><?= htmlspecialchars($doc['category_name'] ?? 'General') ?></p>
                                <small class="text-muted">Document #: <?= htmlspecialchars($doc['document_number'] ?? '') ?></small>
                                <div class="mt-3">
                                    <?php if (!$signed): ?>
                                        <a href="/booking/digital/<?= urlencode($booking['booking_number']) ?>/document/<?= $doc['id'] ?>" class="btn btn-sm btn-primary w-100">
                                            <i class="fas fa-pen me-1"></i>Sign Document
                                        </a>
                                    <?php else: ?>
                                        <a href="/booking/digital/<?= urlencode($booking['booking_number']) ?>/document/<?= $doc['id'] ?>" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="fas fa-eye me-1"></i>View Signed Document
                                        </a>
                                        <a href="/booking/digital/<?= urlencode($booking['booking_number']) ?>/download/<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary w-100 mt-1">
                                            <i class="fas fa-download me-1"></i>Download PDF
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- EMI Schedule Section -->
    <div id="emi" class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-calculator text-success me-2"></i>EMI Payment Schedule</h5>
        </div>
        <div class="card-body">
            <form id="emiForm" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Tenure (Months)</label>
                    <input type="number" name="tenure" class="form-control" value="60" min="6" max="360" step="6">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Interest Rate (% p.a.)</label>
                    <input type="number" name="rate" class="form-control" value="9.5" min="0" max="30" step="0.25">
                </div>
                <div class="col-md-3">
                    <label class="form-label">EMI Type</label>
                    <select name="type" class="form-select">
                        <option value="reducing">Reducing Balance</option>
                        <option value="fixed">Fixed/Flat Rate</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" id="previewEmiBtn">
                        <i class="fas fa-eye me-1"></i>Preview Schedule
                    </button>
                </div>
            </form>
            
            <div id="emiPreview" class="table-responsive d-none">
                <table class="table table-sm table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Due Date</th>
                            <th>Principal</th>
                            <th>Interest</th>
                            <th>Total EMI</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody id="emiTableBody"></tbody>
                </table>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="text-muted small">Total Principal</div>
                                <div class="fw-bold" id="emiTotalPrincipal">₹0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="text-muted small">Total Interest</div>
                                <div class="fw-bold text-danger" id="emiTotalInterest">₹0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="text-muted small">Total Payable</div>
                                <div class="fw-bold text-primary" id="emiTotalPayable">₹0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button type="button" class="btn btn-success" id="confirmEmiBtn">
                        <i class="fas fa-check me-1"></i>Confirm EMI Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Consent Section -->
    <div id="video-consent" class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-video text-info me-2"></i>Video Consent Recording</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Why Video Consent?</h6>
                <p class="mb-0">To protect both parties legally, we require a short video recording where you confirm you've read and understood all terms & conditions, EMI schedule, and cancellation policy. This video serves as digital evidence of your informed consent.</p>
            </div>
            
            <div id="videoRecorder">
                <video id="preview" autoplay muted playsinline class="w-100 border rounded mb-3 d-none style-39110"></video>
                <div class="row g-3">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-primary w-100" id="startRecording">
                            <i class="fas fa-video me-2"></i>Start Recording
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-primary w-100 d-none" id="stopRecording">
                            <i class="fas fa-stop me-2"></i>Stop & Save
                        </button>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="termsAccepted" required>
                        <label class="form-check-label" for="termsAccepted">
                            I have read and understood all <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a>, 
                            <a href="#" data-bs-toggle="modal" data-bs-target="#emiModal">EMI Schedule</a>, and 
                            <a href="#" data-bs-toggle="modal" data-bs-target="#cancellationModal">Cancellation Policy</a>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="privacyAccepted" required>
                        <label class="form-check-label" for="privacyAccepted">
                            I accept the <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a> and consent to my data being stored securely
                        </label>
                    </div>
                </div>
            </div>
            <div id="videoPreview" class="d-none mt-3">
                <video controls class="w-100 border rounded style-39110"></video>
                <div class="mt-2">
                    <button type="button" class="btn btn-success" id="submitVideoConsent">
                        <i class="fas fa-check me-1"></i>Submit Video Consent
                    </button>
                    <button type="button" class="btn btn-outline-secondary ms-2" id="retakeVideo">
                        <i class="fas fa-redo me-1"></i>Retake
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Final Submit -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
            <div class="alert alert-warning d-none" id="validationAlert"></div>
            <button type="button" class="btn btn-primary btn-lg px-5" id="completeBookingBtn" disabled>
                <i class="fas fa-check-circle me-2"></i>Complete Digital Booking
            </button>
            <p class="text-muted small mt-2">By completing, you confirm all documents are signed, EMI schedule is set, and video consent recorded.</p>
        </div>
    </div>
</div>

<!-- Modals for Terms -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Terms & Conditions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="termsContent">
                <p class="text-muted">Loading terms...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emiModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">EMI Schedule Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="emiModalContent">
                <p class="text-muted">Loading EMI details...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancellationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Cancellation & Refund Policy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Standard Cancellation Policy</h6>
                <ul>
                    <li>Cancellation within 30 days: 90% refund of token amount</li>
                    <li>Cancellation 30-60 days: 75% refund</li>
                    <li>Cancellation 60-90 days: 50% refund</li>
                    <li>Cancellation after 90 days: 25% refund</li>
                    <li>Legal/administrative charges: 5% of agreement value (non-refundable)</li>
                </ul>
                <p class="text-muted small">* Exact terms as per your specific booking agreement</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Your personal data and booking information are stored securely and used only for booking processing, legal compliance, and communication. We do not share your data with third parties except as required by law or for payment processing.</p>
            </div>
        </div>
    </div>
</div>

<script>
const bookingNumber = <?= json_encode($booking['booking_number'] ?? '') ?>;
const bookingId = <?= json_encode($booking['id'] ?? 0) ?>;
const baseUrl = '<?= $base ?>';

document.addEventListener('DOMContentLoaded', function() {
    // EMI Preview
    document.getElementById('previewEmiBtn')?.addEventListener('click', previewEMI);
    document.getElementById('confirmEmiBtn')?.addEventListener('click', confirmEMI);
    
    // Video Recording
    const startBtn = document.getElementById('startRecording');
    const stopBtn = document.getElementById('stopRecording');
    const retakeBtn = document.getElementById('retakeVideo');
    const submitBtn = document.getElementById('submitVideoConsent');
    const previewVideo = document.querySelector('#videoPreview video');
    let mediaRecorder = null;
    let recordedChunks = [];
    
    startBtn?.addEventListener('click', startRecording);
    stopBtn?.addEventListener('click', stopRecording);
    retakeBtn?.addEventListener('click', retakeVideo);
    submitBtn?.addEventListener('click', submitVideoConsent);
    
    // Form validation for complete booking
    document.getElementById('completeBookingBtn')?.addEventListener('click', completeBooking);
    
    // Check all requirements
    function checkCompletion() {
        const termsAccepted = document.getElementById('termsAccepted')?.checked;
        const privacyAccepted = document.getElementById('privacyAccepted')?.checked;
        const videoDone = document.getElementById('videoPreview')?.classList.contains('d-none') === false;
        const emiConfirmed = document.getElementById('emiPreview')?.classList.contains('d-none') === false;
        
        const completeBtn = document.getElementById('completeBookingBtn');
        if (completeBtn) {
            completeBtn.disabled = !(termsAccepted && privacyAccepted && videoDone && emiConfirmed);
        }
    }
    
    document.getElementById('termsAccepted')?.addEventListener('change', checkCompletion);
    document.getElementById('privacyAccepted')?.addEventListener('change', checkCompletion);
});

async function previewEMI() {
    const form = document.getElementById('emiForm');
    const formData = new FormData(form);
    
    try {
        const response = await fetch(`${baseUrl}/booking/digital/${bookingNumber}/emi-preview?` + new URLSearchParams(formData));
        const data = await response.json();
        
        if (data.success) {
            renderEMITable(data.schedule);
            document.getElementById('emiTotalPrincipal').textContent = '₹' + data.summary.total_principal.toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('emiTotalInterest').textContent = '₹' + data.summary.total_interest.toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('emiTotalPayable').textContent = '₹' + data.summary.total_payable.toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('emiPreview').classList.remove('d-none');
        } else {
            alert('Error: ' + data.error);
        }
    } catch (e) {
        console.error(e);
        alert('Failed to preview EMI');
    }
}

function renderEMITable(schedule) {
    const tbody = document.getElementById('emiTableBody');
    tbody.innerHTML = '';
    
    schedule.forEach((inst, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${new Date(inst.due_date).toLocaleDateString('en-IN')}</td>
            <td>₹${Number(inst.principal_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
            <td>₹${Number(inst.interest_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
            <td><strong>₹${Number(inst.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></td>
            <td>₹${Number(inst.balance_after).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
        `;
        tbody.appendChild(row);
    });
}

async function confirmEMI() {
    const form = document.getElementById('emiForm');
    const formData = new FormData(form);
    formData.append('booking_number', bookingNumber);
    
    try {
        const response = await fetch(`${baseUrl}/booking/digital/${bookingNumber}/emi-confirm`, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': '<?= $csrf ?>' }
        });
        const data = await response.json();
        
        if (data.success) {
            alert('EMI schedule confirmed!');
            checkCompletion();
        } else {
            alert('Error: ' + data.error);
        }
    } catch (e) {
        console.error(e);
        alert('Failed to confirm EMI');
    }
}

async function startRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { width: 640, height: 480 }, 
            audio: true 
        });
        
        const preview = document.getElementById('preview');
        preview.srcObject = stream;
        preview.classList.remove('d-none');
        
        mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
        recordedChunks = [];
        
        mediaRecorder.ondataavailable = e => {
            if (e.data.size > 0) recordedChunks.push(e.data);
        };
        
        mediaRecorder.onstop = () => {
            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            previewVideo.src = URL.createObjectURL(blob);
        };
        
        mediaRecorder.start(1000);
        
        document.getElementById('startRecording').classList.add('d-none');
        document.getElementById('stopRecording').classList.remove('d-none');
        
    } catch (e) {
        console.error(e);
        alert('Camera access denied. Please enable camera permissions.');
    }
}

function stopRecording() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
        mediaRecorder.stream.getTracks().forEach(track => track.stop());
    }
    
    document.getElementById('videoRecorder').classList.add('d-none');
    document.getElementById('videoPreview').classList.remove('d-none');
    checkCompletion();
}

function retakeVideo() {
    document.getElementById('videoRecorder').classList.remove('d-none');
    document.getElementById('videoPreview').classList.add('d-none');
    document.getElementById('startRecording').classList.remove('d-none');
    document.getElementById('stopRecording').classList.add('d-none');
}

async function submitVideoConsent() {
    const videoBlob = previewVideo.src ? await fetch(previewVideo.src).then(r => r.blob()) : null;
    
    if (!videoBlob) {
        alert('No video recorded');
        return;
    }
    
    const formData = new FormData();
    formData.append('video_blob', videoBlob, 'consent.webm');
    formData.append('booking_number', bookingNumber);
    formData.append('terms_accepted', '1');
    formData.append('privacy_accepted', '1');
    
    try {
        const response = await fetch(`${baseUrl}/booking/digital/${bookingNumber}/video-consent`, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': '<?= $csrf ?>' }
        });
        const data = await response.json();
        
        if (data.success) {
            alert('Video consent recorded successfully!');
            checkCompletion();
        } else {
            alert('Error: ' + data.error);
        }
    } catch (e) {
        console.error(e);
        alert('Failed to submit video consent');
    }
}

async function completeBooking() {
    const formData = new FormData();
    formData.append('booking_number', bookingNumber);
    formData.append('booking_token', '<?= $booking_token ?>');
    formData.append('terms_accepted', '1');
    
    try {
        const response = await fetch(`${baseUrl}/booking/digital/${bookingNumber}/submit`, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': '<?= $csrf ?>' }
        });
        const data = await response.json();
        
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        } else {
            alert('Error: ' + (data.error || 'Completion failed'));
        }
    } catch (e) {
        console.error(e);
        alert('Failed to complete booking');
    }
}

function checkCompletion() {
    const termsAccepted = document.getElementById('termsAccepted')?.checked;
    const privacyAccepted = document.getElementById('privacyAccepted')?.checked;
    const videoDone = document.getElementById('videoPreview')?.classList.contains('d-none') === false;
    const emiConfirmed = document.getElementById('emiPreview')?.classList.contains('d-none') === false;
    
    const completeBtn = document.getElementById('completeBookingBtn');
    if (completeBtn) {
        completeBtn.disabled = !(termsAccepted && privacyAccepted && videoDone && emiConfirmed);
    }
}
</script>