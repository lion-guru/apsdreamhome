<?php

namespace App\Http\Controllers\Api;

class LandmarksApiController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * Get nearby landmarks for a colony
     * GET /api/landmarks/nearby?colony_id=3&type=school&limit=10
     * GET /api/landmarks/nearby?lat=26.75&lng=83.36&type=hospital&radius=5
     */
    public function nearby()
    {
        $colonyId = intval($_GET['colony_id'] ?? 0);
        $lat = floatval($_GET['lat'] ?? 0);
        $lng = floatval($_GET['lng'] ?? 0);
        $type = $_GET['type'] ?? '';
        $limit = min(intval($_GET['limit'] ?? 20), 50);

        // Option 1: By colony_id — use pre-calculated distances
        if ($colonyId > 0) {
            try {
                $sql = "SELECT l.id, l.name, l.type, l.sub_type, l.address, l.city, l.state, l.pincode,
                               l.latitude, l.longitude, l.rating, l.contact, l.website,
                               cld.distance_km, cld.driving_distance_km, cld.driving_time_min,
                               cld.walking_time_min, cld.transport_options
                        FROM colony_landmark_distances cld
                        JOIN landmarks l ON l.id = cld.landmark_id
                        WHERE cld.colony_id = ? AND l.is_active = 1";
                $params = [$colonyId];

                if ($type) {
                    $sql .= " AND l.type = ?";
                    $params[] = $type;
                }

                $sql .= " ORDER BY cld.distance_km ASC LIMIT ?";
                $params[] = $limit;

                $landmarks = $this->db->fetchAll($sql, $params);

                // Parse transport_options JSON
                foreach ($landmarks as &$lm) {
                    if (!empty($lm['transport_options'])) {
                        $lm['transport_options'] = json_decode($lm['transport_options'], true) ?? [];
                    }
                }

                return $this->jsonResponse([
                    'success' => true,
                    'colony_id' => $colonyId,
                    'count' => count($landmarks),
                    'landmarks' => $landmarks,
                ]);
            } catch (\Exception $e) {
                error_log('[LandmarksApiController::nearby] ' . $e->getMessage());
                return $this->jsonResponse(['success' => false, 'error' => 'Failed to fetch landmarks'], 500);
            }
        }

        // Option 2: By lat/lng — use Haversine distance
        if ($lat != 0 && $lng != 0) {
            try {
                $radiusKm = floatval($_GET['radius'] ?? 10);

                $sql = "SELECT l.id, l.name, l.type, l.sub_type, l.address, l.city, l.state, l.pincode,
                               l.latitude, l.longitude, l.rating, l.contact, l.website,
                               (6371 * ACOS(
                                   COS(RADIANS(?)) * COS(RADIANS(l.latitude)) *
                                   COS(RADIANS(l.longitude) - RADIANS(?)) +
                                   SIN(RADIANS(?)) * SIN(RADIANS(l.latitude))
                               )) AS distance_km
                        FROM landmarks l
                        WHERE l.is_active = 1
                        HAVING distance_km <= ?
                        ORDER BY distance_km ASC
                        LIMIT ?";
                $params = [$lat, $lng, $lat, $radiusKm, $limit];

                if ($type) {
                    // Rebuild with type filter
                    $sql = "SELECT l.id, l.name, l.type, l.sub_type, l.address, l.city, l.state, l.pincode,
                                   l.latitude, l.longitude, l.rating, l.contact, l.website,
                                   (6371 * ACOS(
                                       COS(RADIANS(?)) * COS(RADIANS(l.latitude)) *
                                       COS(RADIANS(l.longitude) - RADIANS(?)) +
                                       SIN(RADIANS(?)) * SIN(RADIANS(l.latitude))
                                   )) AS distance_km
                            FROM landmarks l
                            WHERE l.is_active = 1 AND l.type = ?
                            HAVING distance_km <= ?
                            ORDER BY distance_km ASC
                            LIMIT ?";
                    $params = [$lat, $lng, $lat, $type, $radiusKm, $limit];
                }

                $landmarks = $this->db->fetchAll($sql, $params);

                return $this->jsonResponse([
                    'success' => true,
                    'lat' => $lat,
                    'lng' => $lng,
                    'radius_km' => $radiusKm,
                    'count' => count($landmarks),
                    'landmarks' => $landmarks,
                ]);
            } catch (\Exception $e) {
                error_log('[LandmarksApiController::nearby] ' . $e->getMessage());
                return $this->jsonResponse(['success' => false, 'error' => 'Failed to fetch landmarks'], 500);
            }
        }

        return $this->jsonResponse(['success' => false, 'error' => 'Provide colony_id or lat/lng'], 400);
    }

    /**
     * List all landmarks with filters
     * GET /api/landmarks/list?type=school&city=Gorakhpur&limit=20
     */
    public function list()
    {
        $type = $_GET['type'] ?? '';
        $city = $_GET['city'] ?? '';
        $search = $_GET['q'] ?? '';
        $limit = min(intval($_GET['limit'] ?? 20), 50);

        try {
            $sql = "SELECT id, name, type, sub_type, address, city, state, pincode,
                           latitude, longitude, rating, contact, website, is_featured
                    FROM landmarks
                    WHERE is_active = 1";
            $params = [];

            if ($type) {
                $sql .= " AND type = ?";
                $params[] = $type;
            }
            if ($city) {
                $sql .= " AND city = ?";
                $params[] = $city;
            }
            if ($search) {
                $sql .= " AND (name LIKE ? OR address LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $sql .= " ORDER BY is_featured DESC, rating DESC, name ASC LIMIT ?";
            $params[] = $limit;

            $landmarks = $this->db->fetchAll($sql, $params);

            return $this->jsonResponse([
                'success' => true,
                'count' => count($landmarks),
                'landmarks' => $landmarks,
            ]);
        } catch (\Exception $e) {
            error_log('[LandmarksApiController::list] ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to fetch landmarks'], 500);
        }
    }

    /**
     * Get all landmark types with counts
     * GET /api/landmarks/types
     */
    public function types()
    {
        try {
            $types = $this->db->fetchAll("
                SELECT type, COUNT(*) as count
                FROM landmarks
                WHERE is_active = 1
                GROUP BY type
                ORDER BY count DESC
            ");

            return $this->jsonResponse([
                'success' => true,
                'types' => $types,
            ]);
        } catch (\Exception $e) {
            error_log('[LandmarksApiController::types] ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to fetch types'], 500);
        }
    }

    /**
     * Get landmarks for a specific colony (with pre-calculated distances)
     * GET /api/landmarks/colony/{colonyId}
     */
    public function byColony($colonyId)
    {
        $colonyId = (int)$colonyId;
        if ($colonyId <= 0) {
            return $this->jsonResponse(['success' => false, 'error' => 'Invalid colony ID'], 400);
        }

        try {
            // Get colony info
            $colony = $this->db->fetch(
                "SELECT id, name, latitude, longitude FROM colonies WHERE id = ?",
                [$colonyId]
            );

            if (!$colony) {
                return $this->jsonResponse(['success' => false, 'error' => 'Colony not found'], 404);
            }

            // Get all landmarks grouped by type with distances
            $landmarks = $this->db->fetchAll("
                SELECT l.id, l.name, l.type, l.sub_type, l.address, l.city, l.state, l.pincode,
                       l.latitude, l.longitude, l.rating, l.contact, l.website,
                       cld.distance_km, cld.driving_distance_km, cld.driving_time_min,
                       cld.walking_time_min, cld.transport_options
                FROM colony_landmark_distances cld
                JOIN landmarks l ON l.id = cld.landmark_id
                WHERE cld.colony_id = ? AND l.is_active = 1
                ORDER BY l.type, cld.distance_km ASC
            ", [$colonyId]);

            // Parse transport_options and group by type
            $grouped = [];
            foreach ($landmarks as &$lm) {
                if (!empty($lm['transport_options'])) {
                    $lm['transport_options'] = json_decode($lm['transport_options'], true) ?? [];
                }
                $grouped[$lm['type']][] = $lm;
            }

            // Calculate scores
            $totalLandmarks = count($landmarks);
            $walkScore = min(100, max(0, (int)round($totalLandmarks * 2.5)));
            $transitScore = min(100, max(0, (int)round($totalLandmarks * 2.0)));
            $lifestyleScore = min(100, max(0, (int)round($totalLandmarks * 2.8)));

            return $this->jsonResponse([
                'success' => true,
                'colony' => $colony,
                'total_landmarks' => $totalLandmarks,
                'scores' => [
                    'walk' => $walkScore,
                    'transit' => $transitScore,
                    'lifestyle' => $lifestyleScore,
                ],
                'by_type' => $grouped,
                'landmarks' => $landmarks,
            ]);
        } catch (\Exception $e) {
            error_log('[LandmarksApiController::byColony] ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to fetch landmarks'], 500);
        }
    }
}
