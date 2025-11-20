<?php
/**
 * APS Dream Home - WhatsApp Message Templates System
 * Creates and manages reusable WhatsApp message templates
 */

require_once __DIR__ . '/config/config.php';

class WhatsAppTemplateManager {
    private $templates = [];
    private $template_dir;

    public function __construct() {
        $this->template_dir = __DIR__ . '/../templates/whatsapp/';
        $this->loadTemplates();
    }

    /**
     * Load all WhatsApp templates
     */
    private function loadTemplates() {
        if (!file_exists($this->template_dir)) {
            mkdir($this->template_dir, 0755, true);
        }

        $template_files = glob($this->template_dir . '*.json');
        foreach ($template_files as $file) {
            $template_data = json_decode(file_get_contents($file), true);
            if ($template_data) {
                $this->templates[basename($file, '.json')] = $template_data;
            }
        }
    }

    /**
     * Get all available templates
     */
    public function getAllTemplates() {
        return $this->templates;
    }

    /**
     * Get template by name
     */
    public function getTemplate($name) {
        return $this->templates[$name] ?? null;
    }

    /**
     * Create new template
     */
    public function createTemplate($name, $data) {
        $template = [
            'name' => $name,
            'category' => $data['category'] ?? 'general',
            'language' => $data['language'] ?? 'en',
            'header' => $data['header'] ?? '',
            'body' => $data['body'] ?? '',
            'footer' => $data['footer'] ?? '',
            'variables' => $data['variables'] ?? [],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->templates[$name] = $template;
        $this->saveTemplate($name, $template);

        return $template;
    }

    /**
     * Update existing template
     */
    public function updateTemplate($name, $data) {
        if (!isset($this->templates[$name])) {
            return false;
        }

        $template = $this->templates[$name];
        $template['category'] = $data['category'] ?? $template['category'];
        $template['language'] = $data['language'] ?? $template['language'];
        $template['header'] = $data['header'] ?? $template['header'];
        $template['body'] = $data['body'] ?? $template['body'];
        $template['footer'] = $data['footer'] ?? $template['footer'];
        $template['variables'] = $data['variables'] ?? $template['variables'];
        $template['updated_at'] = date('Y-m-d H:i:s');

        $this->templates[$name] = $template;
        $this->saveTemplate($name, $template);

        return $template;
    }

    /**
     * Delete template
     */
    public function deleteTemplate($name) {
        if (!isset($this->templates[$name])) {
            return false;
        }

        unset($this->templates[$name]);
        $template_file = $this->template_dir . $name . '.json';

        if (file_exists($template_file)) {
            unlink($template_file);
        }

        return true;
    }

    /**
     * Process template with variables
     */
    public function processTemplate($name, $variables = []) {
        $template = $this->getTemplate($name);
        if (!$template) {
            return false;
        }

        $message = '';

        // Add header if exists
        if (!empty($template['header'])) {
            $header = $this->replaceVariables($template['header'], $variables);
            $message .= $header . "\n\n";
        }

        // Add body (required)
        $body = $this->replaceVariables($template['body'], $variables);
        $message .= $body . "\n\n";

        // Add footer if exists
        if (!empty($template['footer'])) {
            $footer = $this->replaceVariables($template['footer'], $variables);
            $message .= $footer;
        }

        return trim($message);
    }

    /**
     * Replace variables in template
     */
    private function replaceVariables($text, $variables) {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        return $text;
    }

    /**
     * Save template to file
     */
    private function saveTemplate($name, $template) {
        $template_file = $this->template_dir . $name . '.json';
        file_put_contents($template_file, json_encode($template, JSON_PRETTY_PRINT));
    }

    /**
     * Get templates by category
     */
    public function getTemplatesByCategory($category) {
        $templates = [];
        foreach ($this->templates as $name => $template) {
            if ($template['category'] === $category) {
                $templates[$name] = $template;
            }
        }
        return $templates;
    }

    /**
     * Initialize default templates
     */
    public function initializeDefaultTemplates() {
        $default_templates = [
            'welcome_message' => [
                'category' => 'customer_service',
                'language' => 'en',
                'header' => '🎉 Welcome to APS Dream Home!',
                'body' => 'Hi {{customer_name}}! 👋

Thank you for choosing APS Dream Home for your property needs.

We\'re excited to help you find your perfect property! 🏠

Our team will be in touch with you shortly with personalized recommendations.',
                'footer' => '📞 Contact us anytime: {{phone_number}}
🌐 Visit: {{website_url}}',
                'variables' => ['customer_name', 'phone_number', 'website_url']
            ],

            'property_inquiry' => [
                'category' => 'property',
                'language' => 'en',
                'header' => '🏠 Property Inquiry Received',
                'body' => 'Hi {{customer_name}},

Thank you for your interest in our properties! 📋

We\'ve received your inquiry about:
📍 {{property_location}}
💰 Budget: {{budget_range}}
📏 Size: {{property_size}}

Our property expert will contact you within 24 hours with suitable options.',
                'footer' => 'Need immediate assistance? Call us: {{phone_number}}',
                'variables' => ['customer_name', 'property_location', 'budget_range', 'property_size', 'phone_number']
            ],

            'booking_confirmation' => [
                'category' => 'booking',
                'language' => 'en',
                'header' => '✅ Booking Confirmed!',
                'body' => 'Great news {{customer_name}}! 🎊

Your booking has been successfully confirmed:

🏠 Property: {{property_name}}
📅 Date: {{booking_date}}
⏰ Time: {{booking_time}}
📍 Location: {{property_location}}

Booking ID: {{booking_id}}

Our team will meet you at the scheduled time. Please bring valid ID proof.',
                'footer' => 'Questions? Contact: {{agent_name}} - {{agent_phone}}',
                'variables' => ['customer_name', 'property_name', 'booking_date', 'booking_time', 'property_location', 'booking_id', 'agent_name', 'agent_phone']
            ],

            'commission_alert' => [
                'category' => 'commission',
                'language' => 'en',
                'header' => '💰 Commission Earned!',
                'body' => 'Congratulations {{associate_name}}! 🎉

You\'ve earned a commission:

💵 Amount: ₹{{commission_amount}}
🏠 Property: {{property_name}}
👥 Customer: {{customer_name}}
📅 Date: {{transaction_date}}

Commission Status: {{status}}

Your commission will be processed within 7-10 business days.',
                'footer' => 'Thank you for your excellent service! 🌟',
                'variables' => ['associate_name', 'commission_amount', 'property_name', 'customer_name', 'transaction_date', 'status']
            ],

            'payment_reminder' => [
                'category' => 'payment',
                'language' => 'en',
                'header' => '💳 Payment Reminder',
                'body' => 'Hi {{customer_name}},

This is a friendly reminder about your pending payment:

💰 Amount: ₹{{amount}}
📅 Due Date: {{due_date}}
🔢 Invoice: {{invoice_number}}

Payment Methods:
• Online Transfer
• UPI
• Bank Deposit

Please make payment to avoid late fees.',
                'footer' => 'Need help? Contact our finance team: {{finance_phone}}',
                'variables' => ['customer_name', 'amount', 'due_date', 'invoice_number', 'finance_phone']
            ],

            'appointment_reminder' => [
                'category' => 'appointment',
                'language' => 'en',
                'header' => '📅 Appointment Reminder',
                'body' => 'Hi {{customer_name}},

Just a reminder about your upcoming appointment:

📅 Date: {{appointment_date}}
⏰ Time: {{appointment_time}}
📍 Location: {{appointment_location}}
👥 With: {{agent_name}}

Please arrive 10 minutes early. Bring:
• Valid ID proof
• Property documents (if any)

See you soon! 🏠',
                'footer' => 'Need to reschedule? Call: {{agent_phone}}',
                'variables' => ['customer_name', 'appointment_date', 'appointment_time', 'appointment_location', 'agent_name', 'agent_phone']
            ],

            'system_alert' => [
                'category' => 'system',
                'language' => 'en',
                'header' => '⚠️ System Alert',
                'body' => 'Hello {{admin_name}},

A system alert has been triggered:

🚨 Alert Type: {{alert_type}}
📊 Severity: {{severity}}
📅 Time: {{timestamp}}
📍 Source: {{source}}

Details: {{alert_details}}

Please check the system immediately.',
                'footer' => 'APS Dream Home Management System',
                'variables' => ['admin_name', 'alert_type', 'severity', 'timestamp', 'source', 'alert_details']
            ],

            'property_available' => [
                'category' => 'property',
                'language' => 'en',
                'header' => '🏠 New Property Available!',
                'body' => 'Hi {{customer_name}},

Great news! We have a new property that matches your preferences:

🏠 {{property_name}}
📍 {{property_location}}
💰 ₹{{property_price}}
📏 {{property_size}}

Key Features:
{{property_features}}

This property won\'t last long! Contact us today for a viewing.',
                'footer' => 'Call now: {{agent_phone}} | View online: {{property_url}}',
                'variables' => ['customer_name', 'property_name', 'property_location', 'property_price', 'property_size', 'property_features', 'agent_phone', 'property_url']
            ]
        ];

        foreach ($default_templates as $name => $template_data) {
            if (!$this->getTemplate($name)) {
                $this->createTemplate($name, $template_data);
            }
        }

        return count($default_templates);
    }
}

/**
 * Get WhatsApp template manager instance
 */
function getWhatsAppTemplateManager() {
    static $template_manager = null;
    if ($template_manager === null) {
        $template_manager = new WhatsAppTemplateManager();
    }
    return $template_manager;
}

/**
 * Process WhatsApp message using template
 */
function sendWhatsAppTemplateMessage($phone, $template_name, $variables = []) {
    try {
        $template_manager = getWhatsAppTemplateManager();
        $message = $template_manager->processTemplate($template_name, $variables);

        if ($message) {
            return sendWhatsAppMessage($phone, $message);
        } else {
            return ['success' => false, 'error' => 'Template not found or processing failed'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Get all WhatsApp templates
 */
function getWhatsAppTemplates() {
    try {
        $template_manager = getWhatsAppTemplateManager();
        return $template_manager->getAllTemplates();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Initialize default templates if not exists
 */
function initializeWhatsAppTemplates() {
    try {
        $template_manager = getWhatsAppTemplateManager();
        return $template_manager->initializeDefaultTemplates();
    } catch (Exception $e) {
        return 0;
    }
}
