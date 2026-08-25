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
}