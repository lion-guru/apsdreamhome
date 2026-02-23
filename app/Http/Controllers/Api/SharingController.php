<?php

namespace App\Http\Controllers\Api;

use \Exception;

class SharingController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['except' => ['trackClick']]);
        $this->middleware('csrf', ['only' => ['trackClick']]);
    }

    /**
     * Generate sharing data for a resource
     */
    public function generate()
    {
        try {
            $type = $this->request()->input('type');
            $id = $this->request()->input('id');

            if (empty($type) || empty($id)) {
                return $this->jsonError('Type and ID are required', 400);
            }

            $shareData = [];

            if ($type === 'property') {
                $propertyModel = $this->model('Property');
                $prop = $propertyModel->getPropertyById($id);

                if (!$prop) {
                    return $this->jsonError('Property not found', 404);
                }

                $shareData = [
                    'title' => "Check out this property: " . $prop['title'],
                    'text' => $prop['description'],
                    'url' => "https://apsdreamhome.com/property-detail.php?id=" . $id,
                    'deep_link' => "apsdreamhome://property/" . $id,
                    'image' => "https://apsdreamhome.com/api/v1/properties/$id/image"
                ];
            }

            return $this->jsonSuccess($shareData);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Track a click from a shared link
     */
    public function trackClick()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $source = $this->request()->input('source');
            $medium = $this->request()->input('medium');

            if (empty($source) || empty($medium)) {
                return $this->jsonError('Source and medium are required', 400);
            }

            $campaign = $this->request()->input('campaign', 'social_share');
            $landingPage = $this->request()->input('landing_page', '/');
            $ip = $this->request()->server->get('REMOTE_ADDR', 'unknown');
            $isMobile = $this->isMobileDevice() ? 1 : 0;
            $sessionId = $this->request()->session()->getId();

            $trafficStatModel = $this->model('TrafficStat');
            $tracked = $trafficStatModel->trackVisit([
                'source' => $source,
                'medium' => $medium,
                'campaign' => $campaign,
                'landing_page' => $landingPage,
                'ip_address' => $ip,
                'is_mobile' => $isMobile,
                'session_id' => $sessionId
            ]);

            if ($tracked) {
                return $this->jsonSuccess(null, 'Click tracked');
            }

            return $this->jsonError('Failed to track click', 500);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Simple mobile device detection
     */
    private function isMobileDevice()
    {
        $ua = $this->request()->headers->get('User-Agent', '');
        return \preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $ua);
    }
}
