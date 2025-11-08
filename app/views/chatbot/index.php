<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'AI Assistant'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .chatbot-container {
            height: 600px;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            background: #f8f9fa;
        }
        .chatbot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .chatbot-messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: white;
        }
        .message {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 15px;
            max-width: 80%;
        }
        .message.user {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .message.bot {
            background: #e9ecef;
            color: #333;
            margin-right: auto;
        }
        .message-actions {
            margin-top: 10px;
        }
        .message-actions button {
            margin-right: 10px;
            margin-bottom: 5px;
            border: none;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .message-actions .bot-actions button {
            background: rgba(0,0,0,0.1);
            color: #333;
        }
        .chatbot-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #e9ecef;
        }
        .typing-indicator {
            display: none;
            font-style: italic;
            color: #6c757d;
        }
        .quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        .quick-reply-btn {
            background: #e9ecef;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .quick-reply-btn:hover {
            background: #007bff;
            color: white;
        }
        .chatbot-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .property-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .property-card h6 {
            color: #007bff;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/layouts/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="chatbot-container">
                    <!-- Header -->
                    <div class="chatbot-header">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="chatbot-avatar me-3">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">APS Dream Home Assistant</h4>
                                <small>I'm here to help you find your perfect property!</small>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div class="chatbot-messages" id="messagesContainer">
                        <!-- Welcome Message -->
                        <div class="message bot">
                            <div class="d-flex align-items-start">
                                <div class="chatbot-avatar me-2">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">APS Assistant</div>
                                    <div>Hello! I'm your personal property assistant. I can help you find properties, answer questions about pricing, locations, and connect you with our agents.</div>
                                    <div class="quick-replies">
                                        <?php foreach ($quick_replies as $reply): ?>
                                            <button class="quick-reply-btn" onclick="sendQuickReply('<?php echo addslashes($reply); ?>')">
                                                <?php echo htmlspecialchars($reply); ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Typing Indicator -->
                    <div class="typing-indicator text-center p-2" id="typingIndicator">
                        <small><i class="fas fa-circle-notch fa-spin me-1"></i>Assistant is typing...</small>
                    </div>

                    <!-- Input Area -->
                    <div class="chatbot-input">
                        <div class="row g-2">
                            <div class="col-10">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="messageInput"
                                           placeholder="Type your message here..." style="border-radius: 25px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="sendMessage()">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-primary w-100" onclick="sendMessage()">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Powered by AI • Available 24/7 • Response time: < 2 seconds
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Chat Features -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-search fa-2x text-primary mb-2"></i>
                                <h6>Property Search</h6>
                                <p class="text-muted mb-0">Find properties by location, type, and budget</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-calculator fa-2x text-success mb-2"></i>
                                <h6>Price Calculator</h6>
                                <p class="text-muted mb-0">Get instant price estimates and EMI calculations</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-headset fa-2x text-warning mb-2"></i>
                                <h6>Agent Connect</h6>
                                <p class="text-muted mb-0">Connect with expert agents instantly</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const messagesContainer = document.getElementById('messagesContainer');
        const messageInput = document.getElementById('messageInput');
        const typingIndicator = document.getElementById('typingIndicator');

        // Auto-focus on input
        messageInput.focus();

        // Send message on Enter
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Add user message
            addMessage('user', message);
            messageInput.value = '';

            // Show typing indicator
            typingIndicator.style.display = 'block';
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // Send to server
            fetch('<?php echo BASE_URL; ?>api/chatbot/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    context: {}
                })
            })
            .then(response => response.json())
            .then(data => {
                typingIndicator.style.display = 'none';

                if (data.success) {
                    // Add bot response
                    addBotResponse(data.data);

                    // Scroll to bottom
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                } else {
                    addMessage('bot', 'Sorry, I encountered an error. Please try again.');
                }
            })
            .catch(error => {
                typingIndicator.style.display = 'none';
                addMessage('bot', 'Sorry, I\'m having trouble connecting. Please try again later.');
                console.error('Chatbot error:', error);
            });
        }

        function sendQuickReply(message) {
            messageInput.value = message;
            sendMessage();
        }

        function addMessage(type, content) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;

            if (type === 'bot') {
                messageDiv.innerHTML = `
                    <div class="d-flex align-items-start">
                        <div class="chatbot-avatar me-2">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <div class="fw-bold">APS Assistant</div>
                            <div>${content}</div>
                        </div>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="d-flex align-items-start justify-content-end">
                        <div>
                            <div class="fw-bold">You</div>
                            <div>${content}</div>
                        </div>
                        <div class="chatbot-avatar ms-2" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                `;
            }

            messagesContainer.appendChild(messageDiv);
        }

        function addBotResponse(data) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message bot';

            let content = `<div class="fw-bold">APS Assistant</div><div>${data.response}</div>`;

            // Add property cards if properties are returned
            if (data.properties && data.properties.length > 0) {
                content += '<div class="mt-3">';
                data.properties.forEach(property => {
                    content += `
                        <div class="property-card">
                            <h6>${property.title}</h6>
                            <p class="mb-1">💰 ₹${property.price.toLocaleString()}</p>
                            <p class="mb-1">📍 ${property.city}, ${property.state}</p>
                            <p class="mb-2">${property.bedrooms}BHK • ${property.area_sqft} sqft</p>
                            <a href="<?php echo BASE_URL; ?>property/${property.id}" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    `;
                });
                content += '</div>';
            }

            // Add action buttons
            if (data.action_buttons && data.action_buttons.length > 0) {
                content += '<div class="message-actions bot-actions">';
                data.action_buttons.forEach(button => {
                    content += `<button onclick="handleAction('${button.action}')">${button.text}</button>`;
                });
                content += '</div>';
            }

            messageDiv.innerHTML = `
                <div class="d-flex align-items-start">
                    <div class="chatbot-avatar me-2">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>${content}</div>
                </div>
            `;

            messagesContainer.appendChild(messageDiv);
        }

        function handleAction(action) {
            switch (action) {
                case 'search_more':
                    sendQuickReply('Show me more properties');
                    break;
                case 'contact_agent':
                    sendQuickReply('I want to contact an agent');
                    break;
                case 'show_emi':
                    sendQuickReply('Show me EMI calculator');
                    break;
                case 'schedule_visit':
                    sendQuickReply('Schedule a property visit');
                    break;
                default:
                    console.log('Unknown action:', action);
            }
        }

        // Initialize with some sample interactions
        setTimeout(() => {
            if (messagesContainer.children.length === 1) {
                // Add sample conversation after 3 seconds
                setTimeout(() => {
                    addMessage('user', 'Show me apartments in Delhi');
                    setTimeout(() => {
                        addBotResponse({
                            response: 'I found several great apartments in Delhi! Here are the top matches:',
                            properties: [
                                {
                                    id: 1,
                                    title: 'Luxury Apartment in Connaught Place',
                                    price: 7500000,
                                    city: 'Delhi',
                                    state: 'Delhi',
                                    bedrooms: 3,
                                    area_sqft: 1500
                                }
                            ],
                            action_buttons: [
                                { text: 'Show More', action: 'search_more' },
                                { text: 'Contact Agent', action: 'contact_agent' }
                            ]
                        });
                    }, 1000);
                }, 3000);
            }
        }, 1000);
    </script>
</body>
</html>
