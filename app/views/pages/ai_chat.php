<?php
/**
 * AI Chat Page
 * Redirects to AI Assistant
 */
$page_title = 'AI Assistant - APS Dream Home';
$page_description = 'Professional AI Chat Assistant for Real Estate & Development';
?>

<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-robot me-2"></i>AI Assistant</h1>
        <p class="lead">Professional AI Chat for Real Estate & Development</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-comments me-2"></i>AI Chat Assistant</h4>
                    </div>
                    <div class="card-body">
                        <div id="chat-container" style="height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                            <div class="text-center text-muted">
                                <i class="fas fa-robot fa-3x mb-3"></i>
                                <p>Welcome to APS Dream Home AI Assistant!</p>
                                <p class="small">How can I help you today?</p>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <input type="text" id="chat-input" class="form-control" placeholder="Type your message...">
                            <button class="btn btn-primary" onclick="sendMessage()">
                                <i class="fas fa-paper-plane"></i> Send
                            </button>
                        </div>
                        
                        <div class="mt-3">
                            <p class="text-muted small mb-2">Quick Questions:</p>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-outline-primary btn-sm" onclick="askQuestion('What properties are available in Gorakhpur?')">Properties in Gorakhpur</button>
                                <button class="btn btn-outline-primary btn-sm" onclick="askQuestion('What is the price range for residential plots?')">Price Range</button>
                                <button class="btn btn-outline-primary btn-sm" onclick="askQuestion('Do you provide home loan assistance?')">Home Loan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
let chatHistory = [];

function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    if (!message) return;
    
    addMessage(message, 'user');
    input.value = '';
    
    // Simulate AI response
    setTimeout(() => {
        addMessage("Thank you for your inquiry! Our team will get back to you shortly. For immediate assistance, please call +91-7007444842.", 'ai');
    }, 1000);
}

function askQuestion(question) {
    document.getElementById('chat-input').value = question;
    sendMessage();
}

function addMessage(text, sender) {
    const container = document.getElementById('chat-container');
    const div = document.createElement('div');
    div.className = sender === 'user' ? 'text-end mb-2' : 'text-start mb-2';
    
    if (sender === 'user') {
        div.innerHTML = '<span class="badge bg-primary">' + text + '</span>';
    } else {
        div.innerHTML = '<span class="badge bg-secondary"><i class="fas fa-robot me-1"></i> ' + text + '</span>';
    }
    
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    chatHistory.push({sender, text});
}

// Enter key support
document.getElementById('chat-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});
</script>
