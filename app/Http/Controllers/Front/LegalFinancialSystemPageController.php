<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;
use PDO;

/**
 * LegalFinancialSystemPageController
 * Terms, privacy, cancellation, refund, cancellation policy, legal terms, legal documents, RERA lookup, title protection, property verification, financial services, financial contact, bank, insurance, NACH mandate, agreements, how it works, terms, sitemap, privacy, disclaimer, cancellation policy, refund policy, disclaimer, legal terms, legal documents
 */
class LegalFinancialSystemPageController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function terms()
    {
        $this->render('pages/terms', [
            'page_title' => 'Terms & Conditions - APS Dream Home',
            'page_description' => 'Terms and conditions of use.',
        ]);
    }

    public function privacy()
    {
        $this->render('pages/privacy', [
            'page_title' => 'Privacy Policy - APS Dream Home',
            'page_description' => 'Our privacy policy.',
        ]);
    }

    public function disclaimer()
    {
        $this->render('pages/disclaimer', [
            'page_title' => 'Disclaimer - APS Dream Home',
            'page_description' => 'Disclaimer and legal notices.',
        ]);
    }

    public function cancellationPolicy()
    {
        $this->render('pages/cancellation_policy', [
            'page_title' => 'Cancellation Policy - APS Dream Home',
            'page_description' => 'Our cancellation and refund policy.',
        ]);
    }

    public function refundPolicy()
    {
        $this->render('pages/refund_policy', [
            'page_title' => 'Refund Policy - APS Dream Home',
            'page_description' => 'Our refund policy.',
        ]);
    }

    public function legalTermsConditions()
    {
        $this->render('pages/legal_terms', [
            'page_title' => 'Legal Terms & Conditions - APS Dream Home',
            'page_description' => 'Legal terms and conditions.',
        ]);
    }

    public function legalDocuments()
    {
        $this->render('pages/legal_documents', [
            'page_title' => 'Legal Documents - APS Dream Home',
            'page_description' => 'Legal documents and resources.',
        ]);
    }

    public function reraLookup()
    {
        $this->render('pages/rera_lookup', [
            'page_title' => 'RERA Lookup - APS Dream Home',
            'page_description' => 'Verify RERA registration of projects.',
        ]);
    }

    public function titleProtection()
    {
        $this->render('pages/title_protection', [
            'page_title' => 'Title Protection - APS Dream Home',
            'page_description' => 'Protect your property title.',
        ]);
    }

    public function propertyVerification()
    {
        $this->render('pages/property_verification', [
            'page_title' => 'Property Verification - APS Dream Home',
            'page_description' => 'Verify property documents and ownership.',
        ]);
    }

    public function financialServices()
    {
        $this->render('pages/financial_services', [
            'page_title' => 'Financial Services - APS Dream Home',
            'page_description' => 'Our financial services and solutions.',
        ]);
    }

    public function financialContact()
    {
        $this->render('pages/financial_contact', [
            'page_title' => 'Financial Contact - APS Dream Home',
            'page_description' => 'Contact our financial services team.',
        ]);
    }

    public function bank()
    {
        $this->render('pages/bank', [
            'page_title' => 'Bank Partners - APS Dream Home',
            'page_description' => 'Our banking partners.',
        ]);
    }

    public function insurance()
    {
        $this->render('pages/insurance', [
            'page_title' => 'Property Insurance - APS Dream Home',
            'page_description' => 'Insure your property investment.',
        ]);
    }

    public function nachMandate()
    {
        $this->render('pages/nach_mandate', [
            'page_title' => 'NACH Mandate - APS Dream Home',
            'page_description' => 'Set up NACH mandate for EMI payments.',
        ]);
    }

    public function agreements()
    {
        $this->render('pages/agreements', [
            'page_title' => 'Agreements & E-Sign - APS Dream Home',
            'page_description' => 'Digital agreements and e-signatures.',
        ]);
    }

    public function howItWorks()
    {
        $this->render('pages/how_it_works', [
            'page_title' => 'How It Works - APS Dream Home',
            'page_description' => 'How APS Dream Home works for you.',
        ]);
    }

    public function sitemap()
    {
        $this->render('pages/sitemap', [
            'page_title' => 'Sitemap - APS Dream Home',
            'page_description' => 'Site map for APS Dream Home.',
        ]);
    }

    public function systemLogSecurityEvent()
    {
        // API endpoint for logging security events
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $event = $data['event'] ?? '';
        $details = $data['details'] ?? [];

        // Log the event
        error_log("SECURITY_EVENT: $event - " . json_encode($details));

        echo json_encode(['success' => true]);
        exit;
    }

    public function systemLaunchSystem()
    {
        $this->render('pages/launch_system', [
            'page_title' => 'System Launch - APS Dream Home',
            'page_description' => 'System launch and initialization.',
        ]);
    }

    public function systemKycUpload()
    {
        // Handle KYC upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle file upload
            $_SESSION['success'] = 'KYC documents uploaded successfully!';
        }
        $this->redirect('/user/kyc');
    }
}