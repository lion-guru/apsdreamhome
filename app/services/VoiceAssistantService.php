<?php
/**
 * AI Voice Assistant Service
 * 
 * Fast, RBAC-aware voice assistant for APS Dream Home
 * Provides instant responses based on user role
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class VoiceAssistantService
{
    use ServiceTenantTrait;
    private $db;
    private $userId;
    private $userRole;
    private $cache = [];

    // Knowledge base - project information
    const KNOWLEDGE_BASE = [
        'project' => [
            'name' => 'APS Dream Home',
            'type' => 'Real Estate ERP/CRM SaaS Platform',
            'location' => 'Gorakhpur, Uttar Pradesh, India',
            'founded' => '2011',
            'colonies' => ['Suryoday Colony', 'Braj Radha Nagri', 'Raghunath Nagri', 'Budh Bihar Colony'],
            'features' => ['Colony Development', 'Plot Sales', 'MLM Commission', 'Finance', 'CRM', 'AI'],
        ],
        'modules' => [
            'colony' => ['name' => 'Colony Development', 'description' => 'Land acquisition to sales ready', 'url' => '/admin/colony-pipeline'],
            'sales' => ['name' => 'Sales & Bookings', 'description' => 'Customer booking and EMI management', 'url' => '/admin/sales'],
            'finance' => ['name' => 'Finance', 'description' => 'Accounting, TDS, GST, Bank reconciliation', 'url' => '/admin/finance'],
            'mlm' => ['name' => 'MLM Commission', 'description' => 'Multi-level marketing commission engine', 'url' => '/admin/mlm'],
            'crm' => ['name' => 'CRM', 'description' => 'Lead management and customer relations', 'url' => '/admin/leads'],
            'hr' => ['name' => 'HR & Payroll', 'description' => 'Employee management and salary', 'url' => '/admin/hrm'],
        ],
        'stats' => [
            'total_plots' => '204+',
            'active_colonies' => 4,
            'active_associates' => 56,
            'commission_total' => '₹1.05 Cr+',
        ],
    ];

    // RBAC permissions for voice assistant
    const RBAC = [
        'super_admin' => ['all'],
        'admin' => ['all'],
        'manager' => ['dashboard', 'reports', 'leads', 'bookings', 'finance', 'crm', 'hr'],
        'employee' => ['leads', 'bookings', 'crm', 'attendance'],
        'associate' => ['leads', 'commissions', 'network', 'wallet'],
        'agent' => ['leads', 'bookings', 'commissions'],
        'customer' => ['bookings', 'emi', 'profile'],
        'farmer' => ['land', 'payments'],
        'telecaller' => ['leads', 'followups'],
    ];

    // Response templates
    const RESPONSES = [
        'greeting' => [
            'Hello! I\'m your APS Dream Home assistant. How can I help you today?',
            'Hi there! Ask me anything about APS Dream Home.',
            'Welcome! I can help you with plots, bookings, commissions, and more.',
        ],
        'unknown' => [
            'I\'m not sure about that. Try asking about plots, bookings, commissions, or finance.',
            'Could you rephrase that? I can help with colony, sales, finance, MLM, CRM, and HR.',
        ],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);
        $this->userRole = $_SESSION['role'] ?? $_SESSION['admin_role'] ?? 'customer';
    }

    /**
     * Process voice query and return response
     */
    public function processQuery(string $query): array
    {
        $query = strtolower(trim($query));
        $cacheKey = md5($query . $this->userRole);

        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $response = $this->generateResponse($query);
        $this->cache[$cacheKey] = $response;

        return $response;
    }

    /**
     * Generate response based on query
     */
    private function generateResponse(string $query): array
    {
        // Greeting
        if ($this->matches($query, ['hello', 'hi', 'hey', 'good morning', 'good evening'])) {
            return $this->successResponse(self::RESPONSES['greeting'][array_rand(self::RESPONSES['greeting'])]);
        }

        // Project info
        if ($this->matches($query, ['what is aps dream home', 'about aps', 'tell me about', 'company'])) {
            return $this->successResponse(
                "APS Dream Home is a Real Estate ERP/CRM SaaS Platform based in Gorakhpur, Uttar Pradesh. " .
                "We have " . self::KNOWLEDGE_BASE['stats']['active_colonies'] . " active colonies with " .
                self::KNOWLEDGE_BASE['stats']['total_plots'] . " plots and " .
                self::KNOWLEDGE_BASE['stats']['active_associates'] . " active associates."
            );
        }

        // Colony info
        if ($this->matches($query, ['colonies', 'colony list', 'which colonies', 'colony names'])) {
            $colonies = implode(', ', self::KNOWLEDGE_BASE['project']['colonies']);
            return $this->successResponse("Our active colonies are: {$colonies}.");
        }

        // Plot availability
        if ($this->matches($query, ['available plots', 'plot count', 'how many plots', 'plots available'])) {
            if (!$this->hasAccess('dashboard')) {
                return $this->accessDenied();
            }
            $count = $this->db->fetchOne("SELECT COUNT(*) as c FROM plots WHERE status = 'available'")['c'] ?? 0;
            return $this->successResponse("There are {$count} plots currently available across all colonies.");
        }

        // Booking stats
        if ($this->matches($query, ['total bookings', 'booking count', 'how many bookings'])) {
            if (!$this->hasAccess('bookings')) {
                return $this->accessDenied();
            }
            $count = $this->db->fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status != 'cancelled'")['c'] ?? 0;
            return $this->successResponse("There are {$count} active bookings in the system.");
        }

        // Lead stats
        if ($this->matches($query, ['total leads', 'lead count', 'how many leads', 'leads status'])) {
            if (!$this->hasAccess('leads')) {
                return $this->accessDenied();
            }
            $new = $this->db->fetchOne("SELECT COUNT(*) as c FROM leads WHERE status = 'new'")['c'] ?? 0;
            $converted = $this->db->fetchOne("SELECT COUNT(*) as c FROM leads WHERE status = 'closed_won'")['c'] ?? 0;
            return $this->successResponse("There are {$new} new leads and {$converted} converted leads.");
        }

        // Commission info
        if ($this->matches($query, ['commission', 'total commission', 'commission earned', 'mlm'])) {
            if (!$this->hasAccess('commissions')) {
                return $this->accessDenied();
            }
            $total = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger")['total'] ?? 0;
            return $this->successResponse("Total commission distributed is ₹" . number_format($total) . ".");
        }

        // Wallet balance
        if ($this->matches($query, ['wallet', 'balance', 'my balance', 'earnings'])) {
            if (!$this->hasAccess('wallet')) {
                return $this->accessDenied();
            }
            $wallet = $this->db->fetchOne("SELECT * FROM wallet_points WHERE user_id = ?", [$this->userId]);
            $balance = $wallet['points_balance'] ?? 0;
            return $this->successResponse("Your wallet balance is ₹" . number_format($balance) . ".");
        }

        // EMI info
        if ($this->matches($query, ['emi', 'emi schedule', 'payment due', 'next emi'])) {
            if (!$this->hasAccess('bookings')) {
                return $this->accessDenied();
            }
            $due = $this->db->fetchOne(
                "SELECT COUNT(*) as c FROM booking_payment_schedules WHERE status = 'pending' AND due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
            )['c'] ?? 0;
            return $this->successResponse("There are {$due} EMI payments due in the next 7 days.");
        }

        // Finance summary
        if ($this->matches($query, ['revenue', 'total revenue', 'income', 'finance'])) {
            if (!$this->hasAccess('finance')) {
                return $this->accessDenied();
            }
            $monthRevenue = $this->db->fetchOne(
                "SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE MONTH(created_at) = MONTH(CURDATE()) AND status != 'cancelled'"
            )['total'] ?? 0;
            return $this->successResponse("This month's revenue is ₹" . number_format($monthRevenue) . ".");
        }

        // HR info
        if ($this->matches($query, ['employees', 'staff', 'total employees', 'team size'])) {
            if (!$this->hasAccess('hr')) {
                return $this->accessDenied();
            }
            $count = $this->db->fetchOne("SELECT COUNT(*) as c FROM employees WHERE status = 'active'")['c'] ?? 0;
            return $this->successResponse("There are {$count} active employees.");
        }

        // Attendance
        if ($this->matches($query, ['attendance', 'today attendance', 'present today'])) {
            if (!$this->hasAccess('attendance')) {
                return $this->accessDenied();
            }
            $present = $this->db->fetchOne(
                "SELECT COUNT(*) as c FROM employee_attendance WHERE DATE(attendance_date) = CURDATE() AND status = 'present'"
            )['c'] ?? 0;
            return $this->successResponse("Today {$present} employees are present.");
        }

        // Help
        if ($this->matches($query, ['help', 'what can you do', 'features', 'commands'])) {
            return $this->successResponse(
                "I can help you with: Plot availability, Booking stats, Lead status, " .
                "Commission info, Wallet balance, EMI schedule, Revenue, Employee info, and Attendance. " .
                "Just ask me anything!"
            );
        }

        // Default response
        return $this->successResponse(self::RESPONSES['unknown'][array_rand(self::RESPONSES['unknown'])]);
    }

    /**
     * Check if query matches any keywords
     */
    private function matches(string $query, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check RBAC access
     */
    private function hasAccess(string $module): bool
    {
        $allowed = self::RBAC[$this->userRole] ?? [];
        return in_array('all', $allowed) || in_array($module, $allowed);
    }

    /**
     * Success response
     */
    private function successResponse(string $message): array
    {
        return [
            'success' => true,
            'message' => $message,
            'role' => $this->userRole,
            'timestamp' => time(),
        ];
    }

    /**
     * Access denied response
     */
    private function accessDenied(): array
    {
        return [
            'success' => false,
            'message' => 'You don\'t have permission to access this information. Please contact your administrator.',
            'role' => $this->userRole,
            'timestamp' => time(),
        ];
    }
}
