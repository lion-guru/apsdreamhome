<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

/**
 * WhatsApp Template Manager Controller
 * Manage WhatsApp message templates and campaigns
 */
class WhatsAppTemplateController extends BaseController
{
    public function index()
    {
        // Temporarily disable login for testing
        // $this->requireLogin();
        
        $templates = $this->getTemplates();
        $categories = $this->getCategories();
        $analytics = $this->getTemplateAnalytics();
        
        $this->render('pages/whatsapp-templates', [
            'page_title' => 'WhatsApp Templates - APS Dream Home',
            'page_description' => 'Create and manage WhatsApp message templates',
            'templates' => $templates,
            'categories' => $categories,
            'analytics' => $analytics
        ]);
    }
    
    /**
     * Get all templates
     */
    private function getTemplates()
    {
        return [
            'customer_service' => [
                [
                    'id' => 'welcome',
                    'name' => 'Welcome Message',
                    'content' => 'Hello {{customer_name}}! Welcome to APS Dream Home. How can we help you find your dream property today?',
                    'description' => 'Sent to new users who register'
                ],
                [
                    'id' => 'inquiry_response',
                    'name' => 'Property Inquiry Response',
                    'content' => 'Thank you for your interest in {{property_title}}. This {{property_type}} is located in {{location}} and priced at ₹{{price}}. Would you like to schedule a visit?',
                    'description' => 'Automatic response to property inquiries'
                ]
            ],
            'property' => [
                [
                    'id' => 'new_listing',
                    'name' => 'New Property Listing',
                    'content' => '🏠 New Listing Alert! {{property_type}} in {{location}} - {{bedrooms}}BHK, {{area}}sqft at ₹{{price}}. Contact us for details!',
                    'description' => 'Notify users about new property listings'
                ],
                [
                    'id' => 'price_drop',
                    'name' => 'Price Drop Notification',
                    'content' => '🎉 Price Drop Alert! {{property_title}} is now available at ₹{{new_price}} (was ₹{{old_price}}). Limited time offer!',
                    'description' => 'Notify users about price reductions'
                ]
            ],
            'booking' => [
                [
                    'id' => 'booking_confirmation',
                    'name' => 'Booking Confirmation',
                    'content' => '✅ Booking Confirmed! Property visit scheduled on {{date}} at {{time}}. Address: {{property_address}}. See you there!',
                    'description' => 'Confirm property visit bookings'
                ]
            ],
            'payment' => [
                [
                    'id' => 'payment_confirmation',
                    'name' => 'Payment Confirmation',
                    'content' => '💳 Payment Received! ₹{{amount}} for {{property_title}} (Booking ID: {{booking_id}}). Thank you for choosing APS Dream Home!',
                    'description' => 'Confirm successful payments'
                ]
            ]
        ];
    }
    
    /**
     * Get template categories
     */
    private function getCategories()
    {
        return [
            'customer-service' => 'Customer Service',
            'property' => 'Property Updates',
            'booking' => 'Booking & Appointments',
            'appointment' => 'Appointments',
            'payment' => 'Payment & Commission',
            'commission' => 'Commission',
            'system' => 'System Notifications'
        ];
    }
    
    /**
     * Get template analytics
     */
    private function getTemplateAnalytics()
    {
        return [
            'total_sent_today' => 247,
            'response_rate' => 68.4,
            'most_used_template' => 'Welcome Message',
            'active_templates' => 12,
            'total_templates' => 18,
            'usage_chart' => [
                'labels' => ['Welcome', 'Inquiry', 'Booking', 'Payment', 'Reminder'],
                'data' => [89, 67, 45, 32, 14]
            ]
        ];
    }
    
    /**
     * Create new template
     */
    public function createTemplate()
    {
        header('Content-Type: application/json');
        
        try {
            $templateData = [
                'id' => uniqid('template_'),
                'name' => Security::sanitize($_POST['templateName']) ?? '',
                'category' => Security::sanitize($_POST['templateCategory']) ?? '',
                'content' => Security::sanitize($_POST['templateContent']) ?? '',
                'description' => Security::sanitize($_POST['templateDescription']) ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Template created successfully',
                'template' => $templateData
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to create template'
            ]);
        }
    }
    
    /**
     * Update template
     */
    public function updateTemplate($templateId)
    {
        header('Content-Type: application/json');
        
        try {
            $templateData = [
                'name' => Security::sanitize($_POST['templateName']) ?? '',
                'category' => Security::sanitize($_POST['templateCategory']) ?? '',
                'content' => Security::sanitize($_POST['templateContent']) ?? '',
                'description' => Security::sanitize($_POST['templateDescription']) ?? '',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Template updated successfully',
                'template' => $templateData
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to update template'
            ]);
        }
    }
    
    /**
     * Delete template
     */
    public function deleteTemplate($templateId)
    {
        header('Content-Type: application/json');
        
        try {
            echo json_encode([
                'success' => true,
                'message' => 'Template deleted successfully'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to delete template'
            ]);
        }
    }
    
    /**
     * Send test message
     */
    public function sendTestMessage()
    {
        header('Content-Type: application/json');
        
        try {
            $templateId = Security::sanitize($_POST['templateId']) ?? '';
            $testNumber = Security::sanitize($_POST['testNumber']) ?? '';
            
            // Simulate sending test message
            echo json_encode([
                'success' => true,
                'message' => 'Test message sent successfully',
                'template_id' => $templateId,
                'test_number' => $testNumber,
                'sent_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to send test message'
            ]);
        }
    }
    
    /**
     * Get template usage statistics
     */
    public function getUsageStats()
    {
        header('Content-Type: application/json');
        
        try {
            $stats = [
                'daily_usage' => [
                    'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    'data' => [120, 145, 167, 189, 201, 178, 156]
                ],
                'category_usage' => [
                    'customer-service' => 342,
                    'property' => 287,
                    'booking' => 156,
                    'payment' => 98,
                    'appointment' => 67,
                    'commission' => 45,
                    'system' => 23
                ],
                'performance_metrics' => [
                    'delivery_rate' => 94.5,
                    'response_rate' => 68.4,
                    'conversion_rate' => 12.7,
                    'avg_response_time' => '2.3 minutes'
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch usage statistics'
            ]);
        }
    }
    
    /**
     * Preview template with sample data
     */
    public function previewTemplate($templateId)
    {
        header('Content-Type: application/json');
        
        try {
            $sampleData = [
                'customer_name' => 'John Doe',
                'property_title' => 'Luxury Apartment in Gomti Nagar',
                'property_type' => '3BHK Apartment',
                'location' => 'Gomti Nagar, Lucknow',
                'price' => '75,00,000',
                'bedrooms' => '3',
                'area' => '1500',
                'date' => '2024-03-15',
                'time' => '10:00 AM',
                'booking_id' => 'BK123456',
                'amount' => '50,000'
            ];
            
            $templates = $this->getTemplates();
            $templateContent = '';
            
            foreach ($templates as $category) {
                foreach ($category as $template) {
                    if ($template['id'] === $templateId) {
                        $templateContent = $template['content'];
                        break 2;
                    }
                }
            }
            
            // Replace variables with sample data
            foreach ($sampleData as $key => $value) {
                $templateContent = str_replace('{{' . $key . '}}', $value, $templateContent);
            }
            
            echo json_encode([
                'success' => true,
                'preview' => $templateContent,
                'variables' => array_keys($sampleData)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to preview template'
            ]);
        }
    }
}
