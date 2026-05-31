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
    document.addEventListener('click', e => {
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
          'Content-Type': 'application/json',
        },
      });

      const data = await response.json();

      if (data.success) {
        window.location.href = data.auth_url;
      } else {
        this.showNotification('Social login failed: ' + data.message, 'error');
      }
    } catch (error) {
      this.showNotification('Social login failed. Please try again.', 'error');
    }
  }

  /**
   * Setup OTP forms
   */
  setupOTPForms() {
    const otpForm = document.querySelector('.otp-verify-form');
    if (!otpForm) return;

    otpForm.addEventListener('submit', async e => {
      e.preventDefault();
      const otp = otpForm.querySelector('[name="otp"]').value;
      const phone = otpForm.querySelector('[name="phone"]').value;

      if (!otp || otp.length !== 6) {
        this.showNotification('Please enter valid 6-digit OTP', 'error');
        return;
      }

      await this.verifyOTP(phone, otp);
    });
  }

  /**
   * Verify OTP
   */
  async verifyOTP(phone, otp) {
    try {
      const response = await fetch('/auth/verify-otp', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ phone, otp }),
      });

      const data = await response.json();

      if (data.success) {
        this.showNotification('OTP verified successfully!', 'success');
        window.location.href = '/dashboard';
      } else {
        this.showNotification(data.message || 'Invalid OTP', 'error');
      }
    } catch (error) {
      this.showNotification('OTP verification failed. Please try again.', 'error');
    }
  }

  /**
   * Setup progressive registration
   */
  setupProgressiveRegistration() {
    const regForms = document.querySelectorAll('.progressive-registration-form');
    regForms.forEach(form => {
      form.addEventListener('submit', async e => {
        e.preventDefault();
        await this.handleProgressiveStep(form);
      });
    });
  }

  /**
   * Handle progressive registration step
   */
  async handleProgressiveStep(form) {
    const step = form.dataset.step;
    const formData = new FormData(form);

    try {
      const response = await fetch('/registration/step/' + step, {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        this.showNotification('Step completed!', 'success');
        if (data.next_step) {
          this.showRegistrationStep(data.next_step);
        } else {
          window.location.href = '/dashboard';
        }
      } else {
        this.showNotification(data.message || 'Step failed', 'error');
      }
    } catch (error) {
      this.showNotification('Registration step failed. Please try again.', 'error');
    }
  }

  /**
   * Show registration step
   */
  showRegistrationStep(stepName) {
    document.querySelectorAll('.registration-step').forEach(el => el.classList.add('hidden'));
    const stepEl = document.querySelector('.registration-step[data-step="' + stepName + '"]');
    if (stepEl) {
      stepEl.classList.remove('hidden');
    }
  }

  /**
   * Setup AI Chatbot
   */
  setupChatbot() {
    const chatToggle = document.querySelector('.chatbot-toggle');
    const chatContainer = document.querySelector('.chatbot-container');

    if (chatToggle && chatContainer) {
      chatToggle.addEventListener('click', () => {
        this.toggleChatbot();
      });
    }

    const chatForm = document.querySelector('.chatbot-form');
    if (chatForm) {
      chatForm.addEventListener('submit', e => {
        e.preventDefault();
        const input = chatForm.querySelector('.chatbot-input');
        if (input && input.value.trim()) {
          this.sendChatMessage(input.value.trim());
          input.value = '';
        }
      });
    }
  }

  /**
   * Toggle chatbot
   */
  toggleChatbot() {
    this.chatbotOpen = !this.chatbotOpen;
    const chatContainer = document.querySelector('.chatbot-container');

    if (chatContainer) {
      chatContainer.classList.toggle('show', this.chatbotOpen);

      if (this.chatbotOpen) {
        chatContainer.querySelector('.chatbot-input')?.focus();
      }
    }
  }

  /**
   * Send chat message
   */
  async sendChatMessage(message) {
    const chatMessages = document.querySelector('.chatbot-messages');
    if (!chatMessages) return;

    // Add user message
    this.addChatMessage(message, 'user');
    this.chatHistory.push({ role: 'user', content: message });

    try {
      const response = await fetch('/api/chatbot/message', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          message: message,
          session_id: this.sessionId,
          history: this.chatHistory.slice(-10),
        }),
      });

      const data = await response.json();

      if (data.success) {
        this.addChatMessage(data.reply, 'bot');
        this.chatHistory.push({ role: 'assistant', content: data.reply });
      } else {
        this.addChatMessage('Sorry, I encountered an error. Please try again.', 'bot');
      }
    } catch (error) {
      this.addChatMessage('Sorry, I encountered an error. Please try again.', 'bot');
    }
  }

  /**
   * Add chat message to UI
   */
  addChatMessage(message, sender) {
    const chatMessages = document.querySelector('.chatbot-messages');
    if (!chatMessages) return;

    const messageEl = document.createElement('div');
    messageEl.className = 'chatbot-message ' + sender;
    messageEl.textContent = message;
    chatMessages.appendChild(messageEl);

    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  /**
   * Setup campaign tracking
   */
  setupCampaignTracking() {
    // Track page views
    this.trackCampaignEvent('page_view', {
      url: window.location.pathname,
      referrer: document.referrer,
    });

    // Track button clicks
    document.addEventListener('click', e => {
      if (e.target.matches('[data-campaign-action]')) {
        const action = e.target.dataset.campaignAction;
        this.trackCampaignEvent(action, {
          element: e.target.textContent,
          url: e.target.href,
        });
      }
    });

    // Track form submissions
    document.addEventListener('submit', e => {
      if (e.target.dataset.campaignId) {
        this.trackCampaignEvent('form_submission', {
          form_id: e.target.dataset.campaignId,
        });
      }
    });
  }

  /**
   * Track campaign event
   */
  async trackCampaignEvent(eventType, eventData) {
    try {
      await fetch('/api/campaigns/track', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          session_id: this.sessionId,
          event_type: eventType,
          event_data: eventData,
          timestamp: new Date().toISOString(),
        }),
      });
    } catch (error) {
      /* Campaign tracking error handled silently */
    }
  }

  /**
   * Show notification
   */
  showNotification(message, type = 'info') {
    const container = document.querySelector('.notification-container');
    if (!container) {
      const notificationContainer = document.createElement('div');
      notificationContainer.className = 'notification-container';
      document.body.appendChild(notificationContainer);
    }

    const notification = document.createElement('div');
    notification.className = 'notification notification-' + type;
    notification.textContent = message;
    container.appendChild(notification);

    setTimeout(() => {
      notification.classList.add('show');
    }, 100);

    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => notification.remove(), 300);
    }, 5000);
  }
}

// Initialize globally
if (typeof window.advancedFeatures === 'undefined') {
  window.advancedFeatures = new AdvancedFeaturesSystem();
}
