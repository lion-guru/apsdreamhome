<?php

namespace App\Http\Controllers;

use App\Services\SocialLoginService;
use App\Services\OTPService;
use App\Services\ProgressiveRegistrationService;
use App\Services\AIChatbotService;
use App\Services\CampaignDeliveryService;
use Exception;

class AdvancedFeaturesController extends BaseController
{
    private $socialLoginService;
    private $otpService;
    private $progressiveRegistrationService;
    private $chatbotService;
    private $campaignDeliveryService;

    public function __construct()
    {
        parent::__construct();
        $this->socialLoginService = new SocialLoginService();
        $this->otpService = new OTPService();
        $this->progressiveRegistrationService = new ProgressiveRegistrationService();
        $this->chatbotService = new AIChatbotService();
        $this->campaignDeliveryService = new CampaignDeliveryService();
    }

    /**
     * Social Login - Get authorization URL
     */
    public function getSocialAuthUrl()
    {
        try {
            $provider = $_GET['provider'] ?? '';
            $state = $_GET['state'] ?? null;

            if (empty($provider)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Provider is required'
                ]);
            }

            $authUrl = $this->socialLoginService->getAuthUrl($provider, $state);

            return $this->jsonResponse([
                'success' => true,
                'auth_url' => $authUrl,
                'provider' => $provider
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get authorization URL: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Social Login - Handle callback
     */
    public function handleSocialCallback()
    {
        try {
            $provider = $_GET['provider'] ?? '';
            $code = $_GET['code'] ?? '';
            $state = $_GET['state'] ?? '';

            if (empty($provider) || empty($code)) {
                throw new Exception('Missing required parameters');
            }

            // Exchange code for token
            $tokenData = $this->socialLoginService->exchangeCodeForToken($provider, $code);

            // Get user info
            $userData = $this->socialLoginService->getUserInfo($provider, $tokenData['access_token']);

            // Authenticate user
            $user = $this->socialLoginService->authenticateSocialUser(
                $provider, 
                $userData, 
                $tokenData['access_token'], 
                $tokenData['refresh_token'] ?? null
            );

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Social login successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'redirect' => $this->getRedirectUrl($user['role'])
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Social login failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send OTP
     */
    public function sendOTP()
    {
        try {
            $identifier = $_POST['identifier'] ?? '';
            $type = $_POST['type'] ?? '';
            $purpose = $_POST['purpose'] ?? 'login';

            if (empty($identifier) || empty($type)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Identifier and type are required'
                ]);
            }

            // Check rate limiting
            if (!$this->otpService->canRequestNewOTP($identifier, $type)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Please wait before requesting a new OTP'
                ]);
            }

            $result = $this->otpService->sendOTP($identifier, $type, $purpose);

            return $this->jsonResponse($result);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to send OTP: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOTP()
    {
        try {
            $identifier = $_POST['identifier'] ?? '';
            $otpCode = $_POST['otp_code'] ?? '';
            $purpose = $_POST['purpose'] ?? 'login';

            if (empty($identifier) || empty($otpCode)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Identifier and OTP code are required'
                ]);
            }

            $result = $this->otpService->verifyOTP($identifier, $otpCode, $purpose);

            return $this->jsonResponse($result);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'OTP verification failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Start Progressive Registration
     */
    public function startProgressiveRegistration()
    {
        try {
            $sessionId = session_id() ?: session_create_id();

            $result = $this->progressiveRegistrationService->startRegistration($sessionId);

            return $this->jsonResponse($result);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to start registration: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get Current Registration Step
     */
    public function getCurrentRegistrationStep()
    {
        try {
            $sessionId = session_id();
            $stepData = $this->progressiveRegistrationService->getCurrentStep($sessionId);

            if (!$stepData) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'No active registration found'
                ]);
            }

            return $this->jsonResponse([
                'success' => true,
                'step_data' => $stepData
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get current step: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save Registration Step Data
     */
    public function saveRegistrationStepData()
    {
        try {
            $sessionId = session_id();
            $stepData = $_POST;

            $result = $this->progressiveRegistrationService->saveStepData($sessionId, $stepData);

            return $this->jsonResponse($result);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to save step data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Move to Next Registration Step
     */
    public function moveToNextRegistrationStep()
    {
        try {
            $sessionId = session_id();
            $result = $this->progressiveRegistrationService->moveToNextStep($sessionId);

            return $this->jsonResponse($result);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to move to next step: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Move to Previous Registration Step
     */
    public function moveToPreviousRegistrationStep()
    {
        try {
            $sessionId = session_id();
            $result = $this->progressiveRegistrationService->moveToPreviousStep($sessionId);

            return $this->jsonResponse($result);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to move to previous step: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Complete Progressive Registration
     */
    public function completeProgressiveRegistration()
    {
        try {
            $sessionId = session_id();
            $result = $this->progressiveRegistrationService->completeRegistration($sessionId);

            if ($result['success']) {
                // Set session for newly registered user
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['user_role'] = 'customer';
            }

            return $this->jsonResponse($result);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to complete registration: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Chatbot - Process Message
     */
    public function processChatbotMessage()
    {
        try {
            $sessionId = session_id() ?: session_create_id();
            $message = $_POST['message'] ?? '';
            $userId = $_SESSION['user_id'] ?? null;

            if (empty($message)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Message is required'
                ]);
            }

            $result = $this->chatbotService->processMessage($sessionId, $message, $userId);

            return $this->jsonResponse($result);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to process message: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Chatbot - Get Conversation History
     */
    public function getChatbotHistory()
    {
        try {
            $sessionId = session_id();
            $limit = $_GET['limit'] ?? 20;

            $history = $this->chatbotService->getConversationHistory($sessionId, $limit);

            return $this->jsonResponse([
                'success' => true,
                'history' => $history
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get conversation history: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Chatbot - Clear Conversation
     */
    public function clearChatbotConversation()
    {
        try {
            $sessionId = session_id();
            $this->chatbotService->clearConversationHistory($sessionId);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Conversation cleared successfully'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to clear conversation: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Campaign - Deliver Campaign
     */
    public function deliverCampaign()
    {
        try {
            $campaignId = $_POST['campaign_id'] ?? '';
            $deliveryTypes = $_POST['delivery_types'] ?? ['notification', 'popup', 'email'];

            if (empty($campaignId)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Campaign ID is required'
                ]);
            }

            $result = $this->campaignDeliveryService->deliverCampaign($campaignId, $deliveryTypes);

            return $this->jsonResponse($result);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to deliver campaign: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Campaign - Get Campaign Stats
     */
    public function getCampaignStats()
    {
        try {
            $campaignId = $_GET['campaign_id'] ?? '';

            if (empty($campaignId)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Campaign ID is required'
                ]);
            }

            $stats = $this->campaignDeliveryService->getCampaignStats($campaignId);

            return $this->jsonResponse([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get campaign stats: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Campaign - Track Engagement
     */
    public function trackCampaignEngagement()
    {
        try {
            $deliveryId = $_POST['delivery_id'] ?? '';
            $action = $_POST['action'] ?? '';

            if (empty($deliveryId) || empty($action)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Delivery ID and action are required'
                ]);
            }

            $result = $this->campaignDeliveryService->trackEngagement($deliveryId, $action);

            return $this->jsonResponse([
                'success' => $result,
                'message' => 'Engagement tracked successfully'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to track engagement: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl($role)
    {
        $redirects = [
            'admin' => '/admin/dashboard',
            'super_admin' => '/admin/dashboard',
            'employee' => '/employee/dashboard',
            'associate' => '/associate/dashboard',
            'customer' => '/customer/dashboard',
            'manager' => '/manager/dashboard'
        ];

        return $redirects[$role] ?? '/dashboard';
    }

    /**
     * Send JSON response
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}