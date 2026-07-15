<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\Communication\NotificationService;
use App\Services\Sales\BookingLifecycleService;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    private function requireCustomerLogin()
    {
        @session_start();

        // Check if user is logged in (user_id exists and user_type is customer or empty)
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }

        // Accept customer type OR default to customer if not specified
        $userType = $_SESSION['role'] ?? '';
        if ($userType !== '' && $userType !== 'customer') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }

    private function getUser()
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/user/logout');
            exit;
        }

        return $user;
    }

    public function dashboard()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$user['email']]);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // ── Hot-path cache: saved-searches count for this user (30 sec TTL) ──
        $savedSearchesCount = \App\Services\Cache\HotPathCacheService::getUserSavedSearchesCount(
            (int) $user['id'],
            function () use ($user) {
                try {
                    $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM saved_searches WHERE user_id = ?");
                    $stmt->execute([(int) $user['id']]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    return (int) ($row['cnt'] ?? 0);
                } catch (\Throwable $e) {
                    error_log("UserController::dashboard - savedSearchesCount: " . $e->getMessage());
                    return 0;
                }
            }
        );

        // Fetch bookings (purchased plots/properties)
        $bookings = $this->db->fetchAll("
            SELECT b.*, 
                   COALESCE(p.plot_number, p2.plot_number) as plot_number,
                   COALESCE(p.block, p2.block) as block,
                   COALESCE(p.area_sqft, p2.area_sqft) as area_sqft,
                   COALESCE(p.total_price, p2.total_price) as plot_price,
                   COALESCE(p.status, p2.status) as plot_status,
                   COALESCE(c.name, c2.name) as colony_name
            FROM bookings b
            LEFT JOIN plots p ON b.property_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN plots p2 ON b.plot_id = p2.id
            LEFT JOIN colonies c2 ON p2.colony_id = c2.id
            WHERE b.user_id = ? OR b.customer_id = ?
            ORDER BY b.created_at DESC
        ", [$user['id'], $user['id']]);

        // Recent payments
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM payment_transactions WHERE user_id = ? AND user_type = 'customer' ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$_SESSION['user_id'] ?? 0]);
            $recentPayments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $recentPayments = [];
        }

        // User documents (KYC)
        try {
            $stmt = $this->db->prepare("SELECT * FROM documents WHERE entity_type = 'user' AND entity_id = ? ORDER BY uploaded_on DESC");
            $stmt->execute([$user['id']]);
            $userDocuments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $userDocuments = [];
        }

        // Support tickets count
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM support_tickets WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $ticketStats = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totalTickets = (int)($ticketStats['cnt'] ?? 0);

            $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM support_tickets WHERE user_id = ? AND status NOT IN ('resolved','closed')");
            $stmt->execute([$user['id']]);
            $openTicketsRow = $stmt->fetch(\PDO::FETCH_ASSOC);
            $openTickets = (int)($openTicketsRow['cnt'] ?? 0);
        } catch (\Exception $e) {
            $totalTickets = 0;
            $openTickets = 0;
        }

        $notifService = new NotificationService();
        $unreadNotifCount = $notifService->getUnreadCount($user['id']);

        $referralCode = $user['referral_code'] ?? '';
        $referralCount = 0;
        $referralEarnings = 0;
        $referralLink = $referralCode ? (defined('BASE_URL') ? BASE_URL : '') . '/register?ref=' . $referralCode : '';
        $twoFactorEnabled = false;
        $savedCount = 0;
        $kycStatus = 'not_started';

        try {
            $stmt = $this->db->prepare("SELECT direct_referrals FROM mlm_profiles WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($profile) {
                $referralCount = (int)($profile['direct_referrals'] ?? 0);
            }
            $stmt = $this->db->prepare("SELECT referral_earnings FROM wallet_points WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $wallet = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($wallet) {
                $referralEarnings = (float)($wallet['referral_earnings'] ?? 0);
            }
            // 2FA status
            $stmt = $this->db->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $tf = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($tf) {
                $twoFactorEnabled = !empty($tf['two_factor_enabled']);
            }
            // Saved searches count
            $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM saved_searches WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $savedCount = (int)($row['cnt'] ?? 0);
            // KYC status
            try {
                $stmt = $this->db->prepare("SELECT status FROM kyc_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1");
                $stmt->execute([$user['id']]);
                $kycRow = $stmt->fetch(\PDO::FETCH_ASSOC);
                $kycStatus = $kycRow['status'] ?? 'not_started';
            } catch (\Exception $e) {
                $kycStatus = 'not_started';
            }
        } catch (\Exception $e) {
            error_log("UserController.php: " . $e->getMessage());
        }

        // ── Payment summary: penalties + NACH + upcoming installments ──
        $paymentSummary = null;
        try {
            $bls = new BookingLifecycleService();
            $paymentSummary = $bls->getCustomerPaymentSummary((int)$user['id']);
        } catch (\Throwable $e) {
            error_log("UserController::dashboard - paymentSummary: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'My Dashboard - APS Dream Home',
            'page_description' => 'Manage your properties and inquiries',
            'user' => $user,
            'properties' => $properties,
            'inquiries' => $inquiries,
            'bookings' => $bookings,
            'userDocuments' => $userDocuments,
            'stats' => [
                'total_properties' => count($properties),
                'active_inquiries' => count(array_filter($inquiries, fn($i) => ($i['status'] ?? '') !== 'closed')),
                'total_bookings' => count($bookings),
                'total_inquiries' => count($inquiries),
                'total_tickets' => $totalTickets,
                'open_tickets' => $openTickets,
            ],
            'recentPayments' => $recentPayments,
            'registered' => isset($_GET['registered']),
            'loginSuccess' => isset($_GET['login']),
            'referral_code' => $referralCode,
            'referral_link' => $referralLink,
            'referral_count' => $referralCount,
            'referral_earnings' => $referralEarnings,
            'unread_notifications' => $unreadNotifCount,
            'investor_stats' => $this->safeInvestorStats((int)$user['id']),
            'twoFactorEnabled' => $twoFactorEnabled,
            'savedCount' => $savedCount,
            'kycStatus' => $kycStatus ?? 'not_started',
            'paymentSummary' => $paymentSummary,
        ];

        $this->layout = 'layouts/customer';
        $this->render('pages/user_dashboard', $data);
    }

    public function myProperties()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'page_title' => 'My Properties - APS Dream Home',
            'page_description' => 'View and manage your listed properties',
            'user' => $user,
            'properties' => $properties,
        ];

        $this->layout = 'layouts/customer';
        $this->render('pages/user_properties', $data);
    }

    public function userBookings()
    {
        $this->myBookings();
    }

    public function myBookings()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];

        $bookings = [];
        try {
            $bookings = $this->db->fetchAll("
                SELECT pb.*,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       c.name as colony_name,
                       (SELECT COALESCE(SUM(bps.paid_amount), 0) FROM booking_payment_schedules bps WHERE bps.booking_id = pb.id) as total_paid,
                       (SELECT COUNT(*) FROM booking_payment_schedules bps WHERE bps.booking_id = pb.id AND bps.status = 'overdue') as overdue_count,
                       (SELECT COUNT(*) FROM booking_payment_schedules bps WHERE bps.booking_id = pb.id AND bps.status = 'pending') as pending_count,
                       (SELECT COUNT(*) FROM booking_payment_schedules bps WHERE bps.booking_id = pb.id) as total_installments
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE pb.customer_id = ?
                ORDER BY pb.created_at DESC
            ", [$userId]);
        } catch (\Throwable $e) {
            error_log("UserController::myBookings - " . $e->getMessage());
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/bookings', [
            'page_title' => 'My Bookings - APS Dream Home',
            'user' => $user,
            'bookings' => $bookings,
        ]);
    }

    public function bookingDetail($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $bookingId = (int)$id;

        if ($bookingId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $booking = null;
        try {
            $booking = $this->db->fetch("
                SELECT pb.*,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       p.corner_plot, p.road_width_ft,
                       c.name as colony_name, c.contact_phone as colony_phone,
                       c.contact_email as colony_email,
                       d.name as district_name, s.name as state_name,
                       u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN states s ON d.state_id = s.id
                LEFT JOIN users u ON pb.customer_id = u.id
                WHERE pb.id = ? AND pb.customer_id = ?
            ", [$bookingId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingDetail - " . $e->getMessage());
        }

        if (!$booking) {
            $this->layout = 'layouts/customer';
            $this->render('pages/user/booking_detail', [
                'page_title' => 'Booking Not Found - APS Dream Home',
                'user' => $user,
                'booking' => null,
                'payments' => [],
                'receipts' => [],
                'documents' => [],
                'history' => [],
            ]);
            return;
        }

        $payments = [];
        try {
            $payments = $this->db->fetchAll("
                SELECT * FROM booking_payment_schedules
                WHERE booking_id = ?
                ORDER BY installment_no ASC
            ", [$bookingId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingDetail payments - " . $e->getMessage());
        }

        $receipts = [];
        try {
            $receipts = $this->db->fetchAll("
                SELECT * FROM booking_payment_receipts
                WHERE booking_id = ?
                ORDER BY receipt_date DESC
            ", [$bookingId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingDetail receipts - " . $e->getMessage());
        }

        $documents = [];
        try {
            $documents = $this->db->fetchAll("
                SELECT * FROM booking_documents
                WHERE booking_id = ?
                ORDER BY created_at DESC
            ", [$bookingId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingDetail documents - " . $e->getMessage());
        }

        $history = [];
        try {
            $history = $this->db->fetchAll("
                SELECT bsh.*, u.name as changed_by_name
                FROM booking_status_history bsh
                LEFT JOIN users u ON bsh.changed_by = u.id
                WHERE bsh.booking_id = ?
                ORDER BY bsh.created_at ASC
            ", [$bookingId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingDetail history - " . $e->getMessage());
        }

        $totalPaid = 0;
        foreach ($payments as $p) {
            $totalPaid += (float)($p['paid_amount'] ?? 0);
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/booking_detail', [
            'page_title' => 'Booking ' . htmlspecialchars($booking['booking_number'] ?? '') . ' - APS Dream Home',
            'user' => $user,
            'booking' => $booking,
            'payments' => $payments,
            'receipts' => $receipts,
            'documents' => $documents,
            'history' => $history,
            'total_paid' => $totalPaid,
        ]);
    }

    public function demandLetter($installmentId = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $instId = (int)$installmentId;

        if ($instId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $installment = null;
        $booking = null;
        $colony = null;
        $plot = null;

        try {
            $installment = $this->db->fetch("
                SELECT bps.*, pb.customer_id, pb.booking_number, pb.total_plot_value
                FROM booking_payment_schedules bps
                JOIN plot_bookings pb ON bps.booking_id = pb.id
                WHERE bps.id = ? AND pb.customer_id = ?
            ", [$instId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::demandLetter installment - " . $e->getMessage());
        }

        if (!$installment) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        try {
            $booking = $this->db->fetch("
                SELECT pb.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM plot_bookings pb
                LEFT JOIN users u ON pb.customer_id = u.id
                WHERE pb.id = ?
            ", [$installment['booking_id']]);
        } catch (\Throwable $e) {
            error_log("UserController::demandLetter booking - " . $e->getMessage());
        }

        try {
            $plot = $this->db->fetch("SELECT * FROM plots WHERE id = ?", [$booking['plot_id'] ?? 0]);
        } catch (\Throwable $e) {
            error_log("UserController::demandLetter plot - " . $e->getMessage());
        }

        try {
            $colony = $this->db->fetch("SELECT c.*, d.name as district_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id WHERE c.id = ?", [$plot['colony_id'] ?? 0]);
        } catch (\Throwable $e) {
            error_log("UserController::demandLetter colony - " . $e->getMessage());
        }

        if (!$booking || !$colony || !$plot) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $html = \App\Services\PDFService::generateDemandLetter($booking, $installment, $colony, $plot);

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public function myInquiries()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'page_title' => 'My Inquiries - APS Dream Home',
            'page_description' => 'Track your property inquiries',
            'user' => $user,
            'inquiries' => $inquiries,
        ];

        $this->layout = 'layouts/customer';
        $this->render('pages/user_inquiries', $data);
    }

    public function myTickets()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        try {
            $stmt = $this->db->prepare("
                SELECT t.*, 
                       (SELECT message FROM support_ticket_replies WHERE ticket_id = t.id ORDER BY created_at DESC LIMIT 1) as last_reply,
                       (SELECT created_at FROM support_ticket_replies WHERE ticket_id = t.id ORDER BY created_at DESC LIMIT 1) as last_reply_at
                FROM support_tickets t 
                WHERE t.user_id = ? 
                ORDER BY t.created_at DESC
            ");
            $stmt->execute([$user['id']]);
            $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Fetch replies for each ticket
            foreach ($tickets as &$ticket) {
                $stmt = $this->db->prepare("SELECT r.*, u.name as user_name FROM support_ticket_replies r LEFT JOIN users u ON r.user_id = u.id WHERE r.ticket_id = ? ORDER BY r.created_at ASC");
                $stmt->execute([$ticket['id']]);
                $ticket['replies'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            unset($ticket);

            // Fetch bookings for dropdown
            $bookings = $this->db->fetchAll("
                SELECT b.id, b.booking_number, COALESCE(p.plot_number, p2.plot_number) as plot_number,
                       COALESCE(c.name, c2.name) as colony_name, b.status
                FROM bookings b
                LEFT JOIN plots p ON b.property_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN plots p2 ON b.plot_id = p2.id
                LEFT JOIN colonies c2 ON p2.colony_id = c2.id
                WHERE (b.user_id = ? OR b.customer_id = ?) AND b.status NOT IN ('cancelled')
                ORDER BY b.created_at DESC
            ", [$user['id'], $user['id']]);
        } catch (\Exception $e) {
            $tickets = [];
            $bookings = [];
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user_tickets', [
            'page_title' => 'My Support Tickets - APS Dream Home',
            'user' => $user,
            'tickets' => $tickets,
            'bookings' => $bookings,
        ]);
    }

    public function createTicket()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $subject = trim($_POST['subject'] ?? '');
        $priority = trim($_POST['priority'] ?? 'medium');
        $message = trim($_POST['message'] ?? '');
        $bookingId = !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;

        if (empty($subject) || empty($message)) {
            $_SESSION['error'] = 'Subject and message are required.';
            header('Location: ' . BASE_URL . '/user/tickets');
            exit;
        }

        if (!in_array($priority, ['low', 'medium', 'high'])) {
            $priority = 'medium';
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO support_tickets (user_id, subject, message, priority, booking_id, status, category, created_at) VALUES (?, ?, ?, ?, ?, 'open', 'general', NOW())");
            $stmt->execute([$user['id'], $subject, $message, $priority, $bookingId]);
            $ticketId = $this->db->lastInsertId();

            // Add initial message as reply
            $stmt = $this->db->prepare("INSERT INTO support_ticket_replies (ticket_id, user_id, message, is_admin) VALUES (?, ?, ?, 0)");
            $stmt->execute([$ticketId, $user['id'], $message]);

            // Send confirmation email
            try {
                $emailSvc = new \App\Services\EmailTemplateService();
                $emailSvc->sendSupportTicketCreated((int)$user['id'], [
                    'ticket_number' => 'TKT-' . str_pad($ticketId, 6, '0', STR_PAD_LEFT),
                    'subject' => $subject,
                    'description' => $message,
                    'priority' => $priority,
                ]);
            } catch (\Throwable $e) {
                error_log("[UserController::createTicket] email failed: " . $e->getMessage());
            }

            $_SESSION['success'] = 'Support ticket created successfully!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to create ticket. Please try again.';
        }

        header('Location: ' . BASE_URL . '/user/tickets');
        exit;
    }

    public function supportTickets()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $service = new \App\Services\SupportTicketService();

        $status = $_GET['status'] ?? null;
        $category = $_GET['category'] ?? null;
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = $service->getTickets((int)$user['id'], $status, $category, null, null, $page);
        $userStats = $service->getUserTicketStats((int)$user['id']);

        $this->layout = 'layouts/customer';
        $this->render('pages/user/support_tickets', [
            'page_title' => 'My Support Tickets - APS Dream Home',
            'user' => $user,
            'tickets' => $result['tickets'],
            'total' => $result['total'],
            'page' => $result['page'],
            'total_pages' => $result['total_pages'],
            'status' => $status,
            'category' => $category,
            'userStats' => $userStats,
        ]);
    }

    public function createSupportTicket()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $this->layout = 'layouts/customer';
        $this->render('pages/user/create_ticket', [
            'page_title' => 'Create Support Ticket - APS Dream Home',
            'user' => $user,
        ]);
    }

    public function storeSupportTicket()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $category = trim($_POST['category'] ?? 'general');
        $priority = trim($_POST['priority'] ?? 'medium');
        $bookingId = !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;

        if (strlen($subject) < 5) {
            $_SESSION['error'] = 'Subject must be at least 5 characters.';
            header('Location: ' . BASE_URL . '/user/support/create');
            exit;
        }
        if (strlen($message) < 10) {
            $_SESSION['error'] = 'Message must be at least 10 characters.';
            header('Location: ' . BASE_URL . '/user/support/create');
            exit;
        }
        if (!in_array($category, ['general', 'payment', 'booking', 'legal', 'technical', 'complaint', 'other'])) {
            $category = 'general';
        }
        if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
            $priority = 'medium';
        }

        try {
            $service = new \App\Services\SupportTicketService();
            $ticket = $service->createTicket((int)$user['id'], $subject, $message, $category, $priority, $bookingId);

            // Send confirmation email
            try {
                $emailSvc = new \App\Services\EmailTemplateService();
                $emailSvc->sendSupportTicketCreated((int)$user['id'], [
                    'ticket_number' => $ticket['ticket_number'],
                    'subject' => $subject,
                    'description' => $message,
                    'priority' => $priority,
                ]);
            } catch (\Throwable $e) {
                error_log("[UserController::storeSupportTicket] email failed: " . $e->getMessage());
            }

            $_SESSION['success'] = 'Ticket ' . $ticket['ticket_number'] . ' created successfully!';
            header('Location: ' . BASE_URL . '/user/support/' . $ticket['id']);
            exit;
        } catch (\Exception $e) {
            error_log("storeSupportTicket: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to create ticket. Please try again.';
            header('Location: ' . BASE_URL . '/user/support/create');
            exit;
        }
    }

    public function ticketDetail($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        if (!$id) {
            header('Location: ' . BASE_URL . '/user/support');
            exit;
        }

        $service = new \App\Services\SupportTicketService();
        $ticket = $service->getTicket((int)$id);

        if (!$ticket || (int)$ticket['user_id'] !== (int)$user['id']) {
            $_SESSION['error'] = 'Ticket not found.';
            header('Location: ' . BASE_URL . '/user/support');
            exit;
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/ticket_detail', [
            'page_title' => $ticket['ticket_number'] . ' - APS Dream Home',
            'user' => $user,
            'ticket' => $ticket,
        ]);
    }

    public function ticketReply($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            header('Location: ' . BASE_URL . '/user/support');
            exit;
        }

        $message = trim($_POST['message'] ?? '');
        if (strlen($message) < 2) {
            $_SESSION['error'] = 'Reply message is required.';
            header('Location: ' . BASE_URL . '/user/support/' . $id);
            exit;
        }

        $service = new \App\Services\SupportTicketService();
        $ticket = $service->getTicket((int)$id);

        if (!$ticket || (int)$ticket['user_id'] !== (int)$user['id']) {
            $_SESSION['error'] = 'Ticket not found.';
            header('Location: ' . BASE_URL . '/user/support');
            exit;
        }

        if (in_array($ticket['status'], ['resolved', 'closed'])) {
            $_SESSION['error'] = 'This ticket is ' . $ticket['status'] . ' and cannot accept new replies.';
            header('Location: ' . BASE_URL . '/user/support/' . $id);
            exit;
        }

        try {
            $service->addReply((int)$id, (int)$user['id'], $message, false);
            $_SESSION['success'] = 'Reply sent successfully!';
        } catch (\Exception $e) {
            error_log("ticketReply: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to send reply.';
        }

        header('Location: ' . BASE_URL . '/user/support/' . $id);
        exit;
    }

    public function profile()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $error = '';
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($name) || empty($phone)) {
                $error = 'Please fill in required fields.';
            } elseif (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    $error = 'Password must be at least 6 characters.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Passwords do not match.';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $this->db->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
                    $stmt->execute([$name, $phone, $hashedPassword, $_SESSION['user_id']]);

                    $_SESSION['user_name'] = $name;
                    $success = true;
                    $user['name'] = $name;
                    $user['phone'] = $phone;
                }
            } else {
                $svc = new \App\Services\UserRegistrationService();
                $result = $svc->updateProfile($_SESSION['user_id'], [
                    'name' => $name,
                    'phone' => $phone,
                ]);
                if ($result['success']) {
                    $_SESSION['user_name'] = $name;
                    $success = true;
                    $user['name'] = $name;
                    $user['phone'] = $phone;
                } else {
                    $error = $result['message'];
                }
            }
        }

        // Define BASE_PATH for shared view
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 3));
        }

        // Set variables for shared view
        $userRole = $user['role'] ?? 'customer';
        $profileUrl = BASE_URL . '/user/profile';
        $securityUrl = null; // Front users don't have security page yet
        $canEdit = true;

        // Use customer profile view with customer layout
        $this->layout = 'layouts/customer';
        
        $this->render('pages/user_profile', [
            'user' => $user,
            'error' => $error,
            'success' => $success,
            'current_page' => 'profile'
        ]);
    }

    /**
     * Bank Details Page
     */
    public function bankDetails()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please login first';
            header('Location: ' . BASE_URL . '/login?redirect=/user/bank-details');
            exit;
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user_bank_details', []);
    }

    /**
     * My Address Page
     */
    public function address()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please login first';
            header('Location: ' . BASE_URL . '/login?redirect=/user/address');
            exit;
        }
        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/customer';
        $this->render('pages/user/address', ['page_title' => 'My Address - APS Dream Home', 'current_page' => 'address']);
    }

    /**
     * Insurance Page
     */
    public function insurance()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please login first';
            header('Location: ' . BASE_URL . '/login?redirect=/user/insurance');
            exit;
        }
        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/customer';
        $this->render('pages/user/insurance', ['page_title' => 'Insurance - APS Dream Home', 'current_page' => 'insurance']);
    }

    /**
     * Investment Plans Page
     */
    public function investmentPlans()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please login first';
            header('Location: ' . BASE_URL . '/login?redirect=/user/investment-plans');
            exit;
        }
        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/customer';
        $this->render('pages/user/investment_plans', ['page_title' => 'Investment Plans - APS Dream Home', 'current_page' => 'investment']);
    }

    /**
     * Save Bank Details
     */
    public function saveBankDetails()
    {
        @session_start();

        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $accountHolder = trim($_POST['account_holder'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $ifscCode = trim(strtoupper($_POST['ifsc_code'] ?? ''));
        $bankName = trim($_POST['bank_name'] ?? '');
        $branchName = trim($_POST['branch_name'] ?? '');
        $accountType = $_POST['account_type'] ?? 'savings';
        $upiId = trim($_POST['upi_id'] ?? '');

        // Validation
        if (empty($accountHolder) || empty($accountNumber) || empty($ifscCode)) {
            $_SESSION['error'] = 'Please fill all required fields';
            header('Location: ' . BASE_URL . '/user/bank-details');
            exit;
        }

        // Check if account already exists
        $existing = $this->db->fetch(
            "SELECT id FROM user_bank_accounts WHERE user_id = ?",
            [$userId]
        );

        if ($existing) {
            // Update existing
            $stmt = $this->db->prepare("
                UPDATE user_bank_accounts 
                SET account_holder = ?, account_number = ?, ifsc_code = ?, 
                    bank_name = ?, branch_name = ?, account_type = ?, upi_id = ?
                WHERE user_id = ? AND is_primary = 1
            ");
            $stmt->execute([$accountHolder, $accountNumber, $ifscCode, $bankName, $branchName, $accountType, $upiId, $userId]);
        } else {
            // Insert new
            $stmt = $this->db->prepare("
                INSERT INTO user_bank_accounts 
                (user_id, account_holder, account_number, ifsc_code, bank_name, branch_name, account_type, upi_id, is_primary)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$userId, $accountHolder, $accountNumber, $ifscCode, $bankName, $branchName, $accountType, $upiId]);
        }

        $_SESSION['success'] = 'Bank details saved successfully!';
        header('Location: ' . BASE_URL . '/user/bank-details');
        exit;
    }

    public function bookSiteVisit()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $db->prepare("INSERT INTO site_visit_requests (user_id, user_name, user_email, user_phone, property_id, preferred_date, preferred_time, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $_SESSION['user_name'] ?? '',
                    $_SESSION['user_email'] ?? '',
                    $_POST['phone'] ?? $_SESSION['user_phone'] ?? '',
                    (int)($_POST['property_id'] ?? 0),
                    $_POST['visit_date'] ?? '',
                    $_POST['visit_time'] ?? '',
                    $_POST['notes'] ?? '',
                ]);
                $message = 'Your site visit request has been submitted. Our team will contact you within 24 hours.';
                $message_type = 'success';
            }
            
            // Get user's properties for dropdown
            $props = $db->prepare("SELECT id, CONCAT(property_type, ' - ', address) as name FROM user_properties WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
            $props->execute([$_SESSION['user_id']]);
            $properties = $props->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            $message = $message ?? 'An error occurred. Please try again.';
            $message_type = $message_type ?? 'danger';
            $properties = $properties ?? [];
        }
        
        $this->render('pages/user_book_site_visit', [
            'page_title' => 'Book Site Visit',
            'message' => $message ?? '',
            'message_type' => $message_type ?? '',
            'properties' => $properties ?? [],
        ]);
    }

    public function notificationSettings()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        try {
            $stmt = $this->db->prepare("SELECT * FROM user_notification_preferences WHERE user_id = ?");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stmt->execute([$user['id']]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $prefs = [];
        foreach ($rows as $row) {
            $type = $row['notification_type'];
            $prefs[$type] = [
                'email' => (bool)($row['email_enabled'] ?? true),
                'sms' => (bool)($row['sms_enabled'] ?? false),
                'whatsapp' => (bool)($row['whatsapp_enabled'] ?? false),
                'push' => (bool)($row['push_enabled'] ?? true),
                'in_app' => true,
                'frequency' => $row['frequency'] ?? 'immediate',
            ];
        }

        $flash_error = $_SESSION['error'] ?? '';
        $flash_success = $_SESSION['success'] ?? '';
        unset($_SESSION['error'], $_SESSION['success']);

        $this->layout = 'layouts/customer';
        $this->render('pages/user_notification_settings', [
            'page_title' => 'Notification Settings - APS Dream Home',
            'user' => $user,
            'prefs' => $prefs,
            'flash_error' => $flash_error,
            'flash_success' => $flash_success,
        ]);
    }

    public function updateNotificationSettings()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $channels = $_POST['channels'] ?? [];
        $types = ['booking', 'payment', 'agreement', 'registry', 'possession', 'marketing'];

        foreach ($types as $type) {
            $typeChannels = $channels[$type] ?? [];
            $email = in_array('email', $typeChannels) ? 1 : 0;
            $sms = in_array('sms', $typeChannels) ? 1 : 0;
            $whatsapp = in_array('whatsapp', $typeChannels) ? 1 : 0;
            $push = in_array('push', $typeChannels) ? 1 : 0;

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO user_notification_preferences 
                    (user_id, user_type, notification_type, email_enabled, sms_enabled, whatsapp_enabled, push_enabled, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                    email_enabled = VALUES(email_enabled),
                    sms_enabled = VALUES(sms_enabled),
                    whatsapp_enabled = VALUES(whatsapp_enabled),
                    push_enabled = VALUES(push_enabled),
                    updated_at = NOW()
                ");
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $stmt->execute([
                $user['id'],
                $user['role'] ?? 'customer',
                $type,
                $email,
                $sms,
                $whatsapp,
                $push,
            ]);
        }

        $_SESSION['success'] = 'Notification preferences updated successfully!';
        header('Location: ' . BASE_URL . '/user/notification-settings');
        exit;
    }

    public function updateProfile()
    {
        $this->profile();
    }

    /**
     * User network page
     */
    public function network()
    {
        $this->requireLogin();
        $this->render('pages/user_network', ['page_title' => 'My Network']);
    }

    public function notifications()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $notifService = new NotificationService();
        $notifications = $notifService->getCustomerNotifications($user['id']);
        $unreadCount = $notifService->getUnreadCount($user['id']);

        $this->layout = ($_SESSION['role'] ?? '') === 'associate' ? 'layouts/associate' : 'layouts/customer';
        $this->render('pages/user_notifications', [
            'page_title' => 'Notifications - APS Dream Home',
            'user' => $user,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markNotificationRead($notificationId)
    {
        $this->requireCustomerLogin();

        $notifService = new NotificationService();
        $notifService->markAsRead($notificationId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function markAllNotificationsRead()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $notifService = new NotificationService();
        $notifService->markAllAsRead($user['id']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function apiUnreadCount()
    {
        $count = 0;
        if (!empty($_SESSION['user_id'])) {
            try {
                $notifService = new NotificationService();
                $count = $notifService->getUnreadCount($_SESSION['user_id']);
            } catch (\Exception $e) {
                error_log("UserController::apiUnreadCount: " . $e->getMessage());
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }

    public function favorites()
    {
        $this->requireCustomerLogin();
        $userId = $_SESSION['user_id'];
        try {
            $favorites = $this->db->fetchAll(
                "SELECT p.*, f.created_at as favorited_at
                 FROM favorites f
                 JOIN user_properties p ON f.property_id = p.id
                 WHERE f.user_id = ?
                 ORDER BY f.created_at DESC",
                [$userId]
            );
        } catch (\Exception $e) {
            $favorites = [];
        }
        $this->render('pages/user_favorites', [
            'page_title' => 'My Favorites',
            'favorites' => $favorites
        ]);
    }

    public function savedSearches()
    {
        // Redirect to the new SavedSearchController which has a richer UI
        $controller = new \App\Http\Controllers\Front\SavedSearchController();
        $controller->index();
    }

    public function saveSearch()
    {
        // Redirect legacy calls to the new controller
        $controller = new \App\Http\Controllers\Front\SavedSearchController();
        $controller->store();
    }

    public function deleteSavedSearch($id)
    {
        // Redirect legacy calls to the new controller
        $controller = new \App\Http\Controllers\Front\SavedSearchController();
        $controller->destroy($id);
    }

    public function newBooking()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $colonies = [];
        try {
            $colonies = $this->db->fetchAll("
                SELECT c.id, c.name, c.slug, c.description, c.image_path,
                       c.total_plots, c.available_plots, c.starting_price, c.is_active,
                       d.name as district_name
                FROM colonies c
                LEFT JOIN districts d ON c.district_id = d.id
                WHERE c.is_active = 1 AND c.available_plots > 0
                ORDER BY c.name
            ");
        } catch (\Throwable $e) {
            error_log("UserController::newBooking colonies - " . $e->getMessage());
        }

        $selectedColony = (int)($_GET['colony_id'] ?? 0);
        $plots = [];
        if ($selectedColony > 0) {
            try {
                $plots = $this->db->fetchAll("
                    SELECT p.id, p.plot_number, p.block, p.area_sqft, p.total_price,
                           p.width_ft, p.length_ft, p.dimension_label, p.facing,
                           p.corner_plot, p.road_width_ft, c.name as colony_name
                    FROM plots p
                    JOIN colonies c ON p.colony_id = c.id
                    WHERE p.colony_id = ? AND p.status = 'available'
                    ORDER BY p.block, p.plot_number
                ", [$selectedColony]);
            } catch (\Throwable $e) {
                error_log("UserController::newBooking plots - " . $e->getMessage());
            }
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/new_booking', [
            'page_title' => 'Book a Plot - APS Dream Home',
            'user' => $user,
            'colonies' => $colonies,
            'plots' => $plots,
            'selected_colony' => $selectedColony,
        ]);
    }

    public function createBooking()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'error' => 'Please login to continue'], 401);
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $plotId = (int)($_POST['plot_id'] ?? 0);

        if ($plotId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid plot selection'], 400);
            return;
        }

        try {
            $plot = $this->db->fetch(
                "SELECT p.*, c.name as colony_name FROM plots p JOIN colonies c ON p.colony_id = c.id WHERE p.id = ? AND p.status = 'available'",
                [$plotId]
            );
        } catch (\Throwable $e) {
            error_log("UserController::createBooking plot fetch - " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Unable to verify plot availability'], 500);
            return;
        }

        if (!$plot) {
            $this->json(['success' => false, 'error' => 'This plot is no longer available. Please select another.'], 400);
            return;
        }

        try {
            $existing = $this->db->fetch(
                "SELECT id FROM plot_bookings WHERE plot_id = ? AND status NOT IN ('cancelled')",
                [$plotId]
            );
        } catch (\Throwable $e) {
            error_log("UserController::createBooking existing check - " . $e->getMessage());
            $existing = null;
        }

        if ($existing) {
            $this->json(['success' => false, 'error' => 'This plot already has an active booking.'], 400);
            return;
        }

        $bookingNumber = 'APS-BK-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $totalAmount = (float)$plot['total_price'];
        $tokenAmount = 25000.00;

        try {
            $this->db->beginTransaction();

            $this->db->prepare("
                INSERT INTO plot_bookings
                    (plot_id, customer_id, booking_number, booking_date, total_plot_value,
                     booking_amount, agreement_value, status, channel, notes, created_at, updated_at)
                VALUES (?, ?, ?, CURDATE(), ?, ?, ?, 'token_paid', 'direct', ?, NOW(), NOW())
            ")->execute([
                $plotId,
                $userId,
                $bookingNumber,
                $totalAmount,
                $tokenAmount,
                $totalAmount,
                trim($_POST['notes'] ?? ''),
            ]);

            $bookingId = (int)$this->db->lastInsertId();

            $this->db->prepare("UPDATE plots SET status = 'booked', customer_id = ?, booking_date = CURDATE(), updated_at = NOW() WHERE id = ?")
                ->execute([$userId, $plotId]);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                try { $this->db->rollBack(); } catch (\Throwable $e2) {}
            }
            error_log("UserController::createBooking insert - " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Booking failed. Please try again.'], 500);
            return;
        }

        // Auto-create agreement for this booking
        try {
            $this->db->prepare("
                INSERT INTO agreements (booking_id, plot_id, agreement_type, status, party_a_name, party_b_id, total_value, notes, created_by, created_at, updated_at)
                VALUES (?, ?, 'allotment', 'pending_signature', 'APS Dream Home Pvt. Ltd.', ?, ?, 'Auto-generated on booking', ?, NOW(), NOW())
            ")->execute([
                $bookingId,
                $plotId,
                $userId,
                $totalAmount,
                $userId,
            ]);
        } catch (\Throwable $e) {
            error_log("[UserController] Auto-create agreement failed: " . $e->getMessage());
        }

        // Send booking confirmation notifications (best-effort)
        try {
            $user = $this->getUser();
            $notifier = new \App\Services\BookingNotificationService();
            $colony = $this->db->fetch("SELECT * FROM colonies WHERE id = ?", [$plot['colony_id'] ?? 0]);
            $bookingData = [
                'booking_number' => $bookingNumber,
                'total_plot_value' => $totalAmount,
                'booking_amount' => $tokenAmount,
                'plot_number' => $plot['plot_number'],
                'colony_name' => $plot['colony_name'],
            ];
            $notifier->sendBookingConfirmation($bookingData, $user, $plot, $colony ?: ['name' => $plot['colony_name']]);
        } catch (\Throwable $e) {
            error_log("[UserController] Booking notification failed: " . $e->getMessage());
        }

        // Process referral commission if user was referred
        try {
            $referralService = new \App\Services\ReferralService();
            $referralService->processReferralCommission($userId, $bookingId, $totalAmount);
        } catch (\Throwable $e) {
            error_log("[UserController] Referral commission failed: " . $e->getMessage());
        }

        $this->json([
            'success' => true,
            'booking_id' => $bookingId,
            'booking_number' => $bookingNumber,
            'plot' => $plot['plot_number'],
            'colony' => $plot['colony_name'],
            'total_amount' => $totalAmount,
            'token_amount' => $tokenAmount,
        ]);
    }

    public function bookingConfirmation($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $bookingId = (int)$id;

        if ($bookingId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $booking = null;
        try {
            $booking = $this->db->fetch("
                SELECT pb.*,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       p.corner_plot, p.road_width_ft,
                       c.name as colony_name, d.name as district_name
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                WHERE pb.id = ? AND pb.customer_id = ?
            ", [$bookingId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingConfirmation fetch - " . $e->getMessage());
        }

        if (!$booking) {
            $this->layout = 'layouts/customer';
            $this->render('pages/user/booking_confirmation', [
                'page_title' => 'Booking Not Found - APS Dream Home',
                'user' => $user,
                'booking' => null,
            ]);
            return;
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/booking_confirmation', [
            'page_title' => 'Booking Confirmed - APS Dream Home',
            'user' => $user,
            'booking' => $booking,
        ]);
    }

    /* ------------------------------------------------------------------ *
     *  TOKEN PAYMENT FLOW (Razorpay)
     * ------------------------------------------------------------------ */

    public function payToken($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $bookingId = (int)$id;

        if ($bookingId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $booking = $this->fetchBookingWithPlot($bookingId, $userId);
        if (!$booking) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        // Already paid token — redirect to detail
        if (($booking['status'] ?? '') !== 'token_paid') {
            header('Location: ' . BASE_URL . '/user/bookings/' . $bookingId);
            exit;
        }

        // Create Razorpay order for the token amount
        $tokenAmount = (float)($booking['booking_amount'] ?? 25000);
        $razorpaySvc = new \App\Services\Gateway\RazorpayService();
        $orderResp = $razorpaySvc->createOrder(
            $tokenAmount,
            'INR',
            'TOKEN_' . $booking['booking_number'],
            [
                'booking_id'    => $bookingId,
                'user_id'       => $userId,
                'customer_name' => $user['name'] ?? '',
                'customer_email'=> $user['email'] ?? '',
                'customer_phone'=> $user['phone'] ?? '',
                'description'   => 'Token payment for Plot ' . ($booking['plot_number'] ?? '') . ' — ' . ($booking['colony_name'] ?? ''),
            ]
        );

        $orderId = null;
        if ($orderResp['success'] && isset($orderResp['data']['id'])) {
            $orderId = $orderResp['data']['id'];
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/pay_token', [
            'page_title'   => 'Pay Token Amount — APS Dream Home',
            'user'         => $user,
            'booking'      => $booking,
            'token_amount' => $tokenAmount,
            'order_id'     => $orderId,
            'razorpay'     => [
                'key_id'  => $razorpaySvc->getKeyId(),
                'test'    => $razorpaySvc->isTestMode() || !$razorpaySvc->isConfigured(),
            ],
        ]);
    }

    public function processTokenPayment($id = null)
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'error' => 'Please log in.'], 401);
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token.'], 403);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $bookingId = (int)$id;

        if ($bookingId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid booking.'], 400);
            return;
        }

        $razorpay_order_id   = $_POST['razorpay_order_id']   ?? '';
        $razorpay_payment_id  = $_POST['razorpay_payment_id']  ?? '';
        $razorpay_signature   = $_POST['razorpay_signature']   ?? '';

        if (!$razorpay_order_id || !$razorpay_payment_id || !$razorpay_signature) {
            $this->json(['success' => false, 'error' => 'Missing payment response data.'], 400);
            return;
        }

        $booking = $this->fetchBookingWithPlot($bookingId, $userId);
        if (!$booking) {
            $this->json(['success' => false, 'error' => 'Booking not found.'], 404);
            return;
        }

        if (($booking['status'] ?? '') !== 'token_paid') {
            $this->json(['success' => false, 'error' => 'Token already paid or booking in unexpected state.'], 400);
            return;
        }

        // Verify HMAC-SHA256 signature
        $razorpaySvc = new \App\Services\Gateway\RazorpayService();
        if (!$razorpaySvc->verifyPaymentSignature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature)) {
            error_log("UserController::processTokenPayment signature mismatch booking #{$bookingId}");
            $this->json(['success' => false, 'error' => 'Payment signature verification failed.'], 400);
            return;
        }

        $tokenAmount = (float)($booking['booking_amount'] ?? 25000);

        try {
            $this->db->beginTransaction();

            // Advance booking status
            $this->db->prepare("
                UPDATE plot_bookings
                SET status = 'agreement_signed', updated_at = NOW()
                WHERE id = ? AND customer_id = ? AND status = 'token_paid'
            ")->execute([$bookingId, $userId]);

            // Log payment transaction
            $this->db->prepare("
                INSERT INTO payment_transactions
                    (transaction_id, user_id, booking_id, amount, currency, payment_method,
                     payment_status, gateway_transaction_id, gateway_response, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'INR', 'razorpay', 'completed', ?, ?, NOW(), NOW())
            ")->execute([
                'APS-RCP-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6),
                $userId,
                $bookingId,
                $tokenAmount,
                $razorpay_payment_id,
                json_encode([
                    'order_id'   => $razorpay_order_id,
                    'payment_id' => $razorpay_payment_id,
                    'signature'  => $razorpay_signature,
                ]),
            ]);

            // Update payment_orders record (set paid status)
            try {
                $this->db->prepare("
                    UPDATE payment_orders
                    SET status = 'paid', payment_id = ?, signature = ?, paid_at = NOW()
                    WHERE order_id = ?
                ")->execute([$razorpay_payment_id, $razorpay_signature, $razorpay_order_id]);
            } catch (\Throwable $e) {
                error_log("UserController::processTokenPayment order update: " . $e->getMessage());
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                try { $this->db->rollBack(); } catch (\Throwable $e2) {}
            }
            error_log("UserController::processTokenPayment db error booking #{$bookingId}: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Payment processing failed. Please contact support.'], 500);
            return;
        }

        // Send payment receipt notification (best-effort)
        try {
            $user = $this->getUser();
            $notifier = new \App\Services\BookingNotificationService();
            $notifier->sendPaymentReceipt($booking, $user, $tokenAmount, $razorpay_payment_id);
        } catch (\Throwable $e) {
            error_log("[UserController] Payment notification failed: " . $e->getMessage());
        }

        $this->json([
            'success'  => true,
            'redirect' => BASE_URL . '/user/bookings/' . $bookingId . '/payment-success',
        ]);
    }

    public function paymentSuccess($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $bookingId = (int)$id;

        if ($bookingId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $booking = $this->fetchBookingWithPlot($bookingId, $userId);
        if (!$booking) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        // Fetch the latest payment transaction for this booking
        $payment = null;
        try {
            $payment = $this->db->fetch("
                SELECT * FROM payment_transactions
                WHERE booking_id = ? AND user_id = ? AND payment_status = 'completed'
                ORDER BY created_at DESC LIMIT 1
            ", [$bookingId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::paymentSuccess fetch payment: " . $e->getMessage());
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/payment_success', [
            'page_title' => 'Payment Successful — APS Dream Home',
            'user'       => $user,
            'booking'    => $booking,
            'payment'    => $payment,
            'token_amount' => (float)($booking['booking_amount'] ?? 25000),
        ]);
    }

    /**
     * Shared helper: fetch booking + plot + colony with ownership check.
     */
    private function fetchBookingWithPlot(int $bookingId, int $userId): ?array
    {
        try {
            return $this->db->fetch("
                SELECT pb.*,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       p.corner_plot, p.road_width_ft,
                       c.name as colony_name, d.name as district_name
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                WHERE pb.id = ? AND pb.customer_id = ?
            ", [$bookingId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::fetchBookingWithPlot - " . $e->getMessage());
            return null;
        }
    }

    /* ================================================================
     *  INSTALLMENT ONLINE PAYMENT
     * ================================================================ */

    public function payInstallment($installmentId = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int) $user['id'];
        $instId = (int) $installmentId;

        if ($instId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        try {
            $installment = $this->db->fetch("
                SELECT ips.*,
                       pb.booking_number, pb.customer_id, pb.total_plot_value, pb.status as booking_status,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       c.name as colony_name
                FROM booking_payment_schedules ips
                JOIN plot_bookings pb ON ips.booking_id = pb.id
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE ips.id = ?
            ", [$instId]);
        } catch (\Throwable $e) {
            error_log("UserController::payInstallment fetch: " . $e->getMessage());
            $installment = null;
        }

        if (!$installment) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        if ((int) $installment['customer_id'] !== $userId) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $status = $installment['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'overdue', 'partial'], true)) {
            header('Location: ' . BASE_URL . '/user/bookings/' . (int) $installment['booking_id']);
            exit;
        }

        $emiAmount = (float) ($installment['emi_amount'] ?? 0);
        $paidAmount = (float) ($installment['paid_amount'] ?? 0);
        $lateFee = (float) ($installment['late_fee'] ?? 0);
        $accruedPenalty = (float) ($installment['accrued_penalty'] ?? 0);
        $amountDue = max(0, ($emiAmount - $paidAmount) + $lateFee + $accruedPenalty);

        $razorpaySvc = new \App\Services\Gateway\RazorpayService();
        $orderResp = $razorpaySvc->createOrder(
            $amountDue,
            'INR',
            'EMI_' . ($installment['booking_number'] ?? 'BK') . '_INST' . ($installment['installment_number'] ?? $installment['id']),
            [
                'booking_id'     => (int) $installment['booking_id'],
                'installment_id' => $instId,
                'user_id'        => $userId,
                'customer_name'  => $user['name'] ?? '',
                'customer_email' => $user['email'] ?? '',
                'customer_phone' => $user['phone'] ?? '',
                'description'    => 'EMI Installment #' . ($installment['installment_number'] ?? $instId) . ' — Plot ' . ($installment['plot_number'] ?? ''),
            ]
        );

        $orderId = null;
        if ($orderResp['success'] && isset($orderResp['data']['id'])) {
            $orderId = $orderResp['data']['id'];
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/pay_installment', [
            'page_title'   => 'Pay Installment — APS Dream Home',
            'user'         => $user,
            'installment'  => $installment,
            'booking'      => $installment,
            'amount_due'   => $amountDue,
            'emi_amount'   => $emiAmount,
            'paid_amount'  => $paidAmount,
            'late_fee'     => $lateFee,
            'penalty'      => $accruedPenalty,
            'order_id'     => $orderId,
            'razorpay'     => [
                'key_id' => $razorpaySvc->getKeyId(),
                'test'   => $razorpaySvc->isTestMode() || !$razorpaySvc->isConfigured(),
            ],
        ]);
    }

    public function processInstallmentPayment($installmentId = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'POST required.'], 405);
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token.'], 403);
            return;
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $instId = (int) $installmentId;

        if ($instId <= 0 || $userId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid request.'], 400);
            return;
        }

        $razorpay_order_id  = $_POST['razorpay_order_id']  ?? '';
        $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
        $razorpay_signature  = $_POST['razorpay_signature']  ?? '';

        if (!$razorpay_order_id || !$razorpay_payment_id || !$razorpay_signature) {
            $this->json(['success' => false, 'error' => 'Missing payment response data.'], 400);
            return;
        }

        try {
            $installment = $this->db->fetch("
                SELECT ips.*, pb.customer_id, pb.booking_number, pb.id as bid
                FROM booking_payment_schedules ips
                JOIN plot_bookings pb ON ips.booking_id = pb.id
                WHERE ips.id = ?
            ", [$instId]);
        } catch (\Throwable $e) {
            error_log("UserController::processInstallmentPayment fetch: " . $e->getMessage());
            $installment = null;
        }

        if (!$installment || (int) $installment['customer_id'] !== $userId) {
            $this->json(['success' => false, 'error' => 'Installment not found.'], 404);
            return;
        }

        $status = $installment['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'overdue', 'partial'], true)) {
            $this->json(['success' => false, 'error' => 'Installment is not payable.'], 400);
            return;
        }

        $razorpaySvc = new \App\Services\Gateway\RazorpayService();
        if (!$razorpaySvc->verifyPaymentSignature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature)) {
            error_log("UserController::processInstallmentPayment signature mismatch inst #{$instId}");
            $this->json(['success' => false, 'error' => 'Payment signature verification failed.'], 400);
            return;
        }

        $emiAmount = (float) ($installment['emi_amount'] ?? 0);
        $oldPaid = (float) ($installment['paid_amount'] ?? 0);
        $lateFee = (float) ($installment['late_fee'] ?? 0);
        $accruedPenalty = (float) ($installment['accrued_penalty'] ?? 0);
        $amountDue = max(0, ($emiAmount - $oldPaid) + $lateFee + $accruedPenalty);
        $newPaid = $oldPaid + $amountDue;

        $bookingId = (int) $installment['booking_id'];

        try {
            $this->db->beginTransaction();

            $newStatus = ($newPaid >= $emiAmount) ? 'paid' : 'partial';
            $paidDate = ($newStatus === 'paid') ? date('Y-m-d H:i:s') : null;

            $this->db->prepare("
                UPDATE booking_payment_schedules
                SET paid_amount = ?, status = ?, paid_date = COALESCE(?, paid_date),
                    payment_mode = 'razorpay', updated_at = NOW()
                WHERE id = ?
            ")->execute([$newPaid, $newStatus, $paidDate, $instId]);

            $receiptNumber = 'APS-RCP-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $this->db->prepare("
                INSERT INTO booking_payment_receipts
                    (booking_id, installment_id, receipt_number, amount, payment_mode,
                     transaction_ref, status, receipt_date, created_at)
                VALUES (?, ?, ?, ?, 'razorpay', ?, 'completed', NOW(), NOW())
            ")->execute([$bookingId, $instId, $receiptNumber, $amountDue, $razorpay_payment_id]);

            $this->db->prepare("
                INSERT INTO payment_transactions
                    (transaction_id, user_id, booking_id, amount, currency, payment_method,
                     payment_status, gateway_transaction_id, gateway_response, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'INR', 'razorpay', 'completed', ?, ?, NOW(), NOW())
            ")->execute([
                $receiptNumber, $userId, $bookingId, $amountDue,
                $razorpay_payment_id,
                json_encode([
                    'order_id'        => $razorpay_order_id,
                    'payment_id'      => $razorpay_payment_id,
                    'signature'       => $razorpay_signature,
                    'installment_id'  => $instId,
                ]),
            ]);

            try {
                $this->db->prepare("
                    UPDATE payment_orders
                    SET status = 'paid', payment_id = ?, signature = ?, paid_at = NOW()
                    WHERE order_id = ?
                ")->execute([$razorpay_payment_id, $razorpay_signature, $razorpay_order_id]);
            } catch (\Throwable $e) {
                error_log("UserController::processInstallmentPayment order update: " . $e->getMessage());
            }

            $allPaid = false;
            try {
                $stmtCheck = $this->db->prepare("
                    SELECT COUNT(*) as unpaid
                    FROM booking_payment_schedules
                    WHERE booking_id = ? AND status NOT IN ('paid')
                ");
                $stmtCheck->execute([$bookingId]);
                $unpaidRow = $stmtCheck->fetch(\PDO::FETCH_ASSOC);
                $allPaid = ((int) ($unpaidRow['unpaid'] ?? 1)) === 0;
            } catch (\Throwable $e) {
                error_log("UserController::processInstallmentPayment check-all-paid: " . $e->getMessage());
            }

            if ($allPaid) {
                $this->db->prepare("
                    UPDATE plot_bookings
                    SET status = 'fully_paid', updated_at = NOW()
                    WHERE id = ? AND status NOT IN ('cancelled', 'transferred')
                ")->execute([$bookingId]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                try { $this->db->rollBack(); } catch (\Throwable $e2) {}
            }
            error_log("UserController::processInstallmentPayment db error inst #{$instId}: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Payment processing failed. Please contact support.'], 500);
            return;
        }

        try {
            $notifier = new \App\Services\BookingNotificationService();
            $notifyUser = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]) ?: ['id' => $userId, 'name' => '', 'email' => ''];
            $notifier->sendPaymentReceipt(
                ['booking_number' => $installment['booking_number'], 'id' => $bookingId],
                $notifyUser,
                $amountDue,
                $razorpay_payment_id
            );
        } catch (\Throwable $e) {
            error_log("[UserController] Installment payment notification failed: " . $e->getMessage());
        }

        $this->json([
            'success'  => true,
            'redirect' => BASE_URL . '/user/installments/' . $instId . '/success',
        ]);
    }

    public function installmentSuccess($installmentId = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int) $user['id'];
        $instId = (int) $installmentId;

        if ($instId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        try {
            $installment = $this->db->fetch("
                SELECT ips.*,
                       pb.booking_number, pb.customer_id, pb.total_plot_value, pb.status as booking_status,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       c.name as colony_name
                FROM booking_payment_schedules ips
                JOIN plot_bookings pb ON ips.booking_id = pb.id
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE ips.id = ?
            ", [$instId]);
        } catch (\Throwable $e) {
            error_log("UserController::installmentSuccess fetch: " . $e->getMessage());
            $installment = null;
        }

        if (!$installment || (int) $installment['customer_id'] !== $userId) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $receipt = null;
        try {
            $receipt = $this->db->fetch("
                SELECT * FROM booking_payment_receipts
                WHERE installment_id = ? AND booking_id = ?
                ORDER BY created_at DESC LIMIT 1
            ", [$instId, (int) $installment['booking_id']]);
        } catch (\Throwable $e) {
            error_log("UserController::installmentSuccess fetch receipt: " . $e->getMessage());
        }

        $allInstallments = [];
        try {
            $allInstallments = $this->db->fetchAll("
                SELECT * FROM booking_payment_schedules
                WHERE booking_id = ?
                ORDER BY installment_number ASC
            ", [(int) $installment['booking_id']]);
        } catch (\Throwable $e) {
            error_log("UserController::installmentSuccess fetch schedule: " . $e->getMessage());
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/installment_success', [
            'page_title'     => 'Payment Successful — APS Dream Home',
            'user'           => $user,
            'installment'    => $installment,
            'booking'        => $installment,
            'receipt'        => $receipt,
            'all_installments' => $allInstallments,
        ]);
    }

    /* ------------------------------------------------------------------ *
     *  AGREEMENT SIGNING FLOW
     * ------------------------------------------------------------------ */

    public function agreements()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];

        $agreements = [];
        try {
            $agreements = $this->db->fetchAll("
                SELECT ag.*,
                       pb.booking_number, pb.total_plot_value,
                       p.plot_number, p.block, p.area_sqft,
                       c.name as colony_name
                FROM agreements ag
                LEFT JOIN plot_bookings pb ON ag.booking_id = pb.id
                LEFT JOIN plots p ON ag.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE pb.customer_id = ? OR ag.party_b_id = ?
                ORDER BY ag.created_at DESC
            ", [$userId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::agreements - " . $e->getMessage());
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/agreements', [
            'page_title' => 'My Agreements - APS Dream Home',
            'user' => $user,
            'agreements' => $agreements,
        ]);
    }

    public function agreementDetail($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $agreementId = (int)$id;

        if ($agreementId <= 0) {
            header('Location: ' . BASE_URL . '/user/agreements');
            exit;
        }

        $agreement = null;
        try {
            $agreement = $this->db->fetch("
                SELECT ag.*,
                       pb.booking_number, pb.total_plot_value, pb.booking_amount,
                       pb.status as booking_status, pb.booking_date,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       c.name as colony_name,
                       d.name as district_name,
                       u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM agreements ag
                LEFT JOIN plot_bookings pb ON ag.booking_id = pb.id
                LEFT JOIN plots p ON ag.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN users u ON pb.customer_id = u.id
                WHERE ag.id = ? AND (pb.customer_id = ? OR ag.party_b_id = ?)
            ", [$agreementId, $userId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::agreementDetail - " . $e->getMessage());
        }

        if (!$agreement) {
            header('Location: ' . BASE_URL . '/user/agreements');
            exit;
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/agreement_detail', [
            'page_title' => 'Agreement Details - APS Dream Home',
            'user' => $user,
            'agreement' => $agreement,
        ]);
    }

    public function signAgreement($id = null)
    {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'error' => 'Please login to continue'], 401);
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $agreementId = (int)$id;

        if ($agreementId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid agreement'], 400);
            return;
        }

        // Verify ownership
        $agreement = null;
        try {
            $agreement = $this->db->fetch("
                SELECT ag.*, pb.customer_id, pb.status as booking_status, pb.plot_id
                FROM agreements ag
                LEFT JOIN plot_bookings pb ON ag.booking_id = pb.id
                WHERE ag.id = ? AND (pb.customer_id = ? OR ag.party_b_id = ?)
            ", [$agreementId, $userId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::signAgreement fetch - " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Unable to verify agreement'], 500);
            return;
        }

        if (!$agreement) {
            $this->json(['success' => false, 'error' => 'Agreement not found'], 404);
            return;
        }

        $currentStatus = $agreement['status'] ?? '';
        if ($currentStatus === 'signed' || $currentStatus === 'registered') {
            $this->json(['success' => false, 'error' => 'Agreement is already signed'], 400);
            return;
        }
        if ($currentStatus === 'cancelled' || $currentStatus === 'expired') {
            $this->json(['success' => false, 'error' => 'This agreement can no longer be signed'], 400);
            return;
        }

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown';

        try {
            $this->db->beginTransaction();

            // 1. Update agreement status
            $this->db->prepare("
                UPDATE agreements
                SET status = 'signed', signed_at = NOW(), signed_ip = ?, updated_at = NOW()
                WHERE id = ?
            ")->execute([$ipAddress, $agreementId]);

            // 2. Update agreement_number if not set
            if (empty($agreement['agreement_number'])) {
                $agreementNumber = 'APS-AGR-' . date('Ymd') . '-' . str_pad($agreementId, 4, '0', STR_PAD_LEFT);
                $this->db->prepare("UPDATE agreements SET agreement_number = ? WHERE id = ?")
                    ->execute([$agreementNumber, $agreementId]);
            }

            // 3. Auto-advance plot_bookings status if needed
            $bookingStatus = $agreement['booking_status'] ?? '';
            if ($bookingStatus === 'token_paid') {
                $this->db->prepare("
                    UPDATE plot_bookings SET status = 'agreement_signed', updated_at = NOW()
                    WHERE id = ? AND status = 'token_paid'
                ")->execute([$agreement['booking_id']]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                try { $this->db->rollBack(); } catch (\Throwable $e2) {}
            }
            error_log("UserController::signAgreement db error #{$agreementId}: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Signing failed. Please try again.'], 500);
            return;
        }

        // 4. Send notification (best-effort)
        try {
            $notifier = new \App\Services\BookingNotificationService();
            $user = $this->getUser();
            $booking = $this->fetchBookingWithPlot($agreement['booking_id'], $userId);
            if ($booking && method_exists($notifier, 'sendAgreementSigned')) {
                $notifier->sendAgreementSigned($agreement, $user, $booking);
            }
        } catch (\Throwable $e) {
            error_log("[UserController] Agreement sign notification failed: " . $e->getMessage());
        }

        $this->json([
            'success' => true,
            'redirect' => BASE_URL . '/user/agreements/' . $agreementId,
        ]);
    }

    public function agreementPreview($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $agreementId = (int)$id;

        if ($agreementId <= 0) {
            header('Location: ' . BASE_URL . '/user/agreements');
            exit;
        }

        $agreement = null;
        try {
            $agreement = $this->db->fetch("
                SELECT ag.*,
                       pb.booking_number, pb.total_plot_value, pb.booking_amount,
                       pb.status as booking_status, pb.booking_date,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       c.name as colony_name,
                       d.name as district_name,
                       u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM agreements ag
                LEFT JOIN plot_bookings pb ON ag.booking_id = pb.id
                LEFT JOIN plots p ON ag.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN users u ON pb.customer_id = u.id
                WHERE ag.id = ? AND (pb.customer_id = ? OR ag.party_b_id = ?)
            ", [$agreementId, $userId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::agreementPreview - " . $e->getMessage());
        }

        if (!$agreement) {
            header('Location: ' . BASE_URL . '/user/agreements');
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $this->renderAgreementHtml($agreement);
        exit;
    }

    private function renderAgreementHtml(array $agreement): string
    {
        $customerName = htmlspecialchars($agreement['customer_name'] ?? 'Customer');
        $plotNo = htmlspecialchars($agreement['plot_number'] ?? 'N/A');
        $block = htmlspecialchars($agreement['block'] ?? '');
        $colonyName = htmlspecialchars($agreement['colony_name'] ?? 'N/A');
        $district = htmlspecialchars($agreement['district_name'] ?? 'Gorakhpur');
        $area = number_format((float)($agreement['area_sqft'] ?? 0));
        $dimLabel = htmlspecialchars($agreement['dimension_label'] ?? (($agreement['width_ft'] ?? 0) . ' x ' . ($agreement['length_ft'] ?? 0) . ' ft'));
        $facing = htmlspecialchars($agreement['facing'] ?? 'N/A');
        $totalValue = number_format((float)($agreement['total_value'] ?? $agreement['plot_price'] ?? $agreement['total_plot_value'] ?? 0), 2);
        $bookingNo = htmlspecialchars($agreement['booking_number'] ?? 'N/A');
        $agrNumber = htmlspecialchars($agreement['agreement_number'] ?? 'APS-AGR-' . str_pad($agreement['id'], 4, '0', STR_PAD_LEFT));
        $agrDate = date('d M Y', strtotime($agreement['agreement_date'] ?? $agreement['created_at'] ?? 'now'));
        $signedAt = !empty($agreement['signed_at']) ? date('d M Y, h:i A', strtotime($agreement['signed_at'])) : '';
        $agreementType = ucwords(str_replace('_', ' ', $agreement['agreement_type'] ?? 'allotment'));
        $stampDuty = number_format((float)($agreement['stamp_duty_amount'] ?? 0), 2);
        $registrationFee = number_format((float)($agreement['registration_fee'] ?? 0), 2);
        $today = date('d M Y');
        $isSigned = ($agreement['status'] ?? '') === 'signed';

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agreement Preview - ' . $agrNumber . '</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; color: #1e293b; line-height: 1.7; background: #fff; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-35deg); font-size: 72px; font-weight: 900; color: rgba(0,0,0,0.03); letter-spacing: 12px; text-transform: uppercase; pointer-events: none; z-index: 0; white-space: nowrap; }
        .page { max-width: 800px; margin: 0 auto; padding: 40px 50px; position: relative; z-index: 1; }
        .header { text-align: center; border-bottom: 3px solid #0d9488; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #0d9488; font-size: 26px; margin-bottom: 4px; }
        .header .tagline { color: #6b7280; font-size: 13px; letter-spacing: 2px; text-transform: uppercase; }
        .header .contact { font-size: 12px; color: #6b7280; margin-top: 8px; }
        .header .contact span { margin: 0 8px; }
        .agr-title { text-align: center; font-size: 20px; font-weight: 700; color: #1e293b; margin: 20px 0; text-transform: uppercase; letter-spacing: 1px; }
        .agr-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; margin-bottom: 24px; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
        .agr-meta .row { display: flex; }
        .agr-meta .label { font-weight: 600; color: #6b7280; min-width: 160px; }
        .agr-meta .value { color: #1e293b; font-weight: 500; }
        .section-title { font-size: 16px; font-weight: 700; color: #0d9488; margin: 24px 0 12px; padding-bottom: 6px; border-bottom: 1px solid #e5e7eb; }
        .body-text { margin: 12px 0; line-height: 1.8; }
        .body-text p { margin-bottom: 10px; text-align: justify; }
        .clause { margin-bottom: 14px; }
        .clause-num { font-weight: 700; color: #0d9488; }
        .parties-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 16px 0; }
        .party-card { padding: 16px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f8fafc; }
        .party-card h4 { color: #0d9488; margin-bottom: 8px; font-size: 14px; }
        .party-card p { font-size: 13px; line-height: 1.6; }
        .plot-box { padding: 16px; border: 2px solid #0d9488; border-radius: 8px; margin: 16px 0; background: #f5f3ff; }
        .plot-box h4 { color: #0d9488; margin-bottom: 8px; }
        .plot-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px; }
        .plot-detail .label { font-weight: 600; color: #6b7280; }
        .payment-box { padding: 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; margin: 16px 0; }
        .payment-box h4 { color: #166534; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        table th { background: #0d9488; color: #fff; padding: 10px 12px; text-align: left; font-size: 13px; }
        table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .signature-section { margin-top: 50px; display: flex; justify-content: space-between; }
        .sig-block { text-align: center; width: 220px; }
        .sig-block .line { border-top: 1px solid #1e293b; margin-top: 70px; padding-top: 8px; font-weight: 600; font-size: 13px; }
        .sig-block .sub { font-size: 11px; color: #6b7280; }
        .signed-badge { text-align: center; padding: 12px; background: #dcfce7; border: 1px solid #86efac; border-radius: 8px; margin: 20px 0; }
        .signed-badge i { color: #16a34a; margin-right: 8px; }
        .footer { margin-top: 30px; border-top: 2px solid #0d9488; padding-top: 16px; font-size: 12px; color: #6b7280; text-align: center; }
        .no-print { text-align: center; margin-top: 24px; }
        .no-print button { background: #0d9488; color: #fff; border: none; padding: 10px 28px; border-radius: 8px; font-size: 14px; cursor: pointer; font-weight: 600; margin: 0 6px; }
        .no-print a { display: inline-block; padding: 10px 28px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; border: 1px solid #0d9488; color: #0d9488; margin: 0 6px; }
        @media print {
            body { background: #fff; margin: 0; }
            .page { padding: 20px 30px; max-width: 100%; }
            .no-print { display: none !important; }
            .watermark { color: rgba(0,0,0,0.02); }
        }
    </style>
</head>
<body>
    <div class="watermark">AGREEMENT</div>
    <div class="page">
        <div class="header">
            <h1>APS Dream Home</h1>
            <div class="tagline">Building Dreams, Delivering Trust</div>
            <div class="contact">
                <span><i class="fas fa-map-marker-alt"></i> Head Office: Gorakhpur, Uttar Pradesh</span>
                <span><i class="fas fa-phone"></i> +91 92771 21112</span>
                <span><i class="fas fa-envelope"></i> info@apsdreamhome.com</span>
            </div>
        </div>

        <div class="agr-title">' . $agreementType . '</div>

        <div class="agr-meta">
            <div class="row"><span class="label">Agreement No:</span> <span class="value">' . $agrNumber . '</span></div>
            <div class="row"><span class="label">Date:</span> <span class="value">' . $agrDate . '</span></div>
            <div class="row"><span class="label">Booking Ref:</span> <span class="value">' . $bookingNo . '</span></div>
            <div class="row"><span class="label">Total Value:</span> <span class="value">₹' . $totalValue . '</span></div>
        </div>

        ' . ($isSigned ? '<div class="signed-badge"><i class="fas fa-check-circle"></i> <strong>This agreement has been digitally signed</strong> on ' . $signedAt . '</div>' : '') . '

        <div class="section-title">1. Parties to the Agreement</div>
        <div class="parties-grid">
            <div class="party-card">
                <h4><i class="fas fa-building"></i> Party A (Seller)</h4>
                <p><strong>APS Dream Home Pvt. Ltd.</strong><br>
                Registered Office: Gorakhpur, Uttar Pradesh, India<br>
                CIN: U70102UP2020PTC123456<br>
                GSTIN: 09AAACS1234F1Z5<br>
                Represented by: Authorized Signatory</p>
            </div>
            <div class="party-card">
                <h4><i class="fas fa-user"></i> Party B (Buyer)</h4>
                <p><strong>' . $customerName . '</strong><br>
                Email: ' . htmlspecialchars($agreement['customer_email'] ?? '') . '<br>
                Phone: ' . htmlspecialchars($agreement['customer_phone'] ?? '') . '<br>
                (Hereinafter referred to as the "Purchaser/Buyer")</p>
            </div>
        </div>

        <div class="section-title">2. Property Description</div>
        <div class="plot-box">
            <h4><i class="fas fa-map-marked-alt"></i> immovable Property Details</h4>
            <div class="plot-detail">
                <div><span class="label">Colony/Project:</span> ' . $colonyName . '</div>
                <div><span class="label">District:</span> ' . $district . ', Uttar Pradesh</div>
                <div><span class="label">Plot Number:</span> ' . $plotNo . ($block ? ' (Block ' . $block . ')' : '') . '</div>
                <div><span class="label">Area:</span> ' . $area . ' sq ft</div>
                <div><span class="label">Dimensions:</span> ' . $dimLabel . '</div>
                <div><span class="label">Facing:</span> ' . $facing . '</div>
            </div>
        </div>

        <div class="section-title">3. Terms and Conditions</div>
        <div class="body-text">
            <div class="clause">
                <span class="clause-num">3.1 Agreement Value:</span> The total agreement value for the said property is <strong>₹' . $totalValue . '</strong> (Rupees ' . $this->numberToWords((float)($agreement['total_value'] ?? $agreement['plot_price'] ?? 0)) . ' Only).
            </div>
            <div class="clause">
                <span class="clause-num">3.2 Token Amount:</span> The Purchaser has paid a token amount of <strong>₹' . number_format((float)($agreement['booking_amount'] ?? 25000), 2) . '</strong> at the time of booking, which is adjustable against the total consideration.
            </div>
            <div class="clause">
                <span class="clause-num">3.3 Payment Schedule:</span> The balance amount shall be paid as per the Payment Schedule mutually agreed upon between the parties. The Purchaser shall pay all installments by the due dates. In case of delayed payment, interest at the rate of 18% per annum shall be charged on the overdue amount from the due date until the date of actual payment.
            </div>
            <div class="clause">
                <span class="clause-num">3.4 Possession:</span> The Seller shall endeavor to deliver possession of the property within 24 months from the date of this Agreement, subject to force majeure conditions. Delay in possession beyond 24 months shall entitle the Purchaser to compensation at the rate of ₹5 per sq ft per month for the delayed period.
            </div>
            <div class="clause">
                <span class="clause-num">3.5 Title and Transfer:</span> The Seller warrants that the property is free from all encumbrances, liens, charges, and litigation. Upon receipt of full payment, the Seller shall execute all necessary documents to transfer clear and marketable title to the Purchaser.
            </div>
            <div class="clause">
                <span class="clause-num">3.6 Stamp Duty and Registration:</span> The stamp duty and registration charges for this Agreement and the subsequent sale deed shall be borne by the Purchaser as per the Indian Stamp Act, 1899 and the Indian Registration Act, 1908. Current estimated stamp duty: <strong>₹' . $stampDuty . '</strong> and registration fee: <strong>₹' . $registrationFee . '</strong>.
            </div>
            <div class="clause">
                <span class="clause-num">3.7 Maintenance and Utilities:</span> The Purchaser shall be responsible for all maintenance charges, property taxes, and utility charges from the date of possession. The Seller shall hand over the property with active water, electricity, and sewage connections.
            </div>
            <div class="clause">
                <span class="clause-num">3.8 Cancellation:</span> In case the Purchaser wishes to cancel this Agreement, the token amount shall be forfeited. If 25% or more of the agreement value has been paid, a refund of 75% of the amount paid (excluding the token amount) shall be processed within 90 days of cancellation request.
            </div>
            <div class="clause">
                <span class="clause-num">3.9 Default by Purchaser:</span> If the Purchaser fails to make payments for more than 90 days beyond the due date, the Seller reserves the right to terminate this Agreement after issuing a 30-day written notice. Upon termination, the Seller shall refund 50% of the amount paid (excluding the token amount) within 60 days.
            </div>
            <div class="clause">
                <span class="clause-num">3.10 Dispute Resolution:</span> Any dispute arising out of or in connection with this Agreement shall first be resolved through mutual negotiation within 30 days. Failing which, the dispute shall be referred to arbitration in accordance with the Arbitration and Conciliation Act, 1996. The seat of arbitration shall be <strong>Gorakhpur, Uttar Pradesh</strong> and the language of proceedings shall be English/Hindi.
            </div>
            <div class="clause">
                <span class="clause-num">3.11 Jurisdiction:</span> The courts of <strong>Gorakhpur, Uttar Pradesh</strong> shall have exclusive jurisdiction over any matter arising from this Agreement.
            </div>
            <div class="clause">
                <span class="clause-num">3.12 Force Majeure:</span> Neither party shall be liable for delays or failures in performance resulting from acts beyond reasonable control, including but not limited to natural disasters, government actions, pandemics, strikes, or war.
            </div>
            <div class="clause">
                <span class="clause-num">3.13 RERA Compliance:</span> This project is registered under the Real Estate (Regulation and Development) Act, 2016 (RERA) with the Uttar Pradesh Real Estate Regulatory Authority. The Purchaser may verify the registration details at the official RERA website.
            </div>
            <div class="clause">
                <span class="clause-num">3.14 Entire Agreement:</span> This Agreement constitutes the entire understanding between the parties and supersedes all prior negotiations, representations, or agreements. Any modification must be in writing and signed by both parties.
            </div>
        </div>

        <div class="section-title">4. Signature</div>
        <div class="body-text">
            <p>IN WITNESS WHEREOF, the parties hereto have executed this Agreement on the date first above written.</p>
        </div>

        <div class="signature-section">
            <div class="sig-block">
                <div class="line">For APS Dream Home Pvt. Ltd.</div>
                <div class="sub">Authorized Signatory</div>
                <div class="sub">Date: ' . $agrDate . '</div>
            </div>
            <div class="sig-block">
                <div class="line">' . $customerName . '</div>
                <div class="sub">Purchaser/Buyer</div>
                <div class="sub">Date: ' . ($signedAt ?: $today) . '</div>
            </div>
        </div>

        <div class="footer">
            <p>APS Dream Home Pvt. Ltd. | Head Office: Gorakhpur, Uttar Pradesh, India</p>
            <p>This is a digitally generated agreement. For queries, contact <strong>+91 92771 21112</strong> or <strong>info@apsdreamhome.com</strong></p>
        </div>

        <div class="no-print">
            <button onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
            <a href="' . BASE_URL . '/user/agreements/' . $agreement['id'] . '"><i class="fas fa-arrow-left"></i> Back to Agreement</a>
        </div>
    </div>
</body>
</html>';
    }

    private function numberToWords(float $num): string
    {
        if ($num <= 0) return 'Zero';
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                  'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                  'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $intPart = (int)$num;
        $words = '';

        if ($intPart >= 10000000) { $words .= $ones[(int)($intPart / 10000000)] . ' Crore '; $intPart %= 10000000; }
        if ($intPart >= 100000) { $words .= $ones[(int)($intPart / 100000)] . ' Lakh '; $intPart %= 100000; }
        if ($intPart >= 1000) { $words .= $ones[(int)($intPart / 1000)] . ' Thousand '; $intPart %= 1000; }
        if ($intPart >= 100) { $words .= $ones[(int)($intPart / 100)] . ' Hundred '; $intPart %= 100; }
        if ($intPart >= 20) { $words .= $tens[(int)($intPart / 10)] . ' '; $intPart %= 10; }
        if ($intPart > 0) { $words .= $ones[$intPart] . ' '; }

        return trim($words) . ' Rupees';
    }

    private function safeInvestorStats(int $userId): array
    {
        try {
            return \App\Services\CacheService::getGamification(
                'customer_investor',
                $userId,
                0,
                function () use ($userId) {
                    $svc = new \App\Services\InvestmentService();
                    return $svc->getStats($userId);
                }
            );
        } catch (\Throwable $e) {
            error_log('Investor stats error: ' . $e->getMessage());
            return ['level' => 'Bronze', 'next_level' => 'Silver', 'progress_pct' => 0, 'next_threshold' => 50000, 'total_invested' => 0, 'total' => 0, 'active' => 0, 'principal' => 0, 'current_value' => 0, 'returns' => 0];
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // CUSTOMER EMI TRACKER — full payment schedule view
    // ═══════════════════════════════════════════════════════════════════

    public function emiTracker()
    {
        $this->requireCustomerLogin();
        $this->layout = 'layouts/customer';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $bookings = [];
        $installments = [];
        $stats = ['total_bookings' => 0, 'active_emis' => 0, 'total_paid' => 0, 'total_pending' => 0, 'overdue_count' => 0, 'next_payment' => null];

        try {
            // Get user's bookings
            $st = $pdo->prepare("
                SELECT b.*, p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       c.name as colony_name
                FROM plot_bookings b
                LEFT JOIN plots p ON b.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE b.customer_id = ?
                ORDER BY b.created_at DESC
            ");
            $st->execute([$userId]);
            $bookings = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $stats['total_bookings'] = count($bookings);

            // Get all installments for user's bookings
            if (!empty($bookings)) {
                $bookingIds = array_column($bookings, 'id');
                $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));

                $st = $pdo->prepare("
                    SELECT ips.*, b.booking_number, p.plot_number, c.name as colony_name
                    FROM booking_payment_schedules ips
                    JOIN plot_bookings b ON b.id = ips.booking_id
                    LEFT JOIN plots p ON b.plot_id = p.id
                    LEFT JOIN colonies c ON p.colony_id = c.id
                    WHERE ips.booking_id IN ($placeholders)
                    ORDER BY ips.due_date ASC
                ");
                $st->execute($bookingIds);
                $installments = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                $today = date('Y-m-d');
                $nextPayment = null;

                foreach ($installments as $inst) {
                    $amount = (float)($inst['amount'] ?? 0);
                    $paid = (float)($inst['paid_amount'] ?? 0);
                    $status = $inst['status'] ?? 'pending';
                    $penalty = (float)($inst['accrued_penalty'] ?? 0);

                    if ($status === 'paid' || $status === 'completed') {
                        $stats['total_paid'] += $paid;
                    } else {
                        $stats['total_pending'] += ($amount - $paid + $penalty);
                        if ($status !== 'paid' && strtotime($inst['due_date'] ?? '') < strtotime($today)) {
                            $stats['overdue_count']++;
                        }
                        if (!$nextPayment && strtotime($inst['due_date'] ?? '') >= strtotime($today)) {
                            $nextPayment = $inst;
                        }
                    }

                    if ($status !== 'paid' && $status !== 'completed') {
                        $stats['active_emis']++;
                    }
                }
                $stats['next_payment'] = $nextPayment;
            }
        } catch (\Throwable $e) {
            error_log('EMI Tracker error: ' . $e->getMessage());
        }

        $this->render('pages/user_emi_tracker', [
            'page_title' => 'EMI Tracker - APS Dream Home',
            'page_description' => 'Track your EMI payments',
            'current_page' => 'emi-tracker',
            'bookings' => $bookings,
            'installments' => $installments,
            'stats' => $stats,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    // CUSTOMER PAYMENT HISTORY
    // ═══════════════════════════════════════════════════════════════════

    public function paymentHistory()
    {
        $this->requireCustomerLogin();
        $this->layout = 'layouts/customer';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $payments = [];
        $stats = ['total_paid' => 0, 'total_count' => 0, 'this_month' => 0, 'last_payment' => null];

        try {
            // Payment receipts
            $st = $pdo->prepare("
                SELECT bpr.*, b.booking_number, p.plot_number, c.name as colony_name,
                       ips.installment_number, ips.due_date
                FROM booking_payment_receipts bpr
                JOIN plot_bookings b ON b.id = bpr.booking_id
                LEFT JOIN plots p ON b.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN booking_payment_schedules ips ON ips.id = bpr.installment_id
                WHERE b.customer_id = ?
                ORDER BY bpr.payment_date DESC, bpr.created_at DESC
            ");
            $st->execute([$userId]);
            $payments = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $thisMonth = date('Y-m');
            foreach ($payments as $p) {
                $amt = (float)($p['amount'] ?? 0);
                $stats['total_paid'] += $amt;
                $stats['total_count']++;
                if (date('Y-m', strtotime($p['payment_date'] ?? $p['created_at'])) === $thisMonth) {
                    $stats['this_month'] += $amt;
                }
                if (!$stats['last_payment']) {
                    $stats['last_payment'] = $p;
                }
            }

            // Also check booking_payment_schedules for paid installments
            $st2 = $pdo->prepare("
                SELECT ips.*, b.booking_number, p.plot_number, c.name as colony_name
                FROM booking_payment_schedules ips
                JOIN plot_bookings b ON b.id = ips.booking_id
                LEFT JOIN plots p ON b.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE b.customer_id = ? AND ips.status IN ('paid', 'completed')
                ORDER BY ips.paid_date DESC
            ");
            $st2->execute([$userId]);
            $paidInstallments = $st2->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // Merge if receipts table is empty
            if (empty($payments) && !empty($paidInstallments)) {
                $payments = $paidInstallments;
            }
        } catch (\Throwable $e) {
            error_log('Payment history error: ' . $e->getMessage());
        }

        $this->render('pages/user_payment_history', [
            'page_title' => 'Payment History - APS Dream Home',
            'page_description' => 'Your payment records',
            'current_page' => 'payment-history',
            'payments' => $payments,
            'stats' => $stats,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    // CUSTOMER SITE VISIT BOOKING
    // ═══════════════════════════════════════════════════════════════════

    public function mySiteVisits()
    {
        $this->requireCustomerLogin();
        $this->layout = 'layouts/customer';
        $userId = $_SESSION['user_id'];
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $visits = [];
        $stats = ['total' => 0, 'upcoming' => 0, 'completed' => 0];

        try {
            $st = $pdo->prepare("
                SELECT sv.*, c.name as colony_name, p.plot_number
                FROM site_visits sv
                LEFT JOIN colonies c ON c.id = sv.colony_id
                LEFT JOIN plots p ON p.id = sv.plot_id
                WHERE sv.user_id = ?
                ORDER BY sv.visit_date DESC, sv.visit_time DESC
            ");
            $st->execute([$userId]);
            $visits = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $today = date('Y-m-d');
            foreach ($visits as $v) {
                $stats['total']++;
                if ($v['status'] === 'completed') $stats['completed']++;
                elseif ($v['visit_date'] >= $today && $v['status'] !== 'cancelled') $stats['upcoming']++;
            }
        } catch (\Throwable $e) {
            error_log('mySiteVisits error: ' . $e->getMessage());
        }

        // Get colonies for booking form
        $colonies = [];
        try {
            $colonies = $pdo->query("SELECT id, name FROM colonies WHERE status = 'active' ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {}

        $this->render('pages/user_site_visits', [
            'page_title' => 'My Site Visits - APS Dream Home',
            'page_description' => 'Your site visit appointments',
            'current_page' => 'site-visits',
            'visits' => $visits,
            'stats' => $stats,
            'colonies' => $colonies,
        ]);
    }

    public function bookSiteVisitAction()
    {
        $this->requireCustomerLogin();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/site-visits');
            return;
        }

        $visitorName = trim($_POST['visitor_name'] ?? '');
        $visitorPhone = trim($_POST['visitor_phone'] ?? '');
        $visitDate = $_POST['visit_date'] ?? '';
        $visitTime = $_POST['visit_time'] ?? '';
        $colonyId = (int)($_POST['colony_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if (empty($visitorName) || empty($visitorPhone) || empty($visitDate) || empty($visitTime)) {
            $_SESSION['error'] = 'Please fill all required fields.';
            $this->redirect('/user/site-visits');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();

            $st = $pdo->prepare("
                INSERT INTO site_visits (colony_id, user_id, visitor_name, visitor_phone, visit_date, visit_time, status, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'scheduled', ?, NOW())
            ");
            $st->execute([$colonyId ?: null, $userId, $visitorName, $visitorPhone, $visitDate, $visitTime, $notes]);

            $_SESSION['success'] = 'Site visit scheduled for ' . date('M d, Y', strtotime($visitDate)) . ' at ' . date('h:i A', strtotime($visitTime)) . '!';
        } catch (\Throwable $e) {
            error_log('bookSiteVisitAction error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to schedule visit.';
        }
        $this->redirect('/user/site-visits');
    }
}
