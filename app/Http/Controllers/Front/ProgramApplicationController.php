<?php
/**
 * ProgramApplicationController (Front)
 * Session: 681
 *
 * Public intake for the Student Internship Program (careers page) and the
 * Nari Shakti women-empowerment initiative (team page). Both programs are
 * real, free-entry programs; this single endpoint serves either via
 * program_type (whitelist: student | nari_shakti).
 */
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class ProgramApplicationController extends BaseController
{
    use TenantAwareTrait;

    /**
     * POST /program-application
     * Public: CSRF gate in router already ran; honeypot-less, rate-limited by global middleware.
     */
    public function store()
    {
        $programType = trim($_POST['program_type'] ?? 'student');
        $fullName = trim(strip_tags($_POST['full_name'] ?? $_POST['name'] ?? ''));
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $college = trim(strip_tags($_POST['college'] ?? ''));
        $course = trim(strip_tags($_POST['course'] ?? ''));
        $currentYear = trim(strip_tags($_POST['year'] ?? $_POST['current_year'] ?? ''));
        $city = trim(strip_tags($_POST['city'] ?? ''));
        $motivation = trim(strip_tags($_POST['motivation'] ?? ''));
        $suggestion = trim(strip_tags($_POST['suggestion'] ?? ''));

        if (!in_array($programType, ['student', 'nari_shakti'], true)) {
            return $this->json(['success' => false, 'message' => 'Invalid program type.'], 422);
        }
        if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 120) {
            return $this->json(['success' => false, 'message' => 'Please enter your full name (2-120 characters).'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
            return $this->json(['success' => false, 'message' => 'Please enter a valid email address.'], 422);
        }
        // Indian mobile/landline: 10-15 digits (E.164 allowed with leading +)
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (mb_strlen($digits) < 10 || mb_strlen($digits) > 15) {
            return $this->json(['success' => false, 'message' => 'Please enter a valid phone number (10-15 digits).'], 422);
        }

        try {
            // Duplicate guard: same phone+program within 7 days
            $stmt = $this->db->prepare(
                "SELECT id FROM program_applications WHERE program_type = ? AND phone = ? AND status = 'pending'
                 AND created_at >= (NOW() - INTERVAL 7 DAY) LIMIT 1"
            );
            $stmt->execute([$programType, $digits]);
            if ($stmt->fetchColumn()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Application already received — our HR team will reach out soon.',
                    'duplicate' => true
                ]);
            }

            $stmt = $this->db->prepare(
                "INSERT INTO program_applications
                   (program_type, full_name, email, phone, college, course, current_year, city, motivation, suggestion, tenant_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $programType, $fullName, $email, $digits,
                $college !== '' ? $college : null,
                $course !== '' ? $course : null,
                $currentYear !== '' ? $currentYear : null,
                $city !== '' ? $city : null,
                $motivation !== '' ? mb_substr($motivation, 0, 2000) : null,
                $suggestion !== '' ? mb_substr($suggestion, 0, 2000) : null,
                $this->tenantId(),
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Application received! Our team will contact you within 24 working hours.'
            ]);
        } catch (\Throwable $e) {
            error_log('program-application: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Something went wrong. Please try again or call +91 70074 44842.'], 500);
        }
    }
}
