<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * Lead Follow-up Service
 * Handles follow-up for incomplete registrations and visitor leads
 */
class LeadFollowUpService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Send follow-up for incomplete registrations - now uses leads table
     */
    public function sendFollowUpForIncompleteRegistrations()
    {
        try {
            // Get leads from incomplete registrations (source = 'incomplete_registration')
            $leads = $this->db->fetchAll("
                SELECT * FROM leads
                WHERE source = 'incomplete_registration'
                AND status = 'new'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND (email IS NOT NULL OR phone IS NOT NULL)
                ORDER BY created_at DESC
            ");

            foreach ($leads as $lead) {
                $this->sendLeadFollowUp($lead, 'incomplete_registration');
            }

            return ['success' => true, 'message' => 'Follow-ups sent'];
        } catch (\Exception $e) {
            error_log("Follow-up error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send follow-up for new leads - now uses leads table
     */
    public function sendFollowUpForNewLeads()
    {
        try {
            // Get new leads that haven't been contacted
            $leads = $this->db->fetchAll("
                SELECT * FROM leads
                WHERE status = 'new'
                AND source != 'incomplete_registration'
                AND (email IS NOT NULL OR phone IS NOT NULL)
                ORDER BY created_at DESC
            ");

            foreach ($leads as $lead) {
                $this->sendLeadFollowUp($lead, 'new_lead');
            }

            return ['success' => true, 'message' => 'Lead follow-ups sent'];
        } catch (\Exception $e) {
            error_log("Lead follow-up error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send follow-up for lead - now uses leads table
     */
    private function sendLeadFollowUp($lead, $type = 'new_lead')
    {
        $email = $lead['email'] ?? null;
        $phone = $lead['phone'] ?? null;
        $name = $lead['name'] ?? 'Visitor';

        if ($email) {
            $this->sendLeadEmailFollowUp($email, $name, $type);
        }

        if ($phone) {
            $this->sendLeadSMSFollowUp($phone, $name, $type);
        }

        // Update lead status
        $this->db->query(
            "UPDATE leads SET 
                status = 'contacted',
                updated_at = NOW()
            WHERE id = ?",
            [$lead['id']]
        );
    }

    /**
     * Send email follow-up for lead
     */
    private function sendLeadEmailFollowUp($email, $name, $type = 'new_lead')
    {
        if ($type === 'incomplete_registration') {
            $subject = "Complete Your Registration - APS Dream Home";
            $message = "Hi $name,\n\nYou started the registration process but didn't complete it. We're here to help if you have any questions.\n\nComplete your registration at: http://localhost/apsdreamhome/register";
        } else {
            $subject = "Thank You for Your Interest - APS Dream Home";
            $message = "Hi $name,\n\nThank you for your interest in APS Dream Home. Our team will contact you shortly.\n\nBest regards,\nAPS Dream Home Team";
        }

        error_log("Lead email sent to $email: $subject");

        // TODO: Integrate with actual email service
    }

    /**
     * Send SMS follow-up for lead
     */
    private function sendLeadSMSFollowUp($phone, $name, $type = 'new_lead')
    {
        if ($type === 'incomplete_registration') {
            $message = "Hi $name, complete your APS Dream Home registration. Call 9277121112 for help.";
        } else {
            $message = "Hi $name, thanks for interest in APS Dream Home. Our team will contact you soon.";
        }

        error_log("Lead SMS sent to $phone: $message");

        // TODO: Integrate with actual SMS gateway
    }

    /**
     * Get email template for incomplete registration
     */
    private function getIncompleteRegEmailTemplate($name, $reg)
    {
        $formData = json_decode($reg['form_data'], true) ?? [];
        $step = $reg['step_completed'] ?? 1;
        $totalSteps = $reg['total_steps'] ?? 1;

        return "
            <h2>Hello $name,</h2>
            <p>We noticed you started your registration with APS Dream Home but didn't complete it.</p>
            <p>You were at step $step of $totalSteps.</p>
            <p>Don't worry, your progress is saved!</p>
            <p><a href='" . BASE_URL . "/register'>Click here to complete your registration</a></p>
            <p>If you have any questions, feel free to call us at +91 92771 21112</p>
            <p>Best regards,<br>APS Dream Home Team</p>
        ";
    }

    /**
     * Get SMS template for incomplete registration
     */
    private function getIncompleteRegSMSTemplate($name, $reg)
    {
        return "Hi $name, Complete your APS Dream Home registration at " . BASE_URL . "/register - Your progress is saved! Call +91 92771 21112 for help.";
    }

    /**
     * Get email template for lead
     */
    private function getLeadEmailTemplate($name, $lead)
    {
        $interestType = $lead['interest_type'] ?? 'property';

        return "
            <h2>Hello $name,</h2>
            <p>Thank you for your interest in $interestType at APS Dream Home.</p>
            <p>Our team will contact you shortly to discuss your requirements.</p>
            <p>In the meantime, feel free to browse our properties at " . BASE_URL . "/properties</p>
            <p>If you have any questions, call us at +91 92771 21112</p>
            <p>Best regards,<br>APS Dream Home Team</p>
        ";
    }

    /**
     * Get SMS template for lead
     */
    private function getLeadSMSTemplate($name, $lead)
    {
        return "Hi $name, Thanks for interest in APS Dream Home! Our team will contact you soon. Browse properties at " . BASE_URL . "/properties - Call +91 92771 21112";
    }

    /**
     * Get follow-up statistics
     */
    public function getFollowUpStats()
    {
        try {
            $stats = $this->db->fetchOne("
                SELECT 
                    COUNT(*) as total_incomplete,
                    COUNT(CASE WHEN follow_up_sent = 1 THEN 1 END) as follow_ups_sent,
                    COUNT(CASE WHEN is_converted = 1 THEN 1 END) as converted
                FROM incomplete_registrations
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");

            $leadStats = $this->db->fetchOne("
                SELECT 
                    COUNT(*) as total_leads,
                    COUNT(CASE WHEN lead_status = 'contacted' THEN 1 END) as contacted,
                    COUNT(CASE WHEN lead_status = 'converted' THEN 1 END) as converted_leads
                FROM visitor_leads
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");

            return [
                'success' => true,
                'data' => [
                    'incomplete_registrations' => $stats,
                    'leads' => $leadStats
                ]
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
