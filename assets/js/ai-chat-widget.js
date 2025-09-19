/**
 * AI Chat Widget for APS Dream Homes
 * Provides interactive chat functionality for property inquiries
 */

class AIChatWidget {
    /**
     * Initialize the chat widget
     * @param {Object} config - Configuration options
     */
    constructor(config = {}) {
        // Default configuration
        this.config = {
            apiBaseUrl: '/api/ai',
            csrfToken: '',
            position: 'right', // 'left' or 'right'
            primaryColor: '#4f46e5',
            welcomeMessage: 'Hello! I\'m your AI assistant. How can I help you with your property search today?',
            ...config
        };
        
        // State
        this.isOpen = false;
        this.isLoading = false;
        this.messages = [];
        
        // Initialize the widget
        this.init();
    }
    
    /**
     * Initialize the chat widget
     */
    init() {
        // Create the widget HTML
        this.createWidget();
        
        // Add event listeners
        this.addEventListeners();
        
        // Add welcome message
        this.addBotMessage(this.config.welcomeMessage);
    }
    
    /**
     * Create the widget HTML
     */
    createWidget() {
        // Create container
        this.container = document.createElement('div');
        this.container.className = 'ai-chat-widget';
        this.container.style.setProperty('--primary-color', this.config.primaryColor);
        
        // Create toggle button
        this.toggleButton = document.createElement('button');
        this.toggleButton.className = 'ai-chat-toggle';
        this.toggleButton.innerHTML = `
            <i class="fas fa-comment-dots"></i>
            <span class="ai-chat-badge"></span>
        `;
        
        // Create chat window
        this.chatWindow = document.createElement('div');
        this.chatWindow.className = 'ai-chat-window';
        this.chatWindow.innerHTML = `
            <div class="ai-chat-header">
                <div class="ai-chat-title">
                    <i class="fas fa-robot"></i>
                    <span>AI Assistant</span>
                </div>
                <button class="ai-chat-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ai-chat-messages"></div>
            <div class="ai-chat-input-container">
                <textarea 
                    class="ai-chat-input" 
                    placeholder="Type your message here..."
                    rows="1"
                ></textarea>
                <button class="ai-chat-send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div class="ai-chat-typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        
        // Append elements
        this.container.appendChild(this.toggleButton);
        this.container.appendChild(this.chatWindow);
        document.body.appendChild(this.container);
        
        // Position the widget
        this.positionWidget();
    }
    
    /**
     * Position the widget based on configuration
     */
    positionWidget() {
        if (this.config.position === 'left') {
            this.container.style.left = '20px';
            this.chatWindow.style.left = '80px';
            this.chatWindow.style.right = 'auto';
        } else {
            this.container.style.right = '20px';
            this.chatWindow.style.right = '80px';
            this.chatWindow.style.left = 'auto';
        }
    }
    
    /**
     * Add event listeners
     */
    addEventListeners() {
        // Toggle chat window
        this.toggleButton.addEventListener('click', () => this.toggleChat());
        
        // Close button
        const closeButton = this.chatWindow.querySelector('.ai-chat-close');
        closeButton.addEventListener('click', () => this.toggleChat(false));
        
        // Send message on Enter (Shift+Enter for new line)
        const input = this.chatWindow.querySelector('.ai-chat-input');
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        // Auto-resize textarea
        input.addEventListener('input', () => {
            this.adjustTextareaHeight(input);
        });
        
        // Send button
        const sendButton = this.chatWindow.querySelector('.ai-chat-send');
        sendButton.addEventListener('click', () => this.sendMessage());
    }
    
    /**
     * Toggle chat window
     * @param {boolean} [forceState] - Force the chat to be open or closed
     */
    toggleChat(forceState = null) {
        this.isOpen = forceState !== null ? forceState : !this.isOpen;
        
        if (this.isOpen) {
            this.container.classList.add('open');
            this.chatWindow.style.display = 'block';
            this.chatWindow.querySelector('.ai-chat-input').focus();
            
            // Scroll to bottom
            this.scrollToBottom();
        } else {
            this.container.classList.remove('open');
            // Small delay for the close animation
            setTimeout(() => {
                this.chatWindow.style.display = 'none';
            }, 300);
        }
    }
    
    /**
     * Send a message to the AI
     */
    async sendMessage() {
        const input = this.chatWindow.querySelector('.ai-chat-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        // Add user message to chat
        this.addUserMessage(message);
        
        // Clear input and reset height
        input.value = '';
        this.adjustTextareaHeight(input);
        
        // Show typing indicator
        this.showTypingIndicator(true);
        
        try {
            // Send message to API
            const response = await fetch(`${this.config.apiBaseUrl}/chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken || ''
                },
                body: JSON.stringify({
                    message,
                    conversation_id: this.conversationId || null,
                    context: window.location.href
                })
            });
            
            if (!response.ok) {
                throw new Error('Failed to get response from AI');
            }
            
            const data = await response.json();
            
            // Store conversation ID for context
            if (data.conversation_id) {
                this.conversationId = data.conversation_id;
            }
            
            // Add bot response to chat
            this.addBotMessage(data.response);
            
        } catch (error) {
            console.error('Chat error:', error);
            this.addBotMessage('Sorry, I encountered an error. Please try again later.');
        } finally {
            // Hide typing indicator
            this.showTypingIndicator(false);
        }
    }
    
    /**
     * Add a user message to the chat
     * @param {string} message - The message text
     */
    addUserMessage(message) {
        this.addMessage({
            text: message,
            sender: 'user',
            timestamp: new Date()
        });
    }
    
    /**
     * Add a bot message to the chat
     * @param {string} message - The message text
     */
    addBotMessage(message) {
        this.addMessage({
            text: message,
            sender: 'bot',
            timestamp: new Date()
        });
    }
    
    /**
     * Add a message to the chat
     * @param {Object} message - The message object
     * @param {string} message.text - The message text
     * @param {'user'|'bot'} message.sender - The sender of the message
     * @param {Date} message.timestamp - When the message was sent
     */
    addMessage({ text, sender, timestamp }) {
        const messagesContainer = this.chatWindow.querySelector('.ai-chat-messages');
        const messageElement = document.createElement('div');
        
        messageElement.className = `ai-chat-message ai-chat-message-${sender}`;
        messageElement.innerHTML = `
            <div class="ai-chat-message-content">
                ${text.replace(/\n/g, '<br>')}
            </div>
            <div class="ai-chat-message-time">
                ${this.formatTime(timestamp)}
            </div>
        `;
        
        messagesContainer.appendChild(messageElement);
        this.scrollToBottom();
        
        // Add to messages array
        this.messages.push({ text, sender, timestamp });
        
        // Update unread badge if chat is closed
        if (sender === 'bot' && !this.isOpen) {
            this.updateUnreadBadge(true);
        }
    }
    
    /**
     * Show or hide the typing indicator
     * @param {boolean} show - Whether to show the indicator
     */
    showTypingIndicator(show) {
        const indicator = this.chatWindow.querySelector('.ai-chat-typing-indicator');
        indicator.style.display = show ? 'flex' : 'none';
    }
    
    /**
     * Update the unread message badge
     * @param {boolean} hasUnread - Whether there are unread messages
     */
    updateUnreadBadge(hasUnread) {
        const badge = this.toggleButton.querySelector('.ai-chat-badge');
        if (hasUnread) {
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
    
    /**
     * Scroll the chat to the bottom
     */
    scrollToBottom() {
        const messagesContainer = this.chatWindow.querySelector('.ai-chat-messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    /**
     * Format a timestamp as a time string
     * @param {Date} date - The date to format
     * @returns {string} Formatted time string
     */
    formatTime(date) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    /**
     * Auto-resize the textarea based on content
     * @param {HTMLTextAreaElement} textarea - The textarea to adjust
     */
    adjustTextareaHeight(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = `${Math.min(textarea.scrollHeight, 150)}px`;
    }
    
    /**
     * Update the widget configuration
     * @param {Object} config - New configuration options
     */
    updateConfig(config) {
        this.config = { ...this.config, ...config };
        
        // Update primary color if changed
        if (config.primaryColor) {
            this.container.style.setProperty('--primary-color', config.primaryColor);
        }
        
        // Update position if changed
        if (config.position) {
            this.positionWidget();
        }
    }
}

// Auto-initialize the chat widget if data-ai-chat attribute is present
document.addEventListener('DOMContentLoaded', () => {
    const chatElement = document.querySelector('[data-ai-chat]');
    if (chatElement) {
        const config = {
            position: chatElement.dataset.position || 'right',
            primaryColor: chatElement.dataset.primaryColor || '#4f46e5',
            welcomeMessage: chatElement.dataset.welcomeMessage || 'Hello! How can I help you today?'
        };
        
        // Initialize the chat widget
        window.aiChatWidget = new AIChatWidget(config);
    }
});
