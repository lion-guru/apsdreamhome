<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

class AIAggregatorService
{
    private $db;
    private $geminiKey;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $config = @include __DIR__ . '/../../config/gemini_config.php';
        $this->geminiKey = $config['api_key'] ?? getenv('GEMINI_API_KEY');
        $this->ensureColumnsExist();
    }

    private function ensureColumnsExist()
    {
        try {
            $this->db->getConnection()->query("SELECT source FROM properties LIMIT 1");
        } catch (Exception $e) {
            $sql = "ALTER TABLE properties 
                    ADD COLUMN source ENUM('internal', 'ai_fetched', 'user_submitted') DEFAULT 'internal',
                    ADD COLUMN original_url VARCHAR(500) NULL,
                    ADD COLUMN owner_contact VARCHAR(50) NULL";
            $this->db->getConnection()->exec($sql);
        }
    }

    public function runAggregator($limit = 2)
    {
        $results = ['success' => 0, 'failed' => 0, 'logs' => []];

        // Fetch raw listings (In production, replace this array with a cURL request to external public APIs or RSS feeds)
        $externalListings = $this->fetchExternalListings($limit);

        foreach ($externalListings as $listing) {
            try {
                // Rewrite content using AI to avoid copyright issues
                $aiContent = $this->rewriteWithAI($listing['title'], $listing['description']);

                $sql = "INSERT INTO properties 
                        (title, description, price, location, city, state, type, property_type_id, bedrooms, bathrooms, area, status, source, owner_contact, original_url, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', 'ai_fetched', ?, ?, NOW())";

                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([
                    $aiContent['title'] ?? $listing['title'],
                    $aiContent['description'] ?? $listing['description'],
                    $listing['price'],
                    $listing['location'],
                    $listing['city'],
                    $listing['state'],
                    $listing['type'],
                    $listing['property_type_id'],
                    $listing['bedrooms'],
                    $listing['bathrooms'],
                    $listing['area'],
                    $listing['contact'],
                    $listing['url']
                ]);

                $results['success']++;
                $results['logs'][] = "Added: " . $listing['title'];
            } catch (Exception $e) {
                $results['failed']++;
                $results['logs'][] = "Failed: " . $e->getMessage();
            }
        }
        return $results;
    }

    private function rewriteWithAI($title, $description)
    {
        // Dummy AI rewrite logic (Replace with real Gemini API call when API Key is active)
        return [
            'title' => '🌟 Exclusive: ' . $title,
            'description' => "This is a premium property listing verified by our aggregator system. \n\n" . preg_replace('/[0-9]{10}/', '[HIDDEN]', $description) . "\n\n*Listed via APS AI Aggregator.*"
        ];
    }

    private function fetchExternalListings($limit)
    {
        // Simulated payload of external raw listings (Replace with Scraper Logic)
        $simulated = [
            [
                'title' => 'Modern 3 BHK Flat in Gomti Nagar',
                'description' => 'Spacious 3bhk available for immediate sale. High floor, amazing view. Owner shifting abroad. Call me directly at 9876543210.',
                'price' => 7500000,
                'location' => 'Gomti Nagar',
                'city' => 'Lucknow',
                'state' => 'Uttar Pradesh',
                'type' => 'apartment',
                'property_type_id' => 1,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 1500,
                'contact' => '+91 9876543210',
                'url' => 'https://external-site.com/prop/1'
            ],
            [
                'title' => 'Prime Commercial Shop in Civil Lines',
                'description' => 'Best shop for investment. High footfall area. Urgent sale by owner. Direct buyers only contact 8888888888.',
                'price' => 4500000,
                'location' => 'Civil Lines',
                'city' => 'Gorakhpur',
                'state' => 'Uttar Pradesh',
                'type' => 'commercial',
                'property_type_id' => 4,
                'bedrooms' => 0,
                'bathrooms' => 1,
                'area' => 400,
                'contact' => '+91 8888888888',
                'url' => 'https://external-site.com/prop/2'
            ]
        ];

        // Randomize to simulate fresh data fetching
        shuffle($simulated);
        return array_slice($simulated, 0, $limit);
    }
}
