<?php

namespace App\Services\AI;

use PDO;
use Exception;

class PropertyImageTaggingService
{
    /** @var array */
    protected $tagCategories = [
        'room_types' => [
            'bedroom', 'master_bedroom', 'guest_room', 'kids_room',
            'living_room', 'drawing_room', 'family_room', 'study_room',
            'dining_room', 'kitchen', 'modular_kitchen', 'pantry',
            'bathroom', 'attached_bathroom', 'guest_bathroom', 'powder_room',
            'balcony', 'terrace', 'roof_terrace', 'garden_area',
            'pooja_room', 'store_room', 'utility_room', 'servant_room',
        ],
        'amenities' => [
            'swimming_pool', 'gym', 'clubhouse', 'community_hall',
            'tennis_court', 'badminton_court', 'basketball_court',
            'cricket_pitch', 'jogging_track', 'walking_path',
            'childrens_play_area', 'senior_citizen_park', 'yoga_deck',
            'amphitheatre', 'party_lawn', 'gazebo', 'fountain',
            'water_feature', 'landscaped_garden', 'herbal_garden',
            'organic_farming', 'rainwater_harvesting', 'solar_panels',
            'ev_charging', 'cctv', 'intercom', 'video_door_phone',
            'fire_safety', 'power_backup', 'water_treatment',
            'sewage_treatment', 'garbage_management', 'parking',
            'visitor_parking', 'basement_parking', 'stilt_parking',
        ],
        'interior_features' => [
            'marble_flooring', 'vitrified_tiles', 'wooden_flooring',
            'granite_countertop', 'quartz_countertop', 'modular_wardrobe',
            'false_ceiling', 'cove_lighting', 'chandelier',
            'ac_installed', 'vrf_ac', 'split_ac', 'central_ac',
            'chimney', 'hob', 'built_in_oven', 'dishwasher',
            'water_purifier', 'geyser', 'smart_home', 'video_door_phone',
            'digital_lock', 'motion_sensor_lights', 'curtain_rails',
            'blinds', 'mosquito_net', 'exhaust_fan', 'fresh_air_system',
        ],
        'exterior_features' => [
            'elevation_design', 'compound_wall', 'main_gate',
            'security_cabin', 'landscape_lighting', 'pathway_lights',
            'driveway', 'paved_pathway', 'green_buffer', 'tree_avenue',
            'water_body', 'rain_garden', 'permeable_paving',
        ],
        'views' => [
            'park_view', 'lake_view', 'city_view', 'mountain_view',
            'garden_view', 'pool_view', 'road_view', 'corner_plot',
            'east_facing', 'west_facing', 'north_facing', 'south_facing',
            'northeast_corner', 'southeast_corner', 'northwest_corner', 'southwest_corner',
        ],
        'construction_stage' => [
            'foundation', 'plinth', 'ground_floor', 'first_floor',
            'second_floor', 'third_floor', 'roof_slab', 'brickwork',
            'plastering', 'flooring', 'painting', 'finishing',
            'ready_to_move', 'under_construction', 'pre_launch',
        ],
    ];

    /** @var array */
    protected $aiModels = [
        'google_vision' => 'Google Cloud Vision API',
        'azure_custom_vision' => 'Azure Custom Vision',
        'aws_rekognition' => 'AWS Rekognition',
        'local_model' => 'Local TensorFlow/PyTorch Model',
    ];

    protected $db;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                $pdo = null;
            }
        }
        if (!$pdo instanceof PDO) {
            $pdo = null;
        }
        $this->db = $pdo;
    }

    /**
     * Analyze property image and return tags
     * 
     * @param array $data {
     *     image_url: string,
     *     image_base64: string,
     *     property_id: int,
     *     uploaded_by: int,
     * }
     * @return array
     */
    public function analyzeImage(array $data): array
    {
        $imageUrl = $data['image_url'] ?? '';
        $imageBase64 = $data['image_base64'] ?? '';
        $propertyId = (int)($data['property_id'] ?? 0);
        $uploadedBy = (int)($data['uploaded_by'] ?? 0);

        if (!$imageUrl && !$imageBase64) {
            return ['success' => false, 'error' => 'Image URL or base64 required'];
        }

        // Try to use AI service
        $tags = $this->analyzeWithAI($imageUrl, $imageBase64);

        if (empty($tags)) {
            // Fallback: basic analysis based on file name/metadata
            $tags = $this->basicAnalysis($imageUrl, $imageBase64);
        }

        // Categorize tags
        $categorized = $this->categorizeTags($tags);

        // Save to database if property_id provided
        if ($propertyId > 0 && $this->db) {
            $this->saveTags($propertyId, $categorized, $uploadedBy, $imageUrl);
        }

        return [
            'success' => true,
            'tags' => $tags,
            'categorized' => $categorized,
            'confidence' => $this->calculateConfidence($tags),
        ];
    }

    /**
     * Analyze with AI service (Google Vision, Azure, AWS, or local)
     */
    protected function analyzeWithAI(string $imageUrl, string $imageBase64): array
    {
        // Try Google Cloud Vision if credentials available
        if ($this->hasGoogleVisionCredentials()) {
            return $this->analyzeWithGoogleVision($imageUrl, $imageBase64);
        }

        // Try Azure Custom Vision
        if ($this->hasAzureVisionCredentials()) {
            return $this->analyzeWithAzureVision($imageUrl, $imageBase64);
        }

        // Try AWS Rekognition
        if ($this->hasAwsCredentials()) {
            return $this->analyzeWithAwsRekognition($imageUrl, $imageBase64);
        }

        // Try local model
        if ($this->hasLocalModel()) {
            return $this->analyzeWithLocalModel($imageUrl, $imageBase64);
        }

        return [];
    }

    /**
     * Google Cloud Vision API analysis
     */
    protected function analyzeWithGoogleVision(string $imageUrl, string $imageBase64): array
    {
        // Placeholder for Google Cloud Vision integration
        // Requires: composer require google/cloud-vision
        return [];
    }

    /**
     * Azure Custom Vision analysis
     */
    protected function analyzeWithAzureVision(string $imageUrl, string $imageBase64): array
    {
        // Placeholder for Azure Custom Vision integration
        return [];
    }

    /**
     * AWS Rekognition analysis
     */
    protected function analyzeWithAwsRekognition(string $imageUrl, string $imageBase64): array
    {
        // Placeholder for AWS Rekognition integration
        return [];
    }

    /**
     * Local TensorFlow/PyTorch model analysis
     */
    protected function analyzeWithLocalModel(string $imageUrl, string $imageBase64): array
    {
        // Placeholder for local model integration
        // Could use Python script via exec() or PHP-ML
        return [];
    }

    /**
     * Basic analysis fallback (filename, EXIF, etc.)
     */
    protected function basicAnalysis(string $imageUrl, string $imageBase64): array
    {
        $tags = [];
        
        // Extract from filename
        $filename = basename(parse_url($imageUrl, PHP_URL_PATH) ?? '');
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $filename = strtolower($filename);
        
        // Common patterns in filenames
        $patterns = [
            'bedroom' => ['bedroom', 'bed', 'master_bed', 'br'],
            'living_room' => ['living', 'drawing', 'hall', 'lr'],
            'kitchen' => ['kitchen', 'modular_kitchen', 'kit'],
            'bathroom' => ['bathroom', 'bath', 'toilet', 'wc', 'br'],
            'balcony' => ['balcony', 'balc', 'terrace'],
            'garden' => ['garden', 'lawn', 'landscape', 'green'],
            'pool' => ['pool', 'swimming_pool', 'swim'],
            'elevation' => ['elevation', 'exterior', 'front', 'facade'],
            'floor_plan' => ['floor_plan', 'layout', 'plan', 'map'],
            'amenities' => ['clubhouse', 'gym', 'pool', 'park', 'court'],
            'construction' => ['construction', 'progress', 'wip', 'foundation', 'slab'],
        ];

        foreach ($patterns as $tag => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($filename, $keyword) !== false) {
                    $tags[] = $tag;
                    break;
                }
            }
        }

        return $tags;
    }

    /**
     * Categorize tags into groups
     */
    protected function categorizeTags(array $tags): array
    {
        $categorized = [];
        
        foreach ($this->tagCategories as $category => $categoryTags) {
            $matches = array_intersect($tags, $categoryTags);
            if (!empty($matches)) {
                $categorized[$category] = array_values($matches);
            }
        }

        // Add uncategorized tags
        $allKnownTags = array_merge(...array_values($this->tagCategories));
        $unknownTags = array_diff($tags, $allKnownTags);
        if (!empty($unknownTags)) {
            $categorized['other'] = array_values($unknownTags);
        }

        return $categorized;
    }

    /**
     * Calculate overall confidence score
     */
    protected function calculateConfidence(array $tags): float
    {
        if (empty($tags)) return 0.0;
        
        $knownCount = 0;
        $allKnownTags = array_merge(...array_values($this->tagCategories));
        
        foreach ($tags as $tag) {
            if (in_array($tag, $allKnownTags)) {
                $knownCount++;
            }
        }
        
        return round($knownCount / count($tags), 2);
    }

    /**
     * Save tags to database
     */
    protected function saveTags(int $propertyId, array $categorized, int $uploadedBy, string $imageUrl): void
    {
        if (!$this->db) return;

        try {
            // Save main image record
            $stmt = $this->db->prepare("
                INSERT INTO property_images 
                (property_id, image_url, uploaded_by, is_primary, created_at)
                VALUES (?, ?, ?, 0, NOW())
            ");
            $stmt->execute([$propertyId, $imageUrl, $uploadedBy]);
            $imageId = (int)$this->db->lastInsertId();

            // Save tags
            $tagStmt = $this->db->prepare("
                INSERT INTO property_image_tags 
                (image_id, tag_name, category, confidence, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            foreach ($categorized as $category => $categoryTags) {
                foreach ($categoryTags as $tag) {
                    $tagStmt->execute([
                        $imageId,
                        $tag,
                        $category,
                        $this->calculateConfidence([$tag]),
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log('[PropertyImageTaggingService::saveTags] ' . $e->getMessage());
        }
    }

    /**
     * Get tags for a property
     */
    public function getPropertyTags(int $propertyId): array
    {
        if (!$this->db) return [];

        try {
            $stmt = $this->db->prepare("
                SELECT pit.tag_name, pit.category, pit.confidence, pi.image_url
                FROM property_image_tags pit
                JOIN property_images pi ON pit.image_id = pi.id
                WHERE pi.property_id = ?
                ORDER BY pi.created_at DESC, pit.confidence DESC
            ");
            $stmt->execute([$propertyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Search properties by tags
     */
    public function searchByTags(array $tags, int $limit = 20): array
    {
        if (!$this->db || empty($tags)) return [];

        try {
            $placeholders = implode(',', array_fill(0, count($tags), '?'));
            $sql = "
                SELECT DISTINCT pi.property_id, p.title, p.price, p.city, pi.image_url,
                       GROUP_CONCAT(pit.tag_name) as tags
                FROM property_images pi
                JOIN properties p ON pi.property_id = p.id
                JOIN property_image_tags pit ON pi.id = pit.image_id
                WHERE pit.tag_name IN ($placeholders)
                GROUP BY pi.property_id
                HAVING COUNT(DISTINCT pit.tag_name) >= ?
                ORDER BY COUNT(DISTINCT pit.tag_name) DESC, p.created_at DESC
                LIMIT ?
            ";

            $params = array_merge($tags, [count($tags), $limit]);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    // Credential check methods
    protected function hasGoogleVisionCredentials(): bool
    {
        return !empty($_ENV['GOOGLE_VISION_CREDENTIALS'] ?? $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] ?? '');
    }

    protected function hasAzureVisionCredentials(): bool
    {
        return !empty($_ENV['AZURE_VISION_KEY'] ?? $_ENV['AZURE_VISION_ENDPOINT'] ?? '');
    }

    protected function hasAwsCredentials(): bool
    {
        return !empty($_ENV['AWS_ACCESS_KEY_ID'] ?? $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '');
    }

    protected function hasLocalModel(): bool
    {
        return file_exists(__DIR__ . '/../../../models/property_classifier') || 
               file_exists(__DIR__ . '/../../../python/property_classifier.py');
    }
}