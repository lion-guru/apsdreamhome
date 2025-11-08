<?php
/**
 * APS Dream Home - WhatsApp Template Preview API
 * API for getting template preview and variable information
 */

require_once '../includes/config.php';
require_once '../includes/whatsapp_templates.php';

header('Content-Type: application/json');

// Get template name from query parameter
$name = $_GET['name'] ?? '';

if (empty($name)) {
    echo json_encode(['error' => 'Template name is required']);
    exit;
}

try {
    $template_manager = getWhatsAppTemplateManager();
    $template = $template_manager->getTemplate($name);

    if (!$template) {
        echo json_encode(['error' => 'Template not found']);
        exit;
    }

    // Generate preview with sample data
    $sample_variables = [];
    foreach ($template['variables'] as $variable) {
        $sample_variables[$variable] = getSampleValue($variable);
    }

    $preview = $template_manager->processTemplate($name, $sample_variables);

    echo json_encode([
        'success' => true,
        'template' => $template,
        'preview' => $preview,
        'variables' => $template['variables']
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to get template preview: ' . $e->getMessage()]);
}

/**
 * Get sample values for template variables
 */
function getSampleValue($variable) {
    $samples = [
        'customer_name' => 'John Doe',
        'phone_number' => '9876543210',
        'website_url' => 'www.apsdreamhome.com',
        'property_name' => 'Luxury Villa',
        'property_location' => 'Delhi',
        'property_price' => '₹50,00,000',
        'property_size' => '2000 sq ft',
        'budget_range' => '₹40-60 Lakhs',
        'booking_date' => '2024-01-15',
        'booking_time' => '10:00 AM',
        'booking_id' => 'BK2024001',
        'agent_name' => 'Rajesh Kumar',
        'agent_phone' => '9876543210',
        'commission_amount' => '₹25,000',
        'transaction_date' => '2024-01-10',
        'status' => 'Approved',
        'amount' => '₹1,50,000',
        'due_date' => '2024-01-20',
        'invoice_number' => 'INV2024001',
        'finance_phone' => '9876543210',
        'appointment_date' => '2024-01-18',
        'appointment_time' => '2:00 PM',
        'appointment_location' => 'Property Site',
        'admin_name' => 'Admin User',
        'alert_type' => 'System Maintenance',
        'severity' => 'Medium',
        'timestamp' => date('Y-m-d H:i:s'),
        'source' => 'System Monitor',
        'alert_details' => 'Scheduled maintenance completed',
        'property_features' => '3 BHK, Garden, Parking',
        'property_url' => 'www.apsdreamhome.com/property/luxury-villa'
    ];

    return $samples[$variable] ?? 'Sample ' . ucfirst(str_replace('_', ' ', $variable));
}
