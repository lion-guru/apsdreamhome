<?php

namespace App\Services\AI;

use App\Core\Database\Database;
use Exception;
use \App\Traits\ServiceTenantTrait;

/**
 * AI Content Generation Service
 * Auto-generate property descriptions, marketing copy, listing titles
 */
class AIContentGenerationService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $templates = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTablesExist();
        $this->loadTemplates();
    }
    
    private function ensureTablesExist(): void
    {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS generated_content (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    content_type VARCHAR(50) NOT NULL,
                    property_id INT UNSIGNED NULL,
                    generated_content TEXT NOT NULL,
                    metadata JSON,
                    status ENUM('draft','approved','published','rejected') DEFAULT 'draft',
                    created_by INT UNSIGNED,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_type (content_type),
                    INDEX idx_property (property_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $this->db->query("
                CREATE TABLE IF NOT EXISTS content_templates (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    template_type VARCHAR(50) NOT NULL,
                    section VARCHAR(50) NOT NULL,
                    template_text TEXT NOT NULL,
                    variables JSON,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_type_section (template_type, section)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log('Content generation tables error: ' . $e->getMessage());
        }
    }
    
    private function loadTemplates(): void
    {
        try {
            $rows = $this->db->fetchAll("SELECT * FROM content_templates WHERE is_active = 1");
            foreach ($rows as $row) {
                $this->templates[$row['template_type']][$row['section']][] = $row['template_text'];
            }
        } catch (Exception $e) {
            // Use defaults if DB not available
            $this->loadDefaultTemplates();
        }
    }
    
    private function loadDefaultTemplates(): void
    {
        $this->templates = [
            'property_description' => [
                'intro' => [
                    'Welcome to your dream home at {{property_title}} in {{location}}. This stunning {{property_type}} offers the perfect blend of comfort and luxury.',
                    'Discover {{property_title}}, a magnificent {{property_type}} nestled in the heart of {{location}}. Experience modern living at its finest.',
                    'Presenting {{property_title}} - an exceptional {{property_type}} in {{location}} that redefines elegant living.'
                ],
                'features' => [
                    'Featuring {{bedrooms}} spacious bedrooms and {{bathrooms}} modern bathrooms across {{area}} sq ft of thoughtfully designed living space.',
                    'This {{property_type}} boasts {{bedrooms}} bedrooms, {{bathrooms}} bathrooms, and {{area}} sq ft of premium living area.',
                    'With {{bedrooms}} bedrooms and {{bathrooms}} bathrooms spanning {{area}} sq ft, every corner exudes sophistication.'
                ],
                'amenities' => [
                    'Premium amenities include: {{amenities}}. Enjoy lifestyle facilities that elevate everyday living.',
                    'Residents enjoy access to: {{amenities}}. World-class facilities right at your doorstep.',
                    'The community offers: {{amenities}}. Everything you need for a fulfilled lifestyle.'
                ],
                'location' => [
                    'Strategically located in {{location}}, you\'re minutes away from {{nearby}}. Excellent connectivity to major landmarks.',
                    'Situated in the prime area of {{location}}, with easy access to {{nearby}}. Convenience meets connectivity.',
                    'Located in {{location}}, surrounded by {{nearby}}. The perfect address for modern living.'
                ],
                'closing' => [
                    'Don\'t miss this opportunity to own a piece of paradise. Contact us today to schedule a visit!',
                    'Your dream home awaits. Schedule a viewing now and experience the lifestyle you deserve.',
                    'Make {{property_title}} your new address. Contact our sales team for more details.'
                ]
            ],
            'property_title' => [
                'main' => [
                    '{{bedrooms}} BHK {{property_type}} in {{location}} - {{area}} sq ft',
                    'Luxury {{bedrooms}} BHK {{property_type}} at {{location}}',
                    'Premium {{property_type}} with {{bedrooms}} Bedrooms in {{location}}',
                    'Spacious {{bedrooms}} BHK {{property_type}} Near {{nearby_landmark}}'
                ]
            ],
            'ad_copy' => [
                'facebook' => [
                    '🏠 {{property_type}} Alert! {{bedrooms}}BHK in {{location}} at ₹{{price}}. {{amenities_short}}. Book your visit today! #DreamHome #{{location}}',
                    '✨ New Listing: {{bedrooms}}BHK {{property_type}} in {{location}}. Price: ₹{{price}}. {{features_short}}. Limited units available! Contact us now.'
                ],
                'instagram' => [
                    '🏡 {{property_title}} - Where dreams meet reality. {{bedrooms}}BHK {{property_type}} in {{location}} 💫 Price: ₹{{price}} #RealEstate #DreamHome #{{location}}',
                    '🌟 Your perfect home is here! {{bedrooms}}BHK {{property_type}} at {{location}}. {{features_short}} amenities. DM for details! #PropertyForSale #{{location}}'
                ],
                'google' => [
                    '{{bedrooms}}BHK {{property_type}} in {{location}} | {{area}} sq ft | ₹{{price}} | {{amenities_short}} | RERA Approved | Book Visit',
                    'Buy {{property_type}} in {{location}} - {{bedrooms}}BHK, {{area}} sq ft, ₹{{price}}. {{features_short}}. Schedule viewing today!'
                ]
            ],
            'email' => [
                'inquiry_response' => [
                    'subject' => 'Thank you for your interest in {{property_title}}',
                    'body' => 'Dear {{customer_name}},\n\nThank you for your inquiry about {{property_title}} in {{location}}. This {{property_type}} is priced at ₹{{price}} and offers {{bedrooms}} bedrooms across {{area}} sq ft.\n\nKey highlights:\n- {{amenities_short}}\n- Prime location with {{nearby}}\n- RERA registered\n\nWould you like to schedule a site visit? Reply to this email or call us at +91-XXXXXXXXXX.\n\nBest regards,\nAPS Dream Home Team'
                ],
                'price_drop' => [
                    'subject' => 'Price Drop Alert: {{property_title}} now at ₹{{new_price}}',
                    'body' => 'Dear {{customer_name}},\n\nGreat news! The price for {{property_title}} in {{location}} has been reduced from ₹{{old_price}} to ₹{{new_price}}.\n\nThis {{bedrooms}}BHK {{property_type}} ({{\n\nDon\'t miss this limited-time opportunity. Contact us to schedule a visit.\n\nBest regards,\nAPS Dream Home Team'
                ],
                'new_property' => [
                    'subject' => 'New Property Alert: {{property_title}} in {{location}}',
                    'body' => 'Dear {{customer_name}},\n\nWe\'re excited to announce a new listing matching your preferences!\n\n{{property_title}} - {{bedrooms}}BHK {{property_type}} in {{location}}\nPrice: ₹{{price}} | Area: {{area}} sq ft\n\nHighlights: {{amenities_short}}\n\nBe the first to view this property. Reply to schedule a visit.\n\nBest regards,\nAPS Dream Home Team'
                ],
                'follow_up' => [
                    'subject' => 'Following up on your property search',
                    'body' => 'Dear {{customer_name}},\n\nJust checking in on your property search. We have several {{property_type}} options in {{location}} that might interest you.\n\nRecent additions:\n{{recent_properties}}\n\nLet us know if you\'d like to schedule visits or need more information.\n\nBest regards,\nAPS Dream Home Team'
                ]
            ]
        ];
    }
    
    /**
     * Generate property description
     */
    public function generatePropertyDescription(array $propertyData, array $options = []): array
    {
        $data = array_merge([
            'property_title' => $propertyData['title'] ?? 'Premium Property',
            'property_type' => $propertyData['property_type'] ?? 'Apartment',
            'location' => $propertyData['location'] ?? 'Prime Location',
            'bedrooms' => $propertyData['bedrooms'] ?? 2,
            'bathrooms' => $propertyData['bathrooms'] ?? 2,
            'area' => $propertyData['area'] ?? 1000,
            'price' => $propertyData['price'] ?? 0,
            'amenities' => $propertyData['amenities'] ?? [],
            'nearby' => $propertyData['nearby'] ?? 'major landmarks',
            'features' => $propertyData['features'] ?? []
        ], $options);
        
        $data['amenities_short'] = $this->formatAmenities($data['amenities']);
        $data['features_short'] = implode(', ', array_slice($data['features'], 0, 3));
        $data['nearby_landmark'] = $data['nearby'];
        
        $sections = ['intro', 'features', 'amenities', 'location', 'closing'];
        $description = '';
        
        foreach ($sections as $section) {
            $template = $this->getRandomTemplate('property_description', $section);
            $description .= $this->fillTemplate($template, $data) . "\n\n";
        }
        
        $description = $this->cleanDescription($description);
        
        // Save to DB
        $contentId = $this->saveGeneratedContent('property_description', $propertyData['id'] ?? null, $description, $data);
        
        return [
            'success' => true,
            'content_id' => $contentId,
            'description' => $description,
            'seo_score' => $this->calculateSEOScore($data['property_title']),
            'word_count' => str_word_count($description)
        ];
    }
    
    /**
     * Generate property title
     */
    public function generatePropertyTitle(array $propertyData, array $options = []): array
    {
        $data = array_merge([
            'property_type' => $propertyData['property_type'] ?? 'Apartment',
            'location' => $propertyData['location'] ?? 'Prime Location',
            'bedrooms' => $propertyData['bedrooms'] ?? 2,
            'area' => $propertyData['area'] ?? 1000,
            'price' => $propertyData['price'] ?? 0,
            'nearby_landmark' => $propertyData['nearby'] ?? ''
        ], $options);
        
        $titles = [];
        foreach ($this->templates['property_title']['main'] as $template) {
            $titles[] = $this->fillTemplate($template, $data);
        }
        
        $bestTitle = $this->optimizeTitle($titles[0]);
        
        return [
            'success' => true,
            'titles' => $titles,
            'recommended' => $bestTitle,
            'alternatives' => array_slice($titles, 1)
        ];
    }
    
    /**
     * Generate ad copy for platforms
     */
    public function generateAdCopy(array $propertyData, string $platform = 'general', array $options = []): array
    {
        $data = array_merge([
            'property_title' => $propertyData['title'] ?? 'Premium Property',
            'property_type' => $propertyData['property_type'] ?? 'Apartment',
            'location' => $propertyData['location'] ?? 'Prime Location',
            'bedrooms' => $propertyData['bedrooms'] ?? 2,
            'area' => $propertyData['area'] ?? 1000,
            'price' => $propertyData['price'] ?? 0,
            'amenities' => $propertyData['amenities'] ?? [],
            'features' => $propertyData['features'] ?? []
        ], $options);
        
        $data['amenities_short'] = $this->formatAmenities($data['amenities']);
        $data['features_short'] = implode(', ', array_slice($data['features'], 0, 3));
        $data['price_formatted'] = $this->formatPrice($data['price']);
        
        $platformKey = $platform;
        if (!isset($this->templates['ad_copy'][$platformKey])) {
            $platformKey = 'facebook'; // default
        }
        
        $copies = [];
        foreach ($this->templates['ad_copy'][$platformKey] as $template) {
            $copies[] = $this->fillTemplate($template, $data);
        }
        
        return [
            'success' => true,
            'platform' => $platform,
            'copies' => $copies,
            'recommended' => $copies[0],
            'suggested_images' => $this->suggestAdImages($data),
            'suggested_posting_time' => $this->suggestPostingTime($platform)
        ];
    }
    
    /**
     * Generate email content
     */
    public function generateEmailContent(string $type, array $data, array $options = []): array
    {
        $mergedData = array_merge([
            'customer_name' => $data['customer_name'] ?? 'Valued Customer',
            'property_title' => $data['property_title'] ?? 'Property',
            'property_type' => $data['property_type'] ?? 'Apartment',
            'location' => $data['location'] ?? 'Location',
            'price' => $data['price'] ?? 0,
            'bedrooms' => $data['bedrooms'] ?? 2,
            'area' => $data['area'] ?? 1000,
            'amenities' => $data['amenities'] ?? [],
            'nearby' => $data['nearby'] ?? 'major landmarks',
            'recent_properties' => $data['recent_properties'] ?? ''
        ], $options);
        
        $mergedData['amenities_short'] = $this->formatAmenities($mergedData['amenities']);
        $mergedData['price_formatted'] = $this->formatPrice($mergedData['price']);
        
        if (!isset($this->templates['email'][$type])) {
            $type = 'inquiry_response';
        }
        
        $template = $this->templates['email'][$type];
        $subject = $this->fillTemplate($template['subject'] ?? '', $mergedData);
        $body = $this->fillTemplate($template['body'] ?? '', $mergedData);
        
        // Personalize
        $body = $this->personalizeEmail($body, $mergedData);
        
        $spamScore = $this->calculateSpamScore($subject, $body);
        
        return [
            'success' => true,
            'subject' => $subject,
            'body' => $body,
            'spam_score' => $spamScore,
            'type' => $type
        ];
    }
    
    private function getRandomTemplate(string $type, string $section): string
    {
        if (isset($this->templates[$type][$section]) && !empty($this->templates[$type][$section])) {
            return $this->templates[$type][$section][array_rand($this->templates[$type][$section])];
        }
        return '{{' . $section . '}}';
    }
    
    private function fillTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $replacement = is_array($value) ? implode(', ', $value) : (string)$value;
            $template = str_replace($placeholder, $replacement, $template);
        }
        return $template;
    }
    
    private function formatAmenities(array $amenities): string
    {
        if (empty($amenities)) return 'modern amenities';
        return implode(', ', array_slice($amenities, 0, 5));
    }
    
    private function getFeatureQuality(array $data): string
    {
        $score = 0;
        if (($data['area'] ?? 0) > 1500) $score += 2;
        if (($data['bedrooms'] ?? 0) >= 3) $score += 2;
        if (!empty($data['amenities'])) $score += count($data['amenities']);
        
        if ($score >= 8) return 'luxury';
        if ($score >= 5) return 'premium';
        return 'standard';
    }
    
    private function formatPrice(float $price): string
    {
        if ($price >= 10000000) {
            return '₹' . number_format($price / 10000000, 2) . ' Cr';
        } elseif ($price >= 100000) {
            return '₹' . number_format($price / 100000, 2) . ' L';
        }
        return '₹' . number_format($price);
    }
    
    private function cleanDescription(string $description): string
    {
        // Remove extra whitespace
        $description = preg_replace('/\s+/', ' ', $description);
        $description = trim($description);
        // Ensure proper sentence endings
        $description = preg_replace('/([.!?])\s*([A-Z])/', '$1 $2', $description);
        return $description;
    }
    
    private function optimizeTitle(string $title): string
    {
        $title = trim($title);
        if (strlen($title) > 60) {
            $title = substr($title, 0, 57) . '...';
        }
        return $title;
    }
    
    private function getPriceRangeText(float $price): string
    {
        if ($price < 5000000) return 'Budget-friendly';
        if ($price < 10000000) return 'Mid-range';
        if ($price < 20000000) return 'Premium';
        return 'Luxury';
    }
    
    private function extractKeyPhrases(string $description): array
    {
        $phrases = [];
        preg_match_all('/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)\b/', $description, $matches);
        foreach ($matches[1] as $match) {
            if (strlen($match) > 5 && strlen($match) < 40) {
                $phrases[] = $match;
            }
        }
        return array_unique($phrases);
    }
    
    private function calculateSEOScore(string $title): int
    {
        $score = 50;
        $words = str_word_count($title);
        
        if ($words >= 5 && $words <= 10) $score += 20;
        if (strpos(strtolower($title), 'bhk') !== false) $score += 10;
        if (preg_match('/\d+/', $title)) $score += 10;
        if (strlen($title) <= 60) $score += 10;
        
        return min(100, $score);
    }
    
    private function saveGeneratedContent(string $type, ?int $propertyId, string $content, array $metadata): int
    {
        try {
            $tenantData = $this->tenantInsertData();
            $tenantCols = array_keys($tenantData);
            $tenantVals = array_values($tenantData);
            $columns = array_merge(['content_type', 'property_id', 'generated_content', 'metadata', 'status', 'created_by'], $tenantCols);
            $values  = array_merge([$type, $propertyId, $content, json_encode($metadata), 'draft', $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0], $tenantVals);
            $colStr = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $this->db->query("INSERT INTO generated_content ($colStr) VALUES ($placeholders)", $values);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            return 0;
        }
    }
    
    // Stubs for future enhancement
    private function generateTitleAlternatives(array $data, int $count): array { return []; }
    private function suggestAdImages(array $data): array { return ['exterior', 'interior', 'amenities', 'floor_plan', 'location_map']; }
    private function suggestPostingTime(string $platform): string { return '10:00 AM - 12:00 PM'; }
    private function generateInquiryEmail(array $data): string { return ''; }
    private function generatePriceDropEmail(array $data): string { return ''; }
    private function generateNewPropertyEmail(array $data): string { return ''; }
    private function generateFollowUpEmail(array $data): string { return ''; }
    private function personalizeEmail(string $body, array $data): string { return $body; }
    private function calculateSpamScore(string $subject, string $body): int { return 5; }
}