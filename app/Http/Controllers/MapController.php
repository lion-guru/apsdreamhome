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
                "SELECT id, plot_number, block, area_sqft, width_ft, length_ft, status, price_per_sqft, total_price, corner_plot, park_facing, road_facing, gata_number
                 FROM plots WHERE colony_id = ? ORDER BY block, plot_number",
                [$colony['id']]
            );
            $this->render('pages/colony_plot_map', [
                'page_title' => $colony['name'] . ' — Plot Map',
                'colony' => $colony,
                'plots' => $plots,
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
                "SELECT id, plot_number, block, area_sqft, width_ft, length_ft, status, price_per_sqft, total_price, corner_plot, park_facing, road_facing, gata_number
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
                            'road_facing' => (bool)$p['road_facing'],
                            'gata_number' => $p['gata_number'],
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
