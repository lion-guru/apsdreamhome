<!-- Quick Register Modal -->
<div class="modal fade" id="quickRegisterModal" tabindex="-1" aria-labelledby="quickRegisterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="quickRegisterModalLabel">
                    <i class="fas fa-user-plus me-2 text-primary"></i>Quick Register
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Join APS Dream Home in seconds! No password needed.</p>
                
                <form id="quickRegisterForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name *</label>
                        <input type="text" class="form-control" id="qrName" name="name" required placeholder="Enter your full name">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email *</label>
                        <input type="email" class="form-control" id="qrEmail" name="email" required placeholder="Enter your email">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number *</label>
                        <input type="tel" class="form-control" id="qrPhone" name="phone" required placeholder="Enter 10-digit phone number" pattern="[0-9]{10}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Referral Code (Optional)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="qrReferralCode" name="referral_code" placeholder="Enter referral code for 5% discount">
                            <button type="button" class="btn btn-outline-primary" onclick="requestReferralCode()">
                                <i class="fas fa-ticket-alt me-1"></i>Request Code
                            </button>
                        </div>
                        <small class="text-muted">Get referral code if you want to join as Associate/Agent</small>
                    </div>
                    
                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold" onclick="submitQuickRegister()">
                        <i class="fas fa-check-circle me-2"></i>Register Now
                    </button>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            By registering, you agree to our 
                            <a href="/terms" class="text-primary">Terms</a> and 
                            <a href="/privacy" class="text-primary">Privacy Policy</a>
                        </small>
                    </div>
                </form>
                
                <div id="qrLoading" style="display: none;">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Creating your account...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Referral Code Request Modal -->
<div class="modal fade" id="referralRequestModal" tabindex="-1" aria-labelledby="referralRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="referralRequestModalLabel">
                    <i class="fas fa-ticket-alt me-2 text-primary"></i>Request Referral Code
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Get your company referral code to join as Associate/Agent!</p>
                
                <form id="referralRequestForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name *</label>
                        <input type="text" class="form-control" id="rrName" name="name" required placeholder="Enter your full name">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email *</label>
                        <input type="email" class="form-control" id="rrEmail" name="email" required placeholder="Enter your email">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number *</label>
                        <input type="tel" class="form-control" id="rrPhone" name="phone" required placeholder="Enter 10-digit phone number" pattern="[0-9]{10}">
                    </div>
                    
                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold" onclick="submitReferralRequest()">
                        <i class="fas fa-paper-plane me-2"></i>Request Code
                    </button>
                </form>
                
                <div id="rrLoading" style="display: none;">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Processing your request...</p>
                    </div>
                </div>
                
                <div id="rrResult" style="display: none;">
                    <div class="alert alert-success mt-3">
                        <h6 class="fw-bold"><i class="fas fa-check-circle me-2"></i>Referral Code Sent!</h6>
                        <p class="mb-2">Your company referral code:</p>
                        <div class="fs-3 fw-bold text-center py-2" id="rrReferralCode"></div>
                        <p class="mb-0 small">Use this code to join as Associate/Agent</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Quick Register Functions
function showQuickRegisterModal() {
    const modal = new bootstrap.Modal(document.getElementById('quickRegisterModal'));
    modal.show();
}

function submitQuickRegister() {
    const name = document.getElementById('qrName').value;
    const email = document.getElementById('qrEmail').value;
    const phone = document.getElementById('qrPhone').value;
    const referralCode = document.getElementById('qrReferralCode').value;
    
    if (!name || !email || !phone) {
        alert('Please fill all required fields');
        return;
    }
    
    if (phone.length !== 10) {
        alert('Please enter a valid 10-digit phone number');
        return;
    }
    
    // Show loading
    document.getElementById('quickRegisterForm').style.display = 'none';
    document.getElementById('qrLoading').style.display = 'block';
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone);
    formData.append('referral_code', referralCode);
    
    fetch('/auth/quick-register', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert('Registration failed: ' + data.message);
            document.getElementById('quickRegisterForm').style.display = 'block';
            document.getElementById('qrLoading').style.display = 'none';
        }
    })
    .catch(error => {
        alert('Error: ' + error);
        document.getElementById('quickRegisterForm').style.display = 'block';
        document.getElementById('qrLoading').style.display = 'none';
    });
}

function requestReferralCode() {
    // Hide quick register modal
    const quickRegisterModal = bootstrap.Modal.getInstance(document.getElementById('quickRegisterModal'));
    quickRegisterModal.hide();
    
    // Show referral request modal
    const modal = new bootstrap.Modal(document.getElementById('referralRequestModal'));
    modal.show();
}

function submitReferralRequest() {
    const name = document.getElementById('rrName').value;
    const email = document.getElementById('rrEmail').value;
    const phone = document.getElementById('rrPhone').value;
    
    if (!name || !email || !phone) {
        alert('Please fill all required fields');
        return;
    }
    
    if (phone.length !== 10) {
        alert('Please enter a valid 10-digit phone number');
        return;
    }
    
    // Show loading
    document.getElementById('referralRequestForm').style.display = 'none';
    document.getElementById('rrLoading').style.display = 'block';
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone);
    
    fetch('/auth/request-referral-code', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('rrLoading').style.display = 'none';
            document.getElementById('rrResult').style.display = 'block';
            document.getElementById('rrReferralCode').textContent = data.referral_code;
        } else {
            alert('Request failed: ' + data.message);
            document.getElementById('referralRequestForm').style.display = 'block';
            document.getElementById('rrLoading').style.display = 'none';
        }
    })
    .catch(error => {
        alert('Error: ' + error);
        document.getElementById('referralRequestForm').style.display = 'block';
        document.getElementById('rrLoading').style.display = 'none';
    });
}
</script>
