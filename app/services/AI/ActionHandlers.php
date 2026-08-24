<?php

/**
 * ActionHandlers — Executes actions triggered by ConversationEngine
 * Uses existing services (CRMService, PropertySubmissionService, etc.)
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

class ActionHandlers
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Route to the correct handler based on action type
     */
    public function execute(string $action, array $data, ?int $userId, string $userRole): array
    {
        try {
            switch ($action) {
                case 'post_property':
                    return $this->postProperty($data, $userId, $userRole);
                case 'add_lead':
                    return $this->addLead($data, $userId, $userRole);
                case 'book_site_visit':
                    return $this->bookSiteVisit($data, $userId);
                case 'search_property':
                    return $this->searchProperty($data);
                case 'register':
                    return $this->registerUser($data);
                case 'file_complaint':
                    return $this->fileComplaint($data, $userId);
                case 'check_booking':
                    return $this->checkBooking($data);
                default:
                    return ['success' => false, 'message' => "Unknown action: {$action}"];
            }
        } catch (\Exception $e) {
            error_log("ActionHandler error ({$action}): " . $e->getMessage());
            return [
                'success' => false,
                'message' => "⚠️ Kuch gadbad ho gayi. Please try again ya phone karein: +91 92771 21112"
            ];
        }
    }

    /**
     * Post a property listing
     */
    private function postProperty(array $data, ?int $userId, string $userRole): array
    {
        $tid = TenantContext::getId();
        $typeMap = [
            'plot' => 'plot', 'house' => 'house', 'flat' => 'flat',
            'shop' => 'shop', 'land' => 'land', 'farmhouse' => 'farmhouse',
        ];
        $propertyType = $typeMap[strtolower($data['property_type'] ?? '')] ?? 'plot';

        $this->db->query(
            "INSERT INTO user_properties (user_id, posted_by, posted_by_type, name, phone, property_type, listing_type, price, location, description, status, tenant_id, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, 'sell', ?, ?, ?, 'pending', ?, NOW())",
            [
                $userId,
                $userId,
                $userRole ?: 'customer',
                $data['name'],
                $data['phone'],
                $propertyType,
                $data['price'] ?? null,
                $data['location'] ?? null,
                $data['description'] ?? null,
                $tid,
            ]
        );

        $submissionId = $this->db->lastInsertId();

        // Auto-response: log activity for the property submission
        try {
            $this->db->query(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at) VALUES (0, 'property_submission', 'Property submitted via AI chatbot: {$data['name']} (PS-{$submissionId})', ?, NOW())",
                [$userId]
            );
        } catch (\Exception $e) {
        // Graceful — table might not exist
        error_log($e->getMessage());
        }

        return [
            'success' => true,
            'message' => "🎉 Property successfully submit ho gayi!\n\n📋 Submission ID: PS-{$submissionId}\n📝 Title: {$data['name']}\n💰 Price: ₹{$data['price']} Lakh\n📍 Location: {$data['location']}\n📊 Status: Pending Review\n\nHamari team jald hi aapko contact karegi!",
            'submission_id' => $submissionId,
        ];
    }

    /**
     * Add a lead to CRM
     */
    private function addLead(array $data, ?int $userId, string $userRole): array
    {
        $tid = TenantContext::getId();
        // Generate lead number
        $leadNumber = 'CR-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Determine assigned_to based on role
        $assignedTo = null;
        if (in_array($userRole, ['admin', 'manager', 'employee'])) {
            $assignedTo = $userId;
        }

        $this->db->query(
            "INSERT INTO leads (lead_number, name, phone, email, source, status, budget, location_preference, property_interest, priority, lead_category, lead_score, created_by, assigned_to, notes, tenant_id, created_at, updated_at) 
             VALUES (?, ?, ?, ?, 'chatbot', 'new', ?, ?, ?, 'medium', 'warm', 50, ?, ?, ?, ?, NOW(), NOW())",
            [
                $leadNumber,
                $data['name'],
                $data['phone'],
                $data['email'] ?? null,
                !empty($data['budget']) ? $data['budget'] * 100000 : null, // Convert lakh to actual
                $data['location'] ?? null,
                $data['interest'] ?? null,
                $userId,
                $assignedTo,
                "Auto-created via AI chatbot on " . date('Y-m-d H:i'),
                $tid,
            ]
        );

        $leadId = $this->db->lastInsertId();

        // Auto-response: log activity for the lead
        try {
            $this->db->query(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, tenant_id, created_at) VALUES (?, 'auto_response', 'Lead created via AI chatbot. Auto-welcome message queued.', ?, ?, NOW())",
                [$leadId, $userId, $tid]
            );
        } catch (\Exception $e) {
        // Graceful — table might not exist
        error_log($e->getMessage());
        }

        // Auto-response: send welcome SMS if phone available
        if (!empty($data['phone']) && class_exists('App\Services\SMSService')) {
            try {
                $sms = new \App\Services\SMSService();
                $welcomeMsg = "Namaste {$data['name']}! APS Dream Homes mein aapka swagat hai. Aapka lead #{$leadNumber} create ho gaya hai. Hamari team jald hi aapko contact karegi. Dhanyavaad!";
                $sms->sendSMS($data['phone'], $welcomeMsg);
            } catch (\Exception $e) {
            // Graceful — SMS service might not be configured
            error_log($e->getMessage());
            }
        }

        return [
            'success' => true,
            'message' => "🎉 Lead successfully create ho gayi!\n\n👤 Lead: {$data['name']}\n📱 Phone: {$data['phone']}\n📋 Lead #: {$leadNumber}\n💰 Budget: " . ($data['budget'] ? "₹{$data['budget']} Lakh" : "N/A") . "\n📍 Location: " . ($data['location'] ?? "N/A") . "\n🎯 Score: 50/100 (Warm Lead)\n📊 Status: New\n\nAb isse follow-up kar sakte hain!",
            'lead_id' => $leadId,
            'lead_number' => $leadNumber,
        ];
    }

    /**
     * Book a site visit
     */
    private function bookSiteVisit(array $data, ?int $userId): array
    {
        $tid = TenantContext::getId();
        $visitDate = $data['date'] ?? date('Y-m-d', strtotime('+1 day'));

        // Try VisitService first
        if (class_exists('App\Services\VisitService')) {
            try {
                $visitService = new \App\Services\VisitService();
                $result = $visitService->bookVisit([
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'property' => $data['property'],
                    'visit_date' => $visitDate,
                    'user_id' => $userId,
                ]);
                if (!empty($result['success'])) {
                    return [
                        'success' => true,
                        "message" => "🎉 Site visit book ho gayi!\n\n🏢 Property: {$data['property']}\n📅 Date: {$visitDate}\n👤 Name: {$data['name']}\n📱 Phone: {$data['phone']}\n\nAapko confirmation milega. Hamari team aapko call karegi!"
                    ];
                }
            } catch (\Exception $e) {
                error_log("ActionHandlers::bookSiteVisit VisitService error: " . $e->getMessage());
            }
        }

        // Fallback: insert directly
        try {
            $this->db->query(
                "INSERT INTO site_visits (user_id, visitor_name, visitor_phone, notes, visit_date, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, 'scheduled', ?, NOW())",
                [$userId, $data['name'], $data['phone'], $data['property'], $visitDate, $tid]
            );

            // Auto-response: log activity
            try {
                $visitId = $this->db->lastInsertId();
                $this->db->query(
                    "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, tenant_id, created_at) VALUES (0, 'site_visit_booked', 'Site visit booked via AI chatbot: {$data['property']} on {$visitDate}', ?, ?, NOW())",
                    [$userId, $tid]
                );
            } catch (\Exception $e) {
            // Graceful
            error_log($e->getMessage());
            }

            return [
                'success' => true,
                "message" => "🎉 Site visit book ho gayi!\n\n🏢 Property: {$data['property']}\n📅 Date: {$visitDate}\n👤 Name: {$data['name']}\n📱 Phone: {$data['phone']}\n\nConfirmation aapko milega!"
            ];
        } catch (\Exception $e) {
            error_log("ActionHandlers::bookSiteVisit direct insert error: " . $e->getMessage());
            return [
                'success' => false,
                "message" => "⚠️ Site visit book karne mein problem aa rahi hai: " . $e->getMessage() . "\n\n📞 Call karein: +91 92771 21112"
            ];
        }
    }

    /**
     * Search properties
     */
    private function searchProperty(array $data): array
    {
        $tid = TenantContext::getId();
        $location = $data['location'] ?? '';
        $budget = $data['budget'] ?? null;
        $type = $data['type'] ?? null;

        $sql = "SELECT p.*, c.name as colony_name 
                FROM plots p 
                LEFT JOIN colonies c ON p.colony_id = c.id 
                WHERE p.status = 'available' AND p.tenant_id = ?";
        $params = [$tid];

        if ($location) {
            $sql .= " AND (c.name LIKE ? OR p.location LIKE ? OR p.district LIKE ?)";
            $loc = "%{$location}%";
            $params = array_merge($params, [$loc, $loc, $loc]);
        }

        if ($budget) {
            $sql .= " AND p.price <= ?";
            $params[] = $budget * 100000; // lakh to actual
        }

        if ($type && strtolower($type) !== 'any') {
            $sql .= " AND p.plot_type LIKE ?";
            $params[] = "%{$type}%";
        }

        $sql .= " ORDER BY p.is_featured DESC, p.created_at DESC LIMIT 5";

        try {
            $results = $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            // Table might not exist or have different schema — try user_properties
            $sql = "SELECT * FROM user_properties WHERE status IN ('approved', 'verified') AND tenant_id = ?";
            $params = [$tid];
            if ($location) {
                $sql .= " AND (location LIKE ? OR city_name LIKE ?)";
                $loc = "%{$location}%";
                $params = [$tid, $loc, $loc];
            }
            if ($budget) {
                $sql .= " AND price <= ?";
                $params[] = $budget;
            }
            $sql .= " ORDER BY created_at DESC LIMIT 5";
            $results = $this->db->fetchAll($sql, $params);
        }

        if (empty($results)) {
            return [
                'success' => true,
                'message' => "🔍 \"{$location}\" mein koi property nahi mili is budget mein.\n\nKya aap:\n📍 Location change karna chahte hain?\n💰 Budget badhana chahte hain?\n📞 Agent se baat karna chahte hain? (+91 92771 21112)",
            ];
        }

        $response = "🔍 {$location} mein " . count($results) . " properties mili:\n\n";
        foreach ($results as $i => $p) {
            $num = $i + 1;
            $name = $p['name'] ?? $p['plot_number'] ?? "Property #{$p['id']}";
            $price = isset($p['price']) ? '₹' . number_format($p['price'] / 100000, 1) . 'L' : 'Price N/A';
            $loc = $p['location'] ?? $p['colony_name'] ?? $p['city_name'] ?? '';
            $type = $p['property_type'] ?? $p['plot_type'] ?? '';
            $response .= "{$num}. {$name} ({$type})\n   💰 {$price} | 📍 {$loc}\n";
        }
        $response .= "\n📞 Detail ke liye call karein: +91 92771 21112";
        $response .= "\n🌐 Full list: " . BASE_URL . "/properties";

        return [
            'success' => true,
            'message' => $response,
            'results' => $results,
        ];
    }

    /**
     * Register a new user
     */
    private function registerUser(array $data): array
    {
        $tid = TenantContext::getId();
        $roleMap = ['customer' => 'customer', 'associate' => 'associate', 'agent' => 'agent'];
        $role = $roleMap[strtolower($data['role'] ?? '')] ?? 'customer';

        // Check if user already exists
        $existing = $this->db->fetch(
            "SELECT id FROM users WHERE (email = ? OR phone = ?) AND tenant_id = ?",
            [$data['email'], $data['phone'], $tid]
        );
        if ($existing) {
            return [
                'success' => true,
                'message' => "⚠️ Is email ya phone se pehle se account hai!\n\nAb aap login kar sakte hain:\n🔗 " . BASE_URL . "/login\n\nPassword bhool gaye? " . BASE_URL . "/forgot-password"
            ];
        }

        // Create user via UserRegistrationService if available
        if (class_exists('App\Services\UserRegistrationService')) {
            $service = new \App\Services\UserRegistrationService();
            $user = null;
            $result = $service->createUser($role, [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
            ], $user);

            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => "🎉 Account ban gaya!\n\n👤 Naam: {$data['name']}\n📧 Email: {$data['email']}\n📱 Phone: {$data['phone']}\n🎭 Role: " . ucfirst($role) . "\n\nAb login kar sakte hain:\n🔗 " . BASE_URL . "/login"
                ];
            }
            return [
                'success' => false,
                'message' => "⚠️ Account banane mein problem aa rahi hai: " . ($result['message'] ?? 'Unknown error') . "\n\n📞 Call karein: +91 92771 21112"
            ];
        }

        // Fallback: direct DB insert
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->db->query(
            "INSERT INTO users (name, email, phone, password, role, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, 'active', ?, NOW())",
            [$data['name'], $data['email'], $data['phone'], $hashedPassword, $role, $tid]
        );

        $userId = $this->db->lastInsertId();

        return [
            'success' => true,
            'message' => "🎉 Account ban gaya!\n\n👤 Naam: {$data['name']}\n📧 Email: {$data['email']}\n📱 Phone: {$data['phone']}\n🎭 Role: " . ucfirst($role) . "\n\nAb login kar sakte hain:\n🔗 " . BASE_URL . "/login"
        ];
    }

    /**
     * File a complaint
     */
    private function fileComplaint(array $data, ?int $userId): array
    {
        $tid = TenantContext::getId();
        $this->db->query(
            "INSERT INTO support_tickets (user_id, subject, message, category, priority, status, tenant_id, created_at) VALUES (?, ?, ?, ?, 'high', 'open', ?, NOW())",
            [
                $userId,
                "Complaint: " . ($data['type'] ?? 'General'),
                "Type: {$data['type']}\n\n" . ($data['description'] ?? ''),
                $data['type'] ?? 'general',
                $tid,
            ]
        );

        $ticketId = $this->db->lastInsertId();

        // Auto-response: log activity
        try {
            $this->db->query(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, tenant_id, created_at) VALUES (0, 'complaint_filed', 'Complaint filed via AI chatbot: {$data['type']} (Ticket #{$ticketId})', ?, ?, NOW())",
                [$userId, $tid]
            );
        } catch (\Exception $e) {
        // Graceful
        error_log($e->getMessage());
        }

        // Auto-response: send acknowledgment SMS if phone available
        if (!empty($data['phone']) && class_exists('App\Services\SMSService')) {
            try {
                $sms = new \App\Services\SMSService();
                $sms->sendSMS($data['phone'], "APS Dream Homes: Aapki complaint (#{$ticketId}) register ho gayi hai. Hamari team 24 ghante mein contact karegi. Dhanyavaad!");
            } catch (\Exception $e) {
            // Graceful
            error_log($e->getMessage());
            }
        }

        return [
            'success' => true,
            'message' => "🎉 Complaint register ho gayi!\n\n📋 Ticket #: {$ticketId}\n📋 Type: {$data['type']}\n👤 Name: {$data['name']}\n📱 Phone: {$data['phone']}\n\nHamari team 24 ghante mein aapko contact karegi.\n📞 Urgent: +91 92771 21112"
        ];
    }

    /**
     * Check booking status
     */
    private function checkBooking(array $data): array
    {
        $tid = TenantContext::getId();
        $identifier = $data['identifier'] ?? '';

        // Try booking number first
        $booking = $this->db->fetch(
            "SELECT pb.*, c.name as colony_name, p.plot_number 
             FROM plot_bookings pb 
             LEFT JOIN colonies c ON pb.colony_id = c.id 
             LEFT JOIN plots p ON pb.plot_id = p.id 
             WHERE (pb.booking_number = ? OR pb.customer_phone = ? OR pb.customer_email = ?) AND pb.tenant_id = ?
             ORDER BY pb.created_at DESC LIMIT 1",
            [$identifier, $identifier, $identifier, $tid]
        );

        if (!$booking) {
            return [
                'success' => true,
                'message' => "🔍 \"" . htmlspecialchars($identifier) . "\" se koi booking nahi mili.\n\nKya aap:\n📞 Call kar sakte hain: +91 92771 21112\n📧 Email kar sakte hain: info@apsdreamhome.com\n\nBooking number ya phone number sahi hai?"
            ];
        }

        $statusEmoji = [
            'pending' => '🟡', 'token_paid' => '🟢', 'agreement_signed' => '🟢',
            'emi_active' => '🔵', 'completed' => '✅', 'cancelled' => '🔴',
        ];
        $emoji = $statusEmoji[$booking['status']] ?? '⚪';

        return [
            'success' => true,
            'message' => "📋 Booking mil gayi!\n\n🏢 Colony: " . ($booking['colony_name'] ?? 'N/A') . "\n📐 Plot: " . ($booking['plot_number'] ?? 'N/A') . "\n💰 Value: ₹" . number_format($booking['total_plot_value']) . "\n{$emoji} Status: " . ucfirst(str_replace('_', ' ', $booking['status'])) . "\n📅 Date: " . ($booking['booking_date'] ?? 'N/A') . "\n📝 Booking #: " . ($booking['booking_number'] ?? 'N/A'),
            'booking' => $booking,
        ];
    }
}
