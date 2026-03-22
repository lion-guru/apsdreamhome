<?php

/**
 * AI Assistant Page
 * Get AI-powered property recommendations
 */
?>

<!-- AI Assistant Hero -->
<section class="hero-section bg-gradient-warning text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">AI Property Assistant</h1>
                <p class="lead mb-0">Get AI-powered property recommendations and find your dream home</p>
            </div>
        </div>
    </div>
</section>

<!-- Enhanced AI Assistant Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold">🤖 Enhanced AI Assistant</h2>
                <p class="lead text-muted">Role-based AI assistance with real-time chat and lead capture</p>
            </div>
        </div>

        <!-- AI Chat Integration -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">💬 Talk to AI Assistant</h4>
                    </div>
                    <div class="card-body p-0">
                        <!-- Role Selector -->
                        <div class="p-3 border-bottom">
                            <label class="form-label fw-bold">Select Your Role:</label>
                            <select id="ai-role-selector" class="form-select" onchange="updateAIRole()">
                                <option value="customer">👤 Customer - Property Help</option>
                                <option value="sales">💼 Sales - Lead Generation</option>
                                <option value="developer">👨‍💻 Developer - Technical Help</option>
                                <option value="director">👨‍💼 Director - Business Strategy</option>
                                <option value="superadmin">🔐 Admin - System Management</option>
                            </select>
                        </div>

                        <!-- AI Chat Interface -->
                        <div id="ai-chat-container" style="height: 400px; position: relative;">
                            <iframe src="/ai-chat-enhanced"
                                style="width: 100%; height: 100%; border: none; border-radius: 0 0 8px 8px;"
                                frameborder="0">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Features Grid -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-search fa-3x text-primary"></i>
                        </div>
                        <h5>🔍 Smart Property Search</h5>
                        <p class="text-muted">AI-powered property recommendations based on your preferences and budget</p>
                        <button onclick="askAI('Show me properties under 30 lakhs in Gorakhpur')" class="btn btn-primary btn-sm">
                            Try Now
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-calculator fa-3x text-success"></i>
                        </div>
                        <h5>💰 EMI & Financial Planning</h5>
                        <p class="text-muted">Instant EMI calculations, loan eligibility, and financial advice</p>
                        <button onclick="askAI('Calculate EMI for 25 lakh property')" class="btn btn-success btn-sm">
                            Calculate EMI
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-chart-line fa-3x text-warning"></i>
                        </div>
                        <h5>📊 Investment Analysis</h5>
                        <p class="text-muted">ROI calculations and investment opportunity analysis</p>
                        <button onclick="askAI('Analyze investment potential in Raghunath Nagri')" class="btn btn-warning btn-sm">
                            Analyze Investment
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional AI Features -->
        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">🏠 Site Visit Assistant</h5>
                        <p class="text-muted">Schedule property visits and get virtual tour information</p>
                        <div class="d-grid">
                            <button onclick="askAI('Schedule site visit for Suyoday Colony')" class="btn btn-outline-primary">
                                📅 Schedule Visit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">📋 Document Helper</h5>
                        <p class="text-muted">Get help with required documents and application processes</p>
                        <div class="d-grid">
                            <button onclick="askAI('What documents are needed for property registration?')" class="btn btn-outline-info">
                                📄 Get Document List
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- AI Assistant JavaScript -->
<script>
    function updateAIRole() {
        const role = document.getElementById('ai-role-selector').value;
        const iframe = document.querySelector('#ai-chat-container iframe');

        // Update iframe with role parameter
        iframe.src = `/ai-chat-enhanced?role=${role}`;

        // Show role change notification
        showNotification(`Role changed to: ${role}`, 'success');
    }

    function askAI(message) {
        // Scroll to chat
        document.getElementById('ai-chat-container').scrollIntoView({
            behavior: 'smooth'
        });

        // Wait for scroll to complete, then send message
        setTimeout(() => {
            try {
                // Try to send message to iframe
                const iframe = document.querySelector('#ai-chat-container iframe');
                iframe.contentWindow.postMessage({
                    type: 'send_message',
                    message: message
                }, '*');
            } catch (error) {
                // Fallback: open in new window
                window.open(`/ai-chat-enhanced?message=${encodeURIComponent(message)}`, '_blank');
            }
        }, 500);
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Show welcome notification
        showNotification('🤖 AI Assistant is ready! Select your role and start chatting.', 'info');
    });
</script>
</div>
</div>
</div>
</div>
</div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">AI Recommendations</h4>
                    </div>
                    <div class="card-body">
                        <p>AI assistant functionality coming soon...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>