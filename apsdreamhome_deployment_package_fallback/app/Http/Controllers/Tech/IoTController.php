<?php

/**
 * IoT Smart Home Controller
 * Handles IoT device integration and smart home features
 */

namespace App\Http\Controllers\Tech;

use App\Controllers\BaseController;
use Exception;

class IoTController extends BaseController
{

    /**
     * Smart home dashboard for properties
     */
    public function smartHomeDashboard($property_id)
    {
        $property = $this->getPropertyDetails($property_id);

        if (!$property) {
            $this->setFlash('error', 'Property not found');
            $this->redirect(BASE_URL . 'properties');
            return;
        }

        $iot_devices = $this->getIoTDevices($property_id);
        $smart_features = $this->getSmartFeatures($property_id);

        $this->data['page_title'] = 'Smart Home Dashboard - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['iot_devices'] = $iot_devices;
        $this->data['smart_features'] = $smart_features;

        $this->render('iot/smart_home_dashboard');
    }

    /**
     * IoT device management
     */
    public function manageDevices()
    {
        if (!$this->isLoggedIn() || (!$this->isAdmin() && !isset($_SESSION['user_role']))) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->addIoTDevice($_POST);
        }

        $devices = $this->getAllIoTDevices();
        $device_types = $this->getDeviceTypes();

        $this->data['page_title'] = 'IoT Device Management - ' . APP_NAME;
        $this->data['devices'] = $devices;
        $this->data['device_types'] = $device_types;

        $this->render('iot/manage_devices');
    }

    /**
     * IoT device control interface
     */
    public function deviceControl($device_id)
    {
        $device = $this->getIoTDevice($device_id);

        if (!$device) {
            $this->setFlash('error', 'Device not found');
            $this->redirect(BASE_URL . 'iot/devices');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->controlIoTDevice($device_id, $_POST);
        }

        $device_history = $this->getDeviceHistory($device_id);

        $this->data['page_title'] = 'Device Control - ' . $device['device_name'];
        $this->data['device'] = $device;
        $this->data['device_history'] = $device_history;

        $this->render('iot/device_control');
    }

    /**
     * Smart home automation rules
     */
    public function automationRules($property_id)
    {
        $property = $this->getPropertyDetails($property_id);

        if (!$property) {
            $this->setFlash('error', 'Property not found');
            $this->redirect(BASE_URL . 'properties');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->createAutomationRule($property_id, $_POST);
        }

        $automation_rules = $this->getAutomationRules($property_id);

        $this->data['page_title'] = 'Smart Home Automation - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['automation_rules'] = $automation_rules;

        $this->render('iot/automation_rules');
    }

    /**
     * Energy monitoring dashboard
     */
    public function energyMonitoring($property_id)
    {
        $property = $this->getPropertyDetails($property_id);
        $energy_data = $this->getEnergyData($property_id);

        $this->data['page_title'] = 'Energy Monitoring - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['energy_data'] = $energy_data;

        $this->render('iot/energy_monitoring');
    }

    /**
     * Security system monitoring
     */
    public function securityMonitoring($property_id)
    {
        $property = $this->getPropertyDetails($property_id);
        $security_data = $this->getSecurityData($property_id);

        $this->data['page_title'] = 'Security Monitoring - ' . $property['title'];
        $this->data['property'] = $property;
        $this->data['security_data'] = $security_data;

        $this->render('iot/security_monitoring');
    }

    /**
     * API - Get IoT device status
     */
    public function apiDeviceStatus($device_id)
    {
        header('Content-Type: application/json');

        $device = $this->getIoTDevice($device_id);
        $status = $this->getDeviceStatus($device_id);

        sendJsonResponse([
            'success' => true,
            'device' => $device,
            'status' => $status,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * API - Control IoT device
     */
    public function apiControlDevice()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['device_id']) || !isset($input['command'])) {
            sendJsonResponse(['success' => false, 'error' => 'Device ID and command required'], 400);
        }

        $success = $this->controlIoTDevice($input['device_id'], $input);

        sendJsonResponse([
            'success' => $success,
            'message' => $success ? 'Device controlled successfully' : 'Failed to control device'
        ]);
    }

    /**
     * API - Get energy consumption data
     */
    public function apiEnergyData($property_id)
    {
        header('Content-Type: application/json');

        $timeframe = $_GET['timeframe'] ?? '24h';
        $energy_data = $this->getEnergyData($property_id, $timeframe);

        sendJsonResponse([
            'success' => true,
            'data' => $energy_data,
            'timeframe' => $timeframe
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
            $stmt = $this->db->prepare("SELECT * FROM properties WHERE id = :id AND status = 'available'");
            $stmt->execute(['id' => $property_id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Property fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get IoT devices for property
     */
    private function getIoTDevices($property_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT d.*, dt.name as device_type_name, dt.icon, dt.category
                    FROM iot_devices d
                    LEFT JOIN iot_device_types dt ON d.device_type_id = dt.id
                    WHERE d.property_id = :propertyId AND d.status = 'active'
                    ORDER BY dt.category, d.device_name";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['propertyId' => $property_id]);

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('IoT devices fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get smart features for property
     */
    private function getSmartFeatures($property_id)
    {
        return [
            'energy_management' => true,
            'security_system' => true,
            'climate_control' => true,
            'lighting_automation' => true,
            'appliance_control' => true,
            'water_management' => false,
            'garden_automation' => false
        ];
    }

    /**
     * Add IoT device
     */
    private function addIoTDevice($device_data)
    {
        try {
            if (!$this->db) {
                return;
            }

            $sql = "INSERT INTO iot_devices (
                property_id, device_type_id, device_name, device_id, mac_address,
                ip_address, location, status, created_at
            ) VALUES (:propertyId, :deviceTypeId, :deviceName, :deviceId, :macAddress, :ipAddress, :location, 'active', NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'propertyId' => $device_data['property_id'],
                'deviceTypeId' => $device_data['device_type_id'],
                'deviceName' => $device_data['device_name'],
                'deviceId' => $device_data['device_id'] ?? null,
                'macAddress' => $device_data['mac_address'] ?? null,
                'ipAddress' => $device_data['ip_address'] ?? null,
                'location' => $device_data['location'] ?? null
            ]);

            if ($success) {
                $this->setFlash('success', 'IoT device added successfully');
                $this->redirect(BASE_URL . 'iot/devices');
            } else {
                $this->setFlash('error', 'Failed to add IoT device');
            }
        } catch (\Exception $e) {
            error_log('IoT device add error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to add IoT device');
        }
    }

    /**
     * Get all IoT devices (admin)
     */
    private function getAllIoTDevices()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT d.*, p.title as property_title, p.city,
                           dt.name as device_type_name
                    FROM iot_devices d
                    LEFT JOIN properties p ON d.property_id = p.id
                    LEFT JOIN iot_device_types dt ON d.device_type_id = dt.id
                    ORDER BY d.created_at DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('All IoT devices fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get device types
     */
    private function getDeviceTypes()
    {
        return [
            'smart_lights' => ['name' => 'Smart Lights', 'icon' => 'fas fa-lightbulb', 'category' => 'lighting'],
            'smart_thermostat' => ['name' => 'Smart Thermostat', 'icon' => 'fas fa-temperature-half', 'category' => 'climate'],
            'smart_lock' => ['name' => 'Smart Door Lock', 'icon' => 'fas fa-lock', 'category' => 'security'],
            'security_camera' => ['name' => 'Security Camera', 'icon' => 'fas fa-video-camera', 'category' => 'security'],
            'smart_plug' => ['name' => 'Smart Plug', 'icon' => 'fas fa-plug', 'category' => 'appliances'],
            'smoke_detector' => ['name' => 'Smoke Detector', 'icon' => 'fas fa-fire-extinguisher', 'category' => 'security'],
            'motion_sensor' => ['name' => 'Motion Sensor', 'icon' => 'fas fa-running', 'category' => 'security'],
            'smart_speaker' => ['name' => 'Smart Speaker', 'icon' => 'fab fa-amazon', 'category' => 'entertainment'],
            'smart_tv' => ['name' => 'Smart TV', 'icon' => 'fas fa-tv', 'category' => 'entertainment'],
            'smart_fridge' => ['name' => 'Smart Refrigerator', 'icon' => 'fas fa-ice-cream', 'category' => 'appliances']
        ];
    }

    /**
     * Get IoT device details
     */
    private function getIoTDevice($device_id)
    {
        try {
            if (!$this->db) {
                return null;
            }

            $sql = "SELECT d.*, p.title as property_title, p.city,
                           dt.name as device_type_name, dt.icon, dt.category
                    FROM iot_devices d
                    LEFT JOIN properties p ON d.property_id = p.id
                    LEFT JOIN iot_device_types dt ON d.device_type_id = dt.id
                    WHERE d.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $device_id]);

            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('IoT device fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Control IoT device
     */
    private function controlIoTDevice($device_id, $command_data)
    {
        try {
            // In production, this would communicate with actual IoT devices
            // For now, we'll simulate the control and log the action

            $device = $this->getIoTDevice($device_id);
            if (!$device) {
                return false;
            }

            // Log the control action
            $this->logDeviceControl($device_id, $command_data);

            // Simulate device response based on command
            $response = [
                'device_id' => $device_id,
                'command' => $command_data['command'],
                'status' => 'executed',
                'timestamp' => date('Y-m-d H:i:s'),
                'response_data' => $this->getSimulatedDeviceResponse($device, $command_data)
            ];

            return true;
        } catch (\Exception $e) {
            error_log('IoT device control error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get device status
     */
    private function getDeviceStatus($device_id)
    {
        // In production, this would query the actual device status
        // For now, return simulated status

        $statuses = ['online', 'offline', 'error', 'maintenance'];
        $random_status = $statuses[array_rand($statuses)];

        return [
            'status' => $random_status,
            'last_seen' => date('Y-m-d H:i:s', time() - rand(0, 3600)),
            'battery_level' => rand(10, 100),
            'signal_strength' => rand(1, 100)
        ];
    }

    /**
     * Get simulated device response
     */
    private function getSimulatedDeviceResponse($device, $command_data)
    {
        switch ($command_data['command']) {
            case 'turn_on':
                return ['power_state' => 'on'];
            case 'turn_off':
                return ['power_state' => 'off'];
            case 'set_temperature':
                return ['temperature' => $command_data['value'] ?? 24, 'unit' => 'celsius'];
            case 'set_brightness':
                return ['brightness' => $command_data['value'] ?? 80, 'percentage' => true];
            case 'lock':
                return ['lock_state' => 'locked'];
            case 'unlock':
                return ['lock_state' => 'unlocked'];
            default:
                return ['status' => 'unknown_command'];
        }
    }

    /**
     * Log device control action
     */
    private function logDeviceControl($device_id, $command_data)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $sql = "INSERT INTO iot_device_logs (device_id, command, parameters, response, user_id, created_at)
                    VALUES (:deviceId, :command, :parameters, :response, :userId, NOW())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'deviceId' => $device_id,
                'command' => $command_data['command'],
                'parameters' => json_encode($command_data),
                'response' => json_encode($this->getSimulatedDeviceResponse(null, $command_data)),
                'userId' => $_SESSION['user_id'] ?? null
            ]);
        } catch (\Exception $e) {
            error_log('Device control log error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get device history
     */
    private function getDeviceHistory($device_id, $limit = 50)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT dl.*, u.name as user_name
                    FROM iot_device_logs dl
                    LEFT JOIN users u ON dl.user_id = u.id
                    WHERE dl.device_id = :deviceId
                    ORDER BY dl.created_at DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            // PDO doesn't always handle LIMIT with named parameters well unless bindValue is used with PDO::PARAM_INT
            // However, in this project's Database class, it might be handled differently.
            // Let's use positional for LIMIT if necessary, or just bind it as string which usually works in MySQL.
            $stmt->execute(['deviceId' => $device_id, 'limit' => (int)$limit]);

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Device history fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create automation rule
     */
    private function createAutomationRule($property_id, $rule_data)
    {
        try {
            if (!$this->db) {
                return;
            }

            $sql = "INSERT INTO iot_automation_rules (
                property_id, rule_name, trigger_condition, action_command,
                is_active, created_at, updated_at
            ) VALUES (:propertyId, :ruleName, :triggerCondition, :actionCommand, 1, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'propertyId' => $property_id,
                'ruleName' => $rule_data['rule_name'],
                'triggerCondition' => json_encode($rule_data['trigger_condition']),
                'actionCommand' => json_encode($rule_data['action_command'])
            ]);

            if ($success) {
                $this->setFlash('success', 'Automation rule created successfully');
                $this->redirect(BASE_URL . 'iot/automation/' . $property_id);
            } else {
                $this->setFlash('error', 'Failed to create automation rule');
            }
        } catch (\Exception $e) {
            error_log('Automation rule creation error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to create automation rule');
        }
    }

    /**
     * Get automation rules for property
     */
    private function getAutomationRules($property_id)
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT * FROM iot_automation_rules
                    WHERE property_id = :propertyId
                    ORDER BY created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['propertyId' => $property_id]);

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Automation rules fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get energy consumption data
     */
    private function getEnergyData($property_id, $timeframe = '24h')
    {
        // In production, this would fetch real energy meter data
        // For now, return simulated data

        $hours = $timeframe === '24h' ? 24 : ($timeframe === '7d' ? 168 : 720); // 30 days
        $energy_data = [];

        for ($i = $hours; $i >= 0; $i--) {
            $timestamp = date('Y-m-d H:i:s', time() - ($i * 3600));

            $energy_data[] = [
                'timestamp' => $timestamp,
                'consumption_kwh' => rand(50, 200) / 10, // 5-20 kWh
                'cost' => rand(50, 200), // ₹50-200
                'appliances' => [
                    'lighting' => rand(10, 30),
                    'hvac' => rand(20, 60),
                    'appliances' => rand(10, 40),
                    'other' => rand(5, 20)
                ]
            ];
        }

        return $energy_data;
    }

    /**
     * Get security monitoring data
     */
    private function getSecurityData($property_id)
    {
        return [
            'system_status' => 'armed',
            'last_motion' => date('Y-m-d H:i:s', time() - rand(300, 3600)),
            'door_status' => ['front' => 'locked', 'back' => 'locked', 'garage' => 'closed'],
            'camera_status' => ['living_room' => 'online', 'front_door' => 'online', 'backyard' => 'offline'],
            'recent_alerts' => [
                ['type' => 'motion_detected', 'location' => 'Backyard', 'timestamp' => date('Y-m-d H:i:s', time() - 1800)],
                ['type' => 'door_unlocked', 'location' => 'Front Door', 'timestamp' => date('Y-m-d H:i:s', time() - 7200)]
            ]
        ];
    }

    /**
     * Admin - IoT analytics
     */
    public function iotAnalytics()
    {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $analytics_data = [
            'device_stats' => $this->getDeviceStats(),
            'energy_savings' => $this->getEnergySavingsStats(),
            'automation_efficiency' => $this->getAutomationEfficiency(),
            'security_incidents' => $this->getSecurityIncidents()
        ];

        $this->data['page_title'] = 'IoT Analytics - ' . APP_NAME;
        $this->data['analytics'] = $analytics_data;

        $this->render('admin/iot_analytics');
    }

    /**
     * Get device statistics
     */
    private function getDeviceStats()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $sql = "SELECT
                        COUNT(*) as total_devices,
                        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_devices,
                        COUNT(CASE WHEN status = 'offline' THEN 1 END) as offline_devices,
                        COUNT(DISTINCT property_id) as properties_with_iot
                    FROM iot_devices";

            $stmt = $this->db->query($sql);
            return $stmt->fetch();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get energy savings statistics
     */
    private function getEnergySavingsStats()
    {
        return [
            'total_savings' => 125000, // ₹1.25 lakhs
            'avg_monthly_savings' => 8500, // per property
            'co2_reduction' => 2.5, // tons per month
            'top_saving_devices' => [
                'Smart Thermostat' => 35,
                'LED Lighting' => 25,
                'Smart Appliances' => 20
            ]
        ];
    }

    /**
     * Get automation efficiency
     */
    private function getAutomationEfficiency()
    {
        return [
            'automation_rules' => 245,
            'avg_execution_time' => '0.8 seconds',
            'success_rate' => '97.5%',
            'energy_saved_by_automation' => '18%'
        ];
    }

    /**
     * Get security incidents
     */
    private function getSecurityIncidents()
    {
        return [
            'total_incidents' => 12,
            'false_alarms' => 3,
            'real_incidents' => 9,
            'response_time' => '2.3 minutes',
            'prevention_rate' => '94%'
        ];
    }

    /**
     * Smart home compatibility checker
     */
    public function compatibilityCheck()
    {
        header('Content-Type: application/json');

        $property_specs = json_decode(file_get_contents('php://input'), true);

        if (!$property_specs) {
            sendJsonResponse(['success' => false, 'error' => 'Property specifications required'], 400);
        }

        $compatibility = $this->checkSmartHomeCompatibility($property_specs);

        sendJsonResponse([
            'success' => true,
            'data' => $compatibility
        ]);
    }

    /**
     * Check smart home compatibility
     */
    private function checkSmartHomeCompatibility($property_specs)
    {
        $compatibility_score = 0;
        $recommendations = [];
        $required_upgrades = [];

        // Check electrical system
        if (($property_specs['electrical_capacity'] ?? 0) >= 10) {
            $compatibility_score += 25;
        } else {
            $required_upgrades[] = 'Electrical system upgrade (minimum 10kW capacity required)';
        }

        // Check wiring
        if (isset($property_specs['smart_wiring']) && $property_specs['smart_wiring']) {
            $compatibility_score += 20;
        } else {
            $recommendations[] = 'Consider smart wiring for better IoT integration';
        }

        // Check internet connectivity
        if (($property_specs['internet_speed'] ?? 0) >= 50) {
            $compatibility_score += 15;
        } else {
            $required_upgrades[] = 'High-speed internet (minimum 50 Mbps) required for smart home features';
        }

        // Check property age
        if (($property_specs['property_age'] ?? 100) <= 20) {
            $compatibility_score += 15;
        } else {
            $recommendations[] = 'Older properties may require additional wiring work';
        }

        // Check available space for devices
        if (($property_specs['smart_ready'] ?? false)) {
            $compatibility_score += 15;
        } else {
            $recommendations[] = 'Property can be made smart-ready with minimal modifications';
        }

        // Determine compatibility level
        if ($compatibility_score >= 80) {
            $level = 'excellent';
        } elseif ($compatibility_score >= 60) {
            $level = 'good';
        } elseif ($compatibility_score >= 40) {
            $level = 'moderate';
        } else {
            $level = 'requires_upgrades';
        }

        return [
            'compatibility_score' => $compatibility_score,
            'compatibility_level' => $level,
            'recommendations' => $recommendations,
            'required_upgrades' => $required_upgrades,
            'estimated_cost' => $this->estimateSmartHomeCost($compatibility_score, $property_specs)
        ];
    }

    /**
     * Estimate smart home installation cost
     */
    private function estimateSmartHomeCost($compatibility_score, $property_specs)
    {
        $base_cost = 50000; // Base smart home setup cost

        if ($compatibility_score < 60) {
            $base_cost += 30000; // Additional wiring and infrastructure
        }

        $property_size = $property_specs['area_sqft'] ?? 1000;
        $cost_per_sqft = $property_size > 2000 ? 150 : 100;

        $total_cost = $base_cost + ($property_size * $cost_per_sqft);

        return [
            'total_cost' => $total_cost,
            'breakdown' => [
                'basic_setup' => $base_cost,
                'wiring_infrastructure' => $compatibility_score < 60 ? 30000 : 0,
                'per_sqft_cost' => $property_size * $cost_per_sqft,
                'installation_labor' => $total_cost * 0.2
            ],
            'roi_months' => 18 // Average ROI period
        ];
    }

    /**
     * Smart home device catalog
     */
    public function deviceCatalog()
    {
        $catalog = [
            'lighting' => [
                'smart_bulbs' => ['price' => 2500, 'features' => ['Color changing', 'Voice control', 'Energy monitoring']],
                'smart_switches' => ['price' => 1800, 'features' => ['Remote control', 'Scheduling', 'Energy saving']],
                'smart_strips' => ['price' => 3200, 'features' => ['Multiple outlets', 'USB charging', 'Surge protection']]
            ],
            'security' => [
                'smart_locks' => ['price' => 15000, 'features' => ['Keyless entry', 'Remote access', 'Access logs']],
                'security_cameras' => ['price' => 8000, 'features' => ['HD video', 'Night vision', 'Motion detection']],
                'doorbells' => ['price' => 12000, 'features' => ['Video doorbell', 'Two-way audio', 'Motion alerts']]
            ],
            'climate' => [
                'smart_thermostat' => ['price' => 22000, 'features' => ['Learning algorithms', 'Energy optimization', 'Remote control']],
                'smart_ac' => ['price' => 35000, 'features' => ['Voice control', 'Scheduling', 'Energy monitoring']]
            ],
            'appliances' => [
                'smart_fridge' => ['price' => 85000, 'features' => ['Inventory tracking', 'Recipe suggestions', 'Energy monitoring']],
                'smart_washing_machine' => ['price' => 45000, 'features' => ['Auto-dispense', 'Remote start', 'Energy optimization']]
            ]
        ];

        $this->data['page_title'] = 'Smart Home Device Catalog - ' . APP_NAME;
        $this->data['catalog'] = $catalog;

        $this->render('iot/device_catalog');
    }

    /**
     * IoT device integration API
     */
    public function apiDeviceIntegration()
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'register_device':
                $this->registerIoTDevice();
                break;
            case 'device_heartbeat':
                $this->deviceHeartbeat();
                break;
            case 'send_command':
                $this->sendDeviceCommand();
                break;
            case 'get_device_data':
                $this->getDeviceData();
                break;
            default:
                sendJsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
        }
    }

    /**
     * Register new IoT device
     */
    private function registerIoTDevice()
    {
        $device_data = json_decode(file_get_contents('php://input'), true);

        // Validate device data
        if (!$device_data || !isset($device_data['device_id']) || !isset($device_data['device_type'])) {
            sendJsonResponse(['success' => false, 'error' => 'Device ID and type required'], 400);
        }

        // In production, this would register the device with the IoT platform
        sendJsonResponse([
            'success' => true,
            'message' => 'Device registered successfully',
            'device_token' => 'token_' . uniqid()
        ]);
    }

    /**
     * Handle device heartbeat
     */
    private function deviceHeartbeat()
    {
        $device_id = $_GET['device_id'] ?? '';
        $status = $_GET['status'] ?? 'online';

        if (empty($device_id)) {
            sendJsonResponse(['success' => false, 'error' => 'Device ID required'], 400);
        }

        // Update device status in database
        $this->updateDeviceStatus($device_id, $status);

        sendJsonResponse([
            'success' => true,
            'message' => 'Heartbeat received',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Send command to IoT device
     */
    private function sendDeviceCommand()
    {
        $command_data = json_decode(file_get_contents('php://input'), true);

        // In production, this would send commands to actual devices
        sendJsonResponse([
            'success' => true,
            'message' => 'Command sent to device',
            'command_id' => 'cmd_' . uniqid()
        ]);
    }

    /**
     * Get device sensor data
     */
    private function getDeviceData()
    {
        $device_id = $_GET['device_id'] ?? '';
        $data_type = $_GET['type'] ?? 'current';

        // In production, this would fetch real sensor data
        $mock_data = [
            'temperature' => rand(18, 30),
            'humidity' => rand(40, 80),
            'energy_consumption' => rand(50, 200) / 10,
            'motion_detected' => rand(0, 1) === 1,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        sendJsonResponse([
            'success' => true,
            'data' => $mock_data
        ]);
    }

    /**
     * Update device status in database
     */
    private function updateDeviceStatus($device_id, $status)
    {
        try {
            if (!$this->db) {
                return false;
            }

            $sql = "UPDATE iot_devices SET status = :status, last_seen = NOW() WHERE device_id = :deviceId";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['status' => $status, 'deviceId' => $device_id]);
        } catch (\Exception $e) {
            error_log('Device status update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Smart home demo/trial
     */
    public function demo()
    {
        $demo_features = [
            'virtual_tour' => '3D property tour with AR furniture placement',
            'smart_lighting' => 'Automated lighting control and scheduling',
            'security_system' => 'Real-time security monitoring and alerts',
            'energy_monitoring' => 'Energy consumption tracking and optimization',
            'climate_control' => 'Intelligent temperature and humidity control',
            'appliance_integration' => 'Smart appliance control and monitoring'
        ];

        $this->data['page_title'] = 'Smart Home Demo - ' . APP_NAME;
        $this->data['demo_features'] = $demo_features;

        $this->render('iot/demo');
    }

    /**
     * Get IoT market trends and insights
     */
    public function marketInsights()
    {
        $insights = [
            'market_growth' => [
                'global_market_size' => '₹15,000 crores',
                'annual_growth_rate' => '25%',
                'indian_market_potential' => '₹2,500 crores by 2025'
            ],
            'popular_devices' => [
                'Smart Security Systems' => 35,
                'Smart Lighting' => 28,
                'Smart Thermostats' => 22,
                'Smart Appliances' => 15
            ],
            'adoption_trends' => [
                'residential_properties' => 45,
                'commercial_properties' => 30,
                'new_constructions' => 25
            ],
            'cost_benefits' => [
                'energy_savings' => '20-30%',
                'security_improvement' => '40%',
                'property_value_increase' => '15%',
                'maintenance_reduction' => '25%'
            ]
        ];

        $this->data['page_title'] = 'Smart Home Market Insights - ' . APP_NAME;
        $this->data['insights'] = $insights;

        $this->render('iot/market_insights');
    }

    /**
     * IoT service packages
     */
    public function servicePackages()
    {
        $packages = [
            'basic' => [
                'name' => 'Smart Home Basic',
                'price' => 25000,
                'devices' => ['Smart Lights', 'Smart Plugs', 'Motion Sensors'],
                'features' => ['Remote Control', 'Basic Automation', 'Energy Monitoring'],
                'support' => 'Email Support'
            ],
            'premium' => [
                'name' => 'Smart Home Premium',
                'price' => 75000,
                'devices' => ['Smart Lights', 'Smart Thermostat', 'Security Cameras', 'Smart Locks', 'Smart Appliances'],
                'features' => ['Advanced Automation', 'Voice Control', 'Security Monitoring', 'Energy Optimization', 'Mobile App'],
                'support' => '24/7 Phone & Chat Support'
            ],
            'enterprise' => [
                'name' => 'Smart Home Enterprise',
                'price' => 150000,
                'devices' => ['Complete Smart Home Setup', 'Professional Installation', 'Custom Integration'],
                'features' => ['Custom Automation Rules', 'Multi-property Management', 'Advanced Analytics', 'API Integration', 'White-label Solution'],
                'support' => 'Dedicated Account Manager'
            ]
        ];

        $this->data['page_title'] = 'Smart Home Service Packages - ' . APP_NAME;
        $this->data['packages'] = $packages;

        $this->render('iot/service_packages');
    }

    /**
     * IoT installation and setup guide
     */
    public function installationGuide()
    {
        $guide_steps = [
            'planning' => [
                'title' => 'Planning & Assessment',
                'steps' => [
                    'Evaluate property electrical capacity',
                    'Check internet connectivity requirements',
                    'Identify optimal device placement locations',
                    'Plan network infrastructure needs'
                ]
            ],
            'installation' => [
                'title' => 'Device Installation',
                'steps' => [
                    'Install smart home hub/gateway',
                    'Set up WiFi mesh network if needed',
                    'Install sensors and smart devices',
                    'Configure device connections'
                ]
            ],
            'configuration' => [
                'title' => 'System Configuration',
                'steps' => [
                    'Set up user accounts and permissions',
                    'Configure automation rules',
                    'Test all device connections',
                    'Set up mobile app integration'
                ]
            ],
            'optimization' => [
                'title' => 'Optimization & Training',
                'steps' => [
                    'Fine-tune automation settings',
                    'Train users on system usage',
                    'Set up energy monitoring goals',
                    'Configure maintenance alerts'
                ]
            ]
        ];

        $this->data['page_title'] = 'Smart Home Installation Guide - ' . APP_NAME;
        $this->data['guide_steps'] = $guide_steps;

        $this->render('iot/installation_guide');
    }

    /**
     * IoT troubleshooting and support
     */
    public function troubleshooting()
    {
        $troubleshooting_guide = [
            'connectivity_issues' => [
                'title' => 'Device Connectivity Issues',
                'symptoms' => ['Device not responding', 'Offline status', 'Connection drops'],
                'solutions' => [
                    'Check WiFi signal strength',
                    'Restart device and hub',
                    'Check network configuration',
                    'Update device firmware'
                ]
            ],
            'automation_failures' => [
                'title' => 'Automation Rule Failures',
                'symptoms' => ['Rules not triggering', 'Incorrect actions', 'Timing issues'],
                'solutions' => [
                    'Verify trigger conditions',
                    'Check device connectivity',
                    'Review automation logs',
                    'Test rules manually'
                ]
            ],
            'energy_monitoring' => [
                'title' => 'Energy Monitoring Problems',
                'symptoms' => ['No data showing', 'Incorrect readings', 'Historical data missing'],
                'solutions' => [
                    'Verify sensor installation',
                    'Check calibration settings',
                    'Review data collection schedule',
                    'Contact support for sensor replacement'
                ]
            ]
        ];

        $this->data['page_title'] = 'Smart Home Troubleshooting - ' . APP_NAME;
        $this->data['troubleshooting_guide'] = $troubleshooting_guide;

        $this->render('iot/troubleshooting');
    }

    /**
     * Smart home ROI calculator
     */
    public function roiCalculator()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $property_value = (float)($_POST['property_value'] ?? 0);
            $smart_home_cost = (float)($_POST['smart_home_cost'] ?? 0);
            $monthly_energy_savings = (float)($_POST['energy_savings'] ?? 0);

            $roi_data = $this->calculateROI($property_value, $smart_home_cost, $monthly_energy_savings);

            echo json_encode([
                'success' => true,
                'data' => $roi_data
            ]);
            exit;
        }

        $this->data['page_title'] = 'Smart Home ROI Calculator - ' . APP_NAME;
        $this->render('iot/roi_calculator');
    }

    /**
     * Calculate smart home ROI
     */
    private function calculateROI($property_value, $investment_cost, $monthly_savings)
    {
        $annual_savings = $monthly_savings * 12;
        $property_value_increase = $property_value * 0.15; // 15% value increase

        $total_benefits_year1 = $annual_savings + $property_value_increase;
        $roi_percentage = ($total_benefits_year1 / $investment_cost) * 100;
        $payback_period_months = $investment_cost / $monthly_savings;

        return [
            'investment_cost' => $investment_cost,
            'annual_savings' => $annual_savings,
            'property_value_increase' => $property_value_increase,
            'total_benefits_year1' => $total_benefits_year1,
            'roi_percentage' => round($roi_percentage, 2),
            'payback_period_months' => round($payback_period_months, 1),
            'break_even_date' => date('Y-m-d', strtotime("+{$payback_period_months} months"))
        ];
    }
}
