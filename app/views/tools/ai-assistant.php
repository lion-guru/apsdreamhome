<?php

// TODO: Add proper error handling with try-catch blocks

page_title = 'AI Property Assistant - APS Dream Home';
$page_description = 'Get AI-powered property recommendations and find your dream home with our intelligent assistant';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-4">
    <div class="ai-container">
        <div class="ai-header">
            <h1><i class="fas fa-robot me-3"></i>AI Property Assistant</h1>
            <p>Your intelligent real estate companion - Find your dream property with AI-powered recommendations</p>
        </div>
        
        <div class="ai-body">
            <div class="row">
                <div class="col-lg-8">
                    <div class="chat-container">
                        <div class="chat-messages" id="chatMessages">
                            <div class="message ai-message">
                                <div class="message-avatar">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content">
                                    <p>Hello! I'm your AI Property Assistant. I can help you find the perfect property based on your preferences. What kind of property are you looking for?</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="typing-indicator" id="typingIndicator">
                            <div class="typing-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                        
                        <div class="chat-input-container">
                            <div class="chat-input">
                                <input type="text" id="chatInput" placeholder="Ask me about properties, locations, prices..." />
                                <button onclick="sendMessage()">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="ai-features">
                        <h5><i class="fas fa-star me-2"></i>AI Features</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Property Recommendations</li>
                            <li><i class="fas fa-check text-success me-2"></i>Price Predictions</li>
                            <li><i class="fas fa-check text-success me-2"></i>Location Insights</li>
                            <li><i class="fas fa-check text-success me-2"></i>Market Analysis</li>
                            <li><i class="fas fa-check text-success me-2"></i>Investment Advice</li>
                        </ul>
                    </div>
                    
                    <div class="quick-actions">
                        <h6><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="askQuestion('Show me luxury apartments')">
                                <i class="fas fa-building me-2"></i>Luxury Apartments
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="askQuestion('Find properties under 50 lakhs')">
                                <i class="fas fa-rupee-sign me-2"></i>Budget Properties
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="askQuestion('Best locations for investment')">
                                <i class="fas fa-chart-line me-2"></i>Investment Areas
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ai-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    margin: 20px auto;
    max-width: 1200px;
    overflow: hidden;
}

.ai-header {
    background: var(--primary-gradient, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
    color: white;
    padding: 30px;
    text-align: center;
}

.ai-header h1 {
    margin: 0;
    font-size: 2.5rem;
    font-weight: 700;
}

.chat-container {
    height: 500px;
    display: flex;
    flex-direction: column;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
}

.message {
    display: flex;
    margin-bottom: 20px;
    align-items: flex-start;
}

.ai-message {
    justify-content: flex-start;
}

.user-message {
    justify-content: flex-end;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin: 0 10px;
}

.ai-message .message-avatar {
    background: var(--primary-color, #1a237e);
    color: white;
}

.user-message .message-avatar {
    background: var(--secondary-color, #ffc107);
    color: white;
    order: 2;
}

.message-content {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 18px;
    background: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.user-message .message-content {
    background: var(--primary-color, #1a237e);
    color: white;
    order: 1;
}

.typing-indicator {
    padding: 10px 16px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 18px;
    width: 60px;
    margin: 0 20px;
    display: none;
}

.typing-dots {
    display: flex;
    gap: 4px;
}

.typing-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #666;
    animation: typing 1.4s infinite;
}

.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% { opacity: 0.3; }
    30% { opacity: 1; }
}

.chat-input-container {
    padding: 20px;
    background: white;
    border-top: 1px solid #e0e0e0;
}

.chat-input {
    display: flex;
    gap: 10px;
}

.chat-input input {
    flex: 1;
    border: 1px solid #e0e0e0;
    border-radius: 25px;
    padding: 12px 20px;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.3s;
}

.chat-input input:focus {
    border-color: var(--primary-color, #1a237e);
}

.chat-input button {
    background: var(--primary-color, #1a237e);
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s;
}

.chat-input button:hover {
    transform: scale(1.1);
}

.ai-features, .quick-actions {
    background: white;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<script>
function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (message) {
        addUserMessage(message);
        input.value = '';
        
        // Show typing indicator
        showTypingIndicator();
        
        // Simulate AI response
        setTimeout(() => {
            hideTypingIndicator();
            addAIResponse(generateAIResponse(message));
        }, 1500);
    }
}

function askQuestion(question) {
    document.getElementById('chatInput').value = question;
    sendMessage();
}

function addUserMessage(message) {
    const chatMessages = document.getElementById('chatMessages');
    const messageHtml = `
        <div class="message user-message">
            <div class="message-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="message-content">
                <p>${message}</p>
            </div>
        </div>
    `;
    chatMessages.innerHTML += messageHtml;
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function addAIResponse(response) {
    const chatMessages = document.getElementById('chatMessages');
    const messageHtml = `
        <div class="message ai-message">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <p>${response}</p>
            </div>
        </div>
    `;
    chatMessages.innerHTML += messageHtml;
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function showTypingIndicator() {
    document.getElementById('typingIndicator').style.display = 'block';
}

function hideTypingIndicator() {
    document.getElementById('typingIndicator').style.display = 'none';
}

function generateAIResponse(message) {
    const responses = {
        'luxury': 'I found some excellent luxury apartments in Gomti Nagar and Hazratganj. These properties feature modern amenities, premium finishes, and prime locations. Would you like me to show you specific options?',
        'budget': 'Great! I have several properties under 50 lakhs in areas like Alambagh and Gomti Nagar Extension. These offer great value for money with good connectivity. Should I arrange a site visit?',
        'investment': 'Based on market analysis, Gomti Nagar Extension and Vibhuti Khand are showing excellent investment potential with 15-20% annual returns. The infrastructure development is driving property values up.',
        'default': 'That\'s a great question! Based on your requirements, I can help you find the perfect property. We have options across different budgets and locations. Could you tell me more about your specific needs?'
    };
    
    const lowerMessage = message.toLowerCase();
    for (const [key, response] of Object.entries(responses)) {
        if (lowerMessage.includes(key)) {
            return response;
        }
    }
    
    return responses.default;
}

// Handle Enter key
document.getElementById('chatInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});
</script>
