<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'APS Dream Home - Premium Real Estate in Uttar Pradesh'; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Discover premium residential and commercial properties in Gorakhpur, Lucknow, Kushinagar, and across Uttar Pradesh with APS Dream Home. Premium plots, modern amenities, and trusted service.'; ?>">
    <meta name="keywords" content="real estate, plots, homes, Gorakhpur, Lucknow, Kushinagar, Varanasi, Uttar Pradesh, property, residential, commercial">
    <meta name="author" content="APS Dream Home">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $page_title ?? 'APS Dream Home - Premium Real Estate'; ?>">
    <meta property="og:description" content="<?php echo $page_description ?? 'Discover premium residential and commercial properties in Gorakhpur and across Uttar Pradesh.'; ?>">
    <meta property="og:image" content="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg">
    <meta property="og:url" content="<?php echo BASE_URL . ($_SERVER['REQUEST_URI'] ?? '/'); ?>">
    <meta property="og:site_name" content="APS Dream Home">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $page_title ?? 'APS Dream Home'; ?>">
    <meta name="twitter:description" content="<?php echo $page_description ?? 'Premium Real Estate in Uttar Pradesh'; ?>">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo BASE_URL . ($_SERVER['REQUEST_URI'] ?? '/'); ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Extra head content from views -->
    <?php if (!empty($extraHead)) echo $extraHead; ?>

    <!-- Critical CSS Inline -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --white-color: #ffffff;
            --text-color: #333333;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--white-color);
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(44, 62, 80, 0.9), rgba(52, 73, 94, 0.9)),
                url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover;
            display: flex;
            align-items: center;
            color: white;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-content .lead {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .trust-indicators {
            margin-top: 3rem;
        }

        .trust-item {
            text-align: center;
            padding: 1rem;
        }

        .trust-item i {
            color: var(--accent-color);
            margin-bottom: 0.5rem;
        }

        .trust-item h6 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        /* Navigation */
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color) !important;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--text-color) !important;
            margin: 0 0.5rem;
            transition: var(--transition);
        }

        .navbar-nav .nav-link:hover {
            color: var(--accent-color) !important;
        }

        /* Buttons */
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.8);
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        /* Sections */
        .section-padding {
            padding: 5rem 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .section-subtitle {
            font-size: 1.125rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
        }

        /* Search Section */
        .search-section {
            background: var(--light-color);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .form-control,
        .form-select {
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        /* Stats Section */
        .stats-section {
            background: var(--gradient-primary);
            color: white;
            padding: 4rem 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.125rem;
            opacity: 0.9;
        }

        /* Contact Section */
        .contact-section {
            background: var(--light-color);
        }

        /* Footer */
        footer {
            background: var(--dark-color);
            color: white;
            padding: 3rem 0 1rem;
        }

        footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
        }

        footer a:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }

            .section-padding {
                padding: 3rem 0;
            }

            .trust-indicators .col-4 {
                margin-bottom: 1rem;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.8s ease-out;
        }

        .animate-fade-in-delay {
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .animate-fade-in-delay-2 {
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        /* Fix: Add padding for fixed header (dynamic via CSS variable) */
        main {
            padding-top: var(--header-height, 80px);
        }

        /* Hero sections need extra padding adjustment */
        .hero-section,
        .py-5:first-of-type {
            margin-top: -80px;
            padding-top: 160px !important;
        }
    </style>
    <script>
        // Dynamically set header height for page offset
        (function() {
            function setHeaderHeight() {
                var hdr = document.querySelector('header.premium-header');
                var h = hdr ? hdr.offsetHeight : 80;
                document.documentElement.style.setProperty('--header-height', h + 'px');
            }
            window.addEventListener('load', setHeaderHeight);
            window.addEventListener('resize', setHeaderHeight);
        })();
    </script>

    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/apple-touch-icon.png">
</head>

<body>
    <?php
    // Admin pages skip public header entirely
    $isAdminPage = isset($admin_layout) && $admin_layout === true;
    $isPremiumPage = isset($premium_layout) && $premium_layout === true;

    if (!$isAdminPage) {
        if ($isPremiumPage) {
            include __DIR__ . '/active/header_new.php';
        } else {
            include __DIR__ . '/header.php';
        }
    }
    ?>

    <main>
        <?php echo $content ?? ''; ?>
    </main>

    <?php
    if (!$isAdminPage) {
        if ($isPremiumPage) {
            include __DIR__ . '/active/footer_new.php';
        } else {
            include __DIR__ . '/footer.php';
        }
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AI Chatbot (Left Side) -->
    <div id="ai-chatbot" class="ai-chatbot-container">
        <!-- Chat Popup -->
        <div class="ai-chat-popup" id="chatPopup">
            <div class="ai-chat-header">
                <div class="ai-avatar">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg" alt="APS Assistant" onerror="this.style.display='none'">
                    <span class="online-indicator"></span>
                </div>
                <div class="ai-header-info">
                    <h5>APS Property Assistant</h5>
                    <span class="status-text">Online • Ready to Help</span>
                </div>
                <button class="ai-close-btn" onclick="toggleChat()">&times;</button>
            </div>
            <div class="ai-chat-body" id="chatBody">
                <div class="ai-message bot">
                    <div class="ai-message-content">
                        Namaste! 🙏<br><br>
                        Welcome to <strong>APS Dream Home</strong>! 🏠<br><br>
                        I'm your personal property assistant. Tell me what you're looking for!
                    </div>
                    <span class="ai-time">Just now</span>
                </div>
                <div class="quick-actions">
                    <button class="quick-btn" onclick="sendQuickMessage('View Properties')">
                        <i class="fas fa-home"></i> Properties
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Plot Prices')">
                        <i class="fas fa-tag"></i> Prices
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Book Site Visit')">
                        <i class="fas fa-calendar-check"></i> Book Visit
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Home Loan Help')">
                        <i class="fas fa-university"></i> Home Loan
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('RERA Info')">
                        <i class="fas fa-shield-alt"></i> RERA Verified
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Contact Agent')">
                        <i class="fas fa-phone"></i> Call Us
                    </button>
                </div>
            </div>
            <div class="ai-chat-footer">
                <input type="text" id="chatInput" placeholder="Ask about properties..." onkeypress="handleChatKeypress(event)">
                <button class="ai-send-btn" onclick="sendChatMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>

        <!-- Floating Button -->
        <button class="ai-float-btn" id="aiFloatBtn" onclick="toggleChat()">
            <i class="fas fa-comments"></i>
            <span class="ai-pulse"></span>
        </button>
    </div>

    <!-- WhatsApp Button (Right Side - Manual Chat) -->
    <a href="https://wa.me/919277121112?text=Hi, I'm interested in APS Dream Home properties" target="_blank" class="whatsapp-float-btn" title="Chat on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <style>
        .ai-chatbot-container {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 9999;
        }

        .ai-float-btn {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
            transition: all 0.3s ease;
            position: relative;
        }

        .ai-float-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.7);
        }

        .ai-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            animation: pulse 2s infinite;
            z-index: -1;
            top: 0;
            left: 0;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.7;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        .ai-chat-popup {
            position: absolute;
            bottom: 80px;
            left: 0;
            width: 380px;
            height: 550px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: slideUp 0.3s ease;
        }

        .ai-chat-popup.active {
            display: flex;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .ai-chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ai-avatar {
            position: relative;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: white;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ai-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .online-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: #25D366;
            border-radius: 50%;
            border: 2px solid white;
        }

        .ai-header-info {
            flex: 1;
        }

        .ai-header-info h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .status-text {
            font-size: 12px;
            opacity: 0.9;
        }

        .ai-close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .ai-chat-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }

        .ai-message {
            margin-bottom: 15px;
            max-width: 85%;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .ai-message.bot {
            margin-right: auto;
        }

        .ai-message.user {
            margin-left: auto;
            text-align: right;
        }

        .ai-message-content {
            padding: 12px 16px;
            border-radius: 18px;
            line-height: 1.5;
            font-size: 14px;
        }

        .ai-message.bot .ai-message-content {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .ai-message.user .ai-message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .ai-time {
            font-size: 10px;
            color: #999;
            margin-top: 4px;
            display: block;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 15px;
        }

        .quick-btn {
            background: white;
            border: 1px solid #e0e0e0;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            color: #333;
        }

        .quick-btn:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
        }

        .quick-btn i {
            font-size: 14px;
        }

        .ai-chat-footer {
            padding: 15px;
            background: white;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .ai-chat-footer input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
        }

        .ai-chat-footer input:focus {
            border-color: #667eea;
        }

        .ai-send-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .ai-send-btn:hover {
            transform: scale(1.1);
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 15px;
            background: white;
            border-radius: 18px;
            width: fit-content;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-6px);
            }
        }

        .ai-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
        }

        .ai-suggestion {
            background: #f0f0f0;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .ai-suggestion:hover {
            background: #667eea;
            color: white;
        }

        /* WhatsApp Button - Right Side */
        .whatsapp-float-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #25D366;
            color: white;
            font-size: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
            z-index: 9998;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .whatsapp-float-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.6);
        }

        @media (max-width: 480px) {
            .ai-chat-popup {
                width: calc(100vw - 20px);
                left: -10px;
                height: 70vh;
            }

            .ai-float-btn {
                width: 55px;
                height: 55px;
                font-size: 24px;
            }

            .whatsapp-float-btn {
                width: 50px;
                height: 50px;
                font-size: 26px;
                bottom: 25px;
                right: 25px;
            }

            .ai-chatbot-container {
                left: 15px;
                bottom: 25px;
            }
        }
    </style>

    <script>
        let chatOpen = false;

        function toggleChat() {
            const popup = document.getElementById('chatPopup');
            chatOpen = !chatOpen;
            if (chatOpen) {
                popup.classList.add('active');
                document.getElementById('chatInput').focus();
            } else {
                popup.classList.remove('active');
            }
        }

        function addMessage(text, isUser = false) {
            const chatBody = document.getElementById('chatBody');
            const msgDiv = document.createElement('div');
            msgDiv.className = `ai-message ${isUser ? 'user' : 'bot'}`;

            const time = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });

            msgDiv.innerHTML = `
                <div class="ai-message-content">${text.replace(/\n/g, '<br>')}</div>
                <span class="ai-time">${time}</span>
            `;

            chatBody.appendChild(msgDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function showTyping() {
            const chatBody = document.getElementById('chatBody');
            const typing = document.createElement('div');
            typing.id = 'typingIndicator';
            typing.className = 'typing-indicator';
            typing.innerHTML = '<span></span><span></span><span></span>';
            chatBody.appendChild(typing);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function hideTyping() {
            const typing = document.getElementById('typingIndicator');
            if (typing) typing.remove();
        }

        function sendQuickMessage(message) {
            document.getElementById('chatInput').value = message;
            sendChatMessage();
        }

        async function sendChatMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            if (!message) return;

            addMessage(message, true);
            input.value = '';

            showTyping();

            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/gemini/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message
                    })
                });

                const data = await response.json();
                hideTyping();

                if (data.success) {
                    addMessage(data.reply);

                    // Add quick reply suggestions
                    if (data.quick_replies && data.quick_replies.length > 0) {
                        const chatBody = document.getElementById('chatBody');
                        const suggestionsDiv = document.createElement('div');
                        suggestionsDiv.className = 'ai-suggestions';
                        data.quick_replies.forEach(reply => {
                            suggestionsDiv.innerHTML += `<span class="ai-suggestion" onclick="sendQuickMessage('${reply}')">${reply}</span>`;
                        });
                        chatBody.appendChild(suggestionsDiv);
                    }
                } else {
                    addMessage("Sorry, I'm having trouble understanding. Try calling us at <strong>+91 92771 21112</strong> or <a href='<?php echo BASE_URL; ?>/contact'>Contact Form</a>");
                }
            } catch (error) {
                hideTyping();
                addMessage("Connection issue! Please try again or call <strong>+91 92771 21112</strong> for instant help.");
            }
        }

        function handleChatKeypress(e) {
            if (e.key === 'Enter') sendChatMessage();
        }

        // Auto-greet after 10 seconds
        setTimeout(() => {
            if (!chatOpen) {
                const btn = document.getElementById('aiFloatBtn');
                btn.style.animation = 'pulse 1s infinite';
            }
        }, 10000);
    </script>

    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/premium-header.js"></script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animated counter for stats
        const counters = document.querySelectorAll('.stat-number');
        const speed = 200;

        counters.forEach(counter => {
            const animate = () => {
                const value = +counter.getAttribute('data-target');
                const data = +counter.innerText;
                const time = value / speed;

                if (data < value) {
                    counter.innerText = Math.ceil(data + time);
                    setTimeout(animate, 1);
                } else {
                    counter.innerText = value;
                }
            }

            // Start animation when element is in viewport
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animate();
                        observer.unobserve(entry.target);
                    }
                });
            });

            observer.observe(counter);
        });

        // Form submission - Skip admin, login, and enterprise admin forms
        const form = document.querySelector('form');
        if (form && !form.id.includes('adminLoginForm') && !form.classList.contains('admin-login-form') && !form.action.includes('/admin/login') && !form.action.includes('/admin/enterprise_dashboard')) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Thank you for your message! We will get back to you soon.');
                this.reset();
            });
        }
    </script>
</body>

</html>