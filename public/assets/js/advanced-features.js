/**
 * APS Dream Home - Advanced Features Frontend
 * Handles Social Login, OTP, Progressive Registration, AI Chatbot, and Campaign Delivery
 */

class AdvancedFeaturesSystem {
    constructor() {
        this.sessionId = this.getSessionId();
        this.currentRegistrationStep = null;
        this.chatbotOpen = false;
        this.chatHistory = [];
        
        this.init();
    }

    /**
     * Initialize the advanced features system
     */
    init() {
        this.setupSocialLogin();
        this.setupOTPForms();
        this.setupProgressiveRegistration();
        this.setupChatbot();
        this.setupCampaignTracking();
    }

    /**
     * Get or create session ID
     */
    getSessionId() {
        let sessionId = localStorage.getItem('aps_session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('aps_session_id', sessionId);
        }
        return sessionId;
    }

    /**
     * Setup social login buttons
     */
    setupSocialLogin() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.social-login-btn')) {
                e.preventDefault();
                const provider = e.target.dataset.provider;
                this.initiateSocialLogin(provider);
            }
        });
    }

    /**
     * Initiate social login
     */
    async initiateSocialLogin(provider) {
        try {
            const response = await fetch('/auth/social/url?provider=' + provider, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                // Redirect to social provider
                window.location.href = data.auth_url;
            } else {
                this.showNotification('Social login failed: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Social login error:', error);
            this.showNotification('Social login failed. Please try again.', 'error');
        }
    }

    /**
     * Setup OTP forms
     */
    setupOTPForms() {
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.otp-send-form')) {
                e.preventDefault();
                this.sendOTP(e.target);
            }
            
            if (e.target.matches('.otp-verify-form')) {
                e.preventDefault();
                this.verifyOTP(e.target);
            }
        });
    }

    /**
     * Send OTP
     */
    async sendOTP(form) {
        const formData = new FormData(form);
        const data = {
            identifier: formData.get('identifier'),
            type: formData.get('type'),
            purpose: formData.get('purpose') || 'login'
        };

        try {
            this.showLoading(form.querySelector('button[type="submit"]'));
            
            const response = await fetch('/auth/otp/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('OTP sent successfully!', 'success');
                this.showOTPVerificationForm(data.identifier, data.type);
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('OTP send error:', error);
            this.showNotification('Failed to send OTP. Please try again.', 'error');
        } finally {
            this.hideLoading(form.querySelector('button[type="submit"]'));
        }
    }

    /**
     * Verify OTP
     */
    async verifyOTP(form) {
        const formData = new FormData(form);
        const data = {
            identifier: formData.get('identifier'),
            otp_code: formData.get('otp_code'),
            purpose: formData.get('purpose') || 'login'
        };

        try {
            this.showLoading(form.querySelector('button[type="submit"]'));
            
            const response = await fetch('/auth/otp/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('OTP verified successfully!', 'success');
                // Handle successful verification (redirect, show next step, etc.)
                if (data.purpose === 'login') {
                    window.location.reload();
                }
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('OTP verification error:', error);
            this.showNotification('OTP verification failed. Please try again.', 'error');
        } finally {
            this.hideLoading(form.querySelector('button[type="submit"]'));
        }
    }

    /**
     * Show OTP verification form
     */
    showOTPVerificationForm(identifier, type) {
        // This would show the OTP input form
        // Implementation depends on your UI design
        const otpForm = document.querySelector('.otp-verification-form');
        if (otpForm) {
            otpForm.style.display = 'block';
            otpForm.querySelector('input[name="identifier"]').value = identifier;
            otpForm.querySelector('input[name="type"]').value = type;
        }
    }

    /**
     * Setup progressive registration
     */
    setupProgressiveRegistration() {
        // Check if there's an active registration
        this.checkActiveRegistration();
        
        // Setup form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.progressive-registration-form')) {
                e.preventDefault();
                this.saveRegistrationStep(e.target);
            }
        });

        // Setup navigation buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.reg-next-step')) {
                e.preventDefault();
                this.moveToNextRegistrationStep();
            }
            
            if (e.target.matches('.reg-prev-step')) {
                e.preventDefault();
                this.moveToPreviousRegistrationStep();
            }
            
            if (e.target.matches('.reg-complete')) {
                e.preventDefault();
                this.completeRegistration();
            }
        });
    }

    /**
     * Check for active registration
     */
    async checkActiveRegistration() {
        try {
            const response = await fetch('/auth/progressive/current');
            const data = await response.json();
            
            if (data.success) {
                this.currentRegistrationStep = data.step_data;
                this.displayRegistrationStep(data.step_data);
            }
        } catch (error) {
            console.error('Registration check error:', error);
        }
    }

    /**
     * Start progressive registration
     */
    async startProgressiveRegistration() {
        try {
            const response = await fetch('/auth/progressive/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.currentRegistrationStep = data;
                this.displayRegistrationStep(data);
            } else {
                this.showNotification('Failed to start registration', 'error');
            }
        } catch (error) {
            console.error('Registration start error:', error);
            this.showNotification('Failed to start registration', 'error');
        }
    }

    /**
     * Save registration step data
     */
    async saveRegistrationStep(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        try {
            this.showLoading(form.querySelector('button[type="submit"]'));
            
            const response = await fetch('/auth/progressive/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Step saved successfully!', 'success');
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Registration save error:', error);
            this.showNotification('Failed to save step', 'error');
        } finally {
            this.hideLoading(form.querySelector('button[type="submit"]'));
        }
    }

    /**
     * Move to next registration step
     */
    async moveToNextRegistrationStep() {
        try {
            const response = await fetch('/auth/progressive/next', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.currentRegistrationStep = data;
                this.displayRegistrationStep(data);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Next step error:', error);
            this.showNotification('Failed to move to next step', 'error');
        }
    }

    /**
     * Move to previous registration step
     */
    async moveToPreviousRegistrationStep() {
        try {
            const response = await fetch('/auth/progressive/previous', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.currentRegistrationStep = data;
                this.displayRegistrationStep(data);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Previous step error:', error);
            this.showNotification('Failed to move to previous step', 'error');
        }
    }

    /**
     * Complete registration
     */
    async completeRegistration() {
        try {
            const response = await fetch('/auth/progressive/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Registration completed successfully!', 'success');
                window.location.href = '/dashboard';
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            console.error('Registration completion error:', error);
            this.showNotification('Failed to complete registration', 'error');
        }
    }

    /**
     * Display registration step
     */
    displayRegistrationStep(stepData) {
        // Update progress bar
        const progressBar = document.querySelector('.registration-progress');
        if (progressBar) {
            progressBar.style.width = stepData.progress + '%';
            progressBar.textContent = stepData.progress + '%';
        }

        // Update step title
        const stepTitle = document.querySelector('.step-title');
        if (stepTitle) {
            stepTitle.textContent = stepData.step_info.title;
        }

        // Update step content based on current step
        this.updateStepContent(stepData);
    }

    /**
     * Update step content
     */
    updateStepContent(stepData) {
        // Implementation depends on your UI design
        // This would show/hide different form sections based on current step
        const stepNumber = stepData.current_step;
        
        // Hide all step sections
        document.querySelectorAll('.step-section').forEach(section => {
            section.style.display = 'none';
        });
        
        // Show current step section
        const currentSection = document.querySelector('.step-' + stepNumber);
        if (currentSection) {
            currentSection.style.display = 'block';
        }
    }

    /**
     * Setup AI Chatbot
     */
    setupChatbot() {
        // Create chatbot widget
        this.createChatbotWidget();
        
        // Setup message sending
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.chatbot-form')) {
                e.preventDefault();
                this.sendChatbotMessage(e.target);
            }
        });

        // Setup chatbot controls
        document.addEventListener('click', (e) => {
            if (e.target.matches('.chatbot-toggle')) {
                e.preventDefault();
                this.toggleChatbot();
            }
            
            if (e.target.matches('.chatbot-clear')) {
                e.preventDefault();
                this.clearChatbot();
            }
        });
    }

    /**
     * Create chatbot widget
     */
    createChatbotWidget() {
        const chatbotHTML = `
            <div class="chatbot-widget" id="chatbotWidget">
                <div class="chatbot-header">
                    <h5>APS Dream Home Assistant</h5>
                    <div class="chatbot-controls">
                        <button class="btn btn-sm btn-link chatbot-clear">Clear</button>
                        <button class="btn btn-sm btn-link chatbot-toggle">×</button>
                    </div>
                </div>
                <div class="chatbot-messages" id="chatbotMessages">
                    <div class="bot-message">
                        <strong>Assistant:</strong> Hello! I'm here to help you with your real estate needs. How can I assist you today?
                    </div>
                </div>
                <form class="chatbot-form">
                    <div class="input-group">
                        <input type="text" class="form-control" name="message" placeholder="Type your message..." required>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </div>
                </form>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    /**
     * Toggle chatbot
     */
    toggleChatbot() {
        const widget = document.getElementById('chatbotWidget');
        if (widget) {
            this.chatbotOpen = !this.chatbotOpen;
            widget.classList.toggle('open', this.chatbotOpen);
        }
    }

    /**
     * Send chatbot message
     */
    async sendChatbotMessage(form) {
        const formData = new FormData(form);
        const message = formData.get('message');

        if (!message.trim()) return;

        // Add user message to chat
        this.addMessageToChat(message, 'user');
        form.querySelector('input[name="message"]').value = '';

        try {
            const response = await fetch('/api/chatbot/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            
            if (data.success) {
                this.addMessageToChat(data.response, 'bot');
            } else {
                this.addMessageToChat('Sorry, I encountered an error. Please try again.', 'bot');
            }
        } catch (error) {
            console.error('Chatbot error:', error);
            this.addMessageToChat('Sorry, I encountered an error. Please try again.', 'bot');
        }
    }

    /**
     * Add message to chat
     */
    addMessageToChat(message, sender) {
        const messagesContainer = document.getElementById('chatbotMessages');
        if (!messagesContainer) return;

        const messageDiv = document.createElement('div');
        messageDiv.className = sender + '-message';
        messageDiv.innerHTML = `<strong>${sender === 'user' ? 'You' : 'Assistant'}:</strong> ${message}`;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    /**
     * Clear chatbot conversation
     */
    async clearChatbot() {
        try {
            await fetch('/api/chatbot/clear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const messagesContainer = document.getElementById('chatbotMessages');
            if (messagesContainer) {
                messagesContainer.innerHTML = `
                    <div class="bot-message">
                        <strong>Assistant:</strong> Hello! I'm here to help you with your real estate needs. How can I assist you today?
                    </div>
                `;
            }
        } catch (error) {
            console.error('Clear chatbot error:', error);
        }
    }

    /**
     * Setup campaign tracking
     */
    setupCampaignTracking() {
        // Track email opens
        if (window.location.search.includes('campaign_id')) {
            const urlParams = new URLSearchParams(window.location.search);
            const campaignId = urlParams.get('campaign_id');
            const deliveryId = urlParams.get('delivery_id');
            
            if (deliveryId) {
                this.trackCampaignEngagement(deliveryId, 'opened');
            }
        }

        // Track link clicks
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-campaign-link]')) {
                const deliveryId = e.target.dataset.deliveryId;
                if (deliveryId) {
                    this.trackCampaignEngagement(deliveryId, 'clicked');
                }
            }
        });
    }

    /**
     * Track campaign engagement
     */
    async trackCampaignEngagement(deliveryId, action) {
        try {
            await fetch('/api/campaigns/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    delivery_id: deliveryId,
                    action: action
                })
            });
        } catch (error) {
            console.error('Campaign tracking error:', error);
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} notification-toast`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.padding = '15px 20px';
        notification.style.borderRadius = '5px';
        notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        notification.style.transition = 'all 0.3s ease';

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 100);

        // Remove after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }

    /**
     * Show loading state
     */
    showLoading(button) {
        if (button) {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
        }
    }

    /**
     * Hide loading state
     */
    hideLoading(button) {
        if (button) {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || 'Submit';
        }
    }
}

// Initialize the advanced features system when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.advancedFeatures = new AdvancedFeaturesSystem();
});