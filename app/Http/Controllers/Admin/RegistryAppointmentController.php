<?php

namespace App\Http\Controllers\Admin;

use App\Services\RegistryAppointmentService;

/**
 * Admin SRO Deed Registration & Legal Execution Desk
 */
class RegistryAppointmentController extends AdminController
{
    private RegistryAppointmentService $regService;

    public function __construct()
    {
        parent::__construct();
        $this->regService = new RegistryAppointmentService();
    }

    public function index()
    {
        $this->requireAdmin();

        $status = trim((string)($_GET['status'] ?? 'all'));
        $appointments = $this->regService->getAllAppointments($status);

        $this->render('admin/legal/registry_appointments', [
            'page_title' => 'SRO Deed Registry Execution Desk',
            'appointments' => $appointments,
            'current_status' => $status,
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();

        $appointment = $this->regService->getAppointmentDetail((int)$id);
        if (!$appointment) {
            $this->setFlash('error', 'Registry appointment record not found.');
            return $this->redirect('/admin/registry-appointments');
        }

        $this->render('admin/legal/registry_appointment_detail', [
            'page_title' => "Registry Token #{$appointment['appointment_token']}",
            'appointment' => $appointment,
        ]);
    }

    public function confirmSlot($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $confirmedDate = trim((string)($_POST['confirmed_date'] ?? ''));
        $executiveName = trim((string)($_POST['assigned_executive_name'] ?? ''));
        $executivePhone = trim((string)($_POST['assigned_executive_phone'] ?? ''));
        $notes = trim((string)($_POST['notes'] ?? ''));

        if ($confirmedDate === '') {
            $this->setFlash('error', 'Confirmed registry appointment date is required.');
            return $this->redirect("/admin/registry-appointments/{$id}");
        }

        $res = $this->regService->confirmSlot((int)$id, $confirmedDate, $executiveName, $executivePhone, $notes);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect("/admin/registry-appointments/{$id}");
    }

    public function complete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $deedNumber = trim((string)($_POST['deed_registration_number'] ?? ''));
        if ($deedNumber === '') {
            $this->setFlash('error', 'Official SRO Bahi / Registration Deed Number is required.');
            return $this->redirect("/admin/registry-appointments/{$id}");
        }

        $deedFile = null;
        if (!empty($_FILES['registered_deed_file']['tmp_name']) && is_uploaded_file($_FILES['registered_deed_file']['tmp_name'])) {
            $uploadDir = dirname(__DIR__, 4) . '/public/uploads/registry_deeds';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['registered_deed_file']['name'], PATHINFO_EXTENSION);
            $fileName = 'deed_' . (int)$id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['registered_deed_file']['tmp_name'], $uploadDir . '/' . $fileName)) {
                $deedFile = '/uploads/registry_deeds/' . $fileName;
            }
        }

        $res = $this->regService->completeRegistration((int)$id, $deedNumber, $deedFile);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect("/admin/registry-appointments/{$id}");
    }

    public function slipPrint($id)
    {
        $this->requireAdmin();

        $appointment = $this->regService->getAppointmentDetail((int)$id);
        if (!$appointment) {
            echo "Appointment not found.";
            exit;
        }

        require dirname(__DIR__, 2) . '/views/admin/legal/registry_appointment_slip.php';
        exit;
    }
}
