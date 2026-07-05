<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

class UserPackageController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function boost($propertyId)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];

        $prop = $this->db->fetch(
            "SELECT * FROM user_properties WHERE id = ? AND email = ? AND status = 'approved'",
            [$propertyId, $user['email']]
        );
        if (!$prop) {
            $this->setFlash('error', 'Property not found or not eligible for boosting');
            $this->redirect('/user/properties');
            return;
        }

        $packages = $this->db->fetchAll(
            "SELECT * FROM premium_packages WHERE is_active = 1 ORDER BY priority_order ASC, price ASC"
        );

        $activePackage = $this->db->fetch(
            "SELECT up.*, pp.name as package_name, pp.slug as package_slug
             FROM user_packages up
             JOIN premium_packages pp ON up.package_id = pp.id
             WHERE up.property_id = ? AND up.user_id = ? AND up.status = 'active'
             LIMIT 1",
            [$propertyId, $userId]
        );

        $this->layout = 'layouts/customer';
        $this->render('pages/boost_property', [
            'page_title' => 'Boost Your Listing - APS Dream Home',
            'property' => $prop,
            'packages' => $packages,
            'activePackage' => $activePackage,
        ]);
    }

    public function purchase()
    {
        $this->requireCustomerLogin();
        $this->validateCsrfOrFail();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $propertyId = (int)($_POST['property_id'] ?? 0);
        $packageId = (int)($_POST['package_id'] ?? 0);

        if (!$propertyId || !$packageId) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('/user/properties');
            return;
        }

        $prop = $this->db->fetch(
            "SELECT * FROM user_properties WHERE id = ? AND email = ? AND status = 'approved'",
            [$propertyId, $user['email']]
        );
        if (!$prop) {
            $this->setFlash('error', 'Property not found');
            $this->redirect('/user/properties');
            return;
        }

        $pkg = $this->db->fetch(
            "SELECT * FROM premium_packages WHERE id = ? AND is_active = 1",
            [$packageId]
        );
        if (!$pkg) {
            $this->setFlash('error', 'Package not found');
            $this->redirect('/user/boost-property/' . $propertyId);
            return;
        }

        $existing = $this->db->fetch(
            "SELECT id FROM user_packages WHERE property_id = ? AND user_id = ? AND status = 'active'",
            [$propertyId, $userId]
        );
        if ($existing) {
            $this->setFlash('error', 'This property already has an active package');
            $this->redirect('/user/boost-property/' . $propertyId);
            return;
        }

        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . (int)$pkg['duration_days'] . ' days'));

        $this->db->execute(
            "INSERT INTO user_packages (user_id, property_id, package_id, amount_paid, status, expires_at, created_at, updated_at) VALUES (?, ?, ?, ?, 'active', ?, NOW(), NOW())",
            [$userId, $propertyId, $packageId, (float)$pkg['price'], $expiresAt]
        );

        $features = json_decode($pkg['features'] ?? '[]', true);
        $slug = $pkg['slug'] ?? '';
        $updates = [];
        if ($slug === 'featured' || in_array('Featured listing', $features)) {
            $updates[] = "is_featured = 1";
        }
        if ($slug === 'urgent' || in_array('Urgent tag', $features)) {
            $updates[] = "is_urgent = 1";
        }
        if ($slug === 'premium-plus' || $slug === 'premium') {
            $updates[] = "is_premium = 1, is_featured = 1";
        }
        if (!empty($updates)) {
            $this->db->execute(
                "UPDATE user_properties SET " . implode(', ', $updates) . ", expires_at = ? WHERE id = ?",
                [$expiresAt, $propertyId]
            );
        }

        $this->setFlash('success', 'Package activated! Your listing is now boosted.');
        $this->redirect('/user/properties');
    }
}
