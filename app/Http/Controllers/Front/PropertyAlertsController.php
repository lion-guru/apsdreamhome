<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\PropertyAlertService;
use App\Traits\TenantAwareTrait;

/**
 * Property Alerts - Customer-facing subscription page
 */
class PropertyAlertsController extends BaseController
{
    use TenantAwareTrait;
    private $alerts;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try {
            $this->alerts = new PropertyAlertService($this->db);
        } catch (\Throwable $e) {
            $this->alerts = null;
        }
    }

    public function index()
    {
        $userId = $_SESSION['user_id'] ?? null;
        $subscriptions = [];
        $matches = [];
        if ($this->alerts && $userId) {
            $subscriptions = $this->alerts->getByUser((int)$userId);
            if (!empty($subscriptions[0])) {
                $matches = $this->alerts->findMatches($subscriptions[0], 6);
            }
        }
        return $this->render('pages.property_alerts.subscribe', [
            'page_title' => 'Property Alerts - Get Notified on New Listings',
            'page_heading' => 'Property Alerts',
            'subscriptions' => $subscriptions,
            'matches' => $matches,
            'logged_in' => !empty($userId)
        ]);
    }

    public function store()
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $propertyType = trim($_POST['property_type'] ?? '');
        $listingType = trim($_POST['listing_type'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $minPrice = (float)($_POST['min_price'] ?? 0) ?: null;
        $maxPrice = (float)($_POST['max_price'] ?? 0) ?: null;
        $minArea = (int)($_POST['min_area_sqft'] ?? 0) ?: null;
        $maxArea = (int)($_POST['max_area_sqft'] ?? 0) ?: null;
        $bedrooms = (int)($_POST['bedrooms'] ?? 0) ?: null;
        $frequency = $_POST['frequency'] ?? 'daily';
        $notifyEmail = !empty($_POST['notify_email']) ? 1 : 0;
        $notifySms = !empty($_POST['notify_sms']) ? 1 : 0;
        $notifyWa = !empty($_POST['notify_whatsapp']) ? 1 : 0;
        $userId = $_SESSION['user_id'] ?? null;

        $errors = [];
        if (!$name) $errors[] = 'Name is required';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (!$propertyType) $errors[] = 'Property type is required';
        if (!$listingType) $errors[] = 'Listing type is required';
        if (!$notifyEmail && !$notifySms && !$notifyWa) $errors[] = 'Select at least one notification channel';

        if ($errors) {
            return $this->render('pages.property_alerts.subscribe', [
                'page_title' => 'Property Alerts',
                'errors' => $errors,
                'logged_in' => !empty($userId)
            ]);
        }
        try {
            $id = $this->alerts->subscribe([
                'user_id' => $userId,
                'email' => $email,
                'phone' => $phone,
                'name' => $name,
                'property_type' => $propertyType,
                'listing_type' => $listingType,
                'city' => $city,
                'state' => $state,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'min_area_sqft' => $minArea,
                'max_area_sqft' => $maxArea,
                'bedrooms' => $bedrooms,
                'notify_email' => $notifyEmail,
                'notify_sms' => $notifySms,
                'notify_whatsapp' => $notifyWa,
                'frequency' => $frequency
            ]);
            return $this->render('pages.property_alerts.success', [
                'page_title' => 'Subscription Confirmed',
                'subscription_id' => $id,
                'email' => $email
            ]);
        } catch (\Throwable $e) {
            return $this->render('pages.property_alerts.subscribe', [
                'page_title' => 'Property Alerts',
                'errors' => ['Subscription failed: ' . $e->getMessage()],
                'logged_in' => !empty($userId)
            ]);
        }
    }

    public function unsubscribe()
    {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        $success = false;
        if ($token && $this->alerts) {
            $success = $this->alerts->unsubscribe($token);
        }
        return $this->render('pages.property_alerts.unsubscribe', [
            'page_title' => 'Unsubscribe',
            'success' => $success
        ]);
    }
}
