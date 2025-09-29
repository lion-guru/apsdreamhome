<?php
/**
 * PropertyAI - AI-powered property search and recommendations
 */
class PropertyAI {
    private $conn;
    private $config;
    private $openai_api_key;
    
    // Cache for property data
    private $propertyCache = [];
    private $conversationContexts = [];
    
    // Common property types and locations for better NLP
    private $propertyTypes = [
        'apartment', 'villa', 'house', 'plot', 'land', 'commercial', 
        'office', 'shop', 'warehouse', 'flat', 'penthouse', 'duplex',
        'bungalow', 'farmhouse', 'studio', 'villa', 'row house', 'townhouse'
    ];
    
    private $locations = [
        'mumbai', 'delhi', 'bangalore', 'hyderabad', 'chennai',
        'kolkata', 'pune', 'ahmedabad', 'surat', 'jaipur',
        'lucknow', 'kanpur', 'nagpur', 'indore', 'thane',
        'bhopal', 'visakhapatnam', 'pimpri-chinchwad', 'patna', 'vadodara'
    ];
    
    /**
     * Constructor
     * 
     * @param mysqli $conn Database connection
     * @param array $config Configuration options
     */
    public function __construct($conn, $config = []) {
        $this->conn = $conn;
        $this->config = array_merge([
            'openai_api_key' => null,
            'use_mock_data' => false,
            'cache_ttl' => 3600, // 1 hour cache
        ], $config);
        
        $this->openai_api_key = $this->config['openai_api_key'];
    }
    
    /**
     * Process chat messages and generate appropriate responses
     * 
     * @param array $data Message data including 'message', 'conversation_id', etc.
     * @return array Response data including 'response' and 'conversation_id'
     */
    public function processChatMessage($data) {
        try {
            $message = trim($data['message']);
            $conversationId = $data['conversation_id'] ?? $this->generateConversationId();
            
            // Initialize or update conversation context
            if (!isset($this->conversationContexts[$conversationId])) {
                $this->conversationContexts[$conversationId] = [
                    'messages' => [],
                    'filters' => [],
                    'last_activity' => time()
                ];
            }
            
            $context = &$this->conversationContexts[$conversationId];
            $context['last_activity'] = time();
            
            // Add user message to context
            $context['messages'][] = [
                'role' => 'user',
                'content' => $message,
                'timestamp' => time()
            ];
            
            // Process the message and generate response
            $response = $this->generateChatResponse($message, $context);
            
            // Add bot response to context
            $context['messages'][] = [
                'role' => 'assistant',
                'content' => $response['response'],
                'timestamp' => time()
            ];
            
            // Clean up old conversations
            $this->cleanupOldConversations();
            
            return [
                'success' => true,
                'response' => $response['response'],
                'conversation_id' => $conversationId,
                'data' => $response['data'] ?? null
            ];
            
        } catch (Exception $e) {
            error_log('Error in processChatMessage: ' . $e->getMessage());
            
            return [
                'success' => false,
                'response' => 'I apologize, but I encountered an error processing your request. Please try again later.',
                'conversation_id' => $conversationId ?? null
            ];
        }
    }
    
    /**
     * Generate a response for a chat message
     */
    private function generateChatResponse($message, &$context) {
        // Simple greetings and small talk
        if ($this->isGreeting($message)) {
            return [
                'response' => $this->getRandomResponse([
                    'Hello! How can I assist you with your property search today?',
                    'Hi there! I\'m here to help you find your dream home. What are you looking for?',
                    'Welcome! I can help you find properties, estimate prices, and answer questions. How can I help?'
                ])
            ];
        }
        
        // Check for property search intent
        $searchIntent = $this->detectSearchIntent($message);
        if ($searchIntent) {
            return $this->handleSearchIntent($searchIntent, $context);
        }
        
        // Check for price prediction intent
        if ($this->isPricePredictionRequest($message)) {
            return $this->handlePricePredictionRequest($message, $context);
        }
        
        // Default response
        return [
            'response' => 'I\'m here to help you with property-related questions. You can ask me about:\n' .
                         '• Available properties in specific areas\n' .
                         '• Property prices and estimates\n' .
                         '• Property features and amenities\n' .
                         '• And more! What would you like to know?'
        ];
    }
    
    /**
     * Get AI-powered property recommendations based on user preferences
     * 
     * @param int|null $userId User ID for personalized recommendations
     * @param int $limit Maximum number of recommendations to return
     * @param array $filters Additional filters to apply
     * @return array Recommended properties with relevance scores
     */
    public function getRecommendedProperties($userId = null, $limit = 6, $filters = []) {
        try {
            // Get user preferences if user is logged in
            $userPrefs = $userId ? $this->getUserPreferences($userId) : [
                'preferred_property_types' => [],
                'preferred_locations' => [],
                'min_bedrooms' => null,
                'max_price' => null,
                'min_area' => null,
                'amenities' => []
            ];
            
            // Build base query with scoring
            $query = "
                SELECT 
                    p.*, 
                    u.first_name as agent_name,
                    u.phone as agent_phone,
                    u.email as agent_email,
                    -- Base score (newer properties get higher score)
                    (DATEDIFF(NOW(), p.created_at) * -0.1) as score
                FROM properties p
                LEFT JOIN users u ON p.agent_id = u.id
                WHERE p.status = 'available'
            ";
            
            $params = [];
            $types = '';
            $conditions = [];
            
            // Apply user preferences to scoring
            $scoreConditions = [];
            
            // Property type match
            if (!empty($userPrefs['preferred_property_types'])) {
                $placeholders = implode(',', array_fill(0, count($userPrefs['preferred_property_types']), '?'));
                $scoreConditions[] = "IF(p.type IN ($placeholders), 20, 0)";
                $params = array_merge($params, $userPrefs['preferred_property_types']);
                $types .= str_repeat('s', count($userPrefs['preferred_property_types']));
            }
            
            // Location match
            if (!empty($userPrefs['preferred_locations'])) {
                foreach ($userPrefs['preferred_locations'] as $location) {
                    $scoreConditions[] = "IF(p.location LIKE ?, 15, 0)";
                    $params[] = "%$location%";
                    $types .= 's';
                }
            }
            
            // Bedrooms preference
            if (!empty($userPrefs['min_bedrooms'])) {
                $scoreConditions[] = "IF(p.bedrooms >= ?, 10, 0)";
                $params[] = $userPrefs['min_bedrooms'];
                $types .= 'i';
            }
            
            // Price range preference
            if (!empty($userPrefs['max_price'])) {
                $scoreConditions[] = "IF(p.price <= ?, 10, -5 * (p.price - ?) / ?)";
                $params[] = $userPrefs['max_price'];
                $params[] = $userPrefs['max_price'];
                $params[] = max(1, $userPrefs['max_price']); // Avoid division by zero
                $types .= 'iii';
            }
            
            // Add scoring to query
            if (!empty($scoreConditions)) {
                $query = str_replace(
                    'as score',
                    'as base_score, (' . implode(' + ', $scoreConditions) . ') as preference_score',
                    $query
                );
                $query = "SELECT *, (base_score + preference_score) as score FROM ($query) as scored_props";
            }
            
            // Apply explicit filters (overrides preferences)
            if (!empty($filters['type'])) {
                $conditions[] = "p.type = ?";
                $params[] = $filters['type'];
                $types .= 's';
            }
            
            if (!empty($filters['location'])) {
                $conditions[] = "p.location LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
                $types .= 's';
            }
            
            if (!empty($filters['min_price'])) {
                $conditions[] = "p.price >= ?";
                $params[] = $filters['min_price'];
                $types .= 'i';
            }
            
            if (!empty($filters['max_price'])) {
                $conditions[] = "p.price <= ?";
                $params[] = $filters['max_price'];
                $types .= 'i';
            }
            
            if (!empty($filters['bedrooms'])) {
                $conditions[] = "p.bedrooms >= ?";
                $params[] = $filters['bedrooms'];
                $types .= 'i';
            }
            
            // Add conditions to query
            if (!empty($conditions)) {
                $query .= ' AND ' . implode(' AND ', $conditions);
            }
            
            // Add ordering and limit
            $query .= ' ORDER BY score DESC';
            
            // Prepare and execute the query
            $stmt = $this->conn->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $properties = [];
            
            while ($row = $result->fetch_assoc()) {
                // Remove internal scoring fields from result
                unset($row['base_score'], $row['preference_score'], $row['score']);
                $properties[] = $row;
                
                // Cache the property
                if (!empty($row['id'])) {
                    $this->propertyCache[$row['id']] = $row;
                }
            }
            
            // If no results with preferences, fall back to simple recommendations
            if (empty($properties) && (!empty($userPrefs['preferred_property_types']) || !empty($userPrefs['preferred_locations']))) {
                return $this->getRecommendedProperties(null, $limit, $filters);
            }
            
            // Limit results
            return array_slice($properties, 0, $limit);
            
        } catch (Exception $e) {
            error_log("Error in getRecommendedProperties: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get similar properties based on a property ID
     * 
     * @param int $propertyId The ID of the property to find similar ones for
     * @param int $limit Number of similar properties to return (default: 4)
     * @param array $excludeIds Array of property IDs to exclude from results
     * @return array Array of similar properties with similarity scores
     */
    public function getSimilarProperties($propertyId, $limit = 4, $excludeIds = []) {
        try {
            // Get the base property details
            $baseProperty = $this->getPropertyById($propertyId);
            
            if (!$baseProperty) {
                return [];
            }
            
            // Start building the query with scoring
            $query = "
                SELECT 
                    p.*,
                    u.first_name as agent_name,
                    u.phone as agent_phone,
                    u.email as agent_email,
                    -- Similarity scoring (weighted)
                    (
                        -- Type match (30% weight)
                        (CASE 
                            WHEN p.type = ? THEN 30 
                            WHEN p.type IN (SELECT type FROM properties WHERE id != ? GROUP BY type HAVING COUNT(*) > 1) THEN 15 
                            ELSE 0 
                        END) +
                        -- Location match (25% weight)
                        (CASE 
                            WHEN p.location = ? THEN 25 
                            WHEN p.location LIKE ? THEN 15
                            ELSE 0 
                        END) +
                        -- Price similarity (20% weight, within 20% range)
                        (CASE 
                            WHEN p.price BETWEEN ? * 0.8 AND ? * 1.2 THEN 20 
                            WHEN p.price BETWEEN ? * 0.6 AND ? * 1.4 THEN 10
                            ELSE 0 
                        END) +
                        -- Bedrooms match (15% weight)
                        (CASE 
                            WHEN p.bedrooms = ? THEN 15 
                            WHEN ABS(p.bedrooms - ?) = 1 THEN 8
                            ELSE 0 
                        END) +
                        -- Property area similarity (10% weight, within 20% range)
                        (CASE 
                            WHEN p.area BETWEEN ? * 0.8 AND ? * 1.2 THEN 10 
                            WHEN p.area BETWEEN ? * 0.6 AND ? * 1.4 THEN 5
                            ELSE 0 
                        END)
                    ) as similarity_score
                FROM properties p
                LEFT JOIN users u ON p.agent_id = u.id
                WHERE p.status = 'available'
                AND p.id != ?
            ";
            
            // Prepare parameters
            $params = [
                $baseProperty['type'],
                $propertyId,
                $baseProperty['location'],
                '%' . $baseProperty['location'] . '%',
                $baseProperty['price'],
                $baseProperty['price'],
                $baseProperty['price'],
                $baseProperty['price'],
                $baseProperty['bedrooms'],
                $baseProperty['bedrooms'],
                $baseProperty['area'],
                $baseProperty['area'],
                $baseProperty['area'],
                $baseProperty['area'],
                $propertyId
            ];
            
            $types = 'ssssddddiiiiiid';
            
            // Add excluded IDs if any
            if (!empty($excludeIds)) {
                $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
                $query .= " AND p.id NOT IN ($placeholders)";
                $params = array_merge($params, $excludeIds);
                $types .= str_repeat('i', count($excludeIds));
            }
            
            // Add ordering and limit
            $query .= " HAVING similarity_score > 0 ORDER BY similarity_score DESC, RAND() LIMIT ?";
            $params[] = $limit;
            $types .= 'i';
            
            // Prepare and execute the query
            $stmt = $this->conn->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $similarProperties = [];
            
            while ($row = $result->fetch_assoc()) {
                // Calculate similarity percentage (normalize to 60-95% range)
                $similarity = min(95, max(60, $row['similarity_score']));
                $row['similarity_percentage'] = $similarity;
                
                // Remove internal scoring field
                unset($row['similarity_score']);
                
                // Cache the property
                if (!empty($row['id'])) {
                    $this->propertyCache[$row['id']] = $row;
                }
                
                $similarProperties[] = $row;
            }
            
            // If not enough similar properties, supplement with recommendations
            if (count($similarProperties) < $limit) {
                $needed = $limit - count($similarProperties);
                $excludeIds = array_merge(
                    $excludeIds,
                    array_column($similarProperties, 'id'),
                    [$propertyId]
                );
                
                $supplemental = $this->getRecommendedProperties(
                    null, 
                    $needed, 
                    [
                        'type' => $baseProperty['type'],
                        'location' => $baseProperty['location'],
                        'exclude_ids' => $excludeIds
                    ]
                );
                
                $similarProperties = array_merge($similarProperties, $supplemental);
            }
            
            return array_slice($similarProperties, 0, $limit);
            
        } catch (Exception $e) {
            error_log("Error in getSimilarProperties: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Predict property price using a combination of rule-based and ML approaches
     * 
     * @param array $propertyData Property features for price prediction
     * @return array Predicted price with confidence and market insights
     */
    public function predictPrice($propertyData) {
        try {
            // Validate required fields
            $requiredFields = ['location', 'property_type', 'area'];
            foreach ($requiredFields as $field) {
                if (empty($propertyData[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }
            
            // Set default values for optional fields
            $propertyData = array_merge([
                'bedrooms' => 2,
                'bathrooms' => 2,
                'year_built' => date('Y') - 10, // Default: 10 years old
                'condition' => 'good',
                'amenities' => [],
                'floor' => 1,
                'total_floors' => 2,
                'furnishing' => 'semi-furnished',
                'facing' => 'east',
                'ownership' => 'freehold',
                'parking' => 1
            ], $propertyData);
            
            // Get base price per sq.ft for the location and property type
            $basePricePerSqFt = $this->getBasePricePerSqFt(
                $propertyData['location'], 
                $propertyData['property_type']
            );
            
            // Calculate base price
            $basePrice = $basePricePerSqFt * floatval($propertyData['area']);
            
            // Apply adjustments based on property features
            $adjustments = $this->calculatePriceAdjustments($propertyData, $basePrice);
            
            // Calculate final predicted price
            $predictedPrice = $basePrice + $adjustments['total_adjustment'];
            
            // Ensure price is within reasonable bounds
            $minPrice = $basePrice * 0.5; // 50% of base price
            $maxPrice = $basePrice * 2.5; // 250% of base price
            $predictedPrice = max($minPrice, min($maxPrice, $predictedPrice));
            
            // Round to nearest 1000 for cleaner numbers
            $predictedPrice = round($predictedPrice / 1000) * 1000;
            
            // Calculate confidence score (0.0 to 1.0)
            $confidence = $this->calculateConfidenceScore($propertyData);
            
            // Get market insights
            $marketInsights = $this->getMarketInsights($propertyData['location'], $propertyData['property_type']);
            
            // Prepare response
            return [
                'predicted_price' => round($predictedPrice),
                'price_per_sqft' => round($predictedPrice / floatval($propertyData['area'])),
                'confidence' => $confidence,
                'currency' => 'INR',
                'price_breakdown' => [
                    'base_price' => round($basePrice),
                    'adjustments' => $adjustments['details'],
                    'total_adjustment' => round($adjustments['total_adjustment'])
                ],
                'market_insights' => $marketInsights,
                'last_updated' => date('Y-m-d H:i:s'),
                'disclaimer' => 'This is an estimate based on available data and may not reflect the actual market value.'
            ];
            
        } catch (Exception $e) {
            error_log("Error in predictPrice: " . $e->getMessage());
            return [
                'error' => 'Unable to generate price prediction',
                'message' => $e->getMessage(),
                'code' => 500
            ];
        }
    }
    
    /**
     * Process natural language search query to extract property search parameters
     * 
     * @param string $query Natural language search query (e.g., "3 BHK apartment in Mumbai under 1.5 crore")
     * @return array Structured search filters compatible with the property search
     */
    public function processNaturalLanguageQuery($query) {
        try {
            // Initialize default filters
            $filters = [
                'keywords' => [],
                'type' => null,
                'min_price' => null,
                'max_price' => null,
                'min_bedrooms' => null,
                'max_bedrooms' => null,
                'min_bathrooms' => null,
                'location' => null,
                'furnishing' => null,
                'facing' => null,
                'keywords_operator' => 'AND',
                'sort_by' => 'relevance',
                'sort_order' => 'desc',
                'page' => 1,
                'per_page' => 10
            ];
            
            // Convert to lowercase for case-insensitive matching
            $query = strtolower(trim($query));
            
            // Extract property types
            $this->extractPropertyType($query, $filters);
            
            // Extract price range
            $this->extractPriceRange($query, $filters);
            
            // Extract bedroom/bathroom requirements
            $this->extractRoomRequirements($query, $filters);
            
            // Extract location
            $this->extractLocation($query, $filters);
            
            // Extract property features
            $this->extractFeatures($query, $filters);
            
            // Extract sort order if specified
            $this->extractSortOrder($query, $filters);
            
            // Extract keywords (remaining words that weren't matched as other filters)
            $this->extractKeywords($query, $filters);
            
            return $filters;
            
        } catch (Exception $e) {
            error_log("Error in processNaturalLanguageQuery: " . $e->getMessage());
            // Return a basic filter with just the keywords if processing fails
            return [
                'keywords' => array_filter(explode(' ', preg_replace('/[^a-z0-9\s]/', '', strtolower($query)))),
                'keywords_operator' => 'AND'
            ];
        }
    }
    
    /**
     * Extract property type from query
     */
    private function extractPropertyType(&$query, &$filters) {
        $propertyTypes = [
            'apartment' => ['apartment', 'flat', 'apt'],
            'villa' => ['villa', 'bungalow', 'house', 'home'],
            'plot' => ['plot', 'land', 'empty land'],
            'office' => ['office', 'commercial office', 'workspace'],
            'shop' => ['shop', 'retail', 'store', 'showroom'],
            'warehouse' => ['warehouse', 'godown', 'storage']
        ];
        
        foreach ($propertyTypes as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($query, $keyword) !== false) {
                    $filters['type'] = $type;
                    $query = str_replace($keyword, '', $query);
                    break 2;
                }
            }
        }
    }
    
    /**
     * Extract price range from query
     */
    private function extractPriceRange($query, &$filters) {
        // Match prices in lakhs (e.g., "50 lakh", "1.5 crore")
        if (preg_match('/(\d+(?:\.\d+)?)\s*(lakh|lac|cr|crore)/', $query, $matches)) {
            $amount = (float)$matches[1];
            $unit = $matches[2];
            
            // Convert to base amount (lakhs)
            if (in_array($unit, ['cr', 'crore'])) {
                $amount *= 100; // 1 crore = 100 lakhs
            }
            
            // Check for price range indicators
            if (strpos($query, 'under') !== false || strpos($query, 'below') !== false || strpos($query, 'less than') !== false) {
                $filters['max_price'] = $amount * 100000; // Convert to actual amount
            } elseif (strpos($query, 'above') !== false || strpos($query, 'over') !== false || strpos($query, 'more than') !== false) {
                $filters['min_price'] = $amount * 100000;
            } else {
                // If no indicator, assume it's a maximum price
                $filters['max_price'] = $amount * 100000;
            }
        }
        
        // Match price range (e.g., "50 to 80 lakh", "1 to 2 crore")
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:to|-|and)\s*(\d+(?:\.\d+)?)\s*(lakh|lac|cr|crore)/i', $query, $matches)) {
            $minAmount = (float)$matches[1];
            $maxAmount = (float)$matches[2];
            $unit = strtolower($matches[3]);
            
            // Convert to base amount (lakhs)
            $multiplier = in_array($unit, ['cr', 'crore']) ? 100 : 1;
            $filters['min_price'] = $minAmount * $multiplier * 100000;
            $filters['max_price'] = $maxAmount * $multiplier * 100000;
        }
    }
    
    /**
     * Extract bedroom and bathroom requirements
     */
    private function extractRoomRequirements($query, &$filters) {
        // Match BHK pattern (e.g., "2 BHK", "3BHK")
        if (preg_match('/(\d+)\s*(?:bhk|bedroom|bed|bed room|beds)/i', $query, $matches)) {
            $filters['min_bedrooms'] = (int)$matches[1];
            $filters['max_bedrooms'] = (int)$matches[1];
        }
        
        // Match bathroom pattern
        if (preg_match('/(\d+)\s*(?:bath|bathroom|bath room|baths)/i', $query, $matches)) {
            $filters['min_bathrooms'] = (int)$matches[1];
        }
        
        // Match studio/1RK
        if (strpos($query, 'studio') !== false || strpos($query, '1 rk') !== false || strpos($query, '1rk') !== false) {
            $filters['type'] = 'apartment';
            $filters['min_bedrooms'] = 0;
            $filters['max_bedrooms'] = 1;
        }
    }
    
    /**
     * Extract location from query
     */
    private function extractLocation($query, &$filters) {
        // This would ideally query a locations database
        // For now, we'll just look for common location indicators
        $locationIndicators = ['in ', 'at ', 'near ', 'around ', 'close to '];
        $location = '';
        
        foreach ($locationIndicators as $indicator) {
            if (($pos = strpos($query, $indicator)) !== false) {
                $location = trim(substr($query, $pos + strlen($indicator)));
                // Remove any trailing prepositions or other words
                $location = preg_replace('/\s+(for|with|and|or|under|above|near|in|at|around|close to|that|have|has|having)\s+.*$/', '', $location);
                $filters['location'] = trim($location);
                break;
            }
        }
        
        // If no location indicator found, assume the last word might be a location
        if (empty($filters['location'])) {
            $words = array_filter(explode(' ', $query));
            if (count($words) > 1) {
                $potentialLocation = end($words);
                // Simple check if it might be a location (not a number, not a common word)
                if (!is_numeric($potentialLocation) && strlen($potentialLocation) > 2) {
                    $filters['location'] = $potentialLocation;
                }
            }
        }
    }
    
    /**
     * Extract property features
     */
    private function extractFeatures($query, &$filters) {
        // Check for furnished status
        if (strpos($query, 'furnished') !== false) {
            $filters['furnishing'] = 'furnished';
        } elseif (strpos($query, 'semi-furnished') !== false || strpos($query, 'semi furnished') !== false) {
            $filters['furnishing'] = 'semi-furnished';
        } elseif (strpos($query, 'unfurnished') !== false) {
            $filters['furnishing'] = 'unfurnished';
        }
        
        // Check for direction facing
        $directions = ['north', 'south', 'east', 'west', 'north-east', 'north east', 'north-west', 'north west', 
                      'south-east', 'south east', 'south-west', 'south west'];
        foreach ($directions as $dir) {
            if (strpos($query, $dir) !== false) {
                $filters['facing'] = str_replace(' ', '-', $dir);
                break;
            }
        }
    }
    
    /**
     * Extract sort order
     */
    private function extractSortOrder($query, &$filters) {
        if (strpos($query, 'cheap') !== false || strpos($query, 'lowest') !== false || strpos($query, 'low price') !== false) {
            $filters['sort_by'] = 'price';
            $filters['sort_order'] = 'asc';
        } elseif (strpos($query, 'expensive') !== false || strpos($query, 'highest') !== false || strpos($query, 'high price') !== false) {
            $filters['sort_by'] = 'price';
            $filters['sort_order'] = 'desc';
        } elseif (strpos($query, 'newest') !== false || strpos($query, 'latest') !== false || strpos($query, 'recent') !== false) {
            $filters['sort_by'] = 'created_at';
            $filters['sort_order'] = 'desc';
        } elseif (strpos($query, 'oldest') !== false) {
            $filters['sort_by'] = 'created_at';
            $filters['sort_order'] = 'asc';
        }
    }
    
    /**
     * Get user preferences for property recommendations
     * 
     * @param int $userId User ID to get preferences for
     * @return array User preferences
     */
    private function getUserPreferences($userId) {
        if (empty($userId)) {
            return [
                'preferred_property_types' => [],
                'preferred_locations' => [],
                'min_bedrooms' => null,
                'max_price' => null,
                'min_area' => null,
                'amenities' => []
            ];
        }

        try {
            // Initialize default preferences
            $prefs = [
                'preferred_property_types' => [],
                'preferred_locations' => [],
                'min_bedrooms' => null,
                'max_price' => null,
                'min_area' => null,
                'amenities' => []
            ];

            // Get basic preferences
            $stmt = $this->conn->prepare("
                SELECT 
                    preferred_property_types, 
                    preferred_locations,
                    min_bedrooms,
                    max_price,
                    min_area
                FROM user_preferences 
                WHERE user_id = ?
            ");
            
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    // Convert comma-separated strings to arrays
                    $prefs['preferred_property_types'] = !empty($row['preferred_property_types']) ? 
                        explode(',', $row['preferred_property_types']) : [];
                    $prefs['preferred_locations'] = !empty($row['preferred_locations']) ? 
                        explode(',', $row['preferred_locations']) : [];
                    $prefs['min_bedrooms'] = $row['min_bedrooms'];
                    $prefs['max_price'] = $row['max_price'];
                    $prefs['min_area'] = $row['min_area'];
                }
                $stmt->close();
            }
            
            // Get preferred amenities
            $stmt = $this->conn->prepare("
                SELECT a.name 
                FROM user_amenity_preferences uap
                JOIN amenities a ON uap.amenity_id = a.id
                WHERE uap.user_id = ?
            ");
            
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $amenities = [];
                
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $amenities[] = $row['name'];
                    }
                }
                $prefs['amenities'] = $amenities;
                $stmt->close();
            }
            
            return $prefs;
            
        } catch (Exception $e) {
            error_log("Error getting user preferences: " . $e->getMessage());
            return [
                'preferred_property_types' => [],
                'preferred_locations' => [],
                'min_bedrooms' => null,
                'max_price' => null,
                'min_area' => null,
                'amenities' => []
            ];
        }
    }

    /**
     * Get property by ID with caching
     * 
     * @param int $id Property ID
     * @return array|null Property data or null if not found
     */
    private function getPropertyById($id) {
        // Check cache first
        if (isset($this->propertyCache[$id])) {
            return $this->propertyCache[$id];
        }

        try {
            $stmt = $this->conn->prepare("
                SELECT p.*, 
                       u.first_name as agent_name,
                       u.phone as agent_phone,
                       u.email as agent_email
                FROM properties p
                LEFT JOIN users u ON p.agent_id = u.id
                WHERE p.id = ?
            ");

            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return null;
            }

            $property = $result->fetch_assoc();

            // Get property images
            $imagesStmt = $this->conn->prepare("
                SELECT image_url, is_primary 
                FROM property_images 
                WHERE property_id = ? 
                ORDER BY is_primary DESC, id ASC
            ");

            $imagesStmt->bind_param('i', $id);
            $imagesStmt->execute();
            $imagesResult = $imagesStmt->get_result();

            $images = [];
            while ($row = $imagesResult->fetch_assoc()) {
                $images[] = $row['image_url'];
                if ($row['is_primary']) {
                    $property['main_image'] = $row['image_url'];
                }
            }

            $property['images'] = $images;

            // Set default image if none is marked as primary
            if (empty($property['main_image']) && !empty($images)) {
                $property['main_image'] = $images[0];
            }

            // Cache the property
            $this->propertyCache[$id] = $property;

            return $property;

        } catch (Exception $e) {
            error_log('Error in getPropertyById: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a unique conversation ID
     */
    private function generateConversationId() {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Clean up old conversations from memory
     */
    private function cleanupOldConversations($maxAge = 3600) {
        $now = time();
        foreach ($this->conversationContexts as $id => $context) {
            if ($now - $context['last_activity'] > $maxAge) {
                unset($this->conversationContexts[$id]);
            }
        }
    }
    
    /**
     * Check if the message is a greeting
     */
    private function isGreeting($message) {
        $greetings = ['hi', 'hello', 'hey', 'greetings', 'good morning', 'good afternoon', 'good evening'];
        $message = strtolower(trim($message));
        
        foreach ($greetings as $greeting) {
            if (strpos($message, $greeting) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get a random response from an array of possible responses
     */
    private function getRandomResponse($responses) {
        return $responses[array_rand($responses)];
    }
    
    /**
     * Detect search intent from message
     */
    private function detectSearchIntent($message) {
        $filters = [
            'keywords' => [],
            'type' => null,
            'min_price' => null,
            'max_price' => null,
            'min_bedrooms' => null,
            'max_bedrooms' => null,
            'min_bathrooms' => null,
            'location' => null,
            'furnishing' => null,
            'facing' => null,
            'keywords_operator' => 'AND',
            'sort_by' => 'relevance',
            'sort_order' => 'desc',
            'page' => 1,
            'per_page' => 10
        ];
        
        // Convert to lowercase for case-insensitive matching
        $query = strtolower(trim($message));
        
        // Extract property types
        $this->extractPropertyType($query, $filters);
        
        // Extract price range
        $this->extractPriceRange($query, $filters);
        
        // Extract bedroom/bathroom requirements
        $this->extractRoomRequirements($query, $filters);
        
        // Extract location
        $this->extractLocation($query, $filters);
        
        // Extract property features
        $this->extractFeatures($query, $filters);
        
        // Extract sort order if specified
        $this->extractSortOrder($query, $filters);
        
        // Extract keywords (remaining words that weren't matched as other filters)
        $this->extractKeywords($query, $filters);
        
        return $filters;
    }
    
    /**
     * Handle search intent
     */
    private function handleSearchIntent($filters, &$context) {
        // Update context with new filters
        $context['filters'] = array_merge($context['filters'] ?? [], $filters);
        
        // Get properties matching the filters
        $properties = $this->getRecommendedProperties(null, 3, $context['filters']);
        
        if (empty($properties)) {
            return [
                'response' => 'I couldn\'t find any properties matching your criteria. Would you like to try different search parameters?',
                'data' => ['filters' => $context['filters']]
            ];
        }
        
        // Format response with property details
        $response = "I found some properties that match your criteria:\n\n";
        
        foreach ($properties as $i => $property) {
            $response .= sprintf(
                "%d. %s in %s - %s\n%s\n\n",
                $i + 1,
                ucfirst($property['type']),
                $property['location'],
                $this->formatPrice($property['price']),
                $property['title']
            );
        }
        
        $response .= "\nWould you like more details about any of these properties?";
        
        return [
            'response' => $response,
            'data' => [
                'properties' => $properties,
                'filters' => $context['filters']
            ]
        ];
    }
    
    /**
     * Check if the message is a price prediction request
     */
    private function isPricePredictionRequest($message) {
        $keywords = ['price', 'cost', 'worth', 'value', 'estimate', 'prediction'];
        $message = strtolower($message);
        
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Handle price prediction request
     */
    private function handlePricePredictionRequest($message, &$context) {
        // Extract property details from message
        $filters = $this->extractSearchFilters($message);
        
        // Require at least area and location for prediction
        if (empty($filters['location'])) {
            return [
                'response' => 'I need to know the location to estimate the price. Could you please specify the area or locality?'
            ];
        }
        
        // Mock prediction for demo
        $prediction = $this->predictPrice([
            'location' => $filters['location'],
            'property_type' => $filters['type'] ?? 'apartment',
            'area' => $filters['area'] ?? 1000, // Default 1000 sq.ft
            'bedrooms' => $filters['bedrooms'] ?? 2,
            'bathrooms' => $filters['bathrooms'] ?? 2,
            'year_built' => date('Y') - 5, // 5 years old
            'condition' => 'good'
        ]);
        
        if (empty($prediction)) {
            return [
                'response' => 'I couldn\'t generate a price estimate at the moment. Please try again later.'
            ];
        }
        
        $response = sprintf(
            "Based on the information provided, the estimated price for a %s in %s is approximately %s. " .
            "This is based on an area of %d sq.ft and %d bedrooms.\n\n" .
            "Note: This is an estimate only. For a more accurate valuation, please contact one of our agents.",
            $filters['type'] ?? 'property',
            $filters['location'],
            $this->formatPrice($prediction['predicted_price']),
            $filters['area'] ?? 1000,
            $filters['bedrooms'] ?? 2
        );
        
        return [
            'response' => $response,
            'data' => [
                'prediction' => $prediction,
                'filters' => $filters
            ]
        ];
    }
    
    /**
     * Format price with currency and commas
     */
    private function formatPrice($price) {
        if ($price >= 10000000) { // Crores
            return '₹' . number_format($price / 10000000, 2) . ' Cr';
        } elseif ($price >= 100000) { // Lakhs
            return '₹' . number_format($price / 100000, 2) . ' L';
        } else {
            return '₹' . number_format($price);
        }
    }
    
    /**
     * Get order by clause based on sort preference
     * 
     * @param string $sortPreference Sort preference (e.g., 'price_asc', 'price_desc', 'newest')
     * @return string SQL ORDER BY clause
     */
    private function getOrderByClause($sortPreference) {
        switch ($sortPreference) {
            case 'price_asc':
                return 'p.price ASC';
            case 'price_desc':
                return 'p.price DESC';
            case 'newest':
                return 'p.created_at DESC';
            case 'oldest':
                return 'p.created_at ASC';
            case 'area_asc':
                return 'p.area ASC';
            case 'area_desc':
                return 'p.area DESC';
            default:
                return 'p.created_at DESC';
        }
    }
    
    /**
     * Get mock price prediction for demo purposes
     * 
     * @param array $features Property features
     * @return array Mock prediction data
     */
    private function getMockPricePrediction($features) {
        // Base price by property type
        $basePrice = 0;
        
        switch (strtolower($features['property_type'])) {
            case 'villa':
                $basePrice = 15000000; // 1.5 Cr
                break;
            case 'apartment':
                $basePrice = 8000000; // 80 Lakhs
                break;
            case 'plot':
                $basePrice = 5000000; // 50 Lakhs
                break;
            default:
                $basePrice = 7000000; // 70 Lakhs
        }
        
        // Adjust for area (assuming price per sq.ft)
        $pricePerSqFt = $basePrice / 2000; // Example rate
        $areaAdjusted = $features['area'] * $pricePerSqFt;
        
        // Adjust for bedrooms
        $bedroomAdjustment = $features['bedrooms'] * 500000; // 5 Lakhs per bedroom
        
        // Adjust for condition
        $conditionMultiplier = 1.0;
        switch (strtolower($features['condition'])) {
            case 'excellent':
                $conditionMultiplier = 1.2;
                break;
            case 'good':
                $conditionMultiplier = 1.0;
                break;
            case 'needs_repair':
                $conditionMultiplier = 0.8;
                break;
        }
        
        // Calculate final price
        $predictedPrice = ($areaAdjusted + $bedroomAdjustment) * $conditionMultiplier;
        
        // Add some random variation (real model wouldn't need this)
        $variation = 1 + (mt_rand(-10, 10) / 100); // ±10% variation
        $predictedPrice *= $variation;
        
        // Round to nearest 10000
        $predictedPrice = round($predictedPrice / 10000) * 10000;
        
        return [
            'predicted_price' => $predictedPrice,
            'confidence' => mt_rand(80, 95) / 100, // 80-95% confidence
            'factors' => [
                'property_type' => $features['property_type'],
                'area' => $features['area'],
                'bedrooms' => $features['bedrooms'],
                'condition' => $features['condition'],
                'base_price' => $basePrice,
                'price_per_sqft' => $pricePerSqFt,
                'condition_multiplier' => $conditionMultiplier
            ]
        ];
    }
    
    /**
     * Process natural language search query to extract property search parameters
     * 
     * @param string $query Natural language search query (e.g., "3 BHK apartment in Mumbai under 1.5 crore")
     * @return array Structured search filters compatible with the property search
     */
    public function processNaturalLanguageQuery($query) {
        try {
            // Initialize default filters
            $filters = [
                'keywords' => [],
                'type' => null,
                'min_price' => null,
                'max_price' => null,
                'min_bedrooms' => null,
                'max_bedrooms' => null,
                'min_bathrooms' => null,
                'location' => null,
                'furnishing' => null,
                'facing' => null,
                'keywords_operator' => 'AND',
                'sort_by' => 'relevance',
                'sort_order' => 'desc',
                'page' => 1,
                'per_page' => 10
            ];
            
            // Convert to lowercase for case-insensitive matching
            $query = strtolower(trim($query));
            
            // Extract property types
            $this->extractPropertyType($query, $filters);
            
            // Extract price range
            $this->extractPriceRange($query, $filters);
            
            // Extract bedroom/bathroom requirements
            $this->extractRoomRequirements($query, $filters);
            
            // Extract location
            $this->extractLocation($query, $filters);
            
            // Extract property features
            $this->extractFeatures($query, $filters);
            
            // Extract sort order if specified
            $this->extractSortOrder($query, $filters);
            
            // Extract keywords (remaining words that weren't matched as other filters)
            $this->extractKeywords($query, $filters);
            
            return $filters;
            
        } catch (Exception $e) {
            error_log("Error in processNaturalLanguageQuery: " . $e->getMessage());
            // Return a basic filter with just the keywords if processing fails
            return [
                'keywords' => array_filter(explode(' ', preg_replace('/[^a-z0-9\s]/', '', strtolower($query)))),
                'keywords_operator' => 'AND'
            ];
        }
    }
    
    /**
     * Extract property type from query
     */
    private function extractPropertyType(&$query, &$filters) {
        $propertyTypes = [
            'apartment' => ['apartment', 'flat', 'apt'],
            'villa' => ['villa', 'bungalow', 'house', 'home'],
            'plot' => ['plot', 'land', 'empty land'],
            'office' => ['office', 'commercial office', 'workspace'],
            'shop' => ['shop', 'retail', 'store', 'showroom'],
            'warehouse' => ['warehouse', 'godown', 'storage']
        ];
        
        foreach ($propertyTypes as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($query, $keyword) !== false) {
                    $filters['type'] = $type;
                    $query = str_replace($keyword, '', $query);
                    break 2;
                }
            }
        }
    }
    
    /**
     * Extract price range from query
     */
    private function extractPriceRange($query, &$filters) {
        // Match prices in lakhs (e.g., "50 lakh", "1.5 crore")
        if (preg_match('/(\d+(?:\.\d+)?)\s*(lakh|lac|cr|crore)/', $query, $matches)) {
            $amount = (float)$matches[1];
            $unit = $matches[2];
            
            // Convert to base amount (lakhs)
            if (in_array($unit, ['cr', 'crore'])) {
                $amount *= 100; // 1 crore = 100 lakhs
            }
            
            // Check for price range indicators
            if (strpos($query, 'under') !== false || strpos($query, 'below') !== false || strpos($query, 'less than') !== false) {
                $filters['max_price'] = $amount * 100000; // Convert to actual amount
            } elseif (strpos($query, 'above') !== false || strpos($query, 'over') !== false || strpos($query, 'more than') !== false) {
                $filters['min_price'] = $amount * 100000;
            } else {
                // If no indicator, assume it's a maximum price
                $filters['max_price'] = $amount * 100000;
            }
        }
        
        // Match price range (e.g., "50 to 80 lakh", "1 to 2 crore")
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:to|-|and)\s*(\d+(?:\.\d+)?)\s*(lakh|lac|cr|crore)/i', $query, $matches)) {
            $minAmount = (float)$matches[1];
            $maxAmount = (float)$matches[2];
            $unit = strtolower($matches[3]);
            
            // Convert to base amount (lakhs)
            $multiplier = in_array($unit, ['cr', 'crore']) ? 100 : 1;
            $filters['min_price'] = $minAmount * $multiplier * 100000;
            $filters['max_price'] = $maxAmount * $multiplier * 100000;
        }
    }
    
    /**
     * Extract bedroom and bathroom requirements
     */
    private function extractRoomRequirements($query, &$filters) {
        // Match BHK pattern (e.g., "2 BHK", "3BHK")
        if (preg_match('/(\d+)\s*(?:bhk|bedroom|bed|bed room|beds)/i', $query, $matches)) {
            $filters['min_bedrooms'] = (int)$matches[1];
            $filters['max_bedrooms'] = (int)$matches[1];
        }
        
        // Match bathroom pattern
        if (preg_match('/(\d+)\s*(?:bath|bathroom|bath room|baths)/i', $query, $matches)) {
            $filters['min_bathrooms'] = (int)$matches[1];
        }
        
        // Match studio/1RK
        if (strpos($query, 'studio') !== false || strpos($query, '1 rk') !== false || strpos($query, '1rk') !== false) {
            $filters['type'] = 'apartment';
            $filters['min_bedrooms'] = 0;
            $filters['max_bedrooms'] = 1;
        }
    }
    
    /**
     * Extract location from query
     */
    private function extractLocation($query, &$filters) {
        // This would ideally query a locations database
        // For now, we'll just look for common location indicators
        $locationIndicators = ['in ', 'at ', 'near ', 'around ', 'close to '];
        $location = '';
        
        foreach ($locationIndicators as $indicator) {
            if (($pos = strpos($query, $indicator)) !== false) {
                $location = trim(substr($query, $pos + strlen($indicator)));
                // Remove any trailing prepositions or other words
                $location = preg_replace('/\s+(for|with|and|or|under|above|near|in|at|around|close to|that|have|has|having)\s+.*$/', '', $location);
                $filters['location'] = trim($location);
                break;
            }
        }
        
        // If no location indicator found, assume the last word might be a location
        if (empty($filters['location'])) {
            $words = array_filter(explode(' ', $query));
            if (count($words) > 1) {
                $potentialLocation = end($words);
                // Simple check if it might be a location (not a number, not a common word)
                if (!is_numeric($potentialLocation) && strlen($potentialLocation) > 2) {
                    $filters['location'] = $potentialLocation;
                }
            }
        }
    }
    
    /**
     * Extract property features
     */
    private function extractFeatures($query, &$filters) {
        // Check for furnished status
        if (strpos($query, 'furnished') !== false) {
            $filters['furnishing'] = 'furnished';
        } elseif (strpos($query, 'semi-furnished') !== false || strpos($query, 'semi furnished') !== false) {
            $filters['furnishing'] = 'semi-furnished';
        } elseif (strpos($query, 'unfurnished') !== false) {
            $filters['furnishing'] = 'unfurnished';
        }
        
        // Check for direction facing
        $directions = ['north', 'south', 'east', 'west', 'north-east', 'north east', 'north-west', 'north west', 
                      'south-east', 'south east', 'south-west', 'south west'];
        foreach ($directions as $dir) {
            if (strpos($query, $dir) !== false) {
                $filters['facing'] = str_replace(' ', '-', $dir);
                break;
            }
        }
    }
    
    /**
     * Extract sort order
     */
    private function extractSortOrder($query, &$filters) {
        if (strpos($query, 'cheap') !== false || strpos($query, 'lowest') !== false || strpos($query, 'low price') !== false) {
            $filters['sort_by'] = 'price';
            $filters['sort_order'] = 'asc';
        } elseif (strpos($query, 'expensive') !== false || strpos($query, 'highest') !== false || strpos($query, 'high price') !== false) {
            $filters['sort_by'] = 'price';
            $filters['sort_order'] = 'desc';
        } elseif (strpos($query, 'newest') !== false || strpos($query, 'latest') !== false || strpos($query, 'recent') !== false) {
            $filters['sort_by'] = 'created_at';
            $filters['sort_order'] = 'desc';
        } elseif (strpos($query, 'oldest') !== false) {
            $filters['sort_by'] = 'created_at';
            $filters['sort_order'] = 'asc';
        }
    }
    
    /**
     * Extract remaining keywords
     */
    private function extractKeywords($query, &$filters) {
        // Remove all the patterns we've already processed
        $patterns = [
            '/\b(?:apartment|flat|villa|house|plot|land|office|shop|warehouse|studio|1rk|1 rk)\b/i',
            '/\b\d+\s*(?:bhk|bedroom|bed|bath|bathroom|bath room|beds|baths)\b/i',
            '/\b(?:under|below|less than|above|over|more than)\s*\d+\s*(?:lakh|lac|cr|crore)\b/i',
            '/\d+\s*(?:to|-|and)\s*\d+\s*(?:lakh|lac|cr|crore)/i',
            '/\b(?:in|at|near|around|close to)\s+[\w\s]+/i',
            '/\b(?:furnished|semi-furnished|semi furnished|unfurnished)\b/i',
            '/\b(?:north|south|east|west|north[ -]east|north[ -]west|south[ -]east|south[ -]west)\b/i',
            '/\b(?:cheap|expensive|lowest|highest|newest|latest|recent|oldest|price|created_at)\b/i'
        ];
        
        $cleanQuery = preg_replace($patterns, '', $query);
        $keywords = array_filter(array_map('trim', preg_split('/\s+/', $cleanQuery)));
        
        // Remove short words and common stop words
        $stopWords = ['a', 'an', 'the', 'and', 'or', 'but', 'is', 'are', 'was', 'were', 'for', 'to', 'of', 'with', 'that', 'this', 'i', 'me', 'my', 'we', 'our', 'you', 'your', 'yours'];
        $keywords = array_filter($keywords, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array(strtolower($word), $stopWords);
        });
        
        if (!empty($keywords)) {
            $filters['keywords'] = array_values(array_unique($keywords));
            
            // If we have multiple keywords, use AND operator by default
            if (count($filters['keywords']) > 1) {
                $filters['keywords_operator'] = 'AND';
            }
        }
    }
}
?>
