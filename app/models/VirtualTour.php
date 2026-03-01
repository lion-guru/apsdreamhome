<?php

namespace App\Models;

use App\Core\UnifiedModel;

class VirtualTour extends UnifiedModel
{
    public static $table = 'virtual_tours';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'property_id',
        'tour_title',
        'tour_description',
        'tour_type',
        'status',
        'is_featured',
        'duration_minutes',
        'view_count',
        'like_count',
        'share_count',
        'completion_rate',
        'seo_title',
        'seo_description',
        'tour_settings',
        'created_by',
        'published_at',
        'created_at',
        'updated_at'
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_PROCESSING = 'processing';

    const TOUR_TYPE_360 = '360_tour';
    const TOUR_TYPE_VIDEO = 'video_tour';
    const TOUR_TYPE_INTERACTIVE = 'interactive_tour';
    const TOUR_TYPE_FLOOR_PLAN = 'floor_plan_tour';

    /**
     * Create a new virtual tour
     */
    public function createTour(array $tourData): array
    {
        $tourRecord = [
            'property_id' => $tourData['property_id'],
            'tour_title' => $tourData['tour_title'],
            'tour_description' => $tourData['tour_description'] ?? null,
            'tour_type' => $tourData['tour_type'] ?? self::TOUR_TYPE_360,
            'status' => self::STATUS_DRAFT,
            'is_featured' => $tourData['is_featured'] ?? 0,
            'duration_minutes' => $tourData['duration_minutes'] ?? null,
            'seo_title' => $tourData['seo_title'] ?? null,
            'seo_description' => $tourData['seo_description'] ?? null,
            'tour_settings' => json_encode($tourData['tour_settings'] ?? []),
            'created_by' => $tourData['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $tourId = $this->insert($tourRecord);

        return [
            'success' => true,
            'tour_id' => $tourId,
            'message' => 'Virtual tour created successfully'
        ];
    }

    /**
     * Get tour with scenes and hotspots
     */
    public function getTourDetails(int $tourId): ?array
    {
        $tour = $this->find($tourId);
        if (!$tour) {
            return null;
        }

        $tour = $tour->toArray();

        // Get scenes
        $scenes = $this->getTourScenes($tourId);

        // Get assets
        $assets = $this->getTourAssets($tourId);

        // Get comments
        $comments = $this->getTourComments($tourId);

        $tour['scenes'] = $scenes;
        $tour['assets'] = $assets;
        $tour['comments'] = $comments;
        $tour['tour_settings'] = json_decode($tour['tour_settings'], true);

        return $tour;
    }

    /**
     * Get tour scenes with hotspots
     */
    private function getTourScenes(int $tourId): array
    {
        $scenes = $this->query(
            "SELECT * FROM tour_scenes WHERE tour_id = ? ORDER BY scene_order ASC, created_at ASC",
            [$tourId]
        )->fetchAll();

        foreach ($scenes as &$scene) {
            // Get hotspots for this scene
            $scene['hotspots'] = $this->query(
                "SELECT * FROM tour_hotspots WHERE scene_id = ? AND is_active = 1 ORDER BY hotspot_title ASC",
                [$scene['id']]
            )->fetchAll();

            // Decode JSON fields
            $scene['initial_view'] = json_decode($scene['initial_view'], true);
            $scene['hotspots_data'] = json_decode($scene['hotspots'], true);
        }

        return $scenes;
    }

    /**
     * Get tour assets
     */
    private function getTourAssets(int $tourId): array
    {
        $assets = $this->query(
            "SELECT * FROM tour_assets WHERE tour_id = ? ORDER BY sort_order ASC, created_at ASC",
            [$tourId]
        )->fetchAll();

        foreach ($assets as &$asset) {
            $asset['metadata'] = json_decode($asset['metadata'], true);
        }

        return $assets;
    }

    /**
     * Get tour comments
     */
    private function getTourComments(int $tourId): array
    {
        $comments = $this->query(
            "SELECT tc.*, u.first_name, u.last_name
             FROM tour_comments tc
             LEFT JOIN users u ON tc.user_id = u.id AND tc.user_type = 'customer'
             WHERE tc.tour_id = ? AND tc.is_approved = 1
             ORDER BY tc.created_at DESC",
            [$tourId]
        )->fetchAll();

        return $comments;
    }

    /**
     * Get tours for a property
     */
    public function getPropertyTours(int $propertyId): array
    {
        return $this->query(
            "SELECT * FROM virtual_tours WHERE property_id = ? AND status = ? ORDER BY is_featured DESC, created_at DESC",
            [$propertyId, self::STATUS_PUBLISHED]
        )->fetchAll();
    }

    /**
     * Publish tour
     */
    public function publishTour(int $tourId): array
    {
        $tour = $this->find($tourId);
        if (!$tour) {
            return ['success' => false, 'message' => 'Tour not found'];
        }

        $this->update($tourId, [
            'status' => self::STATUS_PUBLISHED,
            'published_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => 'Tour published successfully'
        ];
    }

    /**
     * Add scene to tour
     */
    public function addScene(array $sceneData): array
    {
        $sceneRecord = [
            'tour_id' => $sceneData['tour_id'],
            'scene_title' => $sceneData['scene_title'],
            'scene_description' => $sceneData['scene_description'] ?? null,
            'scene_order' => $sceneData['scene_order'] ?? 0,
            'scene_type' => $sceneData['scene_type'] ?? 'panorama',
            'image_path' => $sceneData['image_path'],
            'thumbnail_path' => $sceneData['thumbnail_path'] ?? null,
            'north_offset' => $sceneData['north_offset'] ?? 0,
            'initial_view' => json_encode($sceneData['initial_view'] ?? []),
            'audio_path' => $sceneData['audio_path'] ?? null,
            'transition_type' => $sceneData['transition_type'] ?? 'fade',
            'transition_duration' => $sceneData['transition_duration'] ?? 1000,
            'hotspots' => json_encode($sceneData['hotspots'] ?? []),
            'is_start_scene' => $sceneData['is_start_scene'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $sceneId = $this->insertInto('tour_scenes', $sceneRecord);

        return [
            'success' => true,
            'scene_id' => $sceneId,
            'message' => 'Scene added successfully'
        ];
    }

    /**
     * Add hotspot to scene
     */
    public function addHotspot(array $hotspotData): array
    {
        $hotspotRecord = [
            'scene_id' => $hotspotData['scene_id'],
            'hotspot_type' => $hotspotData['hotspot_type'] ?? 'info',
            'hotspot_title' => $hotspotData['hotspot_title'],
            'hotspot_description' => $hotspotData['hotspot_description'] ?? null,
            'position_x' => $hotspotData['position_x'],
            'position_y' => $hotspotData['position_y'],
            'position_z' => $hotspotData['position_z'] ?? 0,
            'target_scene_id' => $hotspotData['target_scene_id'] ?? null,
            'content_type' => $hotspotData['content_type'] ?? 'text',
            'content_data' => json_encode($hotspotData['content_data'] ?? []),
            'icon_type' => $hotspotData['icon_type'] ?? 'info',
            'icon_color' => $hotspotData['icon_color'] ?? '#007bff',
            'is_active' => $hotspotData['is_active'] ?? 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $hotspotId = $this->insertInto('tour_hotspots', $hotspotRecord);

        return [
            'success' => true,
            'hotspot_id' => $hotspotId,
            'message' => 'Hotspot added successfully'
        ];
    }

    /**
     * Track tour analytics
     */
    public function trackTourEvent(array $eventData): void
    {
        $eventRecord = [
            'tour_id' => $eventData['tour_id'],
            'user_id' => $eventData['user_id'] ?? null,
            'user_type' => $eventData['user_type'] ?? 'guest',
            'session_id' => $eventData['session_id'] ?? session_id(),
            'event_type' => $eventData['event_type'],
            'event_data' => json_encode($eventData['event_data'] ?? []),
            'scene_id' => $eventData['scene_id'] ?? null,
            'hotspot_id' => $eventData['hotspot_id'] ?? null,
            'duration_seconds' => $eventData['duration_seconds'] ?? null,
            'device_type' => $this->detectDeviceType(),
            'browser_info' => json_encode($this->getBrowserInfo()),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'referrer_url' => $_SERVER['HTTP_REFERER'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->insertInto('tour_analytics', $eventRecord);

        // Update tour statistics
        $this->updateTourStats($eventData['tour_id'], $eventData['event_type']);
    }

    /**
     * Update tour statistics
     */
    private function updateTourStats(int $tourId, string $eventType): void
    {
        $updateField = '';
        switch ($eventType) {
            case 'view':
                $updateField = 'view_count = view_count + 1';
                break;
            case 'like':
                $updateField = 'like_count = like_count + 1';
                break;
            case 'share':
                $updateField = 'share_count = share_count + 1';
                break;
        }

        if ($updateField) {
            $this->query(
                "UPDATE virtual_tours SET {$updateField}, updated_at = NOW() WHERE id = ?",
                [$tourId]
            );
        }
    }

    /**
     * Get tour analytics
     */
    public function getTourAnalytics(int $tourId, string $period = '30 days'): array
    {
        $startDate = date('Y-m-d', strtotime("-{$period}"));

        $analytics = $this->query(
            "SELECT
                COUNT(CASE WHEN event_type = 'view' THEN 1 END) as total_views,
                COUNT(CASE WHEN event_type = 'start' THEN 1 END) as total_starts,
                COUNT(CASE WHEN event_type = 'complete' THEN 1 END) as total_completions,
                COUNT(CASE WHEN event_type = 'hotspot_click' THEN 1 END) as hotspot_clicks,
                COUNT(DISTINCT session_id) as unique_visitors,
                AVG(duration_seconds) as avg_duration
             FROM tour_analytics
             WHERE tour_id = ? AND created_at >= ?",
            [$tourId, $startDate]
        )->fetch();

        if ($analytics['total_starts'] > 0) {
            $analytics['completion_rate'] = round(($analytics['total_completions'] / $analytics['total_starts']) * 100, 2);
        } else {
            $analytics['completion_rate'] = 0;
        }

        return $analytics ?: [
            'total_views' => 0,
            'total_starts' => 0,
            'total_completions' => 0,
            'hotspot_clicks' => 0,
            'unique_visitors' => 0,
            'avg_duration' => 0,
            'completion_rate' => 0
        ];
    }

    /**
     * Search tours
     */
    public function searchTours(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $query = "SELECT vt.*, p.title as property_title, p.location, p.city
                  FROM virtual_tours vt
                  LEFT JOIN properties p ON vt.property_id = p.id
                  WHERE vt.status = ?";

        $params = [self::STATUS_PUBLISHED];

        if (!empty($filters['property_type'])) {
            $query .= " AND p.property_type_id = ?";
            $params[] = $filters['property_type'];
        }

        if (!empty($filters['city'])) {
            $query .= " AND p.city LIKE ?";
            $params[] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['tour_type'])) {
            $query .= " AND vt.tour_type = ?";
            $params[] = $filters['tour_type'];
        }

        if (!empty($filters['is_featured'])) {
            $query .= " AND vt.is_featured = ?";
            $params[] = $filters['is_featured'];
        }

        $query .= " ORDER BY vt.is_featured DESC, vt.view_count DESC, vt.created_at DESC";
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->query($query, $params)->fetchAll();
    }

    /**
     * Detect device type
     */
    private function detectDeviceType(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (preg_match('/mobile/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Get browser information
     */
    private function getBrowserInfo(): array
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return [
            'user_agent' => $userAgent,
            'browser' => $this->detectBrowser($userAgent),
            'platform' => $this->detectPlatform($userAgent)
        ];
    }

    /**
     * Detect browser
     */
    private function detectBrowser(string $userAgent): string
    {
        if (preg_match('/chrome/i', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/edge/i', $userAgent)) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Detect platform
     */
    private function detectPlatform(string $userAgent): string
    {
        if (preg_match('/windows/i', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/mac/i', $userAgent)) {
            return 'Mac';
        } elseif (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/ios/i', $userAgent)) {
            return 'iOS';
        } else {
            return 'Unknown';
        }
    }
}
