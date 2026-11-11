/**
 * APS Dream Home AI Chatbot Widget
 * Floating chat button with full chat interface
 * Usage: Include this script and call AIChatbotWidget.init({ options })
 */

(function() {
    'use strict';

    // Configuration defaults
    const DEFAULTS = {
        apiBaseUrl: '/api/ai/chat',
        position: 'bottom-right',
        primaryColor: '#0B194C',
        secondaryColor: '#F58220',
        accentColor: '#E5A93C',
        welcomeMessage: 'Hello! How can I help you with APS Dream Home today?',
        placeholder: 'Type your message...',
        showPoweredBy: true,
        autoOpen: false,
        persistSession: true,
    };

    // State
    let config = {};
    let isOpen = false;
    let sessionId = null;
    let messages = [];
    let isLoading = false;

    // DOM Elements
    let widgetContainer = null;
    let chatButton = null;
    let chatPanel = null;
    let messagesContainer = null;
    let inputField = null;
    let sendButton = null;
    let closeButton = null;
    let minimizeButton = null;

    /**
     * Initialize the widget
     */
    function init(userConfig) {
        config = Object.assign({}, DEFAULTS, userConfig);
        
        // Load session if persisted
        if (config.persistSession) {
            loadSession();
        }

        // Create widget HTML
        createWidget();
        
        // Attach event listeners
        attachEvents();
        
        // Auto-open if configured
        if (config.autoOpen) {
            openWidget();
        }

        console.log('APS Dream Home AI Chatbot Widget initialized');
    }

    /**
     * Create widget HTML structure
     */
    function createWidget() {
        widgetContainer = document.createElement('div');
        widgetContainer.id = 'aps-ai-chatbot-widget';
        widgetContainer.style.cssText = getContainerStyles();
        
        widgetContainer.innerHTML = `
            <!-- Chat Button (Floating) -->
            <button id="aps-chat-button" class="aps-chat-button" aria-label="Open AI Assistant" style="${getButtonStyles()}">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <span class="aps-notification-badge" id="aps-notification-badge" style="display: none;">1</span>
            </button>

            <!-- Chat Panel -->
            <div id="aps-chat-panel" class="aps-chat-panel" style="${getPanelStyles()}">
                <!-- Header -->
                <div class="aps-chat-header" style="${getHeaderStyles()}">
                    <div class="aps-header-left">
                        <div class="aps-avatar" style="${getAvatarStyles()}">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M8 21h8M12 17v-5"></path>
                            </svg>
                        </div>
                        <div class="aps-header-info">
                            <span class="aps-header-title">APS Dream Home AI</span>
                            <span class="aps-header-status" id="aps-connection-status">
                                <span class="aps-status-dot online"></span>
                                <span>Online</span>
                            </span>
                        </div>
                    </div>
                    <div class="aps-header-actions">
                        <button id="aps-minimize-btn" class="aps-icon-btn" aria-label="Minimize" title="Minimize">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </button>
                        <button id="aps-close-btn" class="aps-icon-btn" aria-label="Close" title="Close">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" x2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Messages Container -->
                <div id="aps-messages-container" class="aps-messages-container" style="${getMessagesContainerStyles()}">
                    <div class="aps-welcome-message" style="${getWelcomeMessageStyles()}">
                        <div class="aps-message-bubble bot" style="${getBotBubbleStyles()}">
                            <div class="aps-message-avatar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M8 21h8M12 17v-5"></path>
                                </svg>
                            </div>
                            <div class="aps-message-content">
                                <span id="aps-welcome-text">${escapeHtml(config.welcomeMessage)}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Replies -->
                <div id="aps-quick-replies" class="aps-quick-replies" style="${getQuickRepliesStyles()}">
                    <button class="aps-quick-reply" data-query="Show me available plots in Suryoday Colony">🏘️ Available Plots</button>
                    <button class="aps-quick-reply" data-query="How to book a plot?">📝 Book a Plot</button>
                    <button class="aps-quick-reply" data-query="Calculate EMI for 50 lakh plot">💰 EMI Calculator</button>
                    <button class="aps-quick-reply" data-query="Schedule a site visit">🏗️ Site Visit</button>
                </div>

                <!-- Input Area -->
                <div class="aps-input-area" style="${getInputAreaStyles()}">
                    <input 
                        type="text" 
                        id="aps-chat-input" 
                        class="aps-chat-input" 
                        placeholder="${escapeHtml(config.placeholder)}"
                        autocomplete="off"
                        spellcheck="false"
                        style="${getInputStyles()}"
                    >
                    <button id="aps-send-btn" class="aps-send-btn" aria-label="Send message" style="${getSendButtonStyles()}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>

                <!-- Powered by -->
                ${config.showPoweredBy ? `
                <div class="aps-powered-by" style="${getPoweredByStyles()}">
                    Powered by APS Dream Home AI
                </div>
                ` : ''}
            </div>
        `;

        document.body.appendChild(widgetContainer);

        // Cache DOM elements
        chatButton = document.getElementById('aps-chat-button');
        chatPanel = document.getElementById('aps-chat-panel');
        messagesContainer = document.getElementById('aps-messages-container');
        inputField = document.getElementById('aps-chat-input');
        sendButton = document.getElementById('aps-send-btn');
        closeButton = document.getElementById('aps-close-btn');
        minimizeButton = document.getElementById('aps-minimize-btn');
    }

    /**
     * Attach event listeners
     */
    function attachEvents() {
        // Toggle chat panel
        chatButton.addEventListener('click', toggleWidget);
        
        // Close button
        closeButton.addEventListener('click', closeWidget);
        
        // Minimize button
        minimizeButton.addEventListener('click', minimizeWidget);
        
        // Send message
        sendButton.addEventListener('click', sendMessage);
        inputField.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Quick replies
        document.querySelectorAll('.aps-quick-reply').forEach(btn => {
            btn.addEventListener('click', () => {
                const query = btn.dataset.query;
                if (query) {
                    inputField.value = query;
                    sendMessage();
                }
            });
        });

        // Auto-resize input
        inputField.addEventListener('input', () => {
            inputField.style.height = 'auto';
            inputField.style.height = Math.min(inputField.scrollHeight, 120) + 'px';
        });

        // Click outside to close (optional)
        document.addEventListener('click', (e) => {
            if (isOpen && !widgetContainer.contains(e.target) && e.target !== chatButton) {
                // Optional: closeWidget();
            }
        });

        // Keyboard shortcut: Ctrl/Cmd + Shift + A to toggle
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'A') {
                e.preventDefault();
                toggleWidget();
            }
        });
    }

    /**
     * Send message to API
     */
    async function sendMessage() {
        const text = inputField.value.trim();
        if (!text || isLoading) return;

        // Add user message to UI
        addMessage('user', text);
        inputField.value = '';
        inputField.style.height = 'auto';
        isLoading = true;
        sendButton.disabled = true;
        sendButton.style.opacity = '0.6';

        // Show typing indicator
        showTypingIndicator();

        try {
            const response = await fetch(config.apiBaseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    messages: [{ role: 'user', content: text }],
                    use_rag: true,
                    temperature: 0.7,
                    max_tokens: 1000,
                }),
            });

            const data = await response.json();
            removeTypingIndicator();

            if (data.success) {
                addMessage('bot', data.message);
            } else {
                addMessage('bot', 'Sorry, I encountered an error. Please try again.');
                console.error('Chat API error:', data.message);
            }
        } catch (error) {
            removeTypingIndicator();
            addMessage('bot', 'Unable to connect. Please check your connection and try again.');
            console.error('Chat error:', error);
        } finally {
            isLoading = false;
            sendButton.disabled = false;
            sendButton.style.opacity = '1';
        }
    }

    /**
     * Add message to chat
     */
    function addMessage(role, content) {
        // Remove welcome message if present
        const welcomeMsg = document.querySelector('.aps-welcome-message');
        if (welcomeMsg) welcomeMsg.remove();

        const messageDiv = document.createElement('div');
        messageDiv.className = `aps-message ${role}`;
        
        const isBot = role === 'bot';
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        messageDiv.innerHTML = `
            ${isBot ? `
                <div class="aps-message-avatar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M8 21h8M12 17v-5"></path>
                    </svg>
                </div>
            ` : ''}
            <div class="aps-message-bubble ${isBot ? 'bot' : 'user'}">
                <div class="aps-message-content">${escapeHtml(content)}</div>
                <span class="aps-message-time">${time}</span>
            </div>
            ${!isBot ? `
                <div class="aps-message-avatar user">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
            ` : ''}
        `;

        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
        
        // Save session
        saveSession();
    }

    /**
     * Show typing indicator
     */
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.id = 'aps-typing-indicator';
        typingDiv.className = 'aps-message bot aps-typing';
        typingDiv.innerHTML = `
            <div class="aps-message-avatar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M8 21h8M12 17v-5"></path>
                </svg>
            </div>
            <div class="aps-message-bubble bot aps-typing-bubble">
                <div class="aps-typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        const typing = document.getElementById('aps-typing-indicator');
        if (typing) typing.remove();
    }

    /**
     * Toggle widget open/close
     */
    function toggleWidget() {
        if (isOpen) {
            closeWidget();
        } else {
            openWidget();
        }
    }

    function openWidget() {
        chatPanel.style.display = 'flex';
        chatButton.style.display = 'none';
        isOpen = true;
        inputField.focus();
        scrollToBottom();
    }

    function closeWidget() {
        chatPanel.style.display = 'none';
        chatButton.style.display = 'flex';
        isOpen = false;
    }

    function minimizeWidget() {
        closeWidget();
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function getCsrfToken() {
        // Try to get from meta tag
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        
        // Try from input
        const input = document.querySelector('input[name="csrf_token"]');
        if (input) return input.value;
        
        return '';
    }

    function saveSession() {
        if (!config.persistSession) return;
        const session = {
            messages: messages,
            timestamp: Date.now(),
        };
        try {
            localStorage.setItem('aps_ai_chat_session', JSON.stringify(session));
        } catch (e) {}
    }

    function loadSession() {
        try {
            const saved = localStorage.getItem('aps_ai_chat_session');
            if (saved) {
                const session = JSON.parse(saved);
                if (session.messages && session.messages.length > 0) {
                    messages = session.messages;
                    // Re-render messages
                    messages.forEach(msg => addMessage(msg.role, msg.content));
                }
            }
        } catch (e) {}
    }

    // Utility: Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ==================== STYLES ====================

    function getContainerStyles() {
        return `
            position: fixed;
            z-index: 99999;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            ${config.position === 'bottom-right' ? 'bottom: 24px; right: 24px;' : ''}
            ${config.position === 'bottom-left' ? 'bottom: 24px; left: 24px;' : ''}
        `;
    }

    function getButtonStyles() {
        return `
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, ${config.primaryColor} 0%, ${config.secondaryColor} 100%);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 8px 32px rgba(11, 25, 76, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        `;
    }

    function getPanelStyles() {
        return `
            width: 380px;
            height: 600px;
            max-height: calc(100vh - 48px);
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(11, 25, 76, 0.3);
            display: none;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(11, 25, 76, 0.08);
            animation: aps-slide-up 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        `;
    }

    function getHeaderStyles() {
        return `
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: linear-gradient(135deg, ${config.primaryColor} 0%, #1E3A8A 100%);
            color: white;
            border-radius: 20px 20px 0 0;
        `;
    }

    function getAvatarStyles() {
        return `
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: ${config.accentColor};
        `;
    }

    function getMessagesContainerStyles() {
        return `
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            background: #F8FAFC;
        `;
    }

    function getWelcomeMessageStyles() {
        return `
            animation: aps-fade-in 0.5s ease-out;
        `;
    }

    function getBotBubbleStyles() {
        return `
            background: white;
            border-radius: 18px 18px 18px 4px;
            padding: 12px 16px;
            max-width: 85%;
            box-shadow: 0 2px 8px rgba(11, 25, 76, 0.08);
            border: 1px solid rgba(11, 25, 76, 0.06);
        `;
    }

    function getQuickRepliesStyles() {
        return `
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 16px 16px;
            border-top: 1px solid rgba(11, 25, 76, 0.06);
        `;
    }

    function getInputAreaStyles() {
        return `
            display: flex;
            align-items: flex-end;
            gap: 8px;
            padding: 16px;
            background: white;
            border-top: 1px solid rgba(11, 25, 76, 0.06);
        `;
    }

    function getInputStyles() {
        return `
            flex: 1;
            min-height: 48px;
            max-height: 120px;
            padding: 12px 16px;
            border: 1px solid rgba(11, 25, 76, 0.12);
            border-radius: 24px;
            font-size: 14px;
            line-height: 1.5;
            color: ${config.primaryColor};
            background: #F8FAFC;
            outline: none;
            transition: all 0.2s ease;
            resize: none;
            font-family: inherit;
        `;
    }

    function getSendButtonStyles() {
        return `
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, ${config.secondaryColor} 0%, ${config.accentColor} 100%);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 16px rgba(245, 130, 32, 0.4);
            transition: all 0.2s ease;
            flex-shrink: 0;
        `;
    }

    function getPoweredByStyles() {
        return `
            padding: 12px;
            text-align: center;
            font-size: 11px;
            color: #94A3B8;
            background: #F8FAFC;
            border-top: 1px solid rgba(11, 25, 76, 0.06);
        `;
    }

    // Expose globally
    window.AIChatbotWidget = {
        init: init,
        open: openWidget,
        close: closeWidget,
        toggle: toggleWidget,
    };

    // Auto-init if config exists
    if (window.APS_AI_CHATBOT_CONFIG) {
        document.addEventListener('DOMContentLoaded', () => {
            init(window.APS_AI_CHATBOT_CONFIG);
        });
    }
})();