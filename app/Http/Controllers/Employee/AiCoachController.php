<?php
// Employee AI Coach — personal AI coach for telecallers / field staff / backoffice.

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;

class AiCoachController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/employee';
    }

    public function index()
    {
        $role = $_SESSION['user_role'] ?? $_SESSION['employee_role'] ?? $_SESSION['role'] ?? 'employee';
        $role = in_array($role, ['employee', 'telecaller', 'backoffice'], true) ? $role : 'employee';

        $this->render('_shared/ai_coach', [
            'page_title' => 'AI Coach — APS Employee Portal',
            'page_description' => 'Your personal AI coach for calls, leads and productivity',
            'role' => $role,
            'current_page' => 'ai_coach',
        ]);
    }
}
