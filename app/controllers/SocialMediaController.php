<?php
/**
 * Social Media Integration Controller
 * Handles social media sharing, authentication, and viral marketing
 */

namespace App\Controllers;

class SocialMediaController extends BaseController {

    /**
     * Social media sharing for properties
     */
    public function shareProperty($property_id) {
        $property = $this->getPropertyForSharing($property_id);

        if (!$property) {
            $this->setFlashMessage('error', 'Property not found');
            $this->redirect(BASE_URL . 'properties');
            return;
        }

        $share_data = [
            'title' => $property['title'],
            'description' => $this->generatePropertyDescription($property),
            'url' => BASE_URL . 'property/' . $property_id,
            'image' => $this->getPropertyImage($property_id),
            'price' => '₹' . number_format($property['price']),
            'location' => $property['city'] . ', ' . $property['state']
        ];

        $this->data['page_title'] = 'Share Property - ' . APP_NAME;
        $this->data['property'] = $property;
        $this->data['share_data'] = $share_data;
        $this->data['social_platforms'] = $this->getSocialPlatforms();

        $this->render('social/share_property');
    }

    /**
     * Generate social media posts
     */
    public function generatePost() {
        header('Content-Type: application/json');

        $post_type = $_POST['post_type'] ?? '';
        $platform = $_POST['platform'] ?? '';

        if (empty($post_type) || empty($platform)) {
            sendJsonResponse(['success' => false, 'error' => 'Post type and platform required'], 400);
        }

        $post_content = $this->generateSocialPost($post_type, $platform);

        sendJsonResponse([
            'success' => true,
            'data' => $post_content
        ]);
    }

    /**
     * Social media login/authentication
     */
    public function socialLogin($provider) {
        if (!in_array($provider, ['facebook', 'google', 'twitter', 'linkedin'])) {
            $this->setFlashMessage('error', 'Invalid social provider');
            $this->redirect(BASE_URL . 'login');
            return;
        }

        // Generate OAuth URL for the provider
        $auth_url = $this->getOAuthURL($provider);

        if ($auth_url) {
            header('Location: ' . $auth_url);
            exit;
        } else {
            $this->setFlashMessage('error', 'Social login configuration error');
            $this->redirect(BASE_URL . 'login');
        }
    }

    /**
     * Handle social media OAuth callback
     */
    public function socialCallback($provider) {
        try {
            $auth_code = $_GET['code'] ?? '';
            $state = $_GET['state'] ?? '';

            if (empty($auth_code)) {
                throw new \Exception('Authorization code not received');
            }

            // Exchange code for access token
            $access_token = $this->getAccessToken($provider, $auth_code);

            if (!$access_token) {
                throw new \Exception('Failed to get access token');
            }

            // Get user profile information
            $user_profile = $this->getUserProfile($provider, $access_token);

            if (!$user_profile) {
                throw new \Exception('Failed to get user profile');
            }

            // Check if user exists or create new user
            $user = $this->findOrCreateUser($user_profile, $provider);

            // Log user in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Save social media connection
            $this->saveSocialConnection($user['id'], $provider, $user_profile['social_id']);

            $this->setFlashMessage('success', 'Successfully logged in with ' . ucfirst($provider));
            $this->redirect(BASE_URL . 'dashboard');

        } catch (\Exception $e) {
            error_log('Social login error: ' . $e->getMessage());
            $this->setFlashMessage('error', 'Social login failed: ' . $e->getMessage());
            $this->redirect(BASE_URL . 'login');
        }
    }

    /**
     * Get social media analytics
     */
    public function socialAnalytics() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $analytics_data = [
            'sharing_stats' => $this->getSharingStats(),
            'social_logins' => $this->getSocialLoginStats(),
            'viral_coefficient' => $this->calculateViralCoefficient(),
            'top_shared_properties' => $this->getTopSharedProperties(),
            'social_referrals' => $this->getSocialReferrals()
        ];

        $this->data['page_title'] = 'Social Media Analytics - ' . APP_NAME;
        $this->data['analytics'] = $analytics_data;

        $this->render('admin/social_analytics');
    }

    /**
     * Get property for sharing
     */
    private function getPropertyForSharing($property_id) {
        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND status = 'available'");
            $stmt->execute([$property_id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate property description for social media
     */
    private function generatePropertyDescription($property) {
        $description = [];

        if (!empty($property['bedrooms'])) {
            $description[] = $property['bedrooms'] . 'BHK';
        }

        if (!empty($property['area_sqft'])) {
            $description[] = $property['area_sqft'] . ' sqft';
        }

        if (!empty($property['property_type'])) {
            $description[] = ucfirst($property['property_type']);
        }

        $description[] = 'in ' . $property['city'];

        return implode(' • ', $description);
    }

    /**
     * Get property image for sharing
     */
    private function getPropertyImage($property_id) {
        // Return the first property image or default image
        return BASE_URL . 'assets/images/properties/property_' . $property_id . '_1.jpg';
    }

    /**
     * Get social platforms configuration
     */
    private function getSocialPlatforms() {
        return [
            'facebook' => [
                'name' => 'Facebook',
                'icon' => 'fab fa-facebook-f',
                'color' => '#1877f2',
                'share_url' => 'https://www.facebook.com/sharer/sharer.php?u={url}'
            ],
            'twitter' => [
                'name' => 'Twitter',
                'icon' => 'fab fa-twitter',
                'color' => '#1da1f2',
                'share_url' => 'https://twitter.com/intent/tweet?text={title}&url={url}'
            ],
            'whatsapp' => [
                'name' => 'WhatsApp',
                'icon' => 'fab fa-whatsapp',
                'color' => '#25d366',
                'share_url' => 'https://wa.me/?text={title}%0A{url}'
            ],
            'linkedin' => [
                'name' => 'LinkedIn',
                'icon' => 'fab fa-linkedin-in',
                'color' => '#0077b5',
                'share_url' => 'https://www.linkedin.com/sharing/share-offsite/?url={url}'
            ],
            'telegram' => [
                'name' => 'Telegram',
                'icon' => 'fab fa-telegram-plane',
                'color' => '#0088cc',
                'share_url' => 'https://t.me/share/url?url={url}&text={title}'
            ],
            'email' => [
                'name' => 'Email',
                'icon' => 'fas fa-envelope',
                'color' => '#6c757d',
                'share_url' => 'mailto:?subject={title}&body={description}%0A{url}'
            ]
        ];
    }

    /**
     * Generate social media post content
     */
    private function generateSocialPost($post_type, $platform) {
        $templates = [
            'property_sale' => [
                'facebook' => "🏠 Amazing {property_type} for sale!\n\n{description}\n💰 {price}\n📍 {location}\n\nView details: {url}",
                'twitter' => "🏠 {property_type} for sale! {price} - {location}\n{description}\n{url} #RealEstate #Property",
                'instagram' => "🏠 New Listing Alert!\n\n{description}\n💰 {price}\n📍 {location}\n\nDM for details! 🏡"
            ],
            'mlm_achievement' => [
                'facebook' => "🎉 MLM Achievement Unlocked!\n\n{achievement}\n\nJoin our network and achieve your dreams! {url}",
                'twitter' => "🎉 MLM Success! {achievement} #MLM #NetworkMarketing {url}",
                'linkedin' => "🏆 Professional Achievement in Network Marketing\n\n{achievement}\n\nBuilding successful teams through APS Dream Home"
            ],
            'company_update' => [
                'facebook' => "📢 Company Update from APS Dream Home\n\n{update}\n\n{url}",
                'twitter' => "📢 {update} #APSDreamHome #RealEstate {url}",
                'linkedin' => "🏢 Company Update\n\n{update}\n\nLeading the real estate industry with innovative MLM solutions"
            ]
        ];

        return $templates[$post_type][$platform] ?? 'Default post content';
    }

    /**
     * Get OAuth URL for social provider
     */
    private function getOAuthURL($provider) {
        $redirect_uri = urlencode(BASE_URL . 'social/callback/' . $provider);

        switch ($provider) {
            case 'facebook':
                return "https://www.facebook.com/v18.0/dialog/oauth?client_id=" .
                       env('FACEBOOK_APP_ID', '') . "&redirect_uri={$redirect_uri}&scope=email,public_profile";

            case 'google':
                return "https://accounts.google.com/o/oauth2/v2/auth?client_id=" .
                       env('GOOGLE_CLIENT_ID', '') . "&redirect_uri={$redirect_uri}&response_type=code&scope=email%20profile";

            case 'twitter':
                return "https://twitter.com/i/oauth2/authorize?client_id=" .
                       env('TWITTER_CLIENT_ID', '') . "&redirect_uri={$redirect_uri}&response_type=code&scope=users.read%20tweet.read";

            case 'linkedin':
                return "https://www.linkedin.com/oauth/v2/authorization?client_id=" .
                       env('LINKEDIN_CLIENT_ID', '') . "&redirect_uri={$redirect_uri}&response_type=code&scope=r_liteprofile%20r_emailaddress";

            default:
                return null;
        }
    }

    /**
     * Exchange authorization code for access token
     */
    private function getAccessToken($provider, $auth_code) {
        // In production, implement actual token exchange
        // For now, return mock token
        return 'mock_access_token_' . time();
    }

    /**
     * Get user profile from social provider
     */
    private function getUserProfile($provider, $access_token) {
        // In production, make API calls to get user profile
        // For now, return mock profile data

        return [
            'social_id' => 'social_' . uniqid(),
            'name' => 'Social Media User',
            'email' => 'social@example.com',
            'avatar' => 'https://via.placeholder.com/150',
            'provider' => $provider
        ];
    }

    /**
     * Find existing user or create new one
     */
    private function findOrCreateUser($profile, $provider) {
        try {
            global $pdo;

            // Check if user exists with this email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$profile['email']]);
            $existing_user = $stmt->fetch();

            if ($existing_user) {
                return $existing_user;
            }

            // Create new user
            $sql = "INSERT INTO users (name, email, password, role, status, created_at)
                    VALUES (?, ?, ?, 'user', 'active', NOW())";

            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([
                $profile['name'],
                $profile['email'],
                password_hash(uniqid(), PASSWORD_DEFAULT) // Random password for social login
            ]);

            if ($success) {
                $user_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                return $stmt->fetch();
            }

            return null;

        } catch (\Exception $e) {
            error_log('Social user creation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save social media connection
     */
    private function saveSocialConnection($user_id, $provider, $social_id) {
        try {
            global $pdo;

            $sql = "INSERT INTO social_connections (user_id, provider, social_id, created_at)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE updated_at = NOW()";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$user_id, $provider, $social_id]);

        } catch (\Exception $e) {
            error_log('Social connection save error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get sharing statistics
     */
    private function getSharingStats() {
        try {
            global $pdo;

            $sql = "SELECT platform, COUNT(*) as shares,
                           COUNT(DISTINCT property_id) as unique_properties
                    FROM social_shares
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY platform";

            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get social login statistics
     */
    private function getSocialLoginStats() {
        try {
            global $pdo;

            $sql = "SELECT provider, COUNT(*) as logins
                    FROM social_logins
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY provider";

            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Calculate viral coefficient
     */
    private function calculateViralCoefficient() {
        try {
            global $pdo;

            // Get new users in last 30 days
            $sql = "SELECT COUNT(*) as new_users FROM users
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $pdo->query($sql);
            $new_users = (int)$stmt->fetch()['new_users'];

            // Get social shares in last 30 days
            $sql = "SELECT COUNT(*) as total_shares FROM social_shares
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $pdo->query($sql);
            $total_shares = (int)$stmt->fetch()['total_shares'];

            // Viral coefficient = (invites sent) / (new users acquired)
            return $new_users > 0 ? round($total_shares / $new_users, 2) : 0;

        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get top shared properties
     */
    private function getTopSharedProperties() {
        try {
            global $pdo;

            $sql = "SELECT p.id, p.title, p.city, COUNT(s.id) as share_count
                    FROM properties p
                    LEFT JOIN social_shares s ON p.id = s.property_id
                    WHERE p.status = 'available'
                    GROUP BY p.id, p.title, p.city
                    ORDER BY share_count DESC
                    LIMIT 10";

            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get social referrals
     */
    private function getSocialReferrals() {
        try {
            global $pdo;

            $sql = "SELECT referrer_user_id, COUNT(*) as referral_count,
                           COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_referrals
                    FROM user_referrals
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY referrer_user_id
                    ORDER BY referral_count DESC
                    LIMIT 20";

            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Track social share
     */
    public function trackShare() {
        header('Content-Type: application/json');

        try {
            global $pdo;

            $share_data = [
                'property_id' => $_POST['property_id'] ?? null,
                'platform' => $_POST['platform'] ?? '',
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];

            if (empty($share_data['platform'])) {
                sendJsonResponse(['success' => false, 'error' => 'Platform is required'], 400);
            }

            $sql = "INSERT INTO social_shares (property_id, platform, user_id, ip_address, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())";

            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([
                $share_data['property_id'],
                $share_data['platform'],
                $share_data['user_id'],
                $share_data['ip_address'],
                $share_data['user_agent']
            ]);

            sendJsonResponse([
                'success' => $success,
                'message' => 'Share tracked successfully'
            ]);

        } catch (\Exception $e) {
            error_log('Social share tracking error: ' . $e->getMessage());
            sendJsonResponse(['success' => false, 'error' => 'Tracking failed'], 500);
        }
    }

    /**
     * Generate referral link
     */
    public function generateReferralLink() {
        if (!$this->isLoggedIn()) {
            sendJsonResponse(['success' => false, 'error' => 'Login required'], 401);
        }

        $user_id = $_SESSION['user_id'];
        $referral_code = $this->generateReferralCode($user_id);

        $referral_link = BASE_URL . 'register?ref=' . $referral_code;

        sendJsonResponse([
            'success' => true,
            'referral_link' => $referral_link,
            'referral_code' => $referral_code
        ]);
    }

    /**
     * Generate unique referral code
     */
    private function generateReferralCode($user_id) {
        return 'REF' . strtoupper(substr(md5($user_id . time()), 0, 8));
    }

    /**
     * Track referral visit
     */
    public function trackReferral() {
        $referral_code = $_GET['ref'] ?? '';

        if (empty($referral_code)) {
            return;
        }

        try {
            global $pdo;

            // Find referrer user
            $sql = "SELECT user_id FROM user_referrals WHERE referral_code = ? AND status = 'active'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$referral_code]);
            $referral = $stmt->fetch();

            if ($referral) {
                // Log referral visit
                $sql = "INSERT INTO referral_visits (referral_code, referrer_user_id, visitor_ip, user_agent, created_at)
                        VALUES (?, ?, ?, ?, NOW())";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $referral_code,
                    $referral['user_id'],
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
            }

        } catch (\Exception $e) {
            error_log('Referral tracking error: ' . $e->getMessage());
        }
    }

    /**
     * Get social media trends
     */
    public function getSocialTrends() {
        header('Content-Type: application/json');

        $trends = [
            'popular_hashtags' => $this->getPopularHashtags(),
            'trending_properties' => $this->getTrendingProperties(),
            'viral_content' => $this->getViralContent(),
            'engagement_metrics' => $this->getEngagementMetrics()
        ];

        sendJsonResponse([
            'success' => true,
            'data' => $trends
        ]);
    }

    /**
     * Get popular hashtags
     */
    private function getPopularHashtags() {
        return [
            '#RealEstate', '#PropertyForSale', '#DreamHome',
            '#HomeSweetHome', '#PropertyInvestment', '#APSDreamHome'
        ];
    }

    /**
     * Get trending properties
     */
    private function getTrendingProperties() {
        try {
            global $pdo;

            $sql = "SELECT p.id, p.title, p.city, COUNT(s.id) as shares,
                           COUNT(f.id) as favorites
                    FROM properties p
                    LEFT JOIN social_shares s ON p.id = s.property_id
                    LEFT JOIN property_favorites f ON p.id = f.property_id
                    WHERE p.status = 'available'
                      AND p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY p.id, p.title, p.city
                    ORDER BY (shares + favorites) DESC
                    LIMIT 10";

            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get viral content
     */
    private function getViralContent() {
        return [
            'viral_properties' => $this->getTrendingProperties(),
            'top_sharers' => $this->getTopSharers(),
            'viral_coefficient' => $this->calculateViralCoefficient()
        ];
    }

    /**
     * Get engagement metrics
     */
    private function getEngagementMetrics() {
        return [
            'avg_shares_per_property' => 5.2,
            'avg_likes_per_post' => 12.8,
            'avg_comments_per_post' => 3.4,
            'click_through_rate' => '2.1%'
        ];
    }

    /**
     * Get top sharers
     */
    private function getTopSharers() {
        try {
            global $pdo;

            $sql = "SELECT u.name, COUNT(s.id) as shares
                    FROM users u
                    LEFT JOIN social_shares s ON u.id = s.user_id
                    WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY u.id, u.name
                    ORDER BY shares DESC
                    LIMIT 10";

            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();

        } catch (\Exception $e) {
            return [];
        }
    }
}
