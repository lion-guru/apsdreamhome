<?php

namespace App\Services\AI;

use App\Core\Database\Database;

/**
 * AI Content Generation Service
 * Generate property descriptions, titles, and marketing content using AI
 */
class AIContentGenerationService
{
    private $database;
    private $templates;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->loadTemplates();
        $this->ensureTablesExist();
    }
    
    private function ensureTablesExist(): void
    {
        // Table initialization handled by migration script scripts/create_ai_tables.php
        return;
    }
    
    private function loadTemplates(): void
    {
        $this->templates = [
            'description' => [
                'opening' => [
                    "Discover this stunning {property_type} in the heart of {location}",
                    "Presenting an exceptional {property_type} at {location}",
                    "Welcome to this beautiful {property_type} in {location}",
                    "Experience luxury living in this {property_type} at {location}"
                ],
                'features' => [
                    "This property features {area_sqft} sqft of {feature_quality} space",
                    "Spanning {area_sqft} sqft, this property offers {feature_quality} accommodations",
                    "With {area_sqft} sqft, this {property_type} provides ample {feature_quality} space"
                ],
                'amenities' => [
                    "The property comes with {amenities_list}",
                    "Enjoy {amenities_list} and more",
                    "Premium amenities include {amenities_list}"
                ],
                'location' => [
                    "Located in a prime area with excellent connectivity",
                    "Strategically located with easy access to major landmarks",
                    "Situated in a prestigious neighborhood"
                ],
                'closing' => [
                    "Don't miss this opportunity. Contact us today for a viewing!",
                    "Schedule a visit now and make this your dream home!",
                    "This won't last long. Book your viewing today!",
                    "Your perfect home awaits. Contact us immediately!"
                ]
            ]
        ];
    }
    
    /**
     * Generate property description
     */
    public function generatePropertyDescription(array $propertyData, array $options = []): array
    {
        try {
            $tone = $options['tone'] ?? 'professional';
            $language = $options['language'] ?? 'en';
            
            // Build description sections
            $description = '';
            
            // Opening
            $opening = $this->getRandomTemplate('description', 'opening');
            $description .= $this->fillTemplate($opening, $propertyData) . ". ";
            
            // Features
            $features = $this->getRandomTemplate('description', 'features');
            $propertyData['feature_quality'] = $this->getFeatureQuality($propertyData);
            $description .= $this->fillTemplate($features, $propertyData) . ". ";
            
            // Rooms detail
            if (!empty($propertyData['bedrooms']) || !empty($propertyData['bathrooms'])) {
                $description .= $this->generateRoomDescription($propertyData) . " ";
            }
            
            // Amenities
            if (!empty($propertyData['amenities'])) {
                $amenities = $this->getRandomTemplate('description', 'amenities');
                $propertyData['amenities_list'] = $this->formatAmenities($propertyData['amenities']);
                $description .= $this->fillTemplate($amenities, $propertyData) . ". ";
            }
            
            // Location highlights
            $location = $this->getRandomTemplate('description', 'location');
            $description .= $this->fillTemplate($location, $propertyData) . ". ";
            
            // Nearby facilities
            if (!empty($propertyData['nearby_facilities'])) {
                $description .= $this->generateNearbyDescription($propertyData['nearby_facilities']) . " ";
            }
            
            // Investment potential
            $description .= $this->generateInvestmentText($propertyData) . " ";
            
            // Closing
            $closing = $this->getRandomTemplate('description', 'closing');
            $description .= $this->fillTemplate($closing, $propertyData);
            
            // Clean up
            $description = $this->cleanDescription($description);
            
            // Save to database
            $contentId = $this->saveGeneratedContent('description', $propertyData['id'] ?? null, $description, [
                'tone' => $tone,
                'language' => $language,
                'property_data' => $propertyData
            ]);
            
            return [
                'success' => true,
                'content_id' => $contentId,
                'description' => $description,
                'word_count' => str_word_count($description),
                'tone' => $tone,
                'language' => $language,
                'key_phrases' => $this->extractKeyPhrases($description)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate property title
     */
    public function generatePropertyTitle(array $propertyData, array $options = []): array
    {
        try {
            $templates = [
                "{bedrooms} BHK {property_type} in {location} - {area_sqft} sqft",
                "Premium {property_type} at {location} | {bedrooms} BHK | {area_sqft} sqft",
                "{feature_quality} {bedrooms} BHK {property_type} in {location}",
                "Exclusive {property_type} at {location} - {bedrooms} BHK, {amenities_count}+ Amenities",
                "Ready to Move {bedrooms} BHK {property_type} in {location} - {price_range}"
            ];
            
            $propertyData['feature_quality'] = $this->getFeatureQuality($propertyData);
            $propertyData['amenities_count'] = count($propertyData['amenities'] ?? []);
            $propertyData['price_range'] = $this->getPriceRangeText($propertyData['price'] ?? 0);
            
            // Select best template
            $selectedTemplate = $templates[array_rand($templates)];
            $title = $this->fillTemplate($selectedTemplate, $propertyData);
            
            // Clean and optimize
            $title = $this->optimizeTitle($title);
            
            return [
                'success' => true,
                'title' => $title,
                'character_count' => strlen($title),
                'seo_score' => $this->calculateSEOScore($title),
                'alternatives' => $this->generateTitleAlternatives($propertyData, 3)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate ad copy for marketing
     */
    public function generateAdCopy(array $propertyData, string $platform = 'general', array $options = []): array
    {
        try {
            $adCopies = [];
            
            // Headline
            $headlines = [
                "🏠 Your Dream Home Awaits in {$propertyData['location']}!",
                "💎 Premium {$propertyData['property_type']} Available Now!",
                "🔥 Hot Property Alert: {$propertyData['location']}",
                "✨ Luxury Living at {$propertyData['location']}"
            ];
            
            // Body text
            $body = "Discover this stunning {$propertyData['bedrooms']} BHK {$propertyData['property_type']} ";
            $body .= "with {$propertyData['area_sqft']} sqft of living space. ";
            
            if (!empty($propertyData['amenities'])) {
                $body .= "Featuring " . $this->formatAmenities(array_slice($propertyData['amenities'], 0, 3)) . ". ";
            }
            
            $body .= "Priced at " . $this->formatPrice($propertyData['price'] ?? 0) . ". ";
            
            // CTA
            $ctas = [
                "📞 Call now: +91 92771 21112",
                "📱 WhatsApp us for details",
                "🔍 Visit: apsdreamhome.com",
                "⏰ Limited time offer!"
            ];
            
            // Platform-specific optimization
            switch ($platform) {
                case 'facebook':
                    $adCopies[] = [
                        'headline' => $headlines[0],
                        'body' => $body,
                        'cta' => $ctas[0],
                        'hashtags' => '#RealEstate #Property #DreamHome #Investment'
                    ];
                    break;
                    
                case 'whatsapp':
                    $adCopies[] = [
                        'message' => "🏠 *{$propertyData['bedrooms']} BHK {$propertyData['property_type']}*\n\n" .
                                    "📍 Location: {$propertyData['location']}\n" .
                                    "📐 Area: {$propertyData['area_sqft']} sqft\n" .
                                    "💰 Price: " . $this->formatPrice($propertyData['price'] ?? 0) . "\n\n" .
                                    $ctas[1]
                    ];
                    break;
                    
                case 'sms':
                    $adCopies[] = [
                        'message' => substr("{$propertyData['bedrooms']} BHK {$propertyData['property_type']} at {$propertyData['location']}. " .
                                     "Area: {$propertyData['area_sqft']} sqft. " .
                                     "Price: " . $this->formatPrice($propertyData['price'] ?? 0) . ". " .
                                     "Call 9277121112", 0, 160)
                    ];
                    break;
                    
                default:
                    $adCopies[] = [
                        'headline' => $headlines[array_rand($headlines)],
                        'body' => $body,
                        'cta' => $ctas[array_rand($ctas)],
                        'full_copy' => $headlines[array_rand($headlines)] . "\n\n" . $body . "\n\n" . $ctas[array_rand($ctas)]
                    ];
            }
            
            return [
                'success' => true,
                'platform' => $platform,
                'ad_copies' => $adCopies,
                'suggested_images' => $this->suggestAdImages($propertyData),
                'best_posting_time' => $this->suggestPostingTime($platform)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate email content
     */
    public function generateEmailContent(string $type, array $data, array $options = []): array
    {
        try {
            $templates = [
                'property_inquiry' => [
                    'subject' => "Regarding your interest in {$data['property_title']}",
                    'body' => $this->generateInquiryEmail($data)
                ],
                'price_drop' => [
                    'subject' => "🔥 Price Drop Alert: {$data['property_title']}",
                    'body' => $this->generatePriceDropEmail($data)
                ],
                'new_property' => [
                    'subject' => "🏠 New Property Matching Your Preferences",
                    'body' => $this->generateNewPropertyEmail($data)
                ],
                'follow_up' => [
                    'subject' => "Following up on your property inquiry",
                    'body' => $this->generateFollowUpEmail($data)
                ]
            ];
            
            if (!isset($templates[$type])) {
                return ['success' => false, 'error' => 'Unknown email type'];
            }
            
            $template = $templates[$type];
            
            // Personalize
            $template['body'] = $this->personalizeEmail($template['body'], $data);
            
            return [
                'success' => true,
                'subject' => $template['subject'],
                'body' => $template['body'],
                'preview_text' => substr(strip_tags($template['body']), 0, 150),
                'spam_score' => $this->calculateSpamScore($template['subject'], $template['body'])
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Helper methods
     */
    private function getRandomTemplate(string $type, string $section): string
    {
        $templates = $this->templates[$type][$section] ?? [''];
        return $templates[array_rand($templates)];
    }
    
    private function fillTemplate(string $template, array $data): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function($matches) use ($data) {
            return $data[$matches[1]] ?? '';
        }, $template);
    }
    
    private function getFeatureQuality(array $data): string
    {
        $amenityCount = count($data['amenities'] ?? []);
        $area = $data['area_sqft'] ?? 0;
        
        if ($amenityCount >= 8 && $area >= 2000) {
            return 'luxurious';
        } elseif ($amenityCount >= 5 || $area >= 1500) {
            return 'spacious';
        } elseif ($amenityCount >= 3 || $area >= 1000) {
            return 'well-appointed';
        } else {
            return 'cozy';
        }
    }
    
    private function formatAmenities(array $amenities): string
    {
        $amenityMap = [
            'swimming_pool' => 'swimming pool',
            'gym' => 'gymnasium',
            'club_house' => 'club house',
            'security_24_7' => '24/7 security',
            'parking' => 'parking',
            'garden' => 'garden',
            'elevator' => 'elevator',
            'power_backup' => 'power backup',
            'wifi' => 'WiFi',
            'ac' => 'air conditioning',
            'modular_kitchen' => 'modular kitchen'
        ];
        
        $formatted = [];
        foreach ($amenities as $amenity) {
            $formatted[] = $amenityMap[$amenity] ?? $amenity;
        }
        
        if (count($formatted) <= 2) {
            return implode(' and ', $formatted);
        } else {
            $last = array_pop($formatted);
            return implode(', ', $formatted) . ' and ' . $last;
        }
    }
    
    private function generateRoomDescription(array $data): string
    {
        $parts = [];
        
        if (!empty($data['bedrooms'])) {
            $parts[] = "{$data['bedrooms']} bedroom" . ($data['bedrooms'] > 1 ? 's' : '');
        }
        if (!empty($data['bathrooms'])) {
            $parts[] = "{$data['bathrooms']} bathroom" . ($data['bathrooms'] > 1 ? 's' : '');
        }
        if (!empty($data['balconies'])) {
            $parts[] = "{$data['balconies']} balcony" . ($data['balconies'] > 1 ? 'ies' : 'y');
        }
        
        return "It includes " . implode(', ', $parts) . ".";
    }
    
    private function generateNearbyDescription(array $facilities): string
    {
        $descriptions = [];
        
        if (isset($facilities['metro'])) {
            $descriptions[] = "Metro station is just {$facilities['metro']}m away";
        }
        if (isset($facilities['mall'])) {
            $descriptions[] = "Shopping mall within {$facilities['mall']}m";
        }
        if (isset($facilities['school'])) {
            $descriptions[] = "Schools at {$facilities['school']}m distance";
        }
        if (isset($facilities['hospital'])) {
            $descriptions[] = "Hospital {$facilities['hospital']}m away";
        }
        
        if (empty($descriptions)) {
            return '';
        }
        
        return "Conveniently located with " . implode(', ', $descriptions) . ".";
    }
    
    private function generateInvestmentText(array $data): string
    {
        $texts = [
            "This property offers excellent appreciation potential given its strategic location.",
            "A great investment opportunity with promising ROI in this developing area.",
            "Prices in this locality have shown consistent growth, making this a wise investment.",
            "Perfect for both end-use and investment purposes with high rental demand."
        ];
        
        return $texts[array_rand($texts)];
    }
    
    private function cleanDescription(string $description): string
    {
        // Remove extra spaces
        $description = preg_replace('/\s+/', ' ', $description);
        // Fix double periods
        $description = str_replace('..', '.', $description);
        // Ensure proper spacing after periods
        $description = preg_replace('/\.([A-Z])/', '. $1', $description);
        
        return trim($description);
    }
    
    private function optimizeTitle(string $title): string
    {
        // Capitalize words
        $title = ucwords(strtolower($title));
        // Keep certain words lowercase
        $lowercase = ['in', 'at', 'of', 'and', 'for'];
        foreach ($lowercase as $word) {
            $title = preg_replace('/\b' . ucfirst($word) . '\b/', $word, $title);
        }
        // First word always capitalized
        $title = ucfirst($title);
        
        return $title;
    }
    
    private function formatPrice(float $price): string
    {
        if ($price >= 10000000) {
            return '₹' . round($price / 10000000, 2) . ' Cr';
        } elseif ($price >= 100000) {
            return '₹' . round($price / 100000, 2) . ' L';
        } else {
            return '₹' . number_format($price);
        }
    }
    
    private function getPriceRangeText(float $price): string
    {
        if ($price >= 50000000) {
            return 'Luxury';
        } elseif ($price >= 20000000) {
            return 'Premium';
        } elseif ($price >= 10000000) {
            return 'High Range';
        } elseif ($price >= 5000000) {
            return 'Mid Range';
        } else {
            return 'Affordable';
        }
    }
    
    private function extractKeyPhrases(string $description): array
    {
        // Extract important phrases
        preg_match_all('/\b(stunning|luxury|premium|spacious|beautiful|excellent|prime|exclusive)\s+\w+/', $description, $matches);
        return array_slice($matches[0], 0, 5);
    }
    
    private function calculateSEOScore(string $title): int
    {
        $score = 70; // Base
        
        // Length check (ideal: 50-60 chars)
        $length = strlen($title);
        if ($length >= 40 && $length <= 70) {
            $score += 15;
        }
        
        // Keyword presence
        if (preg_match('/\b(BHK|sqft|property|home)\b/i', $title)) {
            $score += 10;
        }
        
        // Location presence
        if (preg_match('/\b(in|at)\s+\w+/', $title)) {
            $score += 5;
        }
        
        return min($score, 100);
    }
    
    private function saveGeneratedContent(string $type, ?int $propertyId, string $content, array $metadata): int
    {
        try {
            $db = $this->database->getConnection();
            
            $sql = "INSERT INTO ai_generated_content 
                (content_type, property_id, generated_content, prompt_used, language, tone, ai_model)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $type,
                $propertyId,
                $content,
                json_encode($metadata['property_data'] ?? []),
                $metadata['language'] ?? 'en',
                $metadata['tone'] ?? 'professional',
                'local_ml_v1'
            ]);
            
            return $db->lastInsertId();
            
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    // Additional helper methods would be implemented here...
    private function generateTitleAlternatives(array $data, int $count): array { return []; }
    private function suggestAdImages(array $data): array { return []; }
    private function suggestPostingTime(string $platform): string { return '9:00 AM - 11:00 AM'; }
    private function generateInquiryEmail(array $data): string { return ''; }
    private function generatePriceDropEmail(array $data): string { return ''; }
    private function generateNewPropertyEmail(array $data): string { return ''; }
    private function generateFollowUpEmail(array $data): string { return ''; }
    private function personalizeEmail(string $body, array $data): string { return $body; }
    private function calculateSpamScore(string $subject, string $body): int { return 5; }
}
