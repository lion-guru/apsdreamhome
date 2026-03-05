<?php

namespace App\Http\Controllers;

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
