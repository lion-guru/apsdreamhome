<?php
/**
 * Admin SMS Dashboard
 * View SMS logs, send test messages, configure settings
 */

$base = BASE_URL;
$page_title = "SMS Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .stat-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-card.error { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
        .stat-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-value { font-size: 2.5rem; font-weight: bold; }
        .stat-label { font-size: 0.9rem; opacity: 0.9; }
        .log-table { font-size: 0.9rem; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; }
        .status-success { background: #d1fae5; color: #065f46; }
        .status-error { background: #fee2e2; color: #991b1b; }
        .status-pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-sms text-primary me-2"></i>SMS Dashboard</h2>
                <p class="text-muted mb-0">Manage SMS notifications and view logs</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendSMSModal">
                    <i class="fas fa-paper-plane me-2"></i>Send Test SMS
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value" id="totalSent">0</div>
                    <div class="stat-label"><i class="fas fa-check-circle me-1"></i>SMS Sent (30 days)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card error">
                    <div class="stat-value" id="totalFailed">0</div>
                    <div class="stat-label"><i class="fas fa-times-circle me-1"></i>Failed (30 days)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="stat-value" id="otpSent">0</div>
                    <div class="stat-label"><i class="fas fa-key me-1"></i>OTPs Sent</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="stat-value" id="todaySent">0</div>
                    <div class="stat-label"><i class="fas fa-calendar-day me-1"></i>Sent Today</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select" id="typeFilter">
                            <option value="all">All Types</option>
                            <option value="OTP">OTP</option>
                            <option value="WELCOME">Welcome</option>
                            <option value="PAYMENT">Payment</option>
                            <option value="COMMISSION">Commission</option>
                            <option value="ADMIN">Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="success">Success</option>
                            <option value="error">Error</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchMobile" placeholder="Search by mobile number...">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary w-100" onclick="loadSMSLogs()">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMS Logs Table -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>SMS Logs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover log-table" id="smsLogsTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Mobile</th>
                                <th>Type</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Date & Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-spinner fa-spin me-2"></i>Loading SMS logs...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Send SMS Modal -->
    <div class="modal fade" id="sendSMSModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i>Send Test SMS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="sendSMSForm">
                        <div class="mb-3">
                            <label class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" name="mobile" placeholder="9876543210" required>
                            <div class="form-text">Enter 10-digit mobile number</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="4" maxlength="160" required></textarea>
                            <div class="form-text"><span id="charCount">0</span>/160 characters</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="sendTestSMS()">
                        <i class="fas fa-paper-plane me-2"></i>Send SMS
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const baseUrl = '<?php echo $base; ?>';
        
        // Load SMS logs on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSMSLogs();
            loadStats();
            
            // Character counter
            document.querySelector('textarea[name="message"]').addEventListener('input', function() {
                document.getElementById('charCount').textContent = this.value.length;
            });
        });
        
        function loadStats() {
            // Fetch stats from API
            fetch(`${baseUrl}/api/sms/stats`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalSent').textContent = data.total_sent || 0;
                    document.getElementById('totalFailed').textContent = data.total_failed || 0;
                    document.getElementById('otpSent').textContent = data.otp_sent || 0;
                    document.getElementById('todaySent').textContent = data.today_sent || 0;
                })
                .catch(error => console.error('Error loading stats:', error));
        }
        
        function loadSMSLogs() {
            const type = document.getElementById('typeFilter').value;
            const status = document.getElementById('statusFilter').value;
            
            fetch(`${baseUrl}/api/sms/logs?type=${type}&status=${status}`)
                .then(response => response.json())
                .then(data => {
                    renderSMSLogs(data);
                })
                .catch(error => {
                    console.error('Error loading logs:', error);
                    document.querySelector('#smsLogsTable tbody').innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4 text-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>Error loading SMS logs
                            </td>
                        </tr>
                    `;
                });
        }
        
        function renderSMSLogs(logs) {
            const tbody = document.querySelector('#smsLogsTable tbody');
            
            if (logs.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox me-2"></i>No SMS logs found
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = logs.map(log => {
                const statusClass = log.status === 'success' ? 'status-success' : 
                                   log.status === 'error' ? 'status-error' : 'status-pending';
                
                const message = log.message ? (log.message.length > 50 ? log.message.substring(0, 50) + '...' : log.message) : '-';
                
                return `
                    <tr>
                        <td>#${log.id}</td>
                        <td>${log.mobile}</td>
                        <td><span class="badge bg-secondary">${log.type}</span></td>
                        <td>${message}</td>
                        <td><span class="status-badge ${statusClass}">${log.status}</span></td>
                        <td>${new Date(log.created_at).toLocaleString()}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(${log.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        function sendTestSMS() {
            const form = document.getElementById('sendSMSForm');
            const formData = new FormData(form);
            
            fetch(`${baseUrl}/admin/sms/send`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('SMS sent successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('sendSMSModal')).hide();
                    form.reset();
                    loadSMSLogs();
                } else {
                    alert('Failed to send SMS: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending SMS');
            });
        }
        
        function viewDetails(id) {
            // Show details in modal or alert for now
            alert('SMS ID: ' + id);
        }
    </script>
</body>
</html>
