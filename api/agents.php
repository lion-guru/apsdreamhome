<?php
/**
 * API - Agents & Developers
 * Get information about real estate agents and developers
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    global $pdo;

    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Database connection not available'], 500);
    }

    $request_uri = $_SERVER['REQUEST_URI'];
    $path_segments = explode('/', trim($request_uri, '/'));
    $endpoint = end($path_segments);

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            switch ($endpoint) {
                case 'agents':
                    // Get all agents
                    $page = (int)($_GET['page'] ?? 1);
                    $limit = min((int)($_GET['limit'] ?? 10), 50);
                    $offset = ($page - 1) * $limit;

                    // Build filters
                    $where_conditions = ["u.status = 'active'"];
                    $params = [];

                    if (isset($_GET['city']) && !empty($_GET['city'])) {
                        $where_conditions[] = "u.city = ?";
                        $params[] = $_GET['city'];
                    }

                    if (isset($_GET['specialization']) && !empty($_GET['specialization'])) {
                        $where_conditions[] = "u.specialization = ?";
                        $params[] = $_GET['specialization'];
                    }

                    $where_clause = implode(' AND ', $where_conditions);

                    // Get agents
                    $sql = "SELECT u.id, u.name, u.email, u.phone, u.city, u.state,
                                   u.specialization, u.experience_years, u.profile_image,
                                   u.description, u.license_number, u.certifications,
                                   COUNT(p.id) as properties_count,
                                   AVG(p.price) as avg_property_price
                            FROM users u
                            LEFT JOIN properties p ON u.id = p.created_by AND p.status = 'available'
                            WHERE {$where_clause}
                            GROUP BY u.id
                            ORDER BY properties_count DESC, u.name
                            LIMIT ? OFFSET ?";

                    $params[] = $limit;
                    $params[] = $offset;

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $agents = $stmt->fetchAll();

                    // Get total count
                    $count_sql = "SELECT COUNT(*) as total FROM users u WHERE {$where_clause}";
                    $count_params = array_slice($params, 0, -2);

                    $count_stmt = $pdo->prepare($count_sql);
                    $count_stmt->execute($count_params);
                    $total_count = (int)$count_stmt->fetch()['total'];

                    // Format agents data
                    $formatted_agents = [];
                    foreach ($agents as $agent) {
                        $formatted_agents[] = [
                            'id' => $agent['id'],
                            'name' => $agent['name'],
                            'email' => $agent['email'],
                            'phone' => $agent['phone'],
                            'city' => $agent['city'],
                            'state' => $agent['state'],
                            'specialization' => $agent['specialization'],
                            'experience_years' => (int)$agent['experience_years'],
                            'profile_image' => $agent['profile_image'],
                            'description' => $agent['description'],
                            'license_number' => $agent['license_number'],
                            'certifications' => $agent['certifications'] ? explode(',', $agent['certifications']) : [],
                            'stats' => [
                                'properties_count' => (int)$agent['properties_count'],
                                'avg_property_price' => (float)($agent['avg_property_price'] ?? 0)
                            ],
                            'rating' => 4.5, // Placeholder - implement actual rating system
                            'review_count' => 0 // Placeholder - implement review system
                        ];
                    }

                    sendJsonResponse([
                        'success' => true,
                        'data' => [
                            'agents' => $formatted_agents,
                            'pagination' => [
                                'current_page' => $page,
                                'per_page' => $limit,
                                'total_pages' => ceil($total_count / $limit),
                                'total_count' => $total_count
                            ]
                        ]
                    ]);
                    break;

                case 'developers':
                    // Get all developers
                    $page = (int)($_GET['page'] ?? 1);
                    $limit = min((int)($_GET['limit'] ?? 10), 50);
                    $offset = ($page - 1) * $limit;

                    // Get developers (users with developer role or properties > 10)
                    $sql = "SELECT u.id, u.name, u.email, u.phone, u.city, u.state,
                                   u.company_name, u.website, u.profile_image,
                                   u.description, u.established_year,
                                   COUNT(p.id) as projects_count,
                                   AVG(p.price) as avg_project_price
                            FROM users u
                            LEFT JOIN properties p ON u.id = p.created_by AND p.status = 'available'
                            WHERE u.status = 'active'
                              AND (u.role = 'developer' OR u.company_name IS NOT NULL)
                            GROUP BY u.id
                            HAVING projects_count > 0
                            ORDER BY projects_count DESC, u.name
                            LIMIT ? OFFSET ?";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$limit, $offset]);
                    $developers = $stmt->fetchAll();

                    // Get total count
                    $count_sql = "SELECT COUNT(*) as total
                                  FROM users u
                                  WHERE u.status = 'active'
                                    AND (u.role = 'developer' OR u.company_name IS NOT NULL)";

                    $count_stmt = $pdo->prepare($count_sql);
                    $count_stmt->execute();
                    $total_count = (int)$count_stmt->fetch()['total'];

                    // Format developers data
                    $formatted_developers = [];
                    foreach ($developers as $developer) {
                        $formatted_developers[] = [
                            'id' => $developer['id'],
                            'name' => $developer['name'],
                            'company_name' => $developer['company_name'],
                            'email' => $developer['email'],
                            'phone' => $developer['phone'],
                            'city' => $developer['city'],
                            'state' => $developer['state'],
                            'website' => $developer['website'],
                            'profile_image' => $developer['profile_image'],
                            'description' => $developer['description'],
                            'established_year' => (int)$developer['established_year'],
                            'stats' => [
                                'projects_count' => (int)$developer['projects_count'],
                                'avg_project_price' => (float)($developer['avg_project_price'] ?? 0)
                            ],
                            'rating' => 4.2, // Placeholder
                            'completed_projects' => 0 // Placeholder
                        ];
                    }

                    sendJsonResponse([
                        'success' => true,
                        'data' => [
                            'developers' => $formatted_developers,
                            'pagination' => [
                                'current_page' => $page,
                                'per_page' => $limit,
                                'total_pages' => ceil($total_count / $limit),
                                'total_count' => $total_count
                            ]
                        ]
                    ]);
                    break;

                default:
                    if (preg_match('/\/agents?\/(\d+)/', $request_uri, $matches)) {
                        // Get single agent
                        $agent_id = (int)$matches[1];

                        $sql = "SELECT u.*, COUNT(p.id) as properties_count,
                                       AVG(p.price) as avg_property_price
                                FROM users u
                                LEFT JOIN properties p ON u.id = p.created_by AND p.status = 'available'
                                WHERE u.id = ? AND u.status = 'active'
                                GROUP BY u.id";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$agent_id]);
                        $agent = $stmt->fetch();

                        if (!$agent) {
                            sendJsonResponse(['success' => false, 'error' => 'Agent not found'], 404);
                        }

                        // Get agent's recent properties
                        $properties_sql = "SELECT p.*, pt.name as property_type_name
                                          FROM properties p
                                          LEFT JOIN property_types pt ON p.property_type_id = pt.id
                                          WHERE p.created_by = ? AND p.status = 'available'
                                          ORDER BY p.created_at DESC LIMIT 5";

                        $properties_stmt = $pdo->prepare($properties_sql);
                        $properties_stmt->execute([$agent_id]);
                        $recent_properties = $properties_stmt->fetchAll();

                        sendJsonResponse([
                            'success' => true,
                            'data' => [
                                'agent' => [
                                    'id' => $agent['id'],
                                    'name' => $agent['name'],
                                    'email' => $agent['email'],
                                    'phone' => $agent['phone'],
                                    'city' => $agent['city'],
                                    'state' => $agent['state'],
                                    'specialization' => $agent['specialization'],
                                    'experience_years' => (int)$agent['experience_years'],
                                    'profile_image' => $agent['profile_image'],
                                    'description' => $agent['description'],
                                    'license_number' => $agent['license_number'],
                                    'certifications' => $agent['certifications'] ? explode(',', $agent['certifications']) : [],
                                    'stats' => [
                                        'properties_count' => (int)$agent['properties_count'],
                                        'avg_property_price' => (float)($agent['avg_property_price'] ?? 0)
                                    ]
                                ],
                                'recent_properties' => array_map(function($property) {
                                    return [
                                        'id' => $property['id'],
                                        'title' => $property['title'],
                                        'price' => (float)$property['price'],
                                        'city' => $property['city'],
                                        'property_type' => $property['property_type_name'],
                                        'bedrooms' => (int)$property['bedrooms'],
                                        'featured' => (bool)$property['featured']
                                    ];
                                }, $recent_properties)
                            ]
                        ]);
                    } elseif (preg_match('/\/developers?\/(\d+)/', $request_uri, $matches)) {
                        // Get single developer
                        $developer_id = (int)$matches[1];

                        $sql = "SELECT u.*, COUNT(p.id) as projects_count,
                                       AVG(p.price) as avg_project_price
                                FROM users u
                                LEFT JOIN properties p ON u.id = p.created_by AND p.status = 'available'
                                WHERE u.id = ? AND u.status = 'active'
                                GROUP BY u.id";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$developer_id]);
                        $developer = $stmt->fetch();

                        if (!$developer) {
                            sendJsonResponse(['success' => false, 'error' => 'Developer not found'], 404);
                        }

                        sendJsonResponse([
                            'success' => true,
                            'data' => [
                                'id' => $developer['id'],
                                'name' => $developer['name'],
                                'company_name' => $developer['company_name'],
                                'email' => $developer['email'],
                                'phone' => $developer['phone'],
                                'city' => $developer['city'],
                                'state' => $developer['state'],
                                'website' => $developer['website'],
                                'profile_image' => $developer['profile_image'],
                                'description' => $developer['description'],
                                'established_year' => (int)$developer['established_year'],
                                'stats' => [
                                    'projects_count' => (int)$developer['projects_count'],
                                    'avg_project_price' => (float)($developer['avg_project_price'] ?? 0)
                                ]
                            ]
                        ]);
                    } else {
                        sendJsonResponse(['success' => false, 'error' => 'Invalid endpoint'], 404);
                    }
                    break;
            }
            break;

        default:
            sendJsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
    }

} catch (Exception $e) {
    error_log('API Agents/Developers Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
