<?php

namespace App\Utils;

/**
 * WhatsApp Integration Utility Class
 * Handles WhatsApp messaging and sharing functionality
 */
class WhatsApp
{
    private $businessNumber;
    private $defaultMessage;

    public function __construct()
    {
        $this->businessNumber = getenv('WHATSAPP_NUMBER') ?: '91919277121112';
        $this->defaultMessage = 'Hi! I found your contact on APS Dream Home website.';
    }

    /**
     * Generate WhatsApp URL for direct messaging
     */
    public function getWhatsAppUrl($phone = null, $message = null)
    {
        $number = $phone ?: $this->businessNumber;
        $text = $message ?: $this->defaultMessage;

        // Remove any non-numeric characters from phone number
        $number = preg_replace('/\D/', '', $number);

        // Add country code if not present
        if (strlen($number) === 10) {
            $number = '91' . $number;
        }

        // URL encode the message
        $encodedMessage = urlencode($text);

        return "https://wa.me/{$number}?text={$encodedMessage}";
    }

    /**
     * Generate property inquiry WhatsApp message
     */
    public function getPropertyInquiryMessage($propertyData, $userName = null)
    {
        $greeting = $userName ? "Hi! I'm {$userName}." : "Hi!";

        $message = "{$greeting} I'm interested in this property:\n\n";
        $message .= "🏠 *{$propertyData['title']}*\n";
        $message .= "📍 {$propertyData['location']}\n";
        $message .= "💰 ₹" . number_format($propertyData['price']) . "\n";

        if (!empty($propertyData['bedrooms'])) {
            $message .= "🛏️ {$propertyData['bedrooms']} BHK\n";
        }

        if (!empty($propertyData['area'])) {
            $message .= "📐 {$propertyData['area']} sq.ft\n";
        }

        $message .= "\nProperty ID: {$propertyData['id']}\n";
        $message .= "\nCan you please provide more details and arrange a visit?\n";
        $message .= "\nThank you!\nAPS Dream Home";

        return $message;
    }

    /**
     * Generate booking inquiry WhatsApp message
     */
    public function getBookingInquiryMessage($propertyData, $bookingType, $userName = null)
    {
        $greeting = $userName ? "Hi! I'm {$userName}." : "Hi!";

        $bookingTypeText = match($bookingType) {
            'token' => 'Token Amount',
            'full' => 'Full Payment',
            'emi_booking' => 'EMI Booking',
            default => 'Booking'
        };

        $message = "{$greeting} I'm interested in booking this property:\n\n";
        $message .= "🏠 *{$propertyData['title']}*\n";
        $message .= "📍 {$propertyData['location']}\n";
        $message .= "💰 ₹" . number_format($propertyData['price']) . "\n";
        $message .= "📋 Booking Type: {$bookingTypeText}\n";

        if (!empty($propertyData['bedrooms'])) {
            $message .= "🛏️ {$propertyData['bedrooms']} BHK\n";
        }

        $message .= "\nProperty ID: {$propertyData['id']}\n";
        $message .= "\nPlease help me with the booking process and documentation.\n";
        $message .= "\nThank you!\nAPS Dream Home";

        return $message;
    }

    /**
     * Generate contact form WhatsApp message
     */
    public function getContactMessage($contactData)
    {
        $message = "Hi! I have an inquiry from your website:\n\n";
        $message .= "👤 *{$contactData['name']}*\n";
        $message .= "📧 {$contactData['email']}\n";
        $message .= "📱 {$contactData['phone']}\n\n";

        if (!empty($contactData['subject'])) {
            $message .= "📌 *{$contactData['subject']}*\n\n";
        }

        $message .= "💬 *Message:*\n{$contactData['message']}\n\n";
        $message .= "Please respond at your earliest convenience.\n\n";
        $message .= "Best regards,\nAPS Dream Home Contact Form";

        return $message;
    }

    /**
     * Generate property sharing message
     */
    public function getPropertyShareMessage($propertyData, $shareUrl = null)
    {
        $message = "🏠 *Check out this amazing property!*\n\n";
        $message .= "*{$propertyData['title']}*\n";
        $message .= "📍 {$propertyData['location']}\n";
        $message .= "💰 ₹" . number_format($propertyData['price']) . "\n";

        if (!empty($propertyData['bedrooms'])) {
            $message .= "🛏️ {$propertyData['bedrooms']} BHK\n";
        }

        if (!empty($propertyData['area'])) {
            $message .= "📐 {$propertyData['area']} sq.ft\n";
        }

        if ($shareUrl) {
            $message .= "\n🔗 View Details: {$shareUrl}\n";
        } else {
            $message .= "\n🔗 Property ID: {$propertyData['id']}\n";
        }

        $message .= "\nShared via APS Dream Home 🏡";

        return $message;
    }

    /**
     * Generate callback request message
     */
    public function getCallbackRequestMessage($userName, $phone, $preferredTime = null)
    {
        $message = "📞 *Callback Request*\n\n";
        $message .= "👤 Name: {$userName}\n";
        $message .= "📱 Phone: {$phone}\n";

        if ($preferredTime) {
            $message .= "⏰ Preferred Time: {$preferredTime}\n";
        }

        $message .= "\nPlease call me back regarding property inquiry.\n\n";
        $message .= "Best regards,\nAPS Dream Home";

        return $message;
    }

    /**
     * Generate WhatsApp sharing URL for property
     */
    public function getPropertyShareUrl($propertyData, $baseUrl = 'http://localhost:8000')
    {
        $propertyUrl = $baseUrl . '/properties/' . $propertyData['id'];
        $message = $this->getPropertyShareMessage($propertyData, $propertyUrl);

        return $this->getWhatsAppUrl(null, $message);
    }

    /**
     * Generate quick contact buttons HTML
     */
    public function getQuickContactButtons($context = 'general', $data = [])
    {
        $buttons = [];

        switch ($context) {
            case 'property':
                if (!empty($data['property'])) {
                    $buttons[] = [
                        'text' => 'Inquire Now',
                        'url' => $this->getWhatsAppUrl(null, $this->getPropertyInquiryMessage($data['property'], $data['user_name'] ?? null)),
                        'icon' => 'fas fa-envelope',
                        'class' => 'btn-success'
                    ];
                }
                break;

            case 'booking':
                if (!empty($data['property'])) {
                    $buttons[] = [
                        'text' => 'Book Now',
                        'url' => $this->getWhatsAppUrl(null, $this->getBookingInquiryMessage($data['property'], $data['booking_type'] ?? 'token', $data['user_name'] ?? null)),
                        'icon' => 'fas fa-calendar-check',
                        'class' => 'btn-primary'
                    ];
                }
                break;

            case 'contact':
                $buttons[] = [
                    'text' => 'Call Back',
                    'url' => $this->getWhatsAppUrl(null, $this->getCallbackRequestMessage(
                        $data['name'] ?? 'Website Visitor',
                        $data['phone'] ?? '',
                        $data['preferred_time'] ?? null
                    )),
                    'icon' => 'fas fa-phone',
                    'class' => 'btn-info'
                ];
                break;

            default:
                $buttons[] = [
                    'text' => 'Chat on WhatsApp',
                    'url' => $this->getWhatsAppUrl(),
                    'icon' => 'fab fa-whatsapp',
                    'class' => 'btn-success'
                ];
        }

        return $buttons;
    }

    /**
     * Get WhatsApp business profile URL
     */
    public function getBusinessProfileUrl()
    {
        return "https://wa.me/{$this->businessNumber}";
    }

    /**
     * Generate floating WhatsApp button HTML
     */
    public function getFloatingButton($message = null)
    {
        $url = $this->getWhatsAppUrl(null, $message ?: $this->defaultMessage);

        return "
        <a href='{$url}' target='_blank' class='whatsapp-float'>
            <i class='fab fa-whatsapp'></i>
        </a>";
    }

    /**
     * Validate WhatsApp number format
     */
    public function validateNumber($number)
    {
        // Remove all non-numeric characters
        $cleanNumber = preg_replace('/\D/', '', $number);

        // Check if it's a valid Indian mobile number
        if (preg_match('/^[6-9]\d{9}$/', $cleanNumber)) {
            return '91' . $cleanNumber;
        }

        // Check if it already has country code
        if (preg_match('/^91[6-9]\d{9}$/', $cleanNumber)) {
            return $cleanNumber;
        }

        return false;
    }

    /**
     * Get WhatsApp API status (for future WhatsApp Business API integration)
     */
    public function getApiStatus()
    {
        // For now, return mock status
        // In production, this would check WhatsApp Business API health
        return [
            'status' => 'active',
            'api_version' => '2.41.2',
            'webhook_status' => 'active',
            'last_webhook' => date('Y-m-d H:i:s')
        ];
    }
}
