<?php

// TODO: Add proper error handling with try-catch blocks

$page_title = 'WhatsApp Templates - APS Dream Home';
$page_description = 'Create and manage WhatsApp message templates';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card template-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body text-center">
                    <h1><i class="fas fa-edit me-3"></i>WhatsApp Template Manager</h1>
                    <p class="mb-0">Create and Manage WhatsApp Message Templates</p>
                    <small>Templates Created: 12 | Total Templates: 18</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="templateTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                        <i class="fas fa-list me-2"></i>All Templates
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create" type="button" role="tab">
                        <i class="fas fa-plus me-2"></i>Create Template
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab">
                        <i class="fas fa-chart-bar me-2"></i>Analytics
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="templateTabContent">
        <!-- All Templates Tab -->
        <div class="tab-pane fade show active" id="all" role="tabpanel">
            <div class="row">
                <!-- Customer Service Templates -->
                <div class="col-md-6 mb-4">
                    <div class="card template-card">
                        <div class="card-header">
                            <h5><i class="fas fa-headset me-2"></i>Customer Service</h5>
                        </div>
                        <div class="card-body">
                            <div class="template-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Welcome Message</h6>
                                        <span class="category-badge customer-service">Customer Service</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editTemplate('welcome')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="previewTemplate('welcome')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="template-preview">
                                    Hello {{customer_name}}! Welcome to APS Dream Home. How can we help you find your dream property today?
                                </div>
                            </div>
                            
                            <div class="template-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Property Inquiry Response</h6>
                                        <span class="category-badge customer-service">Customer Service</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editTemplate('inquiry')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="previewTemplate('inquiry')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="template-preview">
                                    Thank you for your interest in {{property_title}}. This {{property_type}} is located in {{location}} and priced at ₹{{price}}. Would you like to schedule a visit?
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Templates -->
                <div class="col-md-6 mb-4">
                    <div class="card template-card">
                        <div class="card-header">
                            <h5><i class="fas fa-home me-2"></i>Property Updates</h5>
                        </div>
                        <div class="card-body">
                            <div class="template-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>New Property Listing</h6>
                                        <span class="category-badge property">Property</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editTemplate('new_listing')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="previewTemplate('new_listing')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="template-preview">
                                    🏠 New Listing Alert! {{property_type}} in {{location}} - {{bedrooms}}BHK, {{area}}sqft at ₹{{price}}. Contact us for details!
                                </div>
                            </div>
                            
                            <div class="template-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Price Drop Notification</h6>
                                        <span class="category-badge property">Property</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editTemplate('price_drop')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="previewTemplate('price_drop')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="template-preview">
                                    🎉 Price Drop Alert! {{property_title}} is now available at ₹{{new_price}} (was ₹{{old_price}}). Limited time offer!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Templates -->
                <div class="col-md-6 mb-4">
                    <div class="card template-card">
                        <div class="card-header">
                            <h5><i class="fas fa-calendar-check me-2"></i>Booking & Appointments</h5>
                        </div>
                        <div class="card-body">
                            <div class="template-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Booking Confirmation</h6>
                                        <span class="category-badge booking">Booking</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editTemplate('booking_confirm')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="previewTemplate('booking_confirm')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="template-preview">
                                    ✅ Booking Confirmed! Property visit scheduled on {{date}} at {{time}}. Address: {{property_address}}. See you there!
                                </div>
                            </div>
                            
                            <div class="template-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Appointment Reminder</h6>
                                        <span class="category-badge appointment">Appointment</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editTemplate('reminder')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="previewTemplate('reminder')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="template-preview">
                                    ⏰ Reminder: Your property visit is tomorrow at {{time}} for {{property_title}}. Location: {{location}}. Reply CONFIRM to proceed.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Templates -->
                <div class="col-md-6 mb-4">
                    <div class="card template-card">
                        <div class="card-header">
                            <h5><i class="fas fa-credit-card me-2"></i>Payment & Commission</h5>
                        </div>
                        <div class="card-body">
                            <div class="template-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Payment Confirmation</h6>
                                        <span class="category-badge payment">Payment</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editTemplate('payment_confirm')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="previewTemplate('payment_confirm')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="template-preview">
                                    💳 Payment Received! ₹{{amount}} for {{property_title}} (Booking ID: {{booking_id}}). Thank you for choosing APS Dream Home!
                                </div>
                            </div>
                            
                            <div class="template-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Commission Update</h6>
                                        <span class="category-badge commission">Commission</span>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="editTemplate('commission')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="previewTemplate('commission')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="template-preview">
                                    💰 Commission Earned! ₹{{commission_amount}} for {{property_title}} sale. Total this month: ₹{{monthly_total}}. Great work!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Template Tab -->
        <div class="tab-pane fade" id="create" role="tabpanel">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card template-card">
                        <div class="card-header">
                            <h5><i class="fas fa-plus me-2"></i>Create New Template</h5>
                        </div>
                        <div class="card-body">
                            <form id="templateForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="templateName" class="form-label">Template Name</label>
                                            <input type="text" class="form-control" id="templateName" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="templateCategory" class="form-label">Category</label>
                                            <select class="form-control" id="templateCategory" required>
                                                <option value="">Select Category</option>
                                                <option value="customer-service">Customer Service</option>
                                                <option value="property">Property</option>
                                                <option value="booking">Booking</option>
                                                <option value="appointment">Appointment</option>
                                                <option value="payment">Payment</option>
                                                <option value="commission">Commission</option>
                                                <option value="system">System</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="templateContent" class="form-label">Template Content</label>
                                    <textarea class="form-control" id="templateContent" rows="6" required placeholder="Enter your template content here. Use {{variable_name}} for dynamic content."></textarea>
                                    <small class="form-text text-muted">
                                        Available variables: {{customer_name}}, {{property_title}}, {{property_type}}, {{location}}, {{price}}, {{date}}, {{time}}, {{booking_id}}
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="templateDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="templateDescription" rows="2" placeholder="Describe when this template is used..."></textarea>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-primary" onclick="previewNewTemplate()">
                                        <i class="fas fa-eye me-2"></i>Preview
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Template
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div class="tab-pane fade" id="analytics" role="tabpanel">
            <div class="row">
                <div class="col-md-8">
                    <div class="card template-card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-line me-2"></i>Template Usage Analytics</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="templateUsageChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card template-card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle me-2"></i>Quick Stats</h5>
                        </div>
                        <div class="card-body">
                            <div class="stat-item mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Total Sent Today</span>
                                    <strong>247</strong>
                                </div>
                            </div>
                            <div class="stat-item mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Response Rate</span>
                                    <strong>68.4%</strong>
                                </div>
                            </div>
                            <div class="stat-item mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Most Used Template</span>
                                    <strong>Welcome Message</strong>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="d-flex justify-content-between">
                                    <span>Active Templates</span>
                                    <strong>12/18</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="sendTestMessage()">
                    <i class="fas fa-paper-plane me-2"></i>Send Test Message
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.template-card { margin: 15px 0; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.template-item { padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; }
.template-preview { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9em; margin-top: 10px; }
.category-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8em; }
.customer-service { background: #d4edda; color: #155724; }
.property { background: #d1ecf1; color: #0c5460; }
.booking { background: #fcefdc; color: #8a6d3b; }
.appointment { background: #d4edda; color: #155724; }
.payment { background: #e2e3e5; color: #383d41; }
.commission { background: #f8d7da; color: #721c24; }
.system { background: #fff3cd; color: #856404; }
.variable-highlight { background: #fff3cd; padding: 2px 4px; border-radius: 3px; }
.stat-item { padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
.stat-item:last-child { border-bottom: none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Template Usage Chart
const usageCtx = document.getElementById('templateUsageChart').getContext('2d');
new Chart(usageCtx, {
    type: 'bar',
    data: {
        labels: ['Welcome', 'Inquiry', 'Booking', 'Payment', 'Reminder'],
        datasets: [{
            label: 'Messages Sent',
            data: [89, 67, 45, 32, 14],
            backgroundColor: '#667eea'
        }]
    }
});

function editTemplate(templateId) {
    alert('Editing template: ' + templateId);
    // Implement edit functionality
}

function previewTemplate(templateId) {
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    document.getElementById('previewContent').innerHTML = `
        <div class="template-preview">
            Hello {{customer_name}}! Welcome to APS Dream Home. How can we help you find your dream property today?
        </div>
        <div class="mt-3">
            <h6>Variables Used:</h6>
            <span class="variable-highlight">{{customer_name}}</span>
        </div>
    `;
    modal.show();
}

function previewNewTemplate() {
    const content = document.getElementById('templateContent').value;
    if (content) {
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        document.getElementById('previewContent').innerHTML = `
            <div class="template-preview">${content}</div>
        `;
        modal.show();
    }
}

function sendTestMessage() {
    alert('Test message sent to your WhatsApp number!');
    bootstrap.Modal.getInstance(document.getElementById('previewModal')).hide();
}

// Form submission
document.getElementById('templateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Template saved successfully!');
    // Implement save functionality
});
</script>
