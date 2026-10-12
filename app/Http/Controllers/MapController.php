<?php

namespace App\Http\Controllers;

use App\Core\Security;
use Exception;

/**
 * MapController
 * Handles map-based property browsing and location features
 */
class MapController extends BaseController
{
    /**
     * Show properties map page
     */
    public function index()
    {
        try {
            // Get properties with location data
            $properties = $this->db->table('properties')
                ->where('status', 'active')
                ->whereNotNull('location')
                ->select('id', 'title', 'location', 'price', 'type', 'bedrooms', 'area', 'images')
                ->get();

            $this->render('map/index', [
                'page_title' => 'Property Map - APS Dream Home',
                'page_description' => 'Browse properties on an interactive map',
                'properties' => $properties
            ], 'layouts/base');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load map: ' . $e->getMessage());
            $this->redirect('/properties');
        }
    }

    /**
     * Get properties data for map markers (AJAX)
     */
    public function getPropertiesData()
    {
        try {
            $properties = $this->db->table('properties')
                ->where('status', 'active')
                ->whereNotNull('location')
                ->select('id', 'title', 'location', 'price', 'type', 'bedrooms', 'area', 'images', 'description')
                ->get();

            $markers = [];
            foreach ($properties as $property) {
                // Parse images
                $images = [];
                if (!empty($property['images'])) {
                    $images = json_decode($property['images'], true) ?? [];
                }

                $markers[] = [
                    'id' => $property['id'],
                    'title' => htmlspecialchars($property['title']),
                    'location' => htmlspecialchars($property['location']),
                    'price' => (float)$property['price'],
                    'type' => htmlspecialchars($property['type']),
                    'bedrooms' => (int)$property['bedrooms'],
                    'area' => (float)$property['area'],
                    'image' => !empty($images) ? $images[0] : '/assets/images/property-placeholder.jpg',
                    'url' => '/properties/' . $property['id'],
                    'description' => substr(strip_tags($property['description'] ?? ''), 0, 100) . '...'
                ];
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'markers' => $markers
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Search properties by location bounds (AJAX)
     */
    public function searchByBounds()
    {
        try {
            $northEastLat = (float)(Security::sanitize($_GET['ne_lat']) ?? 0);
            $northEastLng = (float)(Security::sanitize($_GET['ne_lng']) ?? 0);
            $southWestLat = (float)(Security::sanitize($_GET['sw_lat']) ?? 0);
            $southWestLng = (float)(Security::sanitize($_GET['sw_lng']) ?? 0);

            if (!$northEastLat || !$northEastLng || !$southWestLat || !$southWestLng) {
                throw new Exception('Invalid map bounds');
            }

            // For now, return all properties since we don't have coordinates
            // In a real implementation, you'd filter by lat/lng bounds
            $properties = $this->db->table('properties')
                ->where('status', 'active')
                ->select('id', 'title', 'location', 'price', 'type', 'bedrooms', 'area', 'images')
                ->get();

            $markers = [];
            foreach ($properties as $property) {
                $images = [];
                if (!empty($property['images'])) {
                    $images = json_decode($property['images'], true) ?? [];
                }

                $markers[] = [
                    'id' => $property['id'],
                    'title' => htmlspecialchars($property['title']),
                    'location' => htmlspecialchars($property['location']),
                    'price' => (float)$property['price'],
                    'type' => htmlspecialchars($property['type']),
                    'bedrooms' => (int)$property['bedrooms'],
                    'area' => (float)$property['area'],
                    'image' => !empty($images) ? $images[0] : '/assets/images/property-placeholder.jpg',
                    'url' => '/properties/' . $property['id']
                ];
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'markers' => $markers
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Colony Plot Map — Publicly viewable Leaflet map
     * GET /colony/{slug}/map
     */
    public function colonyPlotMap($slug)
    {
        try {
            $colony = $this->db->fetchOne("SELECT c.*, d.name as district_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id WHERE c.slug = ?", [$slug]);
            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/properties');
                return;
            }
            $plots = $this->db->fetchAll(
                "SELECT id, plot_number, block, area_sqft, width_ft, length_ft, status, price_per_sqft, total_price, corner_plot, park_facing, road_width_ft, gata_number
                 FROM plots WHERE colony_id = ? ORDER BY block, plot_number",
                [$colony['id']]
            );
            $this->render('pages/colony_plot_map', [
                'page_title' => $colony['name'] . ' — Plot Map',
                'colony' => $colony,
                'plots' => $plots,
                'price_revision' => \App\Http\Controllers\Admin\PriceRevisionController::activeForColony($this->db, (int)$colony['id']),
            ], 'layouts/base');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load map');
            $this->redirect('/properties');
        }
    }

    /**
     * Colony GeoJSON API — Publicly accessible plot data
     * GET /api/colony/{id}/map/geojson
     */
    public function colonyGeoJson($id)
    {
        header('Content-Type: application/json');
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$id]);
            if (!$colony) {
                echo json_encode(['type' => 'FeatureCollection', 'features' => []]);
                return;
            }
            $plots = $this->db->fetchAll(
                "SELECT id, plot_number, block, area_sqft, width_ft, length_ft, status, price_per_sqft, total_price, corner_plot, park_facing, road_width_ft, gata_number, facing
                 FROM plots WHERE colony_id = ? ORDER BY block, plot_number",
                [$id]
            );
            $features = [];
            $centerLat = $colony['latitude'] ? (float)$colony['latitude'] : 26.76;
            $centerLng = $colony['longitude'] ? (float)$colony['longitude'] : 83.37;
            $blocksMap = [];
            foreach ($plots as $p) {
                $block = $p['block'] ?? 'A';
                if (!isset($blocksMap[$block])) {
                    $blocksMap[$block] = ['plots' => [], 'y' => count($blocksMap)];
                }
                $blocksMap[$block]['plots'][] = $p;
            }
            foreach ($blocksMap as $block => $bData) {
                $x = 0;
                foreach ($bData['plots'] as $p) {
                    $w = max((float)($p['width_ft'] ?? 30), 20);
                    $l = max((float)($p['length_ft'] ?? 50), 30);
                    $scale = 0.000008;
                    $x1 = $centerLng + ($x * $scale * 1.1);
                    $y1 = $centerLat + ($bData['y'] * $scale * 1.1);
                    $x2 = $x1 + $w * $scale;
                    $y2 = $y1 + $l * $scale;
                    $statusColors = [
                        'available' => '#22c55e', 'booked' => '#eab308', 'sold' => '#ef4444',
                        'hold' => '#6b7280', 'reserved' => '#f97316',
                    ];
                    $features[] = [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Polygon', 'coordinates' => [[
                            [$x1, $y1], [$x2, $y1], [$x2, $y2], [$x1, $y2], [$x1, $y1]
                        ]]],
                        'properties' => [
                            'plot_id' => $p['id'],
                            'plot_number' => $p['plot_number'],
                            'block' => $p['block'],
                            'area_sqft' => $p['area_sqft'],
                            'width_ft' => $p['width_ft'],
                            'length_ft' => $p['length_ft'],
                            'status' => $p['status'],
                            'price_per_sqft' => $p['price_per_sqft'],
                            'total_price' => $p['total_price'],
                            'corner_plot' => (bool)$p['corner_plot'],
                            'park_facing' => (bool)$p['park_facing'],
                            'road_facing' => !empty($p['road_width_ft']) && (float)$p['road_width_ft'] > 0,
                            'road_width_ft' => $p['road_width_ft'],
                            'gata_number' => $p['gata_number'],
                            'facing' => $p['facing'] ?? '',
                            'marker_color' => $statusColors[$p['status']] ?? '#94a3b8',
                        ],
                    ];
                    $x++;
                }
            }
            echo json_encode(['type' => 'FeatureCollection', 'features' => $features]);
        } catch (Exception $e) {
            echo json_encode(['type' => 'FeatureCollection', 'features' => []]);
        }
    }

    /**
     * Shareable Live Layout — mobile-optimized public map with associate ref.
     * GET /colony/{id}/live-layout?ref=ASSO123  (id or slug accepted)
     * The global aps_ref cookie net persists ?ref= automatically.
     */
    public function liveLayout($id)
    {
        try {
            if (ctype_digit((string)$id)) {
                $colony = $this->db->fetchOne("SELECT c.*, d.name as district_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id WHERE c.id = ?", [(int)$id]);
            } else {
                $colony = $this->db->fetchOne("SELECT c.*, d.name as district_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id WHERE c.slug = ?", [(string)$id]);
            }
            if (!$colony) {
                $this->setFlash('error', 'Colony not found');
                $this->redirect('/properties');
                return;
            }
            $plots = $this->db->fetchAll(
                "SELECT id, plot_number, block, area_sqft, width_ft, length_ft, status, price_per_sqft, total_price, corner_plot, park_facing, road_width_ft, gata_number
                 FROM plots WHERE colony_id = ? ORDER BY block, plot_number",
                [$colony['id']]
            );
            $refCode = trim((string)($_GET['ref'] ?? $_COOKIE['aps_ref'] ?? $_SESSION['aps_ref'] ?? ''));
            $refCode = substr(preg_replace('/[^A-Za-z0-9\-_]/', '', $refCode), 0, 50);
            $this->render('pages/colony_plot_map', [
                'page_title' => $colony['name'] . ' — Live Layout',
                'colony' => $colony,
                'plots' => $plots,
                'ref_code' => $refCode,
                'is_live_layout' => true,
                'price_revision' => \App\Http\Controllers\Admin\PriceRevisionController::activeForColony($this->db, (int)$colony['id']),
            ], 'layouts/base');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load map');
            $this->redirect('/properties');
        }
    }

    /**
     * Public 24h-hold request from the live layout (creates a pending request;
     * an admin approves it into a real hold). JSON. Rate-limited per phone+plot.
     * POST /colony/hold-request
     */
    public function holdRequest()
    {
        header('Content-Type: application/json');
        try {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'POST only']);
                return;
            }
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', (string)$token)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Security token expired. Please reload the page.']);
                return;
            }
            $plotId = (int)($_POST['plot_id'] ?? 0);
            $name = trim((string)($_POST['name'] ?? ''));
            $phone = preg_replace('/[^0-9]/', '', (string)($_POST['phone'] ?? ''));
            $refCode = substr(preg_replace('/[^A-Za-z0-9\-_]/', '', (string)($_POST['ref_code'] ?? '')), 0, 50);
            if ($plotId <= 0 || $name === '' || strlen($phone) < 10) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Name and a valid 10-digit mobile number are required.']);
                return;
            }
            $plot = $this->db->fetchOne("SELECT id, colony_id, plot_number, status FROM plots WHERE id = ?", [$plotId]);
            if (!$plot) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Plot not found.']);
                return;
            }
            if (($plot['status'] ?? '') !== 'available') {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'This plot is no longer available (' . $plot['status'] . ').']);
                return;
            }
            $dup = $this->db->fetchOne(
                "SELECT id FROM plot_hold_requests WHERE plot_id = ? AND phone LIKE ? AND status = 'pending' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                [$plotId, '%' . substr($phone, -10)]
            );
            if ($dup) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'You already have a pending hold request for this plot.']);
                return;
            }
            $this->db->execute(
                "INSERT INTO plot_hold_requests (tenant_id, plot_id, colony_id, name, phone, ref_code, status) VALUES (1, ?, ?, ?, ?, ?, 'pending')",
                [$plotId, $plot['colony_id'], mb_substr($name, 0, 150), $phone, $refCode !== '' ? $refCode : null]
            );
            echo json_encode(['success' => true, 'message' => 'Hold request received! Our team will confirm your 24-hour hold on call/SMS shortly.']);
        } catch (Exception $e) {
            error_log('MapController::holdRequest: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Something went wrong. Please try again.']);
        }
        exit;
    }

    /**
     * Get property location suggestions (AJAX)
     */
    public function getLocationSuggestions()
    {
        try {
            $query = trim(Security::sanitize($_GET['q']) ?? '');

            if (empty($query) || strlen($query) < 2) {
                echo json_encode(['success' => true, 'suggestions' => []]);
                exit;
            }

            // Get unique locations that match the query
            $locations = $this->db->table('properties')
                ->where('status', 'active')
                ->where('location', 'LIKE', '%' . $query . '%')
                ->select('location')
                ->distinct()
                ->limit(10)
                ->get();

            $suggestions = array_column($locations, 'location');

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
}
