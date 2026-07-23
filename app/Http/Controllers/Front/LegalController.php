<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;

class LegalController extends PageController
{
    public function terms()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('terms');
        $data = [
            'page_title' => ($cmsTitle ?: 'Terms & Conditions') . ' - APS Dream Home',
            'page_description' => 'Terms and conditions of use',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/terms', $data);
    }

    public function privacy()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('privacy');
        $data = [
            'page_title' => ($cmsTitle ?: 'Privacy Policy') . ' - APS Dream Home',
            'page_description' => 'Our privacy policy',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/privacy', $data);
    }

    public function disclaimer()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('disclaimer');
        $data = [
            'page_title' => ($cmsTitle ?: 'Disclaimer') . ' - APS Dream Home',
            'page_description' => 'Disclaimer',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/disclaimer', $data);
    }

    public function cancellationPolicy()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('cancellation-policy');
        $data = [
            'page_title' => ($cmsTitle ?: 'Cancellation Policy') . ' - APS Dream Home',
            'page_description' => 'Cancellation policy',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/cancellation_policy', $data);
    }

    public function refundPolicy()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('refund-policy');
        $data = [
            'page_title' => ($cmsTitle ?: 'Refund Policy') . ' - APS Dream Home',
            'page_description' => 'Refund policy',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/refund_policy', $data);
    }

    public function insurance()
    {
        $data = [
            'page_title' => 'Property Insurance - APS Dream Home',
            'page_description' => 'Property insurance options',
        ];
        $this->render('pages/insurance', $data);
    }

    public function nachMandate()
    {
        $data = [
            'page_title' => 'NACH Mandate - APS Dream Home',
            'page_description' => 'NACH/e-Mandate setup',
        ];
        $this->render('pages/nach_mandate', $data);
    }

    public function agreements()
    {
        $data = [
            'page_title' => 'Agreements & E-Sign - APS Dream Home',
            'page_description' => 'Legal agreements and e-signature',
        ];
        $this->render('pages/agreements', $data);
    }

    public function reraLookup()
    {
        $data = [
            'page_title' => 'RERA Lookup - APS Dream Home',
            'page_description' => 'Check RERA registration',
        ];
        $this->render('pages/rera_lookup', $data);
    }

    public function titleProtection()
    {
        $data = [
            'page_title' => 'Title Protection - APS Dream Home',
            'page_description' => 'Title protection services',
        ];
        $this->render('pages/title_protection', $data);
    }

    public function propertyVerification()
    {
        $data = [
            'page_title' => 'Property Verification - APS Dream Home',
            'page_description' => 'Property verification badge',
        ];
        $this->render('pages/property_verification', $data);
    }

    public function howItWorks()
    {
        $data = [
            'page_title' => 'How It Works - APS Dream Home',
            'page_description' => 'Step by step guide',
        ];
        $this->render('pages/how_it_works', $data);
    }

    public function services()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('services');
        $data = [
            'page_title' => ($cmsTitle ?: 'Our Services') . ' - APS Dream Home',
            'page_description' => 'Our property services',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/services', $data);
    }

    public function legalServices()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('legal-services');
        $data = [
            'page_title' => ($cmsTitle ?: 'Legal Services') . ' - APS Dream Home',
            'page_description' => 'Legal services for property',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/legal/services', $data);
    }

    public function documents()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('legal-documents');
        $data = [
            'page_title' => ($cmsTitle ?: 'Legal Documents') . ' - APS Dream Home',
            'page_description' => 'Legal documents and templates',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/legal/documents', $data);
    }

    public function index()
    {
        $legal_docs = [];
        try {
            $stmt = $this->db->query("SELECT * FROM legal_documents WHERE status = 'active' ORDER BY created_at DESC");
            $legal_docs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Legal index error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'Legal - APS Dream Home',
            'page_description' => 'Legal information and documents',
            'legal_docs' => $legal_docs,
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Legal', 'url' => ''],
            ],
        ];
        $this->render('pages/legal/legal', $data);
    }
}