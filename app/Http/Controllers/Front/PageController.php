<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;

/**
 * PageController (Facade)
 * Delegates to focused sub-controllers
 */
class PageController extends BaseController
{
    use TenantAwareTrait;

    // Sub-controller instances
    private \App\Http\Controllers\Front\HomePageController $homePageController;
    private \App\Http\Controllers\Front\PropertyPageController $propertyPageController;
    private \App\Http\Controllers\Front\ContentPageController $contentPageController;
    private \App\Http\Controllers\Front\ToolsPageController $toolsPageController;
    private \App\Http\Controllers\Front\ContactCareerPageController $contactCareerPageController;
    private \App\Http\Controllers\Front\LegalFinancialSystemPageController $legalFinancialSystemPageController;
    private \App\Http\Controllers\Front\MobileSystemPageController $mobileSystemPageController;

    public function __construct()
    {
        parent::__construct();

        // Initialize sub-controllers
        $this->homePageController             = new \App\Http\Controllers\Front\HomePageController();
        $this->propertyPageController         = new \App\Http\Controllers\Front\PropertyPageController();
        $this->contentPageController          = new \App\Http\Controllers\Front\ContentPageController();
        $this->toolsPageController            = new \App\Http\Controllers\Front\ToolsPageController();
        $this->contactCareerPageController    = new \App\Http\Controllers\Front\ContactCareerPageController();
        $this->legalFinancialSystemPageController = new \App\Http\Controllers\Front\LegalFinancialSystemPageController();
        $this->mobileSystemPageController     = new \App\Http\Controllers\Front\MobileSystemPageController();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /* ============================================================
       HOME & CORE PAGES (delegates to HomePageController)
       ============================================================ */

    public function home()
    {
        return $this->homePageController->home();
    }

    public function threeDTour()
    {
        return $this->homePageController->threeDTour();
    }

    public function about()
    {
        return $this->homePageController->about();
    }

    public function testimonials()
    {
        return $this->homePageController->testimonials();
    }

    public function team()
    {
        return $this->homePageController->team();
    }

    public function gallery()
    {
        return $this->homePageController->gallery();
    }

    public function thankYou()
    {
        return $this->homePageController->thankYou();
    }

    public function comingSoon()
    {
        return $this->homePageController->comingSoon();
    }

    /* ============================================================
       PROPERTY PAGES (delegates to PropertyPageController)
       ============================================================ */

    public function properties()
    {
        return $this->propertyPageController->properties();
    }

    public function plots()
    {
        return $this->propertyPageController->plots();
    }

    public function colonyPlots($slug)
    {
        return $this->propertyPageController->colonyPlots($slug);
    }

    public function propertyDetails($id = null)
    {
        return $this->propertyPageController->propertyDetails($id);
    }

    public function buyProperty()
    {
        return $this->propertyPageController->buyProperty();
    }

    public function sellProperty()
    {
        return $this->propertyPageController->sellProperty();
    }

    public function rentProperty()
    {
        return $this->propertyPageController->rentProperty();
    }

    public function investProperty()
    {
        return $this->propertyPageController->investProperty();
    }

    public function listProperty()
    {
        return $this->propertyPageController->listProperty();
    }

    public function handlePropertyListing()
    {
        return $this->propertyPageController->handlePropertyListing();
    }

    public function propertyInterest()
    {
        return $this->propertyPageController->propertyInterest();
    }

    public function propertyInquiry()
    {
        return $this->propertyPageController->propertyInquiry();
    }

    public function inquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $termsConsent = isset($_POST['terms_consent']);

            $errors = [];
            if (empty($name)) $errors[] = 'Name is required';
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
            if (empty($phone)) $errors[] = 'Phone is required';
            if (empty($subject)) $errors[] = 'Subject is required';
            if (empty($message)) $errors[] = 'Message is required';
            if (!isset($_POST['terms_consent'])) $errors[] = 'You must accept the terms and policies';

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old_input'] = $_POST;
                $this->redirect(BASE_URL . '/inquiry');
                return;
            }

            try {
                $db = \App\Core\Database::getInstance();
                $stmt = $db->prepare("
                    INSERT INTO inquiries (name, email, phone, subject, message, status, tenant_id, created_at)
                    VALUES (?, ?, ?, ?, ?, 'new', ?, NOW())
                ");
                $stmt->execute([
                    $name, $email, $phone, $subject, $message,
                    $this->tenantId() > 1 ? $this->tenantId() : null
                ]);

                $inquiryId = (int)$db->lastInsertId();

                // Dispatch notification for Admin & Sales team
                try {
                    $notifService = new \App\Services\NotificationService($db);
                    $notifService->notify(
                        'inquiry',
                        "New website inquiry from {$name} ({$phone}): " . (strlen($message) > 80 ? substr($message, 0, 80) . '...' : $message),
                        null,
                        "/admin/inquiries/view/{$inquiryId}",
                        "New Website Inquiry: {$name}"
                    );
                    \App\Core\Cache::delete('admin_notify_inquiries_pending');
                    \App\Core\Cache::delete('admin_notify_inquiries_today');
                } catch (\Throwable $ne) {
                    error_log('Inquiry notification error: ' . $ne->getMessage());
                }

                $_SESSION['success'] = 'Your inquiry has been submitted successfully! We will get back to you soon.';
            } catch (\Exception $e) {
                error_log('Inquiry submission error: ' . $e->getMessage());
                $_SESSION['errors'] = ['Something went wrong. Please try again.'];
            }

            $this->redirect(BASE_URL . '/inquiry');
            return;
        }

        $this->render('pages/inquiry', [
            'page_title' => 'Contact Us - APS Dream Home',
            'page_description' => 'Contact us for property inquiries and support',
            'current_page' => 'inquiry'
        ]);
    }

    public function getFeaturedProperties()
    {
        return $this->propertyPageController->getFeaturedProperties();
    }

    public function plotsAvailability()
    {
        return $this->propertyPageController->plotsAvailability();
    }

    public function plotMap()
    {
        return $this->propertyPageController->plotMap();
    }

    public function plotConverter()
    {
        return $this->propertyPageController->plotConverter();
    }

    public function plotSizeConverter()
    {
        return $this->propertyPageController->plotSizeConverter();
    }

    public function resell()
    {
        return $this->propertyPageController->resell();
    }

    public function plot()
    {
        return $this->propertyPageController->plot();
    }

    public function resellProperties()
    {
        return $this->propertyPageController->resellProperties();
    }

    public function featuredProperties()
    {
        return $this->propertyPageController->featuredProperties();
    }

    public function colonies()
    {
        return $this->propertyPageController->colonies();
    }

    public function projects()
    {
        return $this->propertyPageController->projects();
    }

    /* ============================================================
       CONTENT PAGES (delegates to ContentPageController)
       ============================================================ */

    public function blog()
    {
        return $this->contentPageController->blog();
    }

    public function blogPost($slug = null)
    {
        return $this->contentPageController->blogPost($slug);
    }

    public function news()
    {
        return $this->contentPageController->news();
    }

    public function reviews()
    {
        return $this->contentPageController->reviews();
    }

    public function documents()
    {
        return $this->contentPageController->documents();
    }

    public function downloadDocument($id)
    {
        return $this->contentPageController->downloadDocument($id);
    }

    public function constructionServices()
    {
        return $this->contentPageController->constructionServices();
    }

    public function interiorDesign()
    {
        return $this->contentPageController->interiorDesign();
    }

    public function galleryProject($projectId = null)
    {
        return $this->contentPageController->galleryProject($projectId);
    }

    public function documentGallery()
    {
        return $this->contentPageController->documentGallery();
    }

    public function downloads()
    {
        return $this->contentPageController->downloads();
    }

    public function faqs()
    {
        return $this->contentPageController->faqs();
    }

    public function faq()
    {
        return $this->contentPageController->faqs();
    }

    public function systemLogSecurityEvent()
    {
        return $this->contentPageController->systemLogSecurityEvent();
    }

    public function systemLaunchSystem()
    {
        return $this->contentPageController->systemLaunchSystem();
    }

    public function systemKycUpload()
    {
        return $this->contentPageController->systemKycUpload();
    }

    /* ============================================================
       TOOLS PAGES (delegates to ToolsPageController)
       ============================================================ */

    public function emiCalculator()
    {
        return $this->toolsPageController->emiCalculator();
    }

    public function stampDutyCalculator()
    {
        return $this->toolsPageController->stampDutyCalculator();
    }

    public function constructionCostEstimator()
    {
        return $this->toolsPageController->constructionCostEstimator();
    }

    public function rentalYieldCalculator()
    {
        return $this->toolsPageController->rentalYieldCalculator();
    }

    public function rentVsBuyCalculator()
    {
        return $this->toolsPageController->rentVsBuyCalculator();
    }

    public function propertyTaxCalculator()
    {
        return $this->toolsPageController->propertyTaxCalculator();
    }

    public function sipVsRealEstateCalculator()
    {
        return $this->toolsPageController->sipVsRealEstateCalculator();
    }

    public function gstCalculator()
    {
        return $this->toolsPageController->gstCalculator();
    }

    public function capitalGainsCalculator()
    {
        return $this->toolsPageController->capitalGainsCalculator();
    }

    public function propertyValuation()
    {
        return $this->toolsPageController->propertyValuation();
    }

    public function neighborhoodAnalysis()
    {
        return $this->toolsPageController->neighborhoodAnalysis();
    }

    public function virtualTour()
    {
        return $this->toolsPageController->virtualTour();
    }

    public function reraLookup()
    {
        return $this->toolsPageController->reraLookup();
    }

    public function titleProtection()
    {
        return $this->toolsPageController->titleProtection();
    }

    public function propertyVerification()
    {
        return $this->toolsPageController->propertyVerification();
    }

    public function insurance()
    {
        return $this->toolsPageController->insurance();
    }

    public function nachMandate()
    {
        return $this->toolsPageController->nachMandate();
    }

    public function agreements()
    {
        return $this->toolsPageController->agreements();
    }

    public function howItWorks()
    {
        return $this->toolsPageController->howItWorks();
    }

    /* ============================================================
       CONTACT & CAREER PAGES (delegates to ContactCareerPageController)
       ============================================================ */

    public function contact()
    {
        return $this->contactCareerPageController->contact();
    }

    public function serviceInterest()
    {
        return $this->contactCareerPageController->serviceInterest();
    }

    public function careers()
    {
        return $this->contactCareerPageController->careers();
    }

    public function careerApply()
    {
        return $this->contactCareerPageController->careerApply();
    }

    public function submitCareerApplication()
    {
        return $this->contactCareerPageController->submitCareerApplication();
    }

    public function careerJobs()
    {
        return $this->contactCareerPageController->careerJobs();
    }

    public function careerJobDetails($id = null)
    {
        return $this->contactCareerPageController->careerJobDetails($id);
    }

    /* ============================================================
       LEGAL, FINANCIAL, SYSTEM PAGES (delegates to LegalFinancialSystemPageController)
       ============================================================ */

    public function terms()
    {
        return $this->legalFinancialSystemPageController->terms();
    }

    public function privacy()
    {
        return $this->legalFinancialSystemPageController->privacy();
    }

    public function disclaimer()
    {
        return $this->legalFinancialSystemPageController->disclaimer();
    }

    public function cancellationPolicy()
    {
        return $this->legalFinancialSystemPageController->cancellationPolicy();
    }

    public function refundPolicy()
    {
        return $this->legalFinancialSystemPageController->refundPolicy();
    }

    public function legalTermsConditions()
    {
        return $this->legalFinancialSystemPageController->legalTermsConditions();
    }

    public function legalDocuments()
    {
        return $this->legalFinancialSystemPageController->legalDocuments();
    }

    public function financialServices()
    {
        return $this->legalFinancialSystemPageController->financialServices();
    }

    public function financialContact()
    {
        return $this->legalFinancialSystemPageController->financialContact();
    }

    public function bank()
    {
        return $this->legalFinancialSystemPageController->bank();
    }

    public function sitemap()
    {
        return $this->legalFinancialSystemPageController->sitemap();
    }

    /* ============================================================
       MOBILE & SYSTEM PAGES (delegates to MobileSystemPageController)
       ============================================================ */

    public function mobileApp()
    {
        return $this->mobileSystemPageController->mobileApp();
    }

    public function createMobileApp()
    {
        return $this->mobileSystemPageController->createMobileApp();
    }

    public function opportunity()
    {
        $viewPath = APP_PATH . '/views/pages/opportunity.php';
        if (file_exists($viewPath)) {
            $this->render('pages/opportunity', [
                'page_title' => 'Opportunity - APS Dream Home',
                'page_description' => 'Explore career and business opportunities with APS Dream Home.',
            ]);
        } else {
            $this->redirect(BASE_URL);
        }
    }
public function becomeAssociate()
    {
        $isLoggedIn = isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'associate';
        $loggedInReferralCode = $isLoggedIn ? ($_SESSION['referral_code'] ?? null) : null;
        if ($isLoggedIn && empty($loggedInReferralCode) && !empty($_SESSION['user_id'])) {
            try {
                $db = \App\Core\Database\Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT referral_code FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([(int)$_SESSION['user_id']]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $loggedInReferralCode = $row['referral_code'] ?? null;
            } catch (\Throwable $e) {
                error_log('PageController::becomeAssociate referral lookup failed: ' . $e->getMessage());
            }
        }
        $companyCode = getenv('COMPANY_REFERRAL_CODE') ?: 'APSREF';
        $referral_code = trim($_GET['ref'] ?? '') ?: ($loggedInReferralCode ?: $companyCode);

        $this->render('pages/become_associate', [
            'page_title' => __('assoc_page_title'),
            'page_description' => __('assoc_page_desc', [], 'Become an Associate - APS Dream Home'),
            'current_page' => 'become_associate',
            'isLoggedIn' => $isLoggedIn,
            'loggedInReferralCode' => $loggedInReferralCode,
            'referral_code' => $referral_code,
            'companyCode' => $companyCode,
        ]);
    }
    /* ============================================================
       MISCELLANEOUS PUBLIC PAGES
       ============================================================ */

    public function builderRegistration()
    {
        $viewPath = APP_PATH . '/views/pages/builder_registration.php';
        if (file_exists($viewPath)) {
            $this->render('pages/builder_registration', [
                'page_title' => 'Builder Registration - APS Dream Home',
                'page_description' => 'Register as a builder with APS Dream Home.',
            ]);
        } else {
            $this->redirect(BASE_URL);
        }
    }

    public function navigation()
    {
        $this->render('pages/navigation', [
            'page_title' => __('nav_page_title', 'Navigation - APS Dream Homes'),
            'page_description' => __('nav_page_desc', 'Navigate through all pages of APS Dream Homes website'),
            'current_page' => 'navigation'
        ]);
    }

    public function underConstruction()
    {
        $viewPath = APP_PATH . '/views/pages/under_construction.php';
        if (file_exists($viewPath)) {
            extract($_GET + ['base' => defined('BASE_URL') ? BASE_URL : '/apsdreamhome']);
            include $viewPath;
        } else {
            $this->redirect(BASE_URL);
        }
    }

    public function customerReviews()
    {
        $viewPath = APP_PATH . '/views/pages/customer_reviews.php';
        if (file_exists($viewPath)) {
            $this->render('pages/customer_reviews', [
                'page_title' => 'Customer Reviews - APS Dream Home',
                'page_description' => 'Read what our customers say about APS Dream Home.',
            ]);
        } else {
            $this->redirect(BASE_URL);
        }
    }

    public function analytics()
    {
        $this->render('pages/analytics', [
            'page_title' => __('analytics_title', [], 'Analytics - APS Dream Home'),
            'page_description' => __('analytics_desc', [], 'Analytics Dashboard - APS Dream Home'),
            'current_page' => 'analytics'
        ]);
    }

    public function pricing()
    {
        $viewPath = APP_PATH . '/views/pages/pricing.php';
        if (file_exists($viewPath)) {
            extract($_GET + ['base' => defined('BASE_URL') ? BASE_URL : '/apsdreamhome']);
            include $viewPath;
        } else {
            $this->redirect(BASE_URL);
        }
    }

    /* ============================================================
       LANGUAGE SWITCHER
       ============================================================ */

    public function setLanguage($lang = '')
    {
        $allowed = ['en', 'hi'];
        $lang = strtolower(trim($lang));

        if (!in_array($lang, $allowed, true)) {
            $lang = 'en';
        }

        $_SESSION['locale'] = $lang;

        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        if (!filter_var($referer, FILTER_VALIDATE_URL)) {
            $referer = BASE_URL;
        }

        $this->redirect($referer);
    }

    /* ============================================================
       LEGACY METHODS - Kept for backward compatibility
       ============================================================ */

    protected function loadPageContent(string $slug): array
    {
        $pageTitle = '';
        $pageContent = '';
        try {
            $stmt = $this->db->prepare("SELECT title, content FROM pages WHERE slug = ? AND status = 'published' LIMIT 1");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $pageTitle = $row['title'];
                $pageContent = $row['content'];
            }
        } catch (\Exception $e) {
            error_log('PageController loadPageContent: ' . $e->getMessage());
        }
        return [$pageTitle, $pageContent];
    }

    public function newsView($id) {
        try {
            $id = (int)$id;
            $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE id = ? AND status = 'published' LIMIT 1");
            $stmt->execute([$id]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$post) { header('Location: /apsdreamhome/news'); exit; }
            $this->render('pages/news_detail', ['post' => $post]);
        } catch (\Exception $e) {
            error_log('PageController newsView: ' . $e->getMessage());
            header('Location: /apsdreamhome/news'); exit;
        }
    }

    public function userPropertyDetail($id) {
        try {
            $id = (int)$id;
            $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$id]);
            $property = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$property) { header('Location: /apsdreamhome/properties'); exit; }
            $this->render('pages/property_detail', ['property' => $property]);
        } catch (\Exception $e) {
            error_log('PageController userPropertyDetail: ' . $e->getMessage());
            header('Location: /apsdreamhome/properties'); exit;
        }
    }
private function getPlans()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order ASC");
            $stmt->execute();
            $plans = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $plans ?: [];
        } catch (\Exception $e) {
            error_log('PageController::getPlans Error: ' . $e->getMessage());
            return [];
        }
    }

    public function saasHome()
    {
        $plans = $this->getPlans();
        
        $this->render('pages/saas_home', [
            'page_title' => 'APS CRM — All-in-One Business Platform',
            'page_description' => 'India\'s #1 Real Estate CRM Platform — CRM, ERP, Accounting, Legal, AI — everything you need to manage properties, teams, and customers in one powerful SaaS platform.',
            'plans' => $plans
        ]);
    }

    public function tenantSignup() {
        $viewPath = APP_PATH . '/views/pages/tenant_signup.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            header('Location: /apsdreamhome/pricing'); exit;
        }
    }

    public function aiChatbotPage()
    {
        return $this->toolsPageController->aiChatbotPage();
    }
}