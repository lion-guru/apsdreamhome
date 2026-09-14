<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class ContactController extends PageController
{
    use TenantAwareTrait;
    public function contact()
    {
        return parent::contact();
    }

    public function serviceInterest()
    {
        return parent::serviceInterest();
    }

    public function handleQuickInquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $service = trim($_POST['service'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if ($name && $email && $phone && $service && $message) {
                try {
                    $pdo = Database::getInstance()->getConnection();
                    $stmt = $pdo->prepare("
                        INSERT INTO contact_submissions (name, email, phone, subject, message, status, tenant_id, created_at)
                        VALUES (?, ?, ?, ?, ?, 'new', 1, NOW())
                    ");
                    $stmt->execute([$name, $email, $phone, $service, $message]);
                    $_SESSION['success'] = 'Thank you for your message! We will get back to you soon.';
                } catch (\Exception $e) {
                    error_log("Quick inquiry form error: " . $e->getMessage());
                    $_SESSION['error'] = 'Something went wrong. Please try again.';
                }
            } else {
                $_SESSION['error'] = 'Please fill all required fields.';
            }
            $this->redirect(BASE_URL . '#contact');
        }
    }

    public function scheduleMeeting()
    {
        return parent::scheduleMeeting();
    }

    public function handleScheduleMeeting()
    {
        return parent::handleScheduleMeeting();
    }

    public function support()
    {
        return parent::support();
    }
}