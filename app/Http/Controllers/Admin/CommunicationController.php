<?php
namespace App\Http\Controllers\Admin;

use App\Services\Communication\EmailSenderService;
use App\Services\Communication\SmsSenderService;
use App\Services\Communication\QueueProcessorService;

class CommunicationController extends AdminController
{
    private $emailSender;
    private $smsSender;
    private $queueProcessor;

    public function __construct()
    {
        parent::__construct();
        $this->emailSender = new EmailSenderService();
        $this->smsSender = new SmsSenderService();
        $this->queueProcessor = new QueueProcessorService();
    }

    public function queue()
    {
        $this->requireAdmin();

        $stats = $this->queueProcessor->getCombinedStats();
        $emailFilter = $_GET['email_status'] ?? null;
        $smsFilter = $_GET['sms_status'] ?? null;

        $emails = $this->emailSender->getQueueItems($emailFilter ?: null, 50, 0);
        $smsItems = $this->smsSender->getQueueItems($smsFilter ?: null, 50, 0);

        return $this->render('admin/communication/queue', [
            'page_title' => 'Communication Queue - APS Dream Home',
            'stats' => $stats,
            'emails' => $emails,
            'sms_items' => $smsItems,
            'email_filter' => $emailFilter,
            'sms_filter' => $smsFilter,
        ]);
    }

    public function processQueue()
    {
        $this->requireAdmin();

        $emailLimit = (int)($_POST['email_limit'] ?? 10);
        $smsLimit = (int)($_POST['sms_limit'] ?? 10);
        $result = $this->queueProcessor->processAll($emailLimit, $smsLimit);

        $_SESSION['flash_message'] = 'Queue processed: ' . $result['email']['sent'] . ' emails sent, ' . $result['sms']['sent'] . ' SMS sent.';
        $_SESSION['flash_type'] = 'success';
        $this->redirect('/admin/communication/queue');
    }

    public function testEmail()
    {
        $this->requireAdmin();
        return $this->render('admin/communication/test_email', [
            'page_title' => 'Test Email - APS Dream Home',
        ]);
    }

    public function sendTestEmail()
    {
        $this->requireAdmin();

        $to = $_POST['to_email'] ?? '';
        $subject = $_POST['subject'] ?? 'Test Email from APS Dream Home';
        $message = $_POST['message'] ?? 'This is a test email sent from the admin panel.';

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_message'] = 'Invalid email address.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('/admin/communication/test-email');
        }

        $bodyHtml = '<h2>Test Email</h2><p>' . nl2br(htmlspecialchars($message)) . '</p><hr><p><small>Sent from APS Dream Home Admin</small></p>';
        $result = $this->emailSender->send($to, $subject, $bodyHtml, $message);

        if ($result) {
            $_SESSION['flash_message'] = 'Test email sent successfully to ' . $to;
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Failed to send test email. Check email queue for details.';
            $_SESSION['flash_type'] = 'danger';
        }
        $this->redirect('/admin/communication/queue');
    }

    public function testSms()
    {
        $this->requireAdmin();
        return $this->render('admin/communication/test_sms', [
            'page_title' => 'Test SMS - APS Dream Home',
        ]);
    }

    public function sendTestSms()
    {
        $this->requireAdmin();

        $phone = $_POST['to_phone'] ?? '';
        $message = $_POST['message'] ?? 'Test SMS from APS Dream Home';

        if (!preg_match('/^[0-9]{10,15}$/', preg_replace('/[^0-9]/', '', $phone))) {
            $_SESSION['flash_message'] = 'Invalid phone number.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect('/admin/communication/test-sms');
        }

        $result = $this->smsSender->send($phone, $message);

        if ($result['success'] ?? false) {
            $_SESSION['flash_message'] = 'Test SMS sent successfully to ' . $phone;
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Failed to send test SMS: ' . ($result['error'] ?? 'Unknown error');
            $_SESSION['flash_type'] = 'danger';
        }
        $this->redirect('/admin/communication/queue');
    }
}
