<?php
namespace App\Services\User;

use App\Services\Database\DatabaseService;
use App\Services\Email\EmailService;
use App\Services\Cache\RedisCacheService;

class OnboardingManagerService
{
    private $db;
    private $emailService;
    private $cache;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
        $this->emailService = new EmailService();
        $this->cache = new RedisCacheService();
    }
    
    /**
     * Start user onboarding process
     */
    public function startOnboarding($userId)
    {
        $user = $this->getUser($userId);
        
        if (!$user) {
            throw new Exception("User not found");
        }
        
        // Create onboarding session
        $sessionId = $this->createOnboardingSession($userId);
        
        // Send welcome email
        $this->sendWelcomeEmail($user);
        
        // Create onboarding checklist
        $this->createOnboardingChecklist($userId, $sessionId);
        
        // Track onboarding progress
        $this->trackOnboardingProgress($userId, 'started');
        
        return $sessionId;
    }
    
    /**
     * Complete onboarding step
     */
    public function completeStep($userId, $stepId, $data = [])
    {
        $session = $this->getOnboardingSession($userId);
        
        if (!$session) {
            throw new Exception("Onboarding session not found");
        }
        
        // Mark step as completed
        $this->markStepCompleted($session['id'], $stepId, $data);
        
        // Update progress
        $progress = $this->calculateProgress($session['id']);
        
        // Check if onboarding is complete
        if ($progress >= 100) {
            $this->completeOnboarding($userId);
        } else {
            // Send next step notification
            $this->sendNextStepNotification($userId, $progress);
        }
        
        return $progress;
    }
    
    /**
     * Get onboarding progress
     */
    public function getProgress($userId)
    {
        $session = $this->getOnboardingSession($userId);
        
        if (!$session) {
            return 0;
        }
        
        return $this->calculateProgress($session['id']);
    }
    
    /**
     * Get next onboarding step
     */
    public function getNextStep($userId)
    {
        $session = $this->getOnboardingSession($userId);
        
        if (!$session) {
            return null;
        }
        
        $steps = $this->getOnboardingSteps();
        $completedSteps = $this->getCompletedSteps($session['id']);
        
        foreach ($steps as $step) {
            if (!in_array($step['id'], $completedSteps)) {
                return $step;
            }
        }
        
        return null;
    }
    
    /**
     * Create onboarding session
     */
    private function createOnboardingSession($userId)
    {
        $sql = "
            INSERT INTO user_onboarding (
                user_id, status, started_at, created_at
            ) VALUES (?, 'active', NOW(), NOW())
        ";
        
        $this->db->execute($sql, [$userId]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Create onboarding checklist
     */
    private function createOnboardingChecklist($userId, $sessionId)
    {
        $steps = $this->getOnboardingSteps();
        
        foreach ($steps as $step) {
            $sql = "
                INSERT INTO onboarding_checklist (
                    session_id, step_id, step_name, description, required, completed, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ";
            
            $this->db->execute($sql, [
                $sessionId,
                $step['id'],
                $step['name'],
                $step['description'],
                $step['required'] ? 1 : 0,
                0
            ]);
        }
    }
    
    /**
     * Get onboarding steps
     */
    private function getOnboardingSteps()
    {
        return [
            [
                'id' => 'profile_completion',
                'name' => 'Complete Profile',
                'description' => 'Fill in your profile information',
                'required' => true
            ],
            [
                'id' => 'property_search',
                'name' => 'Search Properties',
                'description' => 'Search for properties to see how it works',
                'required' => true
            ],
            [
                'id' => 'save_favorite',
                'name' => 'Save Favorite',
                'description' => 'Save a property to your favorites',
                'required' => true
            ],
            [
                'id' => 'contact_agent',
                'name' => 'Contact Agent',
                'description' => 'Send a message to a property agent',
                'required' => false
            ],
            [
                'id' => 'set_alerts',
                'name' => 'Set Property Alerts',
                'description' => 'Configure alerts for new properties',
                'required' => false
            ],
            [
                'id' => 'mobile_app',
                'name' => 'Try Mobile App',
                'description' => 'Download and try our mobile app',
                'required' => false
            ]
        ];
    }
    
    /**
     * Mark step as completed
     */
    private function markStepCompleted($sessionId, $stepId, $data)
    {
        $sql = "
            UPDATE onboarding_checklist 
            SET completed = 1, completed_at = NOW(), data = ?
            WHERE session_id = ? AND step_id = ?
        ";
        
        $this->db->execute($sql, [json_encode($data), $sessionId, $stepId]);
    }
    
    /**
     * Calculate onboarding progress
     */
    private function calculateProgress($sessionId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_steps,
                SUM(completed) as completed_steps
            FROM onboarding_checklist
            WHERE session_id = ?
        ";
        
        $result = $this->db->fetch($sql, [$sessionId]);
        
        if ($result['total_steps'] == 0) {
            return 0;
        }
        
        return round(($result['completed_steps'] / $result['total_steps']) * 100);
    }
    
    /**
     * Complete onboarding
     */
    private function completeOnboarding($userId)
    {
        $sql = "
            UPDATE user_onboarding 
            SET status = 'completed', completed_at = NOW()
            WHERE user_id = ?
        ";
        
        $this->db->execute($sql, [$userId]);
        
        // Send completion email
        $this->sendCompletionEmail($userId);
        
        // Track completion
        $this->trackOnboardingProgress($userId, 'completed');
    }
    
    /**
     * Send welcome email
     */
    private function sendWelcomeEmail($user)
    {
        $template = $this->getEmailTemplate('welcome');
        
        $this->emailService->sendEmail([
            'to' => $user['email'],
            'subject' => $template['subject'],
            'body' => $this->renderEmailTemplate($template['body'], $user)
        ]);
    }
    
    /**
     * Send completion email
     */
    private function sendCompletionEmail($userId)
    {
        $user = $this->getUser($userId);
        $template = $this->getEmailTemplate('completion');
        
        $this->emailService->sendEmail([
            'to' => $user['email'],
            'subject' => $template['subject'],
            'body' => $this->renderEmailTemplate($template['body'], $user)
        ]);
    }
    
    /**
     * Send next step notification
     */
    private function sendNextStepNotification($userId, $progress)
    {
        $nextStep = $this->getNextStep($userId);
        
        if ($nextStep) {
            $user = $this->getUser($userId);
            
            $this->emailService->sendEmail([
                'to' => $user['email'],
                'subject' => 'Continue Your APS Dream Home Journey',
                'body' => "You're {$progress}% through onboarding! Next step: {$nextStep['name']} - {$nextStep['description']}"
            ]);
        }
    }
    
    /**
     * Track onboarding progress
     */
    private function trackOnboardingProgress($userId, $event)
    {
        $sql = "
            INSERT INTO onboarding_analytics (
                user_id, event, created_at
            ) VALUES (?, ?, NOW())
        ";
        
        $this->db->execute($sql, [$userId, $event]);
    }
    
    /**
     * Get user information
     */
    private function getUser($userId)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetch($sql, [$userId]);
    }
    
    /**
     * Get onboarding session
     */
    private function getOnboardingSession($userId)
    {
        $sql = "SELECT * FROM user_onboarding WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
        return $this->db->fetch($sql, [$userId]);
    }
    
    /**
     * Get completed steps
     */
    private function getCompletedSteps($sessionId)
    {
        $sql = "SELECT step_id FROM onboarding_checklist WHERE session_id = ? AND completed = 1";
        $results = $this->db->fetchAll($sql, [$sessionId]);
        return array_column($results, 'step_id');
    }
    
    /**
     * Get email template
     */
    private function getEmailTemplate($type)
    {
        $templates = [
            'welcome' => [
                'subject' => 'Welcome to APS Dream Home!',
                'body' => 'Dear {name},<br><br>Welcome to APS Dream Home! We\'re excited to have you join our community. Let\'s get you started with your journey to finding your dream home.<br><br>Best regards,<br>The APS Dream Home Team'
            ],
            'completion' => [
                'subject' => 'Congratulations on Completing Onboarding!',
                'body' => 'Dear {name},<br><br>Congratulations! You\'ve successfully completed your onboarding journey. You\'re now ready to explore all the features APS Dream Home has to offer.<br><br>Happy house hunting!<br>The APS Dream Home Team'
            ]
        ];
        
        return $templates[$type] ?? [];
    }
    
    /**
     * Render email template
     */
    private function renderEmailTemplate($template, $user)
    {
        return str_replace('{name}', $user['name'], $template);
    }
}
