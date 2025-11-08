<?php
/**
 * Email Template Manager
 * Professional email marketing system with templates
 */

class EmailTemplateManager {
    private $conn;
    private $logger;

    // Cached templates
    private $template_cache = [];

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;

        // Create email templates table if it doesn't exist
        $this->createEmailTemplatesTable();
    }

    /**
     * Create email templates table
     */
    private function createEmailTemplatesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS email_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            subject VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            variables JSON,
            category VARCHAR(50) DEFAULT 'general',
            active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        $this->conn->query($sql);

        // Insert default templates if table is empty
        $this->insertDefaultTemplates();
    }

    /**
     * Insert default email templates
     */
    private function insertDefaultTemplates() {
        $checkSql = "SELECT COUNT(*) as count FROM email_templates";
        $result = $this->conn->query($checkSql);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            $defaultTemplates = [
                [
                    'name' => 'welcome_user',
                    'subject' => 'Welcome to APS Dream Home!',
                    'body' => 'Dear {{name}},

Welcome to APS Dream Home! We are excited to help you find your dream property.

Your account has been successfully created. You can now:
- Browse properties
- Save your favorite properties
- Contact property agents
- Get property recommendations

If you have any questions, feel free to contact our support team.

Best regards,
APS Dream Home Team',
                    'variables' => '["name"]',
                    'category' => 'user_management'
                ],
                [
                    'name' => 'property_inquiry',
                    'subject' => 'New Property Inquiry - {{property_title}}',
                    'body' => 'Dear Agent,

You have received a new inquiry for the property: {{property_title}}

Inquiry Details:
- Customer Name: {{customer_name}}
- Email: {{customer_email}}
- Phone: {{customer_phone}}
- Message: {{message}}

Property Details:
- Location: {{property_location}}
- Price: {{property_price}}
- Type: {{property_type}}

Please contact the customer as soon as possible to assist them.

Best regards,
APS Dream Home System',
                    'variables' => '["property_title", "customer_name", "customer_email", "customer_phone", "message", "property_location", "property_price", "property_type"]',
                    'category' => 'property'
                ],
                [
                    'name' => 'password_reset',
                    'subject' => 'Password Reset Request - APS Dream Home',
                    'body' => 'Dear {{name}},

You have requested to reset your password for your APS Dream Home account.

Click the following link to reset your password:
{{reset_link}}

This link will expire in 24 hours for security reasons.

If you did not request this password reset, please ignore this email.

Best regards,
APS Dream Home Team',
                    'variables' => '["name", "reset_link"]',
                    'category' => 'security'
                ]
            ];

            foreach ($defaultTemplates as $template) {
                $sql = "INSERT INTO email_templates (name, subject, body, variables, category, active)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssssi",
                    $template['name'],
                    $template['subject'],
                    $template['body'],
                    $template['variables'],
                    $template['category'],
                    1 // active
                );
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    /**
     * Get email template by name
     */
    public function getTemplate($template_name) {
        // Check cache first
        if (isset($this->template_cache[$template_name])) {
            return $this->template_cache[$template_name];
        }

        $sql = "SELECT id, name, subject, body, variables, category
                FROM email_templates
                WHERE name = ? AND active = 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $template_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $template = $result->fetch_assoc();
        $stmt->close();

        // Cache the template
        $this->template_cache[$template_name] = $template;

        return $template;
    }

    /**
     * Process template with variables
     */
    public function processTemplate($template_name, $variables = []) {
        $template = $this->getTemplate($template_name);

        if (!$template) {
            return null;
        }

        $subject = $template['subject'];
        $body = $template['body'];

        // Replace variables in subject and body
        foreach ($variables as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
            'template_id' => $template['id']
        ];
    }

    /**
     * Send email using template
     */
    public function sendTemplateEmail($template_name, $variables, $to_email, $to_name = '') {
        $processed = $this->processTemplate($template_name, $variables);

        if (!$processed) {
            return false;
        }

        // Here you would integrate with your email service
        // For now, we'll simulate the email sending
        $email_data = [
            'to' => $to_email,
            'to_name' => $to_name,
            'subject' => $processed['subject'],
            'body' => $processed['body'],
            'template_id' => $processed['template_id'],
            'sent_at' => date('Y-m-d H:i:s')
        ];

        // Log the email attempt
        if ($this->logger) {
            $this->logger->log("Email sent to: $to_email, Template: $template_name", 'info', 'email');
        }

        // In a real implementation, you would use PHPMailer or similar
        // For now, we'll just return success
        return true;
    }

    /**
     * Get all available templates
     */
    public function getAllTemplates() {
        $sql = "SELECT id, name, subject, category, active, created_at
                FROM email_templates ORDER BY category, name";

        $result = $this->conn->query($sql);

        $templates = [];
        while ($row = $result->fetch_assoc()) {
            $templates[] = $row;
        }

        return $templates;
    }

    /**
     * Create new template
     */
    public function createTemplate($name, $subject, $body, $variables = [], $category = 'general') {
        $variables_json = json_encode($variables);

        $sql = "INSERT INTO email_templates (name, subject, body, variables, category, active)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $active = 1;
        $stmt->bind_param("sssssi", $name, $subject, $body, $variables_json, $category, $active);

        $result = $stmt->execute();
        $stmt->close();

        // Clear cache
        unset($this->template_cache[$name]);

        return $result;
    }

    /**
     * Update template
     */
    public function updateTemplate($name, $subject, $body, $variables = [], $category = 'general') {
        $variables_json = json_encode($variables);

        $sql = "UPDATE email_templates
                SET subject = ?, body = ?, variables = ?, category = ?
                WHERE name = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $subject, $body, $variables_json, $category, $name);

        $result = $stmt->execute();
        $stmt->close();

        // Clear cache
        unset($this->template_cache[$name]);

        return $result;
    }
}
?>
