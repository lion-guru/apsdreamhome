-- Notification Templates Table
CREATE TABLE IF NOT EXISTS notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL,
    channel ENUM('email', 'sms', 'in_app', 'push') NOT NULL,
    title_template TEXT NOT NULL,
    body_template TEXT NOT NULL,
    variables JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Insert default EMI foreclosure notification templates
INSERT INTO notification_templates 
(type, channel, title_template, body_template, variables) 
VALUES 
(
    'emi_foreclosure_success', 
    'email', 
    'EMI Foreclosure Completed - APS Dream Home',
    'Dear {customer_name},

Your EMI plan for the property "{property_title}" has been successfully foreclosed.

Loan Details:
- Total Loan Amount: ₹{total_amount}
- Foreclosure Amount: ₹{foreclosure_amount}
- Remaining Balance: ₹{remaining_amount}

Thank you for your cooperation.

Best regards,
APS Dream Home Team',
    '["customer_name", "property_title", "total_amount", "foreclosure_amount", "remaining_amount"]'
),
(
    'emi_foreclosure_failed', 
    'email', 
    'EMI Foreclosure Attempt Failed - APS Dream Home',
    'Dear {customer_name},

We regret to inform you that the foreclosure attempt for your property "{property_title}" was unsuccessful.

Reason: {error_message}

Please contact our support team for further assistance.

Remaining Loan Amount: ₹{remaining_amount}

Best regards,
APS Dream Home Support',
    '["customer_name", "property_title", "error_message", "remaining_amount"]'
),
(
    'emi_foreclosure_admin_alert', 
    'email', 
    'EMI Foreclosure Event - {status}',
    'EMI Foreclosure {status} Alert

Customer: {customer_name}
Property: {property_title}
Loan Details:
- Total Amount: ₹{total_amount}
- Foreclosure Amount: ₹{foreclosure_amount}

Additional Details:
- Customer ID: {customer_id}
- Property ID: {property_id}
- Foreclosure Date: {foreclosure_date}

Please review and take necessary actions.',
    '["status", "customer_name", "property_title", "total_amount", "foreclosure_amount", "customer_id", "property_id", "foreclosure_date"]'
);

-- Create an index for performance
CREATE INDEX idx_notification_templates_type ON notification_templates(type, channel, status);
