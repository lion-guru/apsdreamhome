<?php
namespace App\Http\Controllers\Admin;
use App\Core\Database\Database;
use App\Services\MLM\MLMNetworkService;
use App\Services\MLM\RERAComplianceService;
use App\Services\MLM\DailyCappingService;
use App\Services\MLM\LeadershipSalaryService;
use App\Services\Booking\BookingComplianceService;
use App\Services\Notification\BookingNotificationService;

class MLMRealEstateController extends \App\Http\Controllers\Admin\AdminController
{
    public function dashboard()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            
            $stats = [];
            $stats['total_networkers'] = $db->query("SELECT COUNT(*) as c FROM users WHERE onboarding_track = 'networker'")->fetch()['c'] ?? 0;
            $stats['total_consultants'] = $db->query("SELECT COUNT(*) as c FROM users WHERE onboarding_track = 'free_consultant'")->fetch()['c'] ?? 0;
            $stats['total_plots'] = $db->query("SELECT COUNT(*) as c FROM inventory_plots")->fetch()['c'] ?? 0;
            $stats['available_plots'] = $db->query("SELECT COUNT(*) as c FROM inventory_plots WHERE status = 'Available'")->fetch()['c'] ?? 0;
            $stats['pending_rera'] = $db->query("SELECT COUNT(*) as c FROM rera_requests WHERE status = 'pending'")->fetch()['c'] ?? 0;
            $stats['active_salaries'] = $db->query("SELECT COUNT(*) as c FROM salary_tracker WHERE status = 'active'")->fetch()['c'] ?? 0;
            $stats['total_bookings'] = $db->query("SELECT COUNT(*) as c FROM bookings WHERE status NOT IN ('cancelled')")->fetch()['c'] ?? 0;
            $stats['pending_bookings'] = $db->query("SELECT COUNT(*) as c FROM bookings WHERE status = 'pending'")->fetch()['c'] ?? 0;
            $stats['booked_plots'] = $db->query("SELECT COUNT(*) as c FROM plots WHERE status IN ('booked','hold')")->fetch()['c'] ?? 0;
            $stats['total_commission_paid'] = $db->query("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger WHERE status = 'paid'")->fetch()['c'] ?? 0;
            $stats['total_revenue'] = $db->query("SELECT COALESCE(SUM(amount),0) as c FROM bookings WHERE status IN ('confirmed','completed')")->fetch()['c'] ?? 0;
            
            $recentBookings = $db->query("SELECT b.*, u.name as agent_name FROM bookings b LEFT JOIN users u ON u.id = b.associate_id ORDER BY b.created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
            
            $dailyCapData = $db->query("SELECT COALESCE(SUM(amount),0) as flushed FROM retained_earnings WHERE DATE(created_at) = CURDATE() AND retention_reason = 'daily_cap_flush'")->fetch();
            
        } catch (\Exception $e) {
            $stats = ['total_networkers'=>0,'total_consultants'=>0,'total_plots'=>0,'available_plots'=>0,'pending_rera'=>0,'active_salaries'=>0,'total_bookings'=>0];
            $recentBookings = [];
            $dailyCapData = ['flushed'=>0];
        }
        
        $this->render('admin/mlm-realestate/dashboard', [
            'page_title' => 'MLM & Real Estate Enterprise Dashboard',
            'stats' => $stats,
            'recent_bookings' => $recentBookings,
            'daily_cap_flushed' => $dailyCapData['flushed'] ?? 0,
        ]);
    }
    
    public function packages()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $packages = $db->query("SELECT * FROM packages ORDER BY price ASC")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $packages = [];
        }
        $this->render('admin/mlm-realestate/packages', ['page_title' => 'Networker Packages', 'packages' => $packages]);
    }
    
    public function savePackage()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            try {
                $db = Database::getInstance()->getConnection();
                $id = (int)($_POST['id'] ?? 0);
                $data = [
                    'name' => $_POST['name'],
                    'price' => (float)$_POST['price'],
                    'direct_reward' => (float)$_POST['direct_reward'],
                    'level_reward' => (float)$_POST['level_reward'],
                    'daily_capping' => (float)$_POST['daily_capping'],
                    'description' => $_POST['description'] ?? '',
                    'is_active' => (int)($_POST['is_active'] ?? 1),
                ];
                if ($id > 0) {
                    $stmt = $db->prepare("UPDATE packages SET name=?, price=?, direct_reward=?, level_reward=?, daily_capping=?, description=?, is_active=? WHERE id=?");
                    $stmt->execute([$data['name'], $data['price'], $data['direct_reward'], $data['level_reward'], $data['daily_capping'], $data['description'], $data['is_active'], $id]);
                } else {
                    $stmt = $db->prepare("INSERT INTO packages (name, price, direct_reward, level_reward, daily_capping, description, is_active) VALUES (?,?,?,?,?,?,?)");
                    $stmt->execute([$data['name'], $data['price'], $data['direct_reward'], $data['level_reward'], $data['daily_capping'], $data['description'], $data['is_active']]);
                }
                $_SESSION['flash_message'] = 'Package saved successfully';
                $_SESSION['flash_type'] = 'success';
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/packages');
        exit;
    }
    
    public function networkers()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $networkers = $db->query("SELECT u.*, p.name as package_name, uw.balance as wallet_balance,
                (SELECT COUNT(*) FROM mlm_network_tree mnt2 WHERE mnt2.parent_id = mnt.id) as downline_count
                FROM users u 
                LEFT JOIN packages p ON p.id = u.current_package_id
                LEFT JOIN user_wallets uw ON uw.user_id = u.id AND uw.user_type = 'associate'
                LEFT JOIN mlm_network_tree mnt ON mnt.associate_id = u.id
                WHERE u.onboarding_track = 'networker'
                ORDER BY u.created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $networkers = [];
        }
        $this->render('admin/mlm-realestate/networkers', ['page_title' => 'Networkers', 'networkers' => $networkers]);
    }
    
    public function freeConsultants()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $consultants = $db->query("SELECT u.*, uw.balance as wallet_balance, u.cumulative_sales, u.associate_payout_slab
                FROM users u 
                LEFT JOIN user_wallets uw ON uw.user_id = u.id AND uw.user_type = 'associate'
                WHERE u.onboarding_track = 'free_consultant'
                ORDER BY u.created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $consultants = [];
        }
        $this->render('admin/mlm-realestate/free_consultants', ['page_title' => 'Free Consultants', 'consultants' => $consultants]);
    }
    
    public function reraRequests()
    {
        $this->requireAdmin();
        try {
            $reraService = new RERAComplianceService();
            $requests = $reraService->getPendingRequests();
            
            $db = Database::getInstance()->getConnection();
            try {
                $allRequests = $db->query("SELECT r.*, u.name as user_name, u.email as user_email, u.is_rera_approved 
                    FROM rera_requests r JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
        } catch (\Exception $e) {
            $allRequests = [];
        }
        $this->render('admin/mlm-realestate/rera_requests', ['page_title' => 'RERA Compliance', 'requests' => $allRequests]);
    }
    
    public function approveRERA()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $id = (int)($_POST['id'] ?? 0);
            $reraNumber = $_POST['rera_number'] ?? '';
            if ($id && $reraNumber) {
                $reraService = new RERAComplianceService();
                $result = $reraService->approveRERA($id, $reraNumber, $_SESSION['admin_id'] ?? 0);
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = $result['success'] ? 'success' : 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/rera');
        exit;
    }
    
    public function plotsInventory()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $block = $_GET['block'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $sql = "SELECT * FROM inventory_plots WHERE 1=1";
            $params = [];
            if ($block) { $sql .= " AND block_name = ?"; $params[] = $block; }
            if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
            $sql .= " ORDER BY block_name, plot_no LIMIT 300";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $plots = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $blocks = $db->query("SELECT DISTINCT block_name FROM inventory_plots ORDER BY block_name")->fetchAll(\PDO::FETCH_COLUMN);
            $statusSummary = $db->query("SELECT status, COUNT(*) as cnt FROM inventory_plots GROUP BY status")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $plots = []; $blocks = []; $statusSummary = [];
        }
        $this->render('admin/mlm-realestate/plots', [
            'page_title' => 'Plot Inventory',
            'plots' => $plots,
            'blocks' => $blocks,
            'status_summary' => $statusSummary,
        ]);
    }
    
    public function bookings()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $bookings = $db->query("SELECT b.*, u.name as agent_name, 
                JSON_UNQUOTE(JSON_EXTRACT(b.notes, '$.plot_id')) as plot_ref,
                JSON_UNQUOTE(JSON_EXTRACT(b.notes, '$.payment_mode')) as payment_mode
                FROM bookings b 
                LEFT JOIN users u ON u.id = b.associate_id 
                ORDER BY b.created_at DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $bookings = [];
        }
        $this->render('admin/mlm-realestate/bookings', ['page_title' => 'Bookings', 'bookings' => $bookings]);
    }
    
    public function bookingDetail(int $id)
    {
        $this->requireAdmin();
        try {
            $bookingService = new BookingComplianceService();
            $status = $bookingService->getBookingStatus($id);
        } catch (\Exception $e) {
            $status = ['error' => $e->getMessage()];
        }
        $this->render('admin/mlm-realestate/booking_detail', ['page_title' => 'Booking Details', 'status' => $status]);
    }
    
    public function recordPayment()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $bookingId = (int)($_POST['booking_id'] ?? 0);
            $amount = (float)($_POST['amount'] ?? 0);
            $mode = $_POST['mode'] ?? 'cash';
            if ($bookingId > 0 && $amount > 0) {
                $bookingService = new BookingComplianceService();
                $result = $bookingService->recordPayment($bookingId, $amount, $mode);
                $_SESSION['flash_message'] = $result['success'] ? 'Payment recorded' : 'Error: ' . ($result['error'] ?? '');
                $_SESSION['flash_type'] = $result['success'] ? 'success' : 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/bookings');
        exit;
    }
    
    public function salaryTracker()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $trackers = $db->query("SELECT st.*, u.name as user_name, u.email 
                FROM salary_tracker st 
                JOIN users u ON u.id = st.user_id 
                ORDER BY st.created_at DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC);
            
            $activeCount = 0; $totalMonthly = 0;
            foreach ($trackers as $t) {
                if ($t['status'] === 'active') { $activeCount++; $totalMonthly += (float)$t['monthly_payout']; }
            }
        } catch (\Exception $e) {
            $trackers = []; $activeCount = 0; $totalMonthly = 0;
        }
        $this->render('admin/mlm-realestate/salary_tracker', [
            'page_title' => 'Leadership Salary Tracker',
            'trackers' => $trackers,
            'active_count' => $activeCount,
            'total_monthly' => $totalMonthly,
        ]);
    }
    
    public function registerNetworker()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $packageId = (int)($_POST['package_id'] ?? 0);
            $sponsorId = !empty($_POST['sponsor_id']) ? (int)$_POST['sponsor_id'] : null;
            
            if ($name && $email && $packageId) {
                $mlmService = new MLMNetworkService();
                $result = $mlmService->registerNetworker(['name'=>$name,'email'=>$email,'phone'=>$phone], $packageId, $sponsorId);
                $_SESSION['flash_message'] = $result['message'] ?? ($result['error'] ?? 'Registration failed');
                $_SESSION['flash_type'] = $result['success'] ? 'success' : 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/networkers');
        exit;
    }
    
    public function registerConsultant()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $sponsorId = !empty($_POST['sponsor_id']) ? (int)$_POST['sponsor_id'] : null;
            if ($name && $email) {
                $mlmService = new MLMNetworkService();
                $result = $mlmService->registerFreeConsultant(['name'=>$name,'email'=>$email,'phone'=>$phone], $sponsorId);
                $_SESSION['flash_message'] = $result['message'] ?? ($result['error'] ?? 'Registration failed');
                $_SESSION['flash_type'] = $result['success'] ? 'success' : 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/free-consultants');
        exit;
    }
    
    public function processCommission()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $agentId = (int)($_POST['agent_id'] ?? 0);
            $bookingId = (int)($_POST['booking_id'] ?? 0);
            $saleAmount = (float)($_POST['sale_amount'] ?? 0);
            if ($agentId > 0 && $saleAmount > 0) {
                $reraService = new RERAComplianceService();
                $result = $reraService->processCommissionWithRERA($agentId, $bookingId, $saleAmount);
                $_SESSION['flash_message'] = $result['message'] ?? ($result['error'] ?? '');
                $_SESSION['flash_type'] = $result['success'] ? 'success' : 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/bookings');
        exit;
    }
    
    public function evaluateSalary()
    {
        $this->requireAdmin();
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId > 0) {
            $salaryService = new LeadershipSalaryService();
            $result = $salaryService->evaluateTargets($userId);
            $_SESSION['flash_message'] = 'Evaluated: ' . json_encode($result);
            $_SESSION['flash_type'] = 'info';
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/salary');
        exit;
    }
    
    public function runCron()
    {
        $this->requireAdmin();
        try {
            $bookingService = new BookingComplianceService();
            $tokenResult = $bookingService->enforceTokenRule();
            
            $salaryService = new LeadershipSalaryService();
            $salaryResult = $salaryService->processMonthlyPayouts();
            
            $_SESSION['flash_message'] = "Token compliance: {$tokenResult['released_plots']} released. Salary: {$salaryResult['processed']} processed.";
            $_SESSION['flash_type'] = 'success';
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = 'Cron error: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate');
        exit;
    }
    
    public function createBooking()
    {
        $this->requireAdmin();
        $db = Database::getInstance()->getConnection();
        try {
            $users = $db->query("SELECT id, name FROM users WHERE onboarding_track IN ('networker','free_consultant') AND status = 'active' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $plots = $db->query("SELECT id, plot_no, block_name, size_sqft, basic_price, status FROM inventory_plots WHERE status = 'Available' ORDER BY block_name, plot_no")->fetchAll(\PDO::FETCH_ASSOC);
            $plots2 = $db->query("SELECT id, plot_number as plot_no, block, area_sqft, total_price, status FROM plots WHERE status = 'available' AND colony_id = 2 ORDER BY block, plot_number")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $users = []; $plots = []; $plots2 = [];
        }
        $this->render('admin/mlm-realestate/create_booking', [
            'page_title' => 'Create Booking',
            'users' => $users,
            'plots' => $plots,
            'plots2' => $plots2,
        ]);
    }
    
    public function storeBooking()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $bookingService = new BookingComplianceService();
            $result = $bookingService->createBooking([
                'plot_id' => (int)$_POST['plot_id'],
                'customer_name' => $_POST['customer_name'] ?? '',
                'customer_id' => (int)($_POST['customer_id'] ?? 0),
                'property_id' => (int)$_POST['plot_id'],
                'agent_id' => (int)($_POST['agent_id'] ?? 0),
                'payment_mode' => $_POST['payment_mode'] ?? 'Full',
                'booking_date' => $_POST['booking_date'] ?? date('Y-m-d'),
                'initial_payment' => (float)($_POST['initial_payment'] ?? 0),
            ]);
            $_SESSION['flash_message'] = $result['message'] ?? ($result['error'] ?? 'Booking created');
            $_SESSION['flash_type'] = $result['success'] ? 'success' : 'danger';
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/bookings');
        exit;
    }

    public function approveBooking(int $id)
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $bookingStmt = $db->prepare("SELECT b.*, p.colony_id, p.total_price as plot_price FROM bookings b LEFT JOIN plots p ON b.plot_id = p.id WHERE b.id = ?");
            $bookingStmt->execute([$id]);
            $booking = $bookingStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking || $booking['status'] !== 'pending') {
                $_SESSION['flash_message'] = 'Booking not found or already processed';
                $_SESSION['flash_type'] = 'danger';
                header('Location: ' . BASE_URL . '/admin/mlm-realestate/bookings');
                exit;
            }

            $db->beginTransaction();

            $stmt = $db->prepare("UPDATE bookings SET status = 'confirmed', confirmed_at = NOW(), confirmed_by = ? WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id'] ?? 0, $id]);

            if (!empty($booking['plot_id'])) {
                $stmt = $db->prepare("UPDATE plots SET status = 'booked' WHERE id = ?");
                $stmt->execute([$booking['plot_id']]);
                $stmt = $db->prepare("UPDATE inventory_plots SET status = 'Booked' WHERE id = ?");
                $stmt->execute([$booking['plot_id']]);
            }

            $commissionResult = [];
            if (!empty($booking['associate_id'])) {
                $saleAmount = (float)($booking['negotiated_price'] ?: $booking['total_amount'] ?: 0);
                if ($saleAmount > 0) {
                    $reraService = new RERAComplianceService();
                    $commissionResult = $reraService->processCommissionWithRERA((int)$booking['associate_id'], $id, $saleAmount);
                    $accountingService = new \App\Services\Accounting\AccountingIntegrationService();
                    $accountingService->recordBookingPayment($booking['plot_id'] ?? $id, $saleAmount * 0.25, 'commission_approval', [
                        'booking_id' => $id, 'commission_processed' => true, 'commission_result' => $commissionResult,
                    ]);
                }
            }

            $db->commit();

            $notificationService = new BookingNotificationService();
            $notificationService->ensureNotificationsTable();
            $notificationService->notifyBookingApproved($id, $booking);

            $msg = "Booking #{$id} approved! Plot booked.";
            if (!empty($commissionResult['success'])) {
                $msg .= " Commission processed.";
                $notificationService->notifyCommissionProcessed($id, (int)$booking['associate_id'], $saleAmount);
            }
            $_SESSION['flash_message'] = $msg;
            $_SESSION['flash_type'] = 'success';
        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $_SESSION['flash_message'] = 'Approval failed: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/bookings');
        exit;
    }

    public function processMonthlyPayouts()
    {
        $this->requireAdmin();
        try {
            $salaryService = new LeadershipSalaryService();
            $result = $salaryService->processMonthlyPayouts();
            $_SESSION['flash_message'] = 'Monthly payouts processed: ' . ($result['processed'] ?? 0) . ' entries.';
            $_SESSION['flash_type'] = 'success';
        } catch (\Exception $e) {
            error_log('[MLMRealEstateController::processMonthlyPayouts] ' . $e->getMessage());
            $_SESSION['flash_message'] = 'Error processing payouts: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/salary');
        exit;
    }

    public function salaryDiagnostics()
    {
        $this->requireAdmin();
        try {
            $salaryService = new LeadershipSalaryService();
            $db = Database::getInstance()->getConnection();
            $activeTrackers = $db->query("SELECT COUNT(*) as c FROM salary_tracker WHERE status = 'active'")->fetch()['c'] ?? 0;
            $totalPaid = $db->query("SELECT COALESCE(SUM(amount_paid),0) as c FROM salary_tracker WHERE status = 'paid'")->fetch()['c'] ?? 0;
            $result = [
                'active_trackers' => $activeTrackers,
                'total_paid' => $totalPaid,
                'service_status' => 'ok',
            ];
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (\Exception $e) {
            error_log('[MLMRealEstateController::salaryDiagnostics] ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function reraStatus()
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $pending = $db->query("SELECT COUNT(*) as c FROM rera_requests WHERE status = 'pending'")->fetch()['c'] ?? 0;
            $approved = $db->query("SELECT COUNT(*) as c FROM rera_requests WHERE status = 'approved'")->fetch()['c'] ?? 0;
            $rejected = $db->query("SELECT COUNT(*) as c FROM rera_requests WHERE status = 'rejected'")->fetch()['c'] ?? 0;
            header('Content-Type: application/json');
            echo json_encode([
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
                'total' => $pending + $approved + $rejected,
            ]);
        } catch (\Exception $e) {
            error_log('[MLMRealEstateController::reraStatus] ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function reraDiagnostics()
    {
        $this->requireAdmin();
        try {
            $reraService = new RERAComplianceService();
            $db = Database::getInstance()->getConnection();
            $recentRequests = $db->query("SELECT id, status, rera_number, application_date FROM rera_requests ORDER BY created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
            header('Content-Type: application/json');
            echo json_encode([
                'service' => 'RERAComplianceService',
                'status' => 'ok',
                'recent_requests' => $recentRequests,
            ]);
        } catch (\Exception $e) {
            error_log('[MLMRealEstateController::reraDiagnostics] ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function rejectBooking(int $id)
    {
        $this->requireAdmin();
        try {
            $db = Database::getInstance()->getConnection();
            $bookingStmt = $db->prepare("SELECT b.*, p.colony_id FROM bookings b LEFT JOIN plots p ON b.plot_id = p.id WHERE b.id = ?");
            $bookingStmt->execute([$id]);
            $booking = $bookingStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$booking || $booking['status'] !== 'pending') {
                $_SESSION['flash_message'] = 'Booking not found or already processed';
                $_SESSION['flash_type'] = 'danger';
                header('Location: ' . BASE_URL . '/admin/mlm-realestate/bookings');
                exit;
            }

            $db->beginTransaction();
            $reason = $_POST['reason'] ?? 'Rejected by admin';
            $stmt = $db->prepare("UPDATE bookings SET status = 'cancelled', cancelled_at = NOW(), cancelled_by = ?, cancellation_reason = ? WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id'] ?? 0, $reason, $id]);

            if (!empty($booking['plot_id'])) {
                $stmt = $db->prepare("UPDATE plots SET status = 'available' WHERE id = ?");
                $stmt->execute([$booking['plot_id']]);
                $stmt = $db->prepare("UPDATE inventory_plots SET status = 'Available' WHERE id = ?");
                $stmt->execute([$booking['plot_id']]);
            }

            $db->commit();

            $notificationService = new BookingNotificationService();
            $notificationService->ensureNotificationsTable();
            $notificationService->notifyBookingRejected($id, $booking, $reason);

            $_SESSION['flash_message'] = "Booking #{$id} rejected. Plot returned to available.";
            $_SESSION['flash_type'] = 'warning';
        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $_SESSION['flash_message'] = 'Rejection failed: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ' . BASE_URL . '/admin/mlm-realestate/bookings');
        exit;
    }
}
