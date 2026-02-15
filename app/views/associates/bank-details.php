<?php require_once 'app/views/layouts/associate_header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/associate/dashboard">डैशबोर्ड</a></li>
                    <li class="breadcrumb-item active" aria-current="page">बैंक डिटेल्स</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-university mr-2"></i>बैंक अकाउंट डिटेल्स
                    </h6>
                    <span id="bankingStatusBadge" class="badge badge-secondary">Loading...</span>
                </div>
                <div class="card-body">
                    <div id="bankingAlert" class="alert d-none" role="alert"></div>

                    <form id="bankingForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="bank_name" class="form-label">बैंक का नाम <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" required readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="ifsc_code" class="form-label">IFSC कोड <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="जैसे: SBIN0001234" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" id="verifyIFSC">वेरिफाई</button>
                                        </div>
                                    </div>
                                    <small id="ifscHelp" class="form-text text-muted">IFSC कोड डालें और वेरिफाई बटन दबाएं</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="account_number" class="form-label">अकाउंट नंबर <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="account_number" name="account_number" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="account_holder_name" class="form-label">अकाउंट होल्डर का नाम <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" required>
                                    <small class="form-text text-muted">बैंक रिकॉर्ड के अनुसार नाम लिखें</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="branch_name" class="form-label">ब्रांच का नाम</label>
                                    <input type="text" class="form-control" id="branch_name" name="branch_name" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="account_type" class="form-label">अकाउंट टाइप</label>
                                    <select class="form-control" id="account_type" name="account_type">
                                        <option value="SAVINGS">Savings (बचत)</option>
                                        <option value="CURRENT">Current (चालू)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary" id="saveBankingBtn">
                                <i class="fas fa-save mr-2"></i>बैंक डिटेल्स सेव करें
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Verification History -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-2"></i>वेरिफिकेशन हिस्ट्री
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="auditLogTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>तारीख</th>
                                    <th>एक्शन</th>
                                    <th>स्टेटस</th>
                                    <th>मैसेज</th>
                                </tr>
                            </thead>
                            <tbody id="auditLogBody">
                                <!-- Logs will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Guidelines -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>जरूरी गाइडलाइन्स
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                            <span>पेआउट प्राप्त करने के लिए बैंक डिटेल्स सही होना जरूरी है।</span>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                            <span>अकाउंट होल्डर का नाम आपके पैन कार्ड और आधार कार्ड से मैच होना चाहिए।</span>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                            <span>सिर्फ अपना ही बैंक अकाउंट डिटेल्स दें, किसी और का अकाउंट स्वीकार नहीं किया जाएगा।</span>
                        </li>
                        <li class="list-group-item d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                            <span>एक बार वेरिफाई होने के बाद, बैंक डिटेल्स चेंज करने के लिए एडमिन से संपर्क करना होगा।</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- KYC Quick Status -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-id-card mr-2"></i>KYC स्टेटस
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div id="kycStatusContainer">
                        <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                        <h5 id="kycStatusText">Pending</h5>
                        <p class="text-muted small">बैंक डिटेल्स वेरिफाई करने के लिए KYC कंप्लीट होना चाहिए।</p>
                        <a href="/associate/kyc" class="btn btn-sm btn-outline-primary">KYC अपडेट करें</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadBankingDetails();
    loadAuditLogs();
    checkKYCStatus();

    // Verify IFSC
    document.getElementById('verifyIFSC').addEventListener('click', function() {
        const ifsc = document.getElementById('ifsc_code').value.trim();
        if (!ifsc) {
            showAlert('danger', 'कृपया IFSC कोड डालें');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        fetch(`/api/banking?action=validate_ifsc&ifsc=${ifsc}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('bank_name').value = data.data.BANK;
                    document.getElementById('branch_name').value = data.data.BRANCH;
                    showAlert('success', 'IFSC कोड सफलतापूर्वक वेरिफाई हो गया है');
                } else {
                    showAlert('danger', 'अमान्य IFSC कोड: ' + (data.error || 'वेरिफिकेशन फेल'));
                    document.getElementById('bank_name').value = '';
                    document.getElementById('branch_name').value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'IFSC वेरिफाई करते समय एरर आया');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'वेरिफाई';
            });
    });

    // Save Banking Details
    document.getElementById('bankingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'save_details');

        const btn = document.getElementById('saveBankingBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> सेव हो रहा है...';

        fetch('/api/banking', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'बैंक डिटेल्स सफलतापूर्वक सेव हो गए हैं। वेरिफिकेशन के लिए पेंडिंग है।');
                    loadBankingDetails();
                    loadAuditLogs();
                } else {
                    showAlert('danger', 'एरर: ' + (data.error || 'सेव करने में विफल'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'डाटा सेव करते समय सर्वर एरर आया');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save mr-2"></i>बैंक डिटेल्स सेव करें';
            });
    });

    function loadBankingDetails() {
        fetch('/api/banking?action=get_details')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const d = data.data;
                    document.getElementById('bank_name').value = d.bank_name || '';
                    document.getElementById('ifsc_code').value = d.ifsc_code || '';
                    document.getElementById('account_number').value = d.account_number || '';
                    document.getElementById('account_holder_name').value = d.account_holder_name || '';
                    document.getElementById('branch_name').value = d.branch_name || '';
                    document.getElementById('account_type').value = d.account_type || 'SAVINGS';

                    const statusBadge = document.getElementById('bankingStatusBadge');
                    statusBadge.innerText = d.is_verified == 1 ? 'Verified' : (d.id ? 'Pending Verification' : 'Not Set');
                    statusBadge.className = 'badge badge-' + (d.is_verified == 1 ? 'success' : (d.id ? 'warning' : 'secondary'));

                    if (d.is_verified == 1) {
                        document.getElementById('saveBankingBtn').disabled = true;
                        document.getElementById('saveBankingBtn').title = 'वेरिफाइड अकाउंट डिटेल्स चेंज नहीं किये जा सकते';
                    }
                } else {
                    document.getElementById('bankingStatusBadge').innerText = 'Not Set';
                }
            });
    }

    function loadAuditLogs() {
        fetch('/api/banking?action=get_audit_logs')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('auditLogBody');
                    tbody.innerHTML = '';
                    data.data.forEach(log => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${new Date(log.created_at).toLocaleString()}</td>
                            <td>${log.action}</td>
                            <td><span class="badge badge-${log.status === 'SUCCESS' ? 'success' : (log.status === 'FAILED' ? 'danger' : 'info')}">${log.status}</span></td>
                            <td>${log.message || '-'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            });
    }

    function checkKYCStatus() {
        fetch('/api/kyc?action=get_status')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('kycStatusContainer');
                if (data.success) {
                    const status = data.data.status;
                    let icon = 'clock', color = 'warning', text = 'Pending';
                    if (status === 'verified') {
                        icon = 'check-circle'; color = 'success'; text = 'Verified';
                    } else if (status === 'rejected') {
                        icon = 'times-circle'; color = 'danger'; text = 'Rejected';
                    }
                    
                    container.innerHTML = `
                        <i class="fas fa-${icon} fa-3x text-${color} mb-3"></i>
                        <h5 class="text-${color}">${text}</h5>
                        <p class="text-muted small">${status === 'verified' ? 'आपका KYC वेरिफाइड है।' : 'कृपया अपना KYC कंप्लीट करें।'}</p>
                        ${status !== 'verified' ? '<a href="/associate/kyc" class="btn btn-sm btn-outline-primary">KYC अपडेट करें</a>' : ''}
                    `;
                }
            });
    }

    function showAlert(type, message) {
        const alert = document.getElementById('bankingAlert');
        alert.className = `alert alert-${type}`;
        alert.innerText = message;
        alert.classList.remove('d-none');
        setTimeout(() => alert.classList.add('d-none'), 5000);
    }
});
</script>
