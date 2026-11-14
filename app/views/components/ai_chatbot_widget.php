<?php
/**
 * AI Chatbot Widget Component
 * Include this in any view to add the AI chatbot floating button
 */

$config = [
    'apiBaseUrl' => BASE_URL . '/api/smart-ai/chat',
    'position' => 'bottom-right',
    'primaryColor' => '#0B194C',
    'secondaryColor' => '#F58220',
    'accentColor' => '#E5A93C',
    'welcomeMessage' => 'Hello! I\'m your APS Dream Home assistant. Ask me about plots, bookings, EMI, site visits, or anything else!',
    'placeholder' => 'Ask about plots, bookings, EMI...',
    'showPoweredBy' => true,
    'autoOpen' => false,
    'persistSession' => true,
];

$configJson = json_encode($config);
?>

<!-- AI Chatbot Widget Configuration -->
<script>
    window.APS_AI_CHATBOT_CONFIG = <?= $configJson ?>;
</script>

<!-- AI Chatbot Widget Script -->
<script src="<?= BASE_URL ?>/assets/js/ai-chatbot-widget.js" defer></script>

<!-- Widget Styles -->
<style>
/* Animation keyframes */
@keyframes aps-slide-up {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes aps-fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes aps-typing-bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-4px); }
}

/* Chat Button */
.aps-chat-button {
    position: relative;
}
.aps-chat-button:hover {
    transform: scale(1.08) !important;
    box-shadow: 0 12px 40px rgba(11, 25, 76, 0.5) !important;
}
.aps-chat-button:active {
    transform: scale(0.95) !important;
}

.aps-notification-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 20px;
    height: 20px;
    background: #EF4444;
    color: white;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
    border: 2px solid white;
    animation: aps-pulse 2s infinite;
}

@keyframes aps-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Header */
.aps-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.aps-header-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.aps-header-title {
    font-size: 15px;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.aps-header-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    opacity: 0.9;
}

.aps-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10B981;
    animation: aps-pulse 2s infinite;
}

.aps-status-dot.offline {
    background: #EF4444;
}

.aps-header-actions {
    display: flex;
    gap: 4px;
}

.aps-icon-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.15);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.2s ease;
}

.aps-icon-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(1.05);
}

.aps-icon-btn:active {
    transform: scale(0.95);
}

/* Messages */
.aps-message {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    animation: aps-fade-in 0.3s ease-out;
}

.aps-message.user {
    flex-direction: row-reverse;
}

.aps-message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #64748B;
    background: #F1F5F9;
}

.aps-message-avatar.user {
    background: linear-gradient(135deg, #F58220 0%, #E5A93C 100%);
    color: white;
}

.aps-message-bubble {
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-width: 85%;
    word-wrap: break-word;
}

.aps-message-bubble.bot {
    background: white;
    border-radius: 18px 18px 18px 4px;
    padding: 12px 16px;
    box-shadow: 0 2px 8px rgba(11, 25, 76, 0.08);
    border: 1px solid rgba(11, 25, 76, 0.06);
}

.aps-message-bubble.user {
    background: linear-gradient(135deg, #0B194C 0%, #1E3A8A 100%);
    border-radius: 18px 18px 4px 18px;
    padding: 12px 16px;
    color: white;
}

.aps-message-content {
    font-size: 14px;
    line-height: 1.6;
    color: #1E293B;
}

.aps-message-bubble.user .aps-message-content {
    color: white;
}

.aps-message-time {
    font-size: 10px;
    color: #94A3B8;
    align-self: flex-end;
    margin-top: 2px;
}

.aps-message-bubble.user .aps-message-time {
    color: rgba(255, 255, 255, 0.6);
}

/* Typing Indicator */
.aps-typing .aps-message-bubble {
    background: #F1F5F9;
    border: 1px solid rgba(11, 25, 76, 0.06);
    padding: 12px 16px;
}

.aps-typing-bubble {
    display: flex;
    align-items: center;
    gap: 4px;
}

.aps-typing-dots {
    display: flex;
    gap: 4px;
}

.aps-typing-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #64748B;
    animation: aps-typing-bounce 1.4s infinite ease-in-out;
}

.aps-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.aps-typing-dots span:nth-child(3) { animation-delay: 0.4s; }

/* Quick Replies */
.aps-quick-reply {
    padding: 8px 14px;
    background: white;
    border: 1px solid rgba(11, 25, 76, 0.12);
    border-radius: 20px;
    font-size: 12px;
    color: #1E3A8A;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
    font-weight: 500;
}

.aps-quick-reply:hover {
    background: linear-gradient(135deg, #0B194C 0%, #1E3A8A 100%);
    color: white;
    border-color: transparent;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(11, 25, 76, 0.3);
}

.aps-quick-reply:active {
    transform: scale(0.98);
}

/* Input Area */
.aps-chat-input {
    font-family: inherit;
}

.aps-chat-input:focus {
    border-color: #F58220 !important;
    box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.15);
    background: white;
}

.aps-send-btn:hover {
    transform: scale(1.05) rotate(5deg);
    box-shadow: 0 6px 20px rgba(245, 130, 32, 0.5);
}

.aps-send-btn:active {
    transform: scale(0.95) rotate(5deg);
}

.aps-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Scrollbar */
.aps-messages-container::-webkit-scrollbar {
    width: 6px;
}

.aps-messages-container::-webkit-scrollbar-track {
    background: transparent;
}

.aps-messages-container::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 3px;
}

.aps-messages-container::-webkit-scrollbar-thumb:hover {
    background: #94A3B8;
}

/* Responsive */
@media (max-width: 480px) {
    .aps-chat-panel {
        width: 100% !important;
        height: 100% !important;
        max-height: 100vh !important;
        bottom: 0 !important;
        right: 0 !important;
        left: 0 !important;
        border-radius: 20px 20px 0 0 !important;
        animation: aps-slide-up 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .aps-chat-button {
        bottom: 24px !important;
        right: 24px !important;
    }
}
</style>