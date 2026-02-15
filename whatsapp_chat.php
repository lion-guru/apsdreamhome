<?php

/**
 * WhatsApp Chat Widget - APS Dream Homes
 * Integrated WhatsApp support for customer inquiries
 */

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/config.php';

$db = \App\Core\App::database();

// Handle WhatsApp message submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['whatsapp_message'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($phone) && !empty($message)) {
        // Store inquiry in database
        $db->execute("INSERT INTO whatsapp_inquiries (name, phone, message, status, created_at) VALUES (:name, :phone, :message, 'new', NOW())", [
            'name' => $name,
            'phone' => $phone,
            'message' => $message
        ]);

        // Send WhatsApp message to business
        $business_message = "🔔 New Customer Inquiry\n\n";
        $business_message .= "👤 Name: " . $name . "\n";
        $business_message .= "📱 Phone: " . $phone . "\n";
        $business_message .= "💬 Message: " . $message . "\n";
        $business_message .= "⏰ Time: " . date('d M Y H:i') . "\n\n";
        $business_message .= "Please respond to this inquiry promptly.";

        // Send to business WhatsApp (placeholder)
        sendWhatsAppToBusiness($phone, $business_message);

        echo json_encode(['success' => true, 'message' => 'Your message has been sent successfully! Our team will contact you soon.']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Please fill all fields']);
        exit;
    }
}

// Function to send WhatsApp to business
function sendWhatsAppToBusiness($customer_phone, $message)
{
    // This would integrate with WhatsApp Business API
    // For now, just log the message
    error_log("WhatsApp Business Notification:\n" . $message);
    return true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Chat Support - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .whatsapp-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .whatsapp-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #25D366;
            border: none;
            color: white;
            font-size: 30px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .whatsapp-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(37, 211, 102, 0.4);
        }

        .whatsapp-popup {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            display: none;
            z-index: 1001;
            overflow: hidden;
        }

        .whatsapp-popup.show {
            display: block;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .whatsapp-header {
            background: #25D366;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .whatsapp-body {
            padding: 20px;
            height: 380px;
            overflow-y: auto;
        }

        .whatsapp-footer {
            padding: 15px;
            border-top: 1px solid #eee;
        }

        .chat-message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 18px;
            max-width: 80%;
        }

        .chat-message.user {
            background: #25D366;
            color: white;
            margin-left: auto;
            text-align: right;
        }

        .chat-message.support {
            background: #f0f0f0;
            color: #333;
        }

        .quick-reply {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 8px 15px;
            margin: 5px;
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
            transition: all 0.2s;
        }

        .quick-reply:hover {
            background: #25D366;
            color: white;
            border-color: #25D366;
        }

        .typing-indicator {
            display: none;
            font-style: italic;
            color: #666;
            font-size: 14px;
        }

        .whatsapp-input {
            border: 1px solid #ddd;
            border-radius: 25px;
            padding: 10px 15px;
            width: 100%;
            margin-bottom: 10px;
        }

        .whatsapp-send {
            background: #25D366;
            border: none;
            border-radius: 25px;
            color: white;
            padding: 10px 20px;
            width: 100%;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <!-- WhatsApp Widget -->
    <div class="whatsapp-widget">
        <!-- Main WhatsApp Button -->
        <button class="whatsapp-button" id="whatsappToggle" title="Chat with us on WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </button>

        <!-- WhatsApp Chat Popup -->
        <div class="whatsapp-popup" id="whatsappPopup">
            <div class="whatsapp-header">
                <h6 class="mb-1">💬 APS Dream Homes Support</h6>
                <small>Typically replies in a few minutes</small>
            </div>

            <div class="whatsapp-body" id="chatBody">
                <div class="chat-message support">
                    👋 Hi! Welcome to APS Dream Homes!<br>
                    How can we help you today?
                </div>

                <div class="mb-3">
                    <strong>Quick Questions:</strong>
                    <div class="mt-2">
                        <div class="quick-reply" data-message="I'm looking for a property to buy">
                            🏠 Property Search
                        </div>
                        <div class="quick-reply" data-message="I want to sell my property">
                            🏷️ Sell Property
                        </div>
                        <div class="quick-reply" data-message="I need financing help">
                            💰 Home Loan
                        </div>
                        <div class="quick-reply" data-message="I want to become an agent">
                            👥 Join as Agent
                        </div>
                    </div>
                </div>

                <div class="typing-indicator" id="typingIndicator">
                    Support is typing...
                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const whatsappToggle = document.getElementById('whatsappToggle');
            const whatsappPopup = document.getElementById('whatsappPopup');
            const whatsappForm = document.getElementById('whatsappForm');
            const chatBody = document.getElementById('chatBody');
            const typingIndicator = document.getElementById('typingIndicator');
            const quickReplies = document.querySelectorAll('.quick-reply');

            // Toggle WhatsApp popup
            whatsappToggle.addEventListener('click', function() {
                whatsappPopup.classList.toggle('show');
            });

            // Close popup when clicking outside
            document.addEventListener('click', function(e) {
                if (!whatsappToggle.contains(e.target) && !whatsappPopup.contains(e.target)) {
                    whatsappPopup.classList.remove('show');
                }
            });

            // Quick reply functionality
            quickReplies.forEach(reply => {
                reply.addEventListener('click', function() {
                    const message = this.getAttribute('data-message');
                    document.getElementById('customerMessage').value = message;
                    document.getElementById('customerMessage').focus();
                });
            });

            // Form submission
            whatsappForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const name = document.getElementById('customerName').value;
                const phone = document.getElementById('customerPhone').value;
                const message = document.getElementById('customerMessage').value;

                if (name && phone && message) {
                    // Show typing indicator
                    typingIndicator.style.display = 'block';

                    // Send message via AJAX
                    const formData = new FormData();
                    formData.append('name', name);
                    formData.append('phone', phone);
                    formData.append('message', message);
                    formData.append('whatsapp_message', '1');

                    fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            typingIndicator.style.display = 'none';

                            if (data.success) {
                                // Add user message to chat
                                const userMessage = document.createElement('div');
                                userMessage.className = 'chat-message user';
                                userMessage.innerHTML = message;
                                chatBody.appendChild(userMessage);

                                // Add success message
                                const successMessage = document.createElement('div');
                                successMessage.className = 'chat-message support';
                                successMessage.innerHTML = '✅ ' + data.message;
                                chatBody.appendChild(successMessage);

                                // Clear form
                                whatsappForm.reset();

                                // Scroll to bottom
                                chatBody.scrollTop = chatBody.scrollHeight;

                                // Close popup after 3 seconds
                                setTimeout(() => {
                                    whatsappPopup.classList.remove('show');
                                }, 3000);
                            } else {
                                const errorMessage = document.createElement('div');
                                errorMessage.className = 'chat-message support';
                                errorMessage.innerHTML = '❌ ' + data.message;
                                chatBody.appendChild(errorMessage);
                                chatBody.scrollTop = chatBody.scrollHeight;
                            }
                        })
                        .catch(error => {
                            typingIndicator.style.display = 'none';
                            console.error('Error:', error);
                            const errorMessage = document.createElement('div');
                            errorMessage.className = 'chat-message support';
                            errorMessage.innerHTML = '❌ Sorry, there was an error sending your message. Please try again or call us directly.';
                            chatBody.appendChild(errorMessage);
                            chatBody.scrollTop = chatBody.scrollHeight;
                        });
                }
            });

            // Auto-scroll to bottom when new messages are added
            const observer = new MutationObserver(() => {
                chatBody.scrollTop = chatBody.scrollHeight;
            });

            observer.observe(chatBody, {
                childList: true,
                subtree: true
            });
        });
    </script>
</body>

</html>