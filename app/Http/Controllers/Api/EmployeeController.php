<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class EmployeeController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function punchIn()
    {
        // Punch in
    }

    public function punchOut()
    {
        // Punch out
    }

    public function attendanceStatus()
    {
        // Attendance status
    }

    public function dashboard()
    {
        // Employee dashboard
    }

    public function tasks()
    {
        // Employee tasks
    }

    public function attendance()
    {
        // Attendance
    }
}