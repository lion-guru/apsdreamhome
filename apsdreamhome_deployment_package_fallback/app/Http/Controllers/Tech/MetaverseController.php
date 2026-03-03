<?php

/**
 * Metaverse Integration Controller
 * Handles VR property showrooms, virtual property development, and metaverse features
 */

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\BaseController;
use Exception;

class MetaverseController extends BaseController
{

    /**
     * VR property showroom
     */
    public function vrShowroom($property_id)
    {
        $property = $this->getPropertyDetails($property_id);

        if (!$property) {
            $this->setFlash('error', 'Property not found');
            $this->redirect(BASE_URL . 'properties');
            return;
        }

        $vr_data = $this->getVRData($property_id);

        $this->data['page_title'] = 'VR Property Showroom - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['vr_data'] = $vr_data;

        $this->render('metaverse/vr_showroom');
    }

    /**
     * Virtual property development
     */
    public function virtualDevelopment()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $development_data = $_POST;
            $virtual_property_id = $this->createVirtualProperty($development_data);

            if ($virtual_property_id) {
                $this->setFlash('success', 'Virtual property created successfully');
                $this->redirect(BASE_URL . 'metaverse/virtual-property/' . $virtual_property_id);
            } else {
                $this->setFlash('error', 'Failed to create virtual property');
            }
        }

        $this->data['page_title'] = 'Virtual Property Development - ' . APP_NAME;
        $this->data['templates'] = $this->getVirtualTemplates();
        $this->data['environments'] = $this->getVirtualEnvironments();

        $this->render('metaverse/virtual_development');
    }

    /**
     * 3D collaborative spaces
     */
    public function collaborativeSpace($space_id = null)
    {
        if (!$space_id) {
            $spaces = $this->getUserCollaborativeSpaces();
            $this->data['spaces'] = $spaces;
            $this->data['page_title'] = 'Collaborative Spaces - ' . APP_NAME;
            $this->render('metaverse/collaborative_spaces');
        } else {
            $space = $this->getCollaborativeSpace($space_id);

            if (!$space) {
                $this->setFlash('error', 'Collaborative space not found');
                $this->redirect(BASE_URL . 'metaverse/collaborative-spaces');
                return;
            }

            $this->data['page_title'] = 'Collaborative Space - ' . $space['name'];
            $this->data['space'] = $space;
            $this->data['participants'] = $this->getSpaceParticipants($space_id);

            $this->render('metaverse/collaborative_space');
        }
    }

    /**
     * Virtual property marketplace
     */
    public function virtualMarketplace()
    {
        $virtual_properties = $this->getVirtualPropertiesForSale();
        $market_stats = $this->getVirtualMarketStats();

        $this->data['page_title'] = 'Virtual Property Marketplace - ' . APP_NAME;
        $this->data['virtual_properties'] = $virtual_properties;
        $this->data['market_stats'] = $market_stats;

        $this->render('metaverse/virtual_marketplace');
    }

    /**
     * Metaverse events and gatherings
     */
    public function virtualEvents()
    {
        $upcoming_events = $this->getUpcomingVirtualEvents();
        $past_events = $this->getPastVirtualEvents();

        $this->data['page_title'] = 'Virtual Events - ' . APP_NAME;
        $this->data['upcoming_events'] = $upcoming_events;
        $this->data['past_events'] = $past_events;

        $this->render('metaverse/virtual_events');
    }

    /**
     * NFT property ownership
     */
    public function nftOwnership($property_id)
    {
        $property = $this->getPropertyDetails($property_id);
        $nft_data = $this->getNFTData($property_id);

        $this->data['page_title'] = 'NFT Property Ownership - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['nft_data'] = $nft_data;

        $this->render('metaverse/nft_ownership');
    }

    /**
     * Virtual reality property tours
     */
    public function vrTours()
    {
        $featured_tours = $this->getFeaturedVRTours();
        $tour_categories = $this->getTourCategories();

        $this->data['page_title'] = 'VR Property Tours - ' . APP_NAME;
        $this->data['featured_tours'] = $featured_tours;
        $this->data['tour_categories'] = $tour_categories;

        $this->render('metaverse/vr_tours');
    }

    /**
     * Metaverse analytics dashboard
     */
    public function metaverseAnalytics()
    {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $analytics_data = [
            'vr_engagement' => $this->getVREngagementMetrics(),
            'virtual_property_sales' => $this->getVirtualPropertySales(),
            'metaverse_events' => $this->getMetaverseEventStats(),
            'nft_marketplace' => $this->getNFTMarketplaceStats()
        ];

        $this->data['page_title'] = 'Metaverse Analytics - ' . APP_NAME;
        $this->data['analytics'] = $analytics_data;

        $this->render('admin/metaverse_analytics');
    }

    /**
     * API - Get VR property data
     */
    public function apiVRData($property_id)
    {
        header('Content-Type: application/json');

        $vr_data = $this->getVRData($property_id);

        sendJsonResponse([
            'success' => true,
            'data' => $vr_data
        ]);
    }

    /**
     * API - Create virtual property
     */
    public function apiCreateVirtualProperty()
    {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
        }

        $property_data = json_decode(file_get_contents('php://input'), true);

        if (!$property_data) {
            sendJsonResponse(['success' => false, 'error' => 'Invalid property data'], 400);
        }

        $virtual_property_id = $this->createVirtualProperty($property_data);

        sendJsonResponse([
            'success' => $virtual_property_id ? true : false,
            'virtual_property_id' => $virtual_property_id,
            'message' => $virtual_property_id ? 'Virtual property created' : 'Creation failed'
        ]);
    }

    /**
     * API - Join collaborative space
     */
    public function apiJoinSpace()
    {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
        }

        $space_id = $_POST['space_id'] ?? '';
        $user_avatar = $_POST['avatar'] ?? 'default';

        if (empty($space_id)) {
            sendJsonResponse(['success' => false, 'error' => 'Space ID required'], 400);
        }

        $join_result = $this->joinCollaborativeSpace($space_id, $_SESSION['user_id'], $user_avatar);

        sendJsonResponse([
            'success' => $join_result['success'],
            'message' => $join_result['message'],
            'space_data' => $join_result['space_data'] ?? null
        ]);
    }

    /**
     * Get property details
     */
    private function getPropertyDetails($property_id)
    {
        try {
            if (!$this->db) {
                return null;
            }
            $stmt = $this->db->prepare("SELECT * FROM properties WHERE id = :id");
            $stmt->execute(['id' => $property_id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Property details fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get VR data for property
     */
    private function getVRData($property_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT * FROM vr_property_data WHERE property_id = :propertyId";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['propertyId' => $property_id]);

            $vr_data = $stmt->fetch();

            if (!$vr_data) {
                // Generate VR data if not exists
                return $this->generateVRData($property_id);
            }

            return $vr_data;
        } catch (Exception $e) {
            error_log('VR data fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate VR data for property
     */
    private function generateVRData($property_id)
    {
        $property = $this->getPropertyDetails($property_id);

        return [
            'property_id' => $property_id,
            'scenes' => [
                'living_room' => [
                    'name' => 'Living Room',
                    'panorama_url' => '/assets/vr/panoramas/living_room_' . $property_id . '.jpg',
                    'hotspots' => [
                        ['x' => 0.3, 'y' => 0.5, 'type' => 'navigation', 'target' => 'bedroom'],
                        ['x' => 0.7, 'y' => 0.4, 'type' => 'info', 'title' => 'Smart TV', 'description' => '55-inch 4K Smart TV']
                    ]
                ],
                'bedroom' => [
                    'name' => 'Master Bedroom',
                    'panorama_url' => '/assets/vr/panoramas/bedroom_' . $property_id . '.jpg',
                    'hotspots' => [
                        ['x' => 0.5, 'y' => 0.6, 'type' => 'navigation', 'target' => 'living_room'],
                        ['x' => 0.8, 'y' => 0.3, 'type' => 'ar_furniture', 'furniture_type' => 'bed']
                    ]
                ]
            ],
            'ar_objects' => [
                'furniture' => [
                    'sofa' => ['model' => '/assets/models/sofa.glb', 'position' => [0, 0, 0]],
                    'table' => ['model' => '/assets/models/table.glb', 'position' => [2, 0, 1]]
                ]
            ],
            'lighting' => [
                'ambient' => '#ffffff',
                'intensity' => 0.8,
                'shadows' => true
            ]
        ];
    }

    /**
     * Create virtual property
     */
    private function createVirtualProperty($property_data)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $sql = "INSERT INTO virtual_properties (
                name, description, property_type, area_sqft, location,
                base_price, virtual_environment, created_by, created_at
            ) VALUES (:name, :description, :propertyType, :area, :location, :price, :environment, :createdBy, NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'name' => $property_data['name'],
                'description' => $property_data['description'],
                'propertyType' => $property_data['property_type'],
                'area' => $property_data['area_sqft'],
                'location' => $property_data['location'],
                'price' => $property_data['base_price'],
                'environment' => $property_data['environment'],
                'createdBy' => $_SESSION['user_id']
            ]);

            if ($success) {
                $virtual_property_id = $this->db->lastInsertId();

                // Generate initial VR data
                $this->generateInitialVRData($virtual_property_id, $property_data);

                return $virtual_property_id;
            }

            return false;
        } catch (Exception $e) {
            error_log('Virtual property creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate initial VR data for virtual property
     */
    private function generateInitialVRData($virtual_property_id, $property_data)
    {
        $initial_scenes = [
            'entrance' => [
                'name' => 'Main Entrance',
                'environment' => $property_data['environment'],
                'skybox' => '/assets/vr/skyboxes/' . $property_data['environment'] . '.jpg'
            ],
            'main_area' => [
                'name' => 'Main Living Area',
                'environment' => $property_data['environment'],
                'skybox' => '/assets/vr/skyboxes/' . $property_data['environment'] . '_interior.jpg'
            ]
        ];

        // Save initial scenes to database
        foreach ($initial_scenes as $scene_id => $scene_data) {
            $this->saveVRScene($virtual_property_id, $scene_id, $scene_data);
        }
    }

    /**
     * Save VR scene data
     */
    private function saveVRScene($virtual_property_id, $scene_id, $scene_data)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $sql = "INSERT INTO virtual_property_scenes (
                virtual_property_id, scene_id, scene_name, environment,
                skybox_url, created_at
            ) VALUES (:virtualPropertyId, :sceneId, :sceneName, :environment, :skyboxUrl, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'virtualPropertyId' => $virtual_property_id,
                'sceneId' => $scene_id,
                'sceneName' => $scene_data['name'],
                'environment' => $scene_data['environment'],
                'skyboxUrl' => $scene_data['skybox']
            ]);
        } catch (Exception $e) {
            error_log('VR scene save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get virtual templates
     */
    private function getVirtualTemplates()
    {
        return [
            'modern_apartment' => [
                'name' => 'Modern Apartment',
                'description' => 'Contemporary urban living space',
                'preview' => '/assets/templates/modern_apartment.jpg',
                'features' => ['Open floor plan', 'Smart home ready', 'City views']
            ],
            'luxury_villa' => [
                'name' => 'Luxury Villa',
                'description' => 'Spacious family home with premium amenities',
                'preview' => '/assets/templates/luxury_villa.jpg',
                'features' => ['Private garden', 'Swimming pool', 'Home theater']
            ],
            'commercial_space' => [
                'name' => 'Commercial Space',
                'description' => 'Professional workspace with modern design',
                'preview' => '/assets/templates/commercial_space.jpg',
                'features' => ['Open workspace', 'Meeting rooms', 'High-speed connectivity']
            ]
        ];
    }

    /**
     * Get virtual environments
     */
    private function getVirtualEnvironments()
    {
        return [
            'urban_city' => [
                'name' => 'Urban City',
                'skybox' => '/assets/vr/environments/urban_skybox.jpg',
                'lighting' => 'city_lights',
                'ambient_sounds' => ['city_traffic', 'people_talking']
            ],
            'beach_resort' => [
                'name' => 'Beach Resort',
                'skybox' => '/assets/vr/environments/beach_skybox.jpg',
                'lighting' => 'sunset_glow',
                'ambient_sounds' => ['ocean_waves', 'seagulls']
            ],
            'mountain_retreat' => [
                'name' => 'Mountain Retreat',
                'skybox' => '/assets/vr/environments/mountain_skybox.jpg',
                'lighting' => 'natural_daylight',
                'ambient_sounds' => ['birds_singing', 'wind_rustling']
            ]
        ];
    }

    /**
     * Get user collaborative spaces
     */
    private function getUserCollaborativeSpaces()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $user_id = $_SESSION['user_id'];

            $sql = "SELECT cs.*, COUNT(csp.user_id) as participant_count
                    FROM collaborative_spaces cs
                    LEFT JOIN collaborative_space_participants csp ON cs.id = csp.space_id
                    WHERE cs.created_by = :createdBy OR csp.user_id = :userId
                    GROUP BY cs.id
                    ORDER BY cs.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['createdBy' => $user_id, 'userId' => $user_id]);

            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('User collaborative spaces fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get collaborative space details
     */
    private function getCollaborativeSpace($space_id)
    {
        try {
            if (!$this->db) {
                return null;
            }

            $sql = "SELECT * FROM collaborative_spaces WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $space_id]);

            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Collaborative space details fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get space participants
     */
    private function getSpaceParticipants($space_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT csp.*, u.name, u.avatar
                    FROM collaborative_space_participants csp
                    LEFT JOIN users u ON csp.user_id = u.id
                    WHERE csp.space_id = :spaceId
                    ORDER BY csp.joined_at";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['spaceId' => $space_id]);

            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Space participants fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Join collaborative space
     */
    private function joinCollaborativeSpace($space_id, $user_id, $avatar)
    {
        try {
            if (!$this->db) {
                return ['success' => false, 'message' => 'Database connection failed'];
            }

            // Check if already a participant
            $sql = "SELECT id FROM collaborative_space_participants WHERE space_id = :spaceId AND user_id = :userId";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['spaceId' => $space_id, 'userId' => $user_id]);
            $existing = $stmt->fetch();

            if ($existing) {
                return [
                    'success' => true,
                    'message' => 'Already a participant',
                    'space_data' => $this->getCollaborativeSpace($space_id)
                ];
            }

            // Add as participant
            $sql = "INSERT INTO collaborative_space_participants (space_id, user_id, avatar, joined_at)
                    VALUES (:spaceId, :userId, :avatar, NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'spaceId' => $space_id,
                'userId' => $user_id,
                'avatar' => $avatar
            ]);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Joined space successfully',
                    'space_data' => $this->getCollaborativeSpace($space_id)
                ];
            }

            return ['success' => false, 'message' => 'Failed to join space'];
        } catch (Exception $e) {
            error_log('Space join error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Join failed'];
        }
    }

    /**
     * Get virtual properties for marketplace
     */
    private function getVirtualPropertiesForSale()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT vp.*, u.name as creator_name
                    FROM virtual_properties vp
                    LEFT JOIN users u ON vp.created_by = u.id
                    WHERE vp.is_for_sale = 1 AND vp.status = 'active'
                    ORDER BY vp.created_at DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Virtual properties marketplace fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get virtual market statistics
     */
    private function getVirtualMarketStats()
    {
        return [
            'total_virtual_properties' => 1247,
            'properties_for_sale' => 89,
            'avg_sale_price' => 25000, // Virtual currency units
            'monthly_volume' => 450000,
            'top_selling_category' => 'Luxury Apartments'
        ];
    }

    /**
     * Get upcoming virtual events
     */
    private function getUpcomingVirtualEvents()
    {
        return [
            [
                'id' => 1,
                'title' => 'Virtual Property Showcase',
                'date' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'venue' => 'Metaverse Convention Center',
                'description' => 'Explore the latest virtual properties in our metaverse',
                'attendees' => 234,
                'max_attendees' => 500
            ],
            [
                'id' => 2,
                'title' => 'Smart Home Technology Expo',
                'date' => date('Y-m-d H:i:s', strtotime('+5 days')),
                'venue' => 'Virtual Tech Hub',
                'description' => 'Discover the future of smart home technology',
                'attendees' => 156,
                'max_attendees' => 300
            ]
        ];
    }

    /**
     * Get past virtual events
     */
    private function getPastVirtualEvents()
    {
        return [
            [
                'id' => 3,
                'title' => 'Blockchain Real Estate Summit',
                'date' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'venue' => 'Crypto Valley Metaverse',
                'description' => 'Exploring blockchain applications in real estate',
                'attendees' => 445,
                'recordings_available' => true
            ]
        ];
    }

    /**
     * Get NFT data for property
     */
    private function getNFTData($property_id)
    {
        try {
            if (!$this->db) {
                return null;
            }

            $sql = "SELECT * FROM property_nfts WHERE property_id = :propertyId";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['propertyId' => $property_id]);

            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('NFT data fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get featured VR tours
     */
    private function getFeaturedVRTours()
    {
        return [
            [
                'id' => 1,
                'property_id' => 123,
                'title' => 'Luxury Villa VR Tour',
                'thumbnail' => '/assets/vr/thumbnails/villa_tour.jpg',
                'duration' => '5 minutes',
                'views' => 15420,
                'rating' => 4.8
            ],
            [
                'id' => 2,
                'property_id' => 456,
                'title' => 'Modern Apartment Experience',
                'thumbnail' => '/assets/vr/thumbnails/apartment_tour.jpg',
                'duration' => '3 minutes',
                'views' => 8934,
                'rating' => 4.6
            ]
        ];
    }

    /**
     * Get tour categories
     */
    private function getTourCategories()
    {
        return [
            'residential' => ['name' => 'Residential Properties', 'count' => 245],
            'commercial' => ['name' => 'Commercial Spaces', 'count' => 89],
            'luxury' => ['name' => 'Luxury Properties', 'count' => 67],
            'smart_homes' => ['name' => 'Smart Homes', 'count' => 134]
        ];
    }

    /**
     * Get VR engagement metrics
     */
    private function getVREngagementMetrics()
    {
        return [
            'total_vr_tours' => 445,
            'avg_session_duration' => '8.5 minutes',
            'tour_completion_rate' => '73%',
            'user_satisfaction' => '4.7/5',
            'conversion_rate' => '12.3%'
        ];
    }

    /**
     * Get virtual property sales data
     */
    private function getVirtualPropertySales()
    {
        return [
            'total_sales' => 156,
            'total_volume' => 4500000,
            'avg_sale_price' => 28846,
            'monthly_growth' => '23.5%',
            'top_selling_type' => 'Virtual Apartments'
        ];
    }

    /**
     * Get metaverse event statistics
     */
    private function getMetaverseEventStats()
    {
        return [
            'total_events' => 67,
            'total_attendees' => 15420,
            'avg_attendance' => 230,
            'event_satisfaction' => '4.6/5',
            'repeat_attendance' => '68%'
        ];
    }

    /**
     * Get NFT marketplace statistics
     */
    private function getNFTMarketplaceStats()
    {
        return [
            'total_nfts' => 2341,
            'properties_tokenized' => 893,
            'trading_volume' => 12500000,
            'avg_nft_price' => 5340,
            'market_cap' => 12500000
        ];
    }

    /**
     * Create collaborative space
     */
    public function createCollaborativeSpace()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $space_data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'max_participants' => $_POST['max_participants'] ?? 10,
                'environment' => $_POST['environment'] ?? 'modern_office',
                'is_public' => isset($_POST['is_public']) ? 1 : 0
            ];

            if (empty($space_data['name'])) {
                $this->setFlash('error', 'Space name is required');
                $this->redirect(BASE_URL . 'metaverse/create-space');
                return;
            }

            $space_id = $this->createSpace($space_data);

            if ($space_id) {
                $this->setFlash('success', 'Collaborative space created successfully');
                $this->redirect(BASE_URL . 'metaverse/collaborative-space/' . $space_id);
            } else {
                $this->setFlash('error', 'Failed to create collaborative space');
            }
        }

        $this->data['page_title'] = 'Create Collaborative Space - ' . APP_NAME;
        $this->data['space_environments'] = $this->getSpaceEnvironments();

        $this->render('metaverse/create_space');
    }

    /**
     * Create collaborative space in database
     */
    private function createSpace($space_data)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $sql = "INSERT INTO collaborative_spaces (
                name, description, max_participants, environment,
                is_public, created_by, created_at
            ) VALUES (:name, :description, :maxParticipants, :environment, :isPublic, :createdBy, NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'name' => $space_data['name'],
                'description' => $space_data['description'],
                'maxParticipants' => $space_data['max_participants'],
                'environment' => $space_data['environment'],
                'isPublic' => $space_data['is_public'],
                'createdBy' => $_SESSION['user_id']
            ]);

            if ($success) {
                $space_id = $this->db->lastInsertId();

                // Add creator as first participant
                $this->joinCollaborativeSpace($space_id, $_SESSION['user_id'], 'host');

                return $space_id;
            }

            return false;
        } catch (Exception $e) {
            error_log('Space creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get space environments
     */
    private function getSpaceEnvironments()
    {
        return [
            'modern_office' => [
                'name' => 'Modern Office',
                'description' => 'Professional meeting space',
                'capacity' => 20,
                'features' => ['Whiteboard', 'Presentation screen', 'Video conferencing']
            ],
            'creative_studio' => [
                'name' => 'Creative Studio',
                'description' => 'Inspiring creative workspace',
                'capacity' => 15,
                'features' => ['Art supplies', 'Mood lighting', 'Music integration']
            ],
            'conference_hall' => [
                'name' => 'Conference Hall',
                'description' => 'Large presentation space',
                'capacity' => 50,
                'features' => ['Stage', 'Large screens', 'Professional audio']
            ],
            'casual_lounge' => [
                'name' => 'Casual Lounge',
                'description' => 'Relaxed social space',
                'capacity' => 25,
                'features' => ['Comfortable seating', 'Entertainment', 'Refreshments']
            ]
        ];
    }

    /**
     * Virtual property customization
     */
    public function customizeVirtualProperty($virtual_property_id)
    {
        $virtual_property = $this->getVirtualProperty($virtual_property_id);

        if (!$virtual_property) {
            $this->setFlash('error', 'Virtual property not found');
            $this->redirect(BASE_URL . 'metaverse/virtual-marketplace');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customization_data = $_POST;
            $this->applyCustomization($virtual_property_id, $customization_data);

            $this->setFlash('success', 'Virtual property customized successfully');
            $this->redirect(BASE_URL . 'metaverse/virtual-property/' . $virtual_property_id);
        }

        $this->data['page_title'] = 'Customize Virtual Property - ' . $virtual_property['name'];
        $this->data['virtual_property'] = $virtual_property;
        $this->data['customization_options'] = $this->getCustomizationOptions();

        $this->render('metaverse/customize_property');
    }

    /**
     * Get virtual property details
     */
    private function getVirtualProperty($virtual_property_id)
    {
        try {
            if (!$this->db) {
                return null;
            }

            $sql = "SELECT vp.*, u.name as creator_name
                    FROM virtual_properties vp
                    LEFT JOIN users u ON vp.created_by = u.id
                    WHERE vp.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $virtual_property_id]);

            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Virtual property details fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get customization options
     */
    private function getCustomizationOptions()
    {
        return [
            'colors' => [
                'walls' => ['White', 'Beige', 'Light Gray', 'Cream'],
                'floors' => ['Hardwood', 'Tile', 'Carpet', 'Laminate'],
                'furniture' => ['Modern', 'Classic', 'Minimalist', 'Rustic']
            ],
            'furniture' => [
                'living_room' => ['Sofa Set', 'Armchairs', 'Coffee Table', 'TV Unit'],
                'bedroom' => ['Bed Frame', 'Nightstands', 'Dresser', 'Wardrobe'],
                'kitchen' => ['Cabinets', 'Countertops', 'Appliances', 'Island']
            ],
            'lighting' => [
                'ambient' => ['Warm White', 'Cool White', 'Natural Light'],
                'accent' => ['LED Strips', 'Pendant Lights', 'Wall Sconces']
            ],
            'decor' => [
                'artwork' => ['Abstract', 'Landscape', 'Modern Art', 'Photography'],
                'plants' => ['Indoor Plants', 'Succulents', 'Flowers', 'Trees'],
                'accessories' => ['Vases', 'Candles', 'Books', 'Decorative Items']
            ]
        ];
    }

    /**
     * Apply customization to virtual property
     */
    private function applyCustomization($virtual_property_id, $customization_data)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $sql = "INSERT INTO virtual_property_customizations (
                virtual_property_id, customization_data, applied_by, created_at
            ) VALUES (:virtualPropertyId, :customizationData, :appliedBy, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'virtualPropertyId' => $virtual_property_id,
                'customizationData' => json_encode($customization_data),
                'appliedBy' => $_SESSION['user_id']
            ]);
        } catch (Exception $e) {
            error_log('Customization apply error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Metaverse social features
     */
    public function socialHub()
    {
        $active_users = $this->getActiveMetaverseUsers();
        $social_activities = $this->getSocialActivities();
        $user_avatars = $this->getUserAvatars();

        $this->data['page_title'] = 'Metaverse Social Hub - ' . APP_NAME;
        $this->data['active_users'] = $active_users;
        $this->data['social_activities'] = $social_activities;
        $this->data['user_avatars'] = $user_avatars;

        $this->render('metaverse/social_hub');
    }

    /**
     * Get active metaverse users
     */
    private function getActiveMetaverseUsers()
    {
        return [
            ['id' => 1, 'name' => 'John Doe', 'avatar' => '/avatars/john.jpg', 'location' => 'VR Showroom', 'status' => 'Exploring properties'],
            ['id' => 2, 'name' => 'Jane Smith', 'avatar' => '/avatars/jane.jpg', 'location' => 'Collaborative Space', 'status' => 'In meeting'],
            ['id' => 3, 'name' => 'Mike Johnson', 'avatar' => '/avatars/mike.jpg', 'location' => 'Virtual Marketplace', 'status' => 'Shopping']
        ];
    }

    /**
     * Get social activities
     */
    private function getSocialActivities()
    {
        return [
            ['type' => 'property_view', 'user' => 'John Doe', 'property' => 'Luxury Villa', 'timestamp' => date('Y-m-d H:i:s', time() - 300)],
            ['type' => 'space_joined', 'user' => 'Jane Smith', 'space' => 'Design Meeting Room', 'timestamp' => date('Y-m-d H:i:s', time() - 600)],
            ['type' => 'virtual_purchase', 'user' => 'Mike Johnson', 'item' => 'Virtual Apartment', 'timestamp' => date('Y-m-d H:i:s', time() - 900)]
        ];
    }

    /**
     * Get user avatars
     */
    private function getUserAvatars()
    {
        return [
            'default' => ['name' => 'Default Avatar', 'preview' => '/avatars/default.jpg'],
            'professional' => ['name' => 'Professional', 'preview' => '/avatars/professional.jpg'],
            'casual' => ['name' => 'Casual', 'preview' => '/avatars/casual.jpg'],
            'creative' => ['name' => 'Creative', 'preview' => '/avatars/creative.jpg']
        ];
    }

    /**
     * Virtual economy and currency
     */
    public function virtualEconomy()
    {
        $economy_data = [
            'virtual_currency' => 'VRC (Virtual Real Estate Coin)',
            'exchange_rate' => '1 VRC = ₹100',
            'market_cap' => '₹50,00,000',
            'daily_volume' => '₹2,50,000',
            'top_traded_assets' => [
                ['name' => 'Virtual Apartments', 'volume' => '₹1,20,000', 'change' => '+15%'],
                ['name' => 'VR Showrooms', 'volume' => '₹80,000', 'change' => '+8%'],
                ['name' => 'Metaverse Land', 'volume' => '₹50,000', 'change' => '+22%']
            ]
        ];

        $this->data['page_title'] = 'Virtual Economy - ' . APP_NAME;
        $this->data['economy_data'] = $economy_data;

        $this->render('metaverse/virtual_economy');
    }

    /**
     * Metaverse education and training
     */
    public function metaverseAcademy()
    {
        $courses = [
            'vr_basics' => [
                'title' => 'VR Navigation Basics',
                'description' => 'Learn to navigate virtual environments',
                'duration' => '2 hours',
                'difficulty' => 'Beginner',
                'enrolled' => 1250
            ],
            'property_tours' => [
                'title' => 'Professional VR Property Tours',
                'description' => 'Create engaging virtual property tours',
                'duration' => '4 hours',
                'difficulty' => 'Intermediate',
                'enrolled' => 890
            ],
            'metaverse_business' => [
                'title' => 'Metaverse Business Strategies',
                'description' => 'Leverage metaverse for real estate business',
                'duration' => '6 hours',
                'difficulty' => 'Advanced',
                'enrolled' => 234
            ]
        ];

        $this->data['page_title'] = 'Metaverse Academy - ' . APP_NAME;
        $this->data['courses'] = $courses;

        $this->render('metaverse/metaverse_academy');
    }

    /**
     * Virtual property investment portfolio
     */
    public function investmentPortfolio()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $portfolio = $this->getUserVirtualPortfolio();
        $market_performance = $this->getMarketPerformance();

        $this->data['page_title'] = 'Virtual Investment Portfolio - ' . APP_NAME;
        $this->data['portfolio'] = $portfolio;
        $this->data['market_performance'] = $market_performance;

        $this->render('metaverse/investment_portfolio');
    }

    /**
     * Get user virtual portfolio
     */
    private function getUserVirtualPortfolio()
    {
        return [
            'total_investment' => 150000,
            'current_value' => 185000,
            'total_return' => 35000,
            'return_percentage' => 23.3,
            'properties_owned' => [
                ['name' => 'Virtual Apartment Complex', 'investment' => 50000, 'current_value' => 65000, 'return' => 30],
                ['name' => 'VR Showroom Space', 'investment' => 30000, 'current_value' => 35000, 'return' => 16.7],
                ['name' => 'Metaverse Land Parcel', 'investment' => 70000, 'current_value' => 85000, 'return' => 21.4]
            ]
        ];
    }

    /**
     * Get market performance data
     */
    private function getMarketPerformance()
    {
        return [
            'market_index' => 'VREI (Virtual Real Estate Index)',
            'current_value' => 125.67,
            'change_today' => '+2.34 (+1.9%)',
            'change_1month' => '+8.45 (+7.2%)',
            'change_1year' => '+45.23 (+56.1%)',
            'volatility' => 'Medium'
        ];
    }
}
