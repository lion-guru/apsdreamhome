<?php
/**
 * Public Live Chat Widget (Visitor Side)
 *
 * Floating chat button + chat window. Use fetch() in live-chat-widget.js
 * to talk to /api/chat/{start,send,poll,widget}.
 *
 * Include in header.php (or any layout) once per page.
 */
if (defined('LIVE_CHAT_WIDGET_LOADED')) {
    return;
}
define('LIVE_CHAT_WIDGET_LOADED', true);
?>
<!-- Live Chat Widget -->
<div id="lcw-root" class="lcw-root lcw-position-br" aria-live="polite">
    <!-- Floating launcher button -->
    <button id="lcw-launcher" class="lcw-launcher" type="button" aria-label="<?= htmlspecialchars(__('component_open_live_chat', 'Open live chat')) ?>">
        <i class="fas fa-comments lcw-launcher-icon" aria-hidden="true"></i>
        <span class="lcw-launcher-close" aria-hidden="true">
            <i class="fas fa-times"></i>
        </span>
        <span id="lcw-launcher-badge" class="lcw-launcher-badge" hidden>0</span>
    </button>

    <!-- Chat window -->
    <div id="lcw-window" class="lcw-window" hidden role="dialog" aria-modal="false" aria-labelledby="lcw-title">
        <header class="lcw-header" id="lcw-title">
            <div class="lcw-header-info">
                <div class="lcw-avatar"><i class="fas fa-headset"></i></div>
                <div>
                    <div class="lcw-title" id="lcw-header-title"><?= __('component_aps_support', 'APS Dream Home Support') ?></div>
                    <div class="lcw-subtitle" id="lcw-header-subtitle">
                        <span class="lcw-dot lcw-dot-online"></span> <?= __('component_typically_reply', 'We typically reply in a few minutes') ?>
                    </div>
                </div>
            </div>
            <button id="lcw-close" class="lcw-close" type="button" aria-label="<?= htmlspecialchars(__('component_close_chat', 'Close chat')) ?>">
                <i class="fas fa-times"></i>
            </button>
        </header>

        <div id="lcw-messages" class="lcw-messages" role="log" aria-live="polite">
            <!-- Pre-chat form (shown when no session) -->
            <div id="lcw-prechat" class="lcw-prechat">
                <div class="lcw-welcome">
                    <i class="fas fa-comments"></i>
                    <h6><?= __('component_hi_how_can_help', 'Hi! How can we help you today?') ?></h6>
                    <p class="text-muted small mb-0"><?= __('component_share_details', 'Please share your details and we\'ll get back to you right away.') ?></p>
                </div>
                <form id="lcw-prechat-form" class="lcw-prechat-form" novalidate>
    <?php echo CSRFProtection::csrfField(); ?>
                    <div class="mb-2">
                        <input type="text" class="form-control form-control-sm" id="lcw-name" name="name"
                               placeholder="<?= htmlspecialchars(__('component_your_name', 'Your name *')) ?>" maxlength="80" required>
                    </div>
                    <div class="mb-2">
                        <input type="email" class="form-control form-control-sm" id="lcw-email" name="email"
                               placeholder="<?= htmlspecialchars(__('component_email_star', 'Email *')) ?>" maxlength="120" required>
                    </div>
                    <div class="mb-2">
                        <input type="tel" class="form-control form-control-sm" id="lcw-phone" name="phone"
                               placeholder="<?= htmlspecialchars(__('component_phone_optional', 'Phone (optional)')) ?>" maxlength="20">
                    </div>
                    <div class="mb-2">
                        <textarea class="form-control form-control-sm" id="lcw-first-message" name="message"
                                  rows="2" placeholder="<?= htmlspecialchars(__('component_how_can_we_help', 'How can we help? (optional)')) ?>" maxlength="500"></textarea>
                    </div>
                    <div id="lcw-prechat-error" class="alert alert-danger py-1 px-2 small d-none" role="alert"></div>
                    <button type="submit" id="lcw-start-btn" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-paper-plane me-1"></i> <?= __('component_start_chat', 'Start chat') ?>
                    </button>
                </form>
            </div>

            <!-- Active chat state (shown after session) -->
            <div id="lcw-thread" class="lcw-thread" hidden>
                <div id="lcw-msgs-list" class="lcw-msgs-list"></div>
                <div id="lcw-typing" class="lcw-typing" hidden>
                    <span class="lcw-typing-dot"></span>
                    <span class="lcw-typing-dot"></span>
                    <span class="lcw-typing-dot"></span>
                </div>
            </div>
        </div>

        <footer class="lcw-input-bar" id="lcw-input-bar" hidden>
            <textarea id="lcw-input" class="lcw-input" rows="1"
                      placeholder="<?= htmlspecialchars(__('component_type_a_message', 'Type a message...')) ?>" maxlength="1000"
                      aria-label="<?= htmlspecialchars(__('component_message_label', 'Message')) ?>"></textarea>
            <button id="lcw-send" class="lcw-send" type="button" aria-label="<?= htmlspecialchars(__('component_send_message_label', 'Send message')) ?>" disabled>
                <i class="fas fa-paper-plane"></i>
            </button>
        </footer>

        <div class="lcw-powered">
            <i class="fas fa-shield-halved me-1"></i> <?= __('component_secure_live_chat', 'Secure live chat') ?>
        </div>
    </div>
</div>
