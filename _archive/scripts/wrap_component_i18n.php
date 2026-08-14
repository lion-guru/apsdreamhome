Ã¯Â»Â¿<?php
/**
 * Wrap ALL 10 component view files with __() i18n calls.
 * Run: php scripts/wrap_component_i18n.php
 */
$root = dirname(__DIR__);
$componentsDir = $root . '/app/views/components';

// Load the translation helper so __() is available
require_once $root . '/app/Helpers/TranslationHelper.php';

function wrapFile(string $path, array $replacements): int {
    if (!file_exists($path)) { echo "SKIP (missing): $path\n"; return 0; }
    $content = file_get_contents($path);
    $original = $content;
    foreach ($replacements as $search => $replace) {
        if (strpos($content, $search) !== false && strpos($content, $replace) === false) {
            $content = str_replace($search, $replace, $content);
        }
    }
    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "WRAPPED: " . basename($path) . " (" . substr_count($content, '__(') . " calls)\n";
        return 1;
    }
    echo "NO-CHANGE: " . basename($path) . "\n";
    return 0;
}

// Use a helper that generates __() call text without evaluating it
function t(string $key, string $default): string {
    // Return the literal PHP code: __('key', 'default')
    // We escape single quotes in the default value
    $escaped = str_replace("'", "\\'", $default);
    return "__('" . $key . "', '" . $escaped . "')";
}

function th(string $key, string $default): string {
    // Return the literal PHP code with htmlspecialchars wrapping
    return "htmlspecialchars(__('" . $key . "', '" . str_replace("'", "\\'", $default) . "'))";
}

$count = 0;

// 1. mobile-dashboard-card.php
$count += wrapFile($componentsDir . '/mobile-dashboard-card.php', [
    "\$title = \$title ?? 'Card Title'" => "\$title = \$title ?? " . t('component_card_title', 'Card Title'),
    "\$action_text = \$action_text ?? 'View'" => "\$action_text = \$action_text ?? " . t('component_view', 'View'),
]);

// 2. gamification_widget.php
$count += wrapFile($componentsDir . '/gamification_widget.php', [
    "\$gTitle = \$gamify['title'] ?? 'Progress'" => "\$gTitle = \$gamify['title'] ?? " . t('component_progress', 'Progress'),
    "\$gLevel = \$gamify['level'] ?? 'Bronze'" => "\$gLevel = \$gamify['level'] ?? " . t('component_bronze', 'Bronze'),
    "\$gNextLabel = \$gamify['next_label'] ?? 'Next'" => "\$gNextLabel = \$gamify['next_label'] ?? " . t('component_next', 'Next'),
    "\$gCtaText = \$gamify['cta_text'] ?? 'Upgrade'" => "\$gCtaText = \$gamify['cta_text'] ?? " . t('component_upgrade', 'Upgrade'),
    "> more to reach <strong>" => "> " . t('component_more_to_reach', 'more to reach') . " <strong>",
    "> Reach <strong>" => "> " . t('component_reach', 'Reach') . " <strong>",
]);

// 3. push_subscribe_button.php
$count += wrapFile($componentsDir . '/push_subscribe_button.php', [
    'aria-label="Enable browser notifications"' => 'aria-label="' . th('component_enable_browser_notifications', 'Enable browser notifications') . '"',
    '<span class="push-subscribe-label">Enable Browser Notifications</span>' => '<span class="push-subscribe-label">' . t('component_enable_browser_notifications_btn', 'Enable Browser Notifications') . '</span>',
]);

// 4. save_search_modal.php
$count += wrapFile($componentsDir . '/save_search_modal.php', [
    '<i class="fas fa-bookmark me-2"></i>Save this search' => '<i class="fas fa-bookmark me-2"></i>' . t('component_save_this_search', 'Save this search'),
    'Save your current filters to access this search later and (optionally) receive email alerts when new properties match.' => t('component_save_filters_description', 'Save your current filters to access this search later and (optionally) receive email alerts when new properties match.'),
    '>Search name <span class="text-danger">*</span>' => '>' . t('component_search_name', 'Search name') . ' <span class="text-danger">*</span>',
    'placeholder="e.g. Plots in Gorakhpur under 20L"' => 'placeholder="' . th('component_search_name_placeholder', 'e.g. Plots in Gorakhpur under 20L') . '"',
    '<i class="fas fa-bell text-success me-1"></i>Send me email alerts when new properties match' => '<i class="fas fa-bell text-success me-1"></i>' . t('component_send_email_alerts', 'Send me email alerts when new properties match'),
    '>Description (optional)</label>' => '>' . t('component_description_optional', 'Description (optional)') . '</label>',
    'placeholder="Add notes about this search..."' => 'placeholder="' . th('component_add_notes_placeholder', 'Add notes about this search...') . '"',
    '<strong>Filters being saved:</strong>' => '<strong>' . t('component_filters_being_saved', 'Filters being saved:') . '</strong>',
    '>Cancel</button>' => '>' . t('component_cancel', 'Cancel') . '</button>',
    '>Save Search' => '>' . t('component_save_search', 'Save Search'),
]);

// 5. saved_search_dropdown.php
$count += wrapFile($componentsDir . '/saved_search_dropdown.php', [
    '<i class="fas fa-bookmark text-primary me-1"></i>My Saved:' => '<i class="fas fa-bookmark text-primary me-1"></i>' . t('component_my_saved', 'My Saved:'),
    'No saved searches. Apply some filters and click "Save Search".' => t('component_no_saved_searches', 'No saved searches. Apply some filters and click "Save Search".'),
    'Choose saved search' => t('component_choose_saved_search', 'Choose saved search'),
    'title="Email alerts enabled"' => 'title="' . th('component_email_alerts_enabled', 'Email alerts enabled') . '"',
    '<i class="fas fa-cog me-1"></i>Manage all saved searches' => '<i class="fas fa-cog me-1"></i>' . t('component_manage_all_saved_searches', 'Manage all saved searches'),
    '<i class="fas fa-list me-1"></i>View all' => '<i class="fas fa-list me-1"></i>' . t('component_view_all', 'View all'),
    '<i class="fas fa-bookmark me-1"></i>Save this search' => '<i class="fas fa-bookmark me-1"></i>' . t('component_save_this_search_btn', 'Save this search'),
]);

// 6. live_chat_widget.php
$count += wrapFile($componentsDir . '/live_chat_widget.php', [
    'aria-label="Open live chat"' => 'aria-label="' . th('component_open_live_chat', 'Open live chat') . '"',
    'id="lcw-header-title">APS Dream Home Support' => 'id="lcw-header-title">' . t('component_aps_support', 'APS Dream Home Support'),
    'We typically reply in a few minutes' => t('component_typically_reply', 'We typically reply in a few minutes'),
    'aria-label="Close chat"' => 'aria-label="' . th('component_close_chat', 'Close chat') . '"',
    '<h6>Hi! How can we help you today?</h6>' => '<h6>' . t('component_hi_how_can_help', 'Hi! How can we help you today?') . '</h6>',
    "Please share your details and we'll get back to you right away." => t('component_share_details', "Please share your details and we'll get back to you right away."),
    'placeholder="Your name *"' => 'placeholder="' . th('component_your_name', 'Your name *') . '"',
    'placeholder="Email *"' => 'placeholder="' . th('component_email_star', 'Email *') . '"',
    'placeholder="Phone (optional)"' => 'placeholder="' . th('component_phone_optional', 'Phone (optional)') . '"',
    'placeholder="How can we help? (optional)"' => 'placeholder="' . th('component_how_can_we_help', 'How can we help? (optional)') . '"',
    '<i class="fas fa-paper-plane me-1"></i> Start chat' => '<i class="fas fa-paper-plane me-1"></i> ' . t('component_start_chat', 'Start chat'),
    'placeholder="Type a message..."' => 'placeholder="' . th('component_type_a_message', 'Type a message...') . '"',
    'aria-label="Message"' => 'aria-label="' . th('component_message_label', 'Message') . '"',
    'aria-label="Send message"' => 'aria-label="' . th('component_send_message_label', 'Send message') . '"',
    '<i class="fas fa-shield-halved me-1"></i> Secure live chat' => '<i class="fas fa-shield-halved me-1"></i> ' . t('component_secure_live_chat', 'Secure live chat'),
]);

// 7. quick_register_modal.php
$count += wrapFile($componentsDir . '/quick_register_modal.php', [
    '<i class="fas fa-user-plus me-2 text-primary"></i>Quick Register' => '<i class="fas fa-user-plus me-2 text-primary"></i>' . t('component_quick_register', 'Quick Register'),
    'Join APS Dream Home in seconds! No password needed.' => t('component_join_aps_seconds', 'Join APS Dream Home in seconds! No password needed.'),
    '>Full Name *</label>' => '>' . t('component_full_name', 'Full Name') . ' *</label>',
    'placeholder="Enter your full name"' => 'placeholder="' . th('component_enter_full_name', 'Enter your full name') . '"',
    '>Email *</label>' => '>' . t('component_email', 'Email') . ' *</label>',
    'placeholder="Enter your email"' => 'placeholder="' . th('component_enter_email', 'Enter your email') . '"',
    '>Phone Number *</label>' => '>' . t('component_phone_number', 'Phone Number') . ' *</label>',
    'placeholder="Enter 10-digit phone number"' => 'placeholder="' . th('component_enter_phone_10', 'Enter 10-digit phone number') . '"',
    '>Referral Code (Optional)</label>' => '>' . t('component_referral_code_optional', 'Referral Code (Optional)') . '</label>',
    'placeholder="Enter referral code for 5% discount"' => 'placeholder="' . th('component_enter_referral_placeholder', 'Enter referral code for 5% discount') . '"',
    'Get referral code if you want to join as Associate/Agent' => t('component_get_referral_small', 'Get referral code if you want to join as Associate/Agent'),
    '<i class="fas fa-check-circle me-2"></i>Register Now' => '<i class="fas fa-check-circle me-2"></i>' . t('component_register_now', 'Register Now'),
    'Creating your account...' => t('component_creating_account', 'Creating your account...'),
    '<i class="fas fa-ticket-alt me-2 text-primary"></i>Request Referral Code' => '<i class="fas fa-ticket-alt me-2 text-primary"></i>' . t('component_request_referral_code', 'Request Referral Code'),
    'Get your company referral code to join as Associate/Agent!' => t('component_get_company_referral', 'Get your company referral code to join as Associate/Agent!'),
    'Processing your request...' => t('component_processing_request', 'Processing your request...'),
    '<i class="fas fa-check-circle me-2"></i>Referral Code Sent!' => '<i class="fas fa-check-circle me-2"></i>' . t('component_referral_code_sent', 'Referral Code Sent!'),
    'Your company referral code:' => t('component_your_referral_code', 'Your company referral code:'),
    'Use this code to join as Associate/Agent' => t('component_use_code_to_join', 'Use this code to join as Associate/Agent'),
    '<i class="fas fa-paper-plane me-2"></i>Request Code' => '<i class="fas fa-paper-plane me-2"></i>' . t('component_request_code_btn2', 'Request Code'),
]);

// 8. smart_chatbot.php
$count += wrapFile($componentsDir . '/smart_chatbot.php', [
    '<span class="chatbot-label">APS AI</span>' => '<span class="chatbot-label">' . t('component_aps_ai_label', 'APS AI') . '</span>',
    '<h5>APS AI Assistant</h5>' => '<h5>' . t('component_aps_ai_assistant', 'APS AI Assistant') . '</h5>',
    '<span class="status"><i class="fas fa-circle"></i> Online</span>' => '<span class="status"><i class="fas fa-circle"></i> ' . t('component_online', 'Online') . '</span>',
    'title="Clear Chat"' => 'title="' . th('component_clear_chat', 'Clear Chat') . '"',
    'title="Close"' => 'title="' . th('component_close', 'Close') . '"',
    'placeholder="Type message in Hindi or English..."' => 'placeholder="' . th('component_type_message_hindi_english', 'Type message in Hindi or English...') . '"',
]);

// 9. chatbot_widget.php
$count += wrapFile($componentsDir . '/chatbot_widget.php', [
    '<span class="chatbot-label">Ask AI</span>' => '<span class="chatbot-label">' . t('component_ask_ai', 'Ask AI') . '</span>',
    '<span>APS AI Assistant</span>' => '<span>' . t('component_aps_ai_assistant', 'APS AI Assistant') . '</span>',
    'title="Clear chat"' => 'title="' . th('component_clear_chat_lower', 'Clear chat') . '"',
    '<span class="powered-by">Powered by Gemini AI</span>' => '<span class="powered-by">' . t('component_powered_by_gemini', 'Powered by Gemini AI') . '</span>',
]);

// 10. mobile-header.php
$count += wrapFile($componentsDir . '/mobile-header.php', [
    'alt="APS Dream Home" class="style-2609"' => 'alt="' . th('aps_dream_home', 'APS Dream Home') . '" class="style-2609"',
    '<span class="text-lg font-bold text-primary">APS Dream Home</span>' => '<span class="text-lg font-bold text-primary">' . t('aps_dream_home', 'APS Dream Home') . '</span>',
    '>Profile</a>' => '>' . t('component_profile', 'Profile') . '</a>',
    '>Login</a>' => '>' . t('component_login', 'Login') . '</a>',
    '>Register</a>' => '>' . t('component_register_btn', 'Register') . '</a>',
    '>Home</a>' => '>' . t('component_home', 'Home') . '</a>',
    '>My Team</a>' => '>' . t('component_my_team', 'My Team') . '</a>',
    '>Commissions</a>' => '>' . t('component_commissions', 'Commissions') . '</a>',
    '>Properties</a>' => '>' . t('component_properties', 'Properties') . '</a>',
    '>Leads</a>' => '>' . t('component_leads', 'Leads') . '</a>',
    '>My Inquiries</a>' => '>' . t('component_my_inquiries', 'My Inquiries') . '</a>',
    '>Tasks</a>' => '>' . t('component_tasks', 'Tasks') . '</a>',
    '>Attendance</a>' => '>' . t('component_attendance', 'Attendance') . '</a>',
    '>About</a>' => '>' . t('component_about', 'About') . '</a>',
    '>Contact</a>' => '>' . t('component_contact', 'Contact') . '</a>',
    '>My Leads</a>' => '>' . t('component_my_leads', 'My Leads') . '</a>',
    '>Browse Properties</a>' => '>' . t('component_browse_properties', 'Browse Properties') . '</a>',
    '>Profile Settings</a>' => '>' . t('component_profile_settings', 'Profile Settings') . '</a>',
    '>Payroll</a>' => '>' . t('component_payroll', 'Payroll') . '</a>',
]);

echo "\n=== Component i18n wrapping complete: $count files modified ===\n";?>