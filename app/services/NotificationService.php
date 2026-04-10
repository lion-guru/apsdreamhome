<?php

namespace App\Services;

use PDO;
use Exception;

class NotificationService
{
    private $db;
    private $settings;

    public function __construct()
    {
        $this->db = new PDO("mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->loadSettings();
    }

    private function loadSettings()
    {
        $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM notification_settings");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->settings[$row["setting_key"]] = $row["setting_value"];
        }
    }

    public function sendEmail($to, $subject, $templateName = null, $data = [], $attachments = [])
    {
        try {
            // Get template if specified
            if ($templateName) {
                $template = $this->getTemplate($templateName, "email");
                $subject = $this->replaceVariables($template["subject"], $data);
                $htmlContent = $this->replaceVariables($template["html_content"], $data);
                $textContent = $this->replaceVariables($template["text_content"], $data);
            } else {
                $htmlContent = $data["html_content"] ?? "";
                $textContent = $data["text_content"] ?? "";
            }

            // Create email log
            $stmt = $this->db->prepare("INSERT INTO email_logs (
                template_id, email_type, to_email, to_name, subject, 
                html_content, text_content, status, provider, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $templateId = $templateName ? $this->getTemplateId($templateName, "email") : null;
            $stmt->execute([
                $templateId,
                $templateName ?? "custom",
                $to["email"] ?? "",
                $to["name"] ?? "",
                $subject,
                $htmlContent,
                $textContent,
                "pending",
                "smtp"
            ]);

            $emailId = $this->db->lastInsertId();

            // Send email using PHPMailer
            $result = $this->sendEmailWithPHPMailer($to, $subject, $htmlContent, $textContent, $attachments);

            // Update email log
            $status = $result["success"] ? "sent" : "failed";
            $errorMessage = $result["success"] ? null : $result["error"];

            $updateStmt = $this->db->prepare("UPDATE email_logs SET 
                status = ?, sent_at = ?, provider_response = ?, updated_at = NOW()
                WHERE id = ?");

            $updateStmt->execute([
                $status,
                date("Y-m-d H:i:s"),
                json_encode(["error" => $errorMessage, "provider_message_id" => $result["message_id"] ?? null]),
                $emailId
            ]);

            return [
                "success" => $result["success"],
                "email_id" => $emailId,
                "message" => $result["message"] ?? ($result["success"] ? "Email sent successfully" : "Email sending failed")
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function sendSMS($to, $message, $templateName = null, $data = [])
    {
        try {
            // Get template if specified
            if ($templateName) {
                $template = $this->getTemplate($templateName, "sms");
                $message = $this->replaceVariables($template["message"], $data);
            }

            // Create SMS log
            $stmt = $this->db->prepare("INSERT INTO sms_logs (
                template_id, sms_type, to_phone, to_name, message, 
                status, provider, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

            $templateId = $templateName ? $this->getTemplateId($templateName, "sms") : null;
            $stmt->execute([
                $templateId,
                $templateName ?? "custom",
                $to["phone"] ?? "",
                $to["name"] ?? "",
                $message,
                "pending",
                "twilio"
            ]);

            $smsId = $this->db->lastInsertId();

            // Send SMS using Twilio
            $result = $this->sendSMSWithTwilio($to, $message);

            // Update SMS log
            $status = $result["success"] ? "sent" : "failed";
            $errorMessage = $result["success"] ? null : $result["error"];

            $updateStmt = $this->db->prepare("UPDATE sms_logs SET 
                status = ?, sent_at = ?, provider_response = ?, updated_at = NOW()
                WHERE id = ?");

            $updateStmt->execute([
                $status,
                date("Y-m-d H:i:s"),
                json_encode(["error" => $errorMessage, "provider_message_id" => $result["message_id"] ?? null]),
                $smsId
            ]);

            return [
                "success" => $result["success"],
                "sms_id" => $smsId,
                "message" => $result["message"] ?? ($result["success"] ? "SMS sent successfully" : "SMS sending failed")
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function getTemplate($templateName, $type)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . ($type == "email" ? "email" : "sms") . "_templates 
                                     WHERE template_name = ? AND is_active = 1");
        $stmt->execute([$templateName]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTemplateId($templateName, $type)
    {
        $stmt = $this->db->prepare("SELECT id FROM " . ($type == "email" ? "email" : "sms") . "_templates 
                                     WHERE template_name = ? AND is_active = 1");
        $stmt->execute([$templateName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result["id"] : null;
    }

    public function getTemplates($type)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . ($type == "email" ? "email" : "sms") . "_templates 
                                     WHERE is_active = 1 ORDER BY template_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmailLogs($limit = 50, $offset = 0)
    {
        $stmt = $this->db->prepare("SELECT el.*, et.template_name 
                                     FROM email_logs el 
                                     LEFT JOIN email_templates et ON el.template_id = et.id 
                                     ORDER BY el.created_at DESC 
                                     LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSMSLogs($limit = 50, $offset = 0)
    {
        $stmt = $this->db->prepare("SELECT sl.*, st.template_name 
                                     FROM sms_logs sl 
                                     LEFT JOIN sms_templates st ON sl.template_id = st.id 
                                     ORDER BY sl.created_at DESC 
                                     LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function updateSetting($key, $value)
    {
        $stmt = $this->db->prepare("INSERT INTO notification_settings (setting_key, setting_value, setting_type, setting_category) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        return $stmt->execute([$key, $value, "string", "general"]);
    }

    private function replaceVariables($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        return $content;
    }

    private function sendEmailWithPHPMailer($to, $subject, $htmlContent, $textContent, $attachments = [])
    {
        // This would integrate with PHPMailer library
        // For now, simulate email sending
        return [
            "success" => true,
            "message_id" => "msg_" . time() . "_" . mt_rand(1000, 9999),
            "message" => "Email sent successfully"
        ];
    }

    private function sendSMSWithTwilio($to, $message)
    {
        // This would integrate with Twilio library
        // For now, simulate SMS sending
        return [
            "success" => true,
            "message_id" => "sms_" . time() . "_" . mt_rand(1000, 9999),
            "message" => "SMS sent successfully"
        ];
    }

    public function getNotificationStats()
    {
        $emailStats = $this->db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM email_logs WHERE DATE(created_at) = CURDATE()")->fetch(PDO::FETCH_ASSOC);

        $smsStats = $this->db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM sms_logs WHERE DATE(created_at) = CURDATE()")->fetch(PDO::FETCH_ASSOC);

        return [
            "email" => $emailStats,
            "sms" => $smsStats
        ];
    }
}
