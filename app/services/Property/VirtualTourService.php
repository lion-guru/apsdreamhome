<?php
namespace App\Services\Property;

/**
 * Virtual Tour Service
 * Advanced 360-degree virtual tour management
 */
class VirtualTourService
{
    private $database;
    
    public function __construct()
    {
        $this->database = \App\Core\Database\Database::getInstance();
    }
    
    /**
     * Create virtual tour for property
     */
    public function createVirtualTour($propertyId, $tourData)
    {
        try {
            $stmt = $this->database->prepare("
                INSERT INTO virtual_tours (property_id, tour_type, tour_url, thumbnail_url, 
                created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $propertyId,
                $tourData['tour_type'] ?? '360',
                $tourData['tour_url'],
                $tourData['thumbnail_url'] ?? ''
            ]);
            
            return $this->database->lastInsertId();
            
        } catch (Exception $e) {
            throw new Exception("Failed to create virtual tour: " . $e->getMessage());
        }
    }
    
    /**
     * Get virtual tour for property
     */
    public function getPropertyVirtualTour($propertyId)
    {
        try {
            $stmt = $this->database->prepare("
                SELECT * FROM virtual_tours 
                WHERE property_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            
            $stmt->execute([$propertyId]);
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Generate virtual tour embed code
     */
    public function generateEmbedCode($tourData)
    {
        $tourType = $tourData['tour_type'] ?? '360';
        $tourUrl = $tourData['tour_url'];
        
        switch ($tourType) {
            case '360':
                return "
                <div class='virtual-tour-360'>
                    <iframe src='{$tourUrl}' width='100%' height='600' frameborder='0' allowfullscreen>
                    </iframe>
                </div>";
                
            case 'video':
                return "
                <div class='virtual-tour-video'>
                    <video controls width='100%' height='600'>
                        <source src='{$tourUrl}' type='video/mp4'>
                        Your browser does not support the video tag.
                    </video>
                </div>";
                
            case 'matterport':
                return "
                <div class='virtual-tour-matterport'>
                    <iframe src='{$tourUrl}' width='100%' height='600' frameborder='0' allowfullscreen>
                    </iframe>
                </div>";
                
            default:
                return "<div class='tour-placeholder'>Virtual tour not available</div>";
        }
    }
    
    /**
     * Create virtual tour hotspots
     */
    public function createTourHotspots($tourId, $hotspots)
    {
        try {
            foreach ($hotspots as $hotspot) {
                $stmt = $this->database->prepare("
                    INSERT INTO tour_hotspots (tour_id, title, description, x_position, y_position, 
                    hotspot_type, content) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $tourId,
                    $hotspot['title'],
                    $hotspot['description'],
                    $hotspot['x_position'],
                    $hotspot['y_position'],
                    $hotspot['hotspot_type'] ?? 'info',
                    $hotspot['content'] ?? ''
                ]);
            }
            
            return true;
            
        } catch (Exception $e) {
            throw new Exception("Failed to create hotspots: " . $e->getMessage());
        }
    }
}
