<?php
$extraHead = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
?>
<style>
.whatsapp-page-wrapper { padding: 60px 0; }
.whatsapp-page-wrapper h1 { color: #25D366; }
.whatsapp-widget { position: relative; z-index: 1; }
.whatsapp-button {
    width: 60px; height: 60px; border-radius: 50%; background: #25D366;
    border: none; color: white; font-size: 30px; cursor: pointer;
    box-shadow: 0 4px 20px rgba(37, 211, 102, 0.3); transition: all 0.3s ease;
    display: flex; align-items: center; justify-content: center;
    position: fixed; bottom: 20px; right: 20px; z-index: 1000;
}
.whatsapp-button:hover { transform: scale(1.1); box-shadow: 0 6px 25px rgba(37, 211, 102, 0.4); }
.whatsapp-popup {
    position: fixed; bottom: 90px; right: 20px;
    width: 350px; height: 500px; background: white;
    border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    display: none; z-index: 1001; overflow: hidden;
}
.whatsapp-popup.show { display: block; animation: slideUp 0.3s ease; }
@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.whatsapp-header { background: #25D366; color: white; padding: 15px; text-align: center; }
.whatsapp-body { padding: 20px; height: 380px; overflow-y: auto; }
.whatsapp-footer { padding: 15px; border-top: 1px solid #eee; }
.chat-message { margin-bottom: 15px; padding: 10px 15px; border-radius: 18px; max-width: 80%; }
.chat-message.user { background: #25D366; color: white; margin-left: auto; text-align: right; }
.chat-message.support { background: #f0f0f0; color: #333; }
.quick-reply {
    background: #f8f9fa; border: 1px solid #ddd; border-radius: 20px;
    padding: 8px 15px; margin: 5px; cursor: pointer; display: inline-block; font-size: 14px; transition: all 0.2s;
}
.quick-reply:hover { background: #25D366; color: white; border-color: #25D366; }
.typing-indicator { display: none; font-style: italic; color: #666; font-size: 14px; }
.whatsapp-input {
    border: 1px solid #ddd; border-radius: 25px; padding: 10px 15px; width: 100%; margin-bottom: 10px;
}
.whatsapp-send { background: #25D366; border: none; border-radius: 25px; color: white; padding: 10px 20px; width: 100%; cursor: pointer; }
</style>

<div class="container whatsapp-page-wrapper">
    <div class="text-center mb-5">
        <h1><i class="fab fa-whatsapp me-2"></i>WhatsApp Support</h1>
        <p class="lead text-muted">Chat with us directly on WhatsApp for instant property queries</p>
    </div>
</div>

<!-- WhatsApp Widget -->
<div class="whatsapp-widget">
    <button class="whatsapp-button" id="whatsappToggle" title="Chat with us on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </button>
    <div class="whatsapp-popup" id="whatsappPopup">
        <div class="whatsapp-header">
            <h6 class="mb-1"><i class="fab fa-whatsapp me-1"></i> APS Dream Homes Support</h6>
            <small>Typically replies in a few minutes</small>
        </div>
        <div class="whatsapp-body" id="chatBody">
            <div class="chat-message support">
                Hi! Welcome to APS Dream Homes!<br>How can we help you today?
            </div>
            <div class="mb-3">
                <strong>Quick Questions:</strong>
                <div class="mt-2">
                    <div class="quick-reply" data-message="I'm looking for a property to buy">
                        <i class="fas fa-home me-1"></i> Property Search
                    </div>
                    <div class="quick-reply" data-message="I want to sell my property">
                        <i class="fas fa-tag me-1"></i> Sell Property
                    </div>
                    <div class="quick-reply" data-message="I need financing help">
                        <i class="fas fa-rupee-sign me-1"></i> Home Loan
                    </div>
                    <div class="quick-reply" data-message="I want to become an agent">
                        <i class="fas fa-users me-1"></i> Join as Agent
                    </div>
                </div>
            </div>
            <div class="typing-indicator" id="typingIndicator">Support is typing...</div>
        </div>
        <div class="whatsapp-footer">
            <form id="whatsappForm">
                <input type="text" class="whatsapp-input" id="customerName" placeholder="Your name" required>
                <input type="tel" class="whatsapp-input" id="customerPhone" placeholder="Your phone number" required>
                <textarea class="whatsapp-input" id="customerMessage" placeholder="Type your message..." rows="2" required></textarea>
                <button type="submit" class="whatsapp-send">
                    <i class="fab fa-whatsapp me-2"></i>Send Message
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const whatsappToggle = document.getElementById('whatsappToggle');
    const whatsappPopup = document.getElementById('whatsappPopup');
    const whatsappForm = document.getElementById('whatsappForm');
    const chatBody = document.getElementById('chatBody');
    const typingIndicator = document.getElementById('typingIndicator');
    const quickReplies = document.querySelectorAll('.quick-reply');

    whatsappToggle.addEventListener('click', function() { whatsappPopup.classList.toggle('show'); });

    document.addEventListener('click', function(e) {
        if (!whatsappToggle.contains(e.target) && !whatsappPopup.contains(e.target)) {
            whatsappPopup.classList.remove('show');
        }
    });

    quickReplies.forEach(reply => {
        reply.addEventListener('click', function() {
            document.getElementById('customerMessage').value = this.getAttribute('data-message');
            document.getElementById('customerMessage').focus();
        });
    });

    whatsappForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('customerName').value;
        const phone = document.getElementById('customerPhone').value;
        const message = document.getElementById('customerMessage').value;

        if (name && phone && message) {
            typingIndicator.style.display = 'block';
            const formData = new FormData();
            formData.append('name', name);
            formData.append('phone', phone);
            formData.append('message', message);
            formData.append('whatsapp_message', '1');

            fetch('<?= BASE_URL ?>whatsapp-webhook', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                typingIndicator.style.display = 'none';
                const userMsg = document.createElement('div');
                userMsg.className = 'chat-message user';
                userMsg.innerHTML = message;
                chatBody.appendChild(userMsg);

                const replyMsg = document.createElement('div');
                replyMsg.className = 'chat-message support';
                replyMsg.innerHTML = (data.success ? '&#10004; ' : '&#10060; ') + (data.message || 'Message sent!');
                chatBody.appendChild(replyMsg);
                chatBody.scrollTop = chatBody.scrollHeight;

                if (data.success) {
                    whatsappForm.reset();
                    setTimeout(() => { whatsappPopup.classList.remove('show'); }, 3000);
                }
            })
            .catch(error => {
                typingIndicator.style.display = 'none';
                const errMsg = document.createElement('div');
                errMsg.className = 'chat-message support';
                errMsg.innerHTML = 'Sorry, there was an error. Please try calling us directly.';
                chatBody.appendChild(errMsg);
                chatBody.scrollTop = chatBody.scrollHeight;
            });
        }
    });
});
</script>
