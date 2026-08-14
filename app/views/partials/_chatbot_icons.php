<?php
// Chatbot + WhatsApp icons partial for standalone pages (auth, etc.)
// These are normally in base.php layout, but standalone pages need them too.
?>
<!-- AI Chatbot & WhatsApp icons -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/chatbot.min.css">

<div class="ai-chatbot-container" id="ai-chatbot">
    <button class="ai-float-btn" id="aiFloatBtn" onclick="toggleChat()">
        <i class="fas fa-comments"></i>
        <span class="ai-pulse"></span>
    </button>
</div>

<a href="https://wa.me/919277121112?text=Hi, I'm interested in APS Dream Home properties"
   target="_blank" class="whatsapp-float-btn" title="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<script>
window.chatbotApiUrl = '<?= defined('BASE_URL') ? BASE_URL : '' ?>/api/ai/chat';
window.chatbotUserContext = {
    role: '<?= isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_SESSION['user_id']) ? 'customer' : 'guest')) ?>',
    userId: '<?= $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? '' ?>',
    userName: '<?= addslashes($_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? '') ?>',
    isLoggedIn: <?= (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) ? 'true' : 'false' ?>
};
</script>
<script src="<?= BASE_URL ?>/assets/js/chatbot.js"></script>
