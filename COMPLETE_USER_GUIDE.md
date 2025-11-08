# APS Dream Home - Complete User Guide 📚

## 🎯 Overview

APS Dream Home is a comprehensive real estate management system with advanced AI integration, WhatsApp messaging, and email notifications. This guide will help you understand and use all the features effectively.

## 🚀 Quick Start

### 1. Access the Management Dashboard
```
http://localhost/apsdreamhomefinal/management_dashboard.php
```

### 2. Test System Components
```
http://localhost/apsdreamhomefinal/comprehensive_system_test.php
```

### 3. Manage WhatsApp Templates
```
http://localhost/apsdreamhomefinal/whatsapp_template_manager.php
```

---

## 📋 Table of Contents

1. [System Architecture](#system-architecture)
2. [WhatsApp Integration](#whatsapp-integration)
3. [AI Features](#ai-features)
4. [Email System](#email-system)
5. [Management Dashboard](#management-dashboard)
6. [Template System](#template-system)
7. [API Endpoints](#api-endpoints)
8. [Troubleshooting](#troubleshooting)

---

## 🏗️ System Architecture

### Directory Structure
```
/ (Root)
├── management_dashboard.php      # Main control panel
├── comprehensive_system_test.php # System testing
├── whatsapp_template_manager.php # Template management
└── basic_system_test.php         # Quick verification

/api/ (API Endpoints)
├── whatsapp_webhook.php          # WhatsApp webhook handler
├── manage_whatsapp_templates.php # Template CRUD operations
├── test_whatsapp.php             # WhatsApp testing
├── test_email.php                # Email testing
└── get_system_logs.php           # System logs

/includes/ (Core Systems)
├── config.php                    # System configuration
├── whatsapp_integration.php      # WhatsApp integration class
├── whatsapp_templates.php        # Template management
├── ai_integration.php            # AI system
└── email_system.php              # Enhanced email system

/assets/ (Frontend Assets)
└── js/ai_client.js               # AI JavaScript client
```

---

## 📱 WhatsApp Integration

### Configuration
Your WhatsApp integration is configured with:
- **Phone Number:** `9277121112`
- **Country Code:** `91` (India)
- **API Provider:** WhatsApp Business API

### Available Features

#### 1. Message Types
- ✅ **Welcome Messages** - Automated customer onboarding
- ✅ **Property Inquiries** - Real-time inquiry notifications
- ✅ **Booking Confirmations** - Instant booking confirmations
- ✅ **Commission Alerts** - Associate commission notifications
- ✅ **Payment Reminders** - Automated payment reminders
- ✅ **Appointment Reminders** - Meeting and visit reminders
- ✅ **System Alerts** - Important system notifications

#### 2. API Providers Supported
1. **WhatsApp Business API** (Recommended)
   - Official WhatsApp Business solution
   - Rich message support
   - High delivery rates

2. **Twilio WhatsApp API**
   - Easy integration
   - Good delivery rates
   - Pay-per-message pricing

3. **WhatsApp Web** (Fallback)
   - No setup required
   - Opens WhatsApp Web interface

### Using WhatsApp Templates

#### Basic Template Usage
```php
// Send welcome message using template
$result = sendWhatsAppTemplateMessage('9876543210', 'welcome_message', [
    'customer_name' => 'John Doe',
    'phone_number' => '9277121112',
    'website_url' => 'www.apsdreamhome.com'
]);
```

#### Available Templates
1. **welcome_message** - Customer onboarding
2. **property_inquiry** - Property inquiry notifications
3. **booking_confirmation** - Booking confirmations
4. **commission_alert** - Commission notifications
5. **payment_reminder** - Payment reminders
6. **appointment_reminder** - Meeting reminders
7. **system_alert** - System notifications
8. **property_available** - New property alerts

### Webhook Setup
For WhatsApp Business API, set up webhook URL:
```
http://yourdomain.com/api/whatsapp_webhook.php
```

Webhook will handle:
- Incoming text messages
- Image messages
- Document messages
- Auto-responses based on keywords

---

## 🤖 AI Features

### AI Integration
- **Model:** Qwen3-Coder (Advanced code analysis)
- **Provider:** OpenRouter API
- **API Key:** Pre-configured

### Available AI Features

#### 1. Code Analysis
```javascript
// Analyze PHP code
await analyzeCode('PHP', `<?php
function test() {
    return "hello";
}
?>`);
```

#### 2. Code Generation
```javascript
// Generate PHP function
await generateCode('PHP', 'Create a function to calculate EMI');
```

#### 3. Chatbot Integration
The AI chatbot is integrated into the management dashboard and can help with:
- Property recommendations
- Code debugging
- System guidance
- Customer support

### AI Learning System
- Tracks user interactions
- Improves responses over time
- Stores successful patterns
- Analytics and performance metrics

---

## 📧 Email System

### Enhanced Features
- **Dual Notifications:** Email + WhatsApp together
- **Professional Templates:** HTML email templates
- **SMTP Integration:** Pre-configured SMTP settings
- **Logging:** Complete email activity tracking

### Email Types
1. **Welcome Emails** - New customer onboarding
2. **Property Notifications** - Property-related updates
3. **Booking Confirmations** - Booking and appointment confirmations
4. **Commission Notifications** - Associate commission alerts
5. **System Notifications** - Admin and system alerts

### Usage Example
```php
// Send dual notification (email + WhatsApp)
$email_data = [
    'to' => 'customer@example.com',
    'subject' => 'Booking Confirmed',
    'body' => 'Your booking is confirmed...',
    'template' => 'booking'
];

$whatsapp_data = [
    'phone' => '9876543210',
    'message' => '✅ Booking confirmed!'
];

$result = $emailSystem->sendDualNotification($email_data, $whatsapp_data);
```

---

## 🎛️ Management Dashboard

### Dashboard Features
Access at: `http://localhost/apsdreamhomefinal/management_dashboard.php`

#### 1. System Status
- Real-time status of all integrations
- Performance metrics
- Quick health checks

#### 2. Configuration Management
- AI settings management
- WhatsApp configuration
- Email settings

#### 3. Testing Interface
- Test AI responses
- Send WhatsApp messages
- Send test emails

#### 4. System Logs
- WhatsApp activity logs
- Email delivery logs
- AI interaction logs

#### 5. WhatsApp Templates
- Overview of available templates
- Quick template testing
- Template management access

### Dashboard Tabs
1. **Overview** - System status and metrics
2. **AI Management** - AI configuration and testing
3. **WhatsApp Management** - WhatsApp settings and testing
4. **Email Management** - Email configuration and testing
5. **System Logs** - Activity logs and monitoring
6. **WhatsApp Templates** - Template overview and management

---

## 📝 Template System

### WhatsApp Template Manager
Access at: `http://localhost/apsdreamhomefinal/whatsapp_template_manager.php`

### Creating Templates

#### Template Structure
```php
$template = [
    'name' => 'welcome_message',
    'category' => 'customer_service',
    'language' => 'en',
    'header' => '🎉 Welcome to APS Dream Home!',
    'body' => 'Hi {{customer_name}}! 👋

Thank you for choosing APS Dream Home for your property needs.

We\'re excited to help you find your perfect property! 🏠',
    'footer' => '📞 Contact us: {{phone_number}}
🌐 Website: {{website_url}}',
    'variables' => ['customer_name', 'phone_number', 'website_url']
];
```

#### Variable System
- Use `{{variable_name}}` for dynamic content
- Variables are auto-detected when creating templates
- Sample values provided for testing

### Template Categories
1. **customer_service** - Welcome messages, support
2. **property** - Property inquiries, listings
3. **booking** - Booking confirmations, appointments
4. **commission** - Commission alerts, earnings
5. **payment** - Payment reminders, invoices
6. **appointment** - Meeting reminders, schedules
7. **system** - Admin notifications, alerts
8. **general** - General purpose templates

### Template Management
- ✅ **Create** new templates with variables
- ✅ **Edit** existing templates
- ✅ **Delete** unused templates
- ✅ **Test** templates with sample data
- ✅ **Preview** messages before sending

---

## 🔌 API Endpoints

### WhatsApp APIs
```http
POST /api/test_whatsapp.php
Content-Type: application/json
{
  "phone": "9876543210",
  "message": "Hello World"
}
```

```http
POST /api/manage_whatsapp_templates.php
Content-Type: application/json
{
  "name": "new_template",
  "category": "general",
  "header": "Header text",
  "body": "Body with {{variables}}",
  "footer": "Footer text"
}
```

### Email APIs
```http
POST /api/test_email.php
Content-Type: application/json
{
  "email": "test@example.com"
}
```

### System APIs
```http
GET /api/get_system_logs.php
// Returns recent system logs
```

```http
GET /api/get_template_preview.php?name=welcome_message
// Returns template preview with sample data
```

---

## 🔧 Configuration

### WhatsApp Configuration (`config.php`)
```php
$config['whatsapp'] = [
    'enabled' => true,
    'phone_number' => '9277121112',
    'country_code' => '91',
    'api_provider' => 'whatsapp_business_api',
    'notification_types' => [
        'welcome_message' => true,
        'property_inquiry' => true,
        'booking_confirmation' => true,
        // ... more notification types
    ],
    'auto_responses' => [
        'greeting_hours' => '09:00-18:00',
        'welcome_message' => 'Welcome to APS Dream Home!',
        'away_message' => 'We\'ll respond within 24 hours.'
    ]
];
```

### AI Configuration
```php
$config['ai'] = [
    'enabled' => true,
    'provider' => 'openrouter',
    'api_key' => 'your-api-key',
    'model' => 'qwen/qwen3-coder:free',
    'features' => [
        'property_descriptions' => true,
        'chatbot' => true,
        'code_analysis' => true,
        'development_assistance' => true
    ]
];
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. WhatsApp Messages Not Sending
- Check API provider configuration
- Verify phone number format (+CountryCodeNumber)
- Check API credentials (for Business API/Twilio)
- Review system logs for error messages

#### 2. AI Responses Not Working
- Verify OpenRouter API key
- Check internet connection
- Review AI integration logs
- Test with simple prompts

#### 3. Email Delivery Issues
- Check SMTP configuration
- Verify email credentials
- Check spam/junk folders
- Review email logs

#### 4. Webhook Not Receiving Messages
- Verify webhook URL is accessible
- Check WhatsApp Business API configuration
- Review webhook logs
- Ensure proper token verification

### Debug Mode
Enable debug mode in `config.php`:
```php
// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
```

### Log Files Location
- WhatsApp logs: `/logs/whatsapp.log`
- Email logs: `/logs/email.log`
- AI logs: `/logs/ai_interactions.log`
- System logs: `/logs/system.log`

---

## 📞 Support & Contact

For technical support or questions:
- **Phone:** 9277121112
- **Email:** apsdreamhomes44@gmail.com
- **System Dashboard:** `http://localhost/apsdreamhomefinal/management_dashboard.php`

### Getting Help
1. Check the troubleshooting section above
2. Review system logs in the management dashboard
3. Test individual components using the test interfaces
4. Contact support with specific error messages and screenshots

---

## 🚀 Advanced Features

### Webhook Integration
Set up WhatsApp Business API webhook for incoming messages:
```php
// In WhatsApp Business API Dashboard
Webhook URL: https://yourdomain.com/api/whatsapp_webhook.php
Verify Token: aps_dream_home_webhook_token
```

### Custom Templates
Create custom message templates for your specific business needs:
```php
// Example: Custom property alert template
$custom_template = [
    'name' => 'luxury_property_alert',
    'category' => 'property',
    'body' => '🏰 Luxury property available: {{property_name}} at {{location}} for ₹{{price}}',
    'variables' => ['property_name', 'location', 'price']
];
```

### AI Customization
Customize AI responses for your business:
```javascript
// Custom AI prompts in ai_client.js
const customPrompts = {
    property_search: "Help customer find properties in {location} within budget {budget}",
    price_analysis: "Analyze property prices in {area} and provide market insights"
};
```

---

## 📈 Performance & Monitoring

### System Performance
- PHP execution time monitoring
- Database query optimization
- API response time tracking
- Memory usage monitoring

### WhatsApp Analytics
- Message delivery rates
- Template usage statistics
- Response time tracking
- Provider performance comparison

### AI Performance
- Response accuracy tracking
- Learning progress monitoring
- Token usage optimization
- Feature usage analytics

---

## 🔒 Security Considerations

### Best Practices
1. **API Keys:** Keep API keys secure and rotate regularly
2. **Webhook Tokens:** Use strong, unique tokens for webhooks
3. **Input Validation:** Validate all user inputs and API data
4. **HTTPS:** Use HTTPS for production deployments
5. **Access Control:** Implement proper authentication for admin areas

### Data Protection
- Customer data encryption
- Secure API communication
- Regular security audits
- Backup and recovery procedures

---

## 🎯 Next Steps

1. **Test all features** using the management dashboard
2. **Customize templates** for your specific business needs
3. **Set up WhatsApp Business API** for production use
4. **Configure monitoring** and alerting systems
5. **Train staff** on using the system effectively
6. **Set up backups** for data protection

---

## 📚 Additional Resources

- [WhatsApp Business API Documentation](https://developers.facebook.com/docs/whatsapp/)
- [OpenRouter AI API](https://openrouter.ai/docs)
- [PHPMailer Documentation](https://github.com/PHPMailer/PHPMailer)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.1/)

---

**Congratulations! 🎉** Your APS Dream Home system is now fully operational with advanced AI, WhatsApp integration, and comprehensive management tools.

For any questions or support, use the management dashboard or contact our support team.
