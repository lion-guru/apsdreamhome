<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Services\CRM\LeadScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * EmployeeController - Employee Portal Controller
 *
 * Provides employee-facing functionality for attendance tracking,
 * payroll management, performance reviews, and company communication.
 */
class EmployeeController extends Controller
{
    protected $leadScoringService;

    public function __construct(LeadScoringService $leadScoringService)
    {
        $this->leadScoringService = $leadScoringService;
    }

    /**
     * Employee Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $employee = $user->employee;

        // Employee statistics
        $stats = [
            'total_working_days' => $this->getWorkingDaysThisMonth(),
            'present_days' => $this->getAttendanceCount('present'),
            'absent_days' => $this->getAttendanceCount('absent'),
            'monthly_salary' => $employee ? $employee->monthly_salary : 0,
            'pending_leaves' => $this->getPendingLeaves(),
            'completed_tasks' => $this->getCompletedTasks(),
            'performance_score' => $this->getPerformanceScore(),
        ];

        // Recent activities
        $recentActivities = [
            'attendance' => DB::table('employee_attendance')
                ->where('employee_id', $user->id)
                ->latest()->take(5)->get(),
            'payroll' => DB::table('employee_payroll')
                ->where('employee_id', $user->id)
                ->latest()->take(3)->get(),
            'tasks' => DB::table('employee_tasks')
                ->where('employee_id', $user->id)
                ->latest()->take(5)->get(),
        ];

        // Upcoming events
        $upcomingEvents = [
            'meetings' => DB::table('company_meetings')
                ->where('meeting_date', '>=', now())
                ->where('is_mandatory', true)
                ->orderBy('meeting_date')
                ->take(3)->get(),
            'deadlines' => DB::table('employee_tasks')
                ->where('employee_id', $user->id)
                ->where('due_date', '>=', now())
                ->where('status', '!=', 'completed')
                ->orderBy('due_date')
                ->take(5)->get(),
        ];

        // Company announcements
        $announcements = DB::table('company_announcements')
            ->where('is_active', true)
            ->where('target_audience', 'all')
            ->orWhere('target_audience', 'employees')
            ->latest()
            ->take(3)
            ->get();

        return view('employees.dashboard', compact('stats', 'recentActivities', 'upcomingEvents', 'announcements'));
    }

    /**
     * Attendance Management
     */
    public function attendance(Request $request)
    {
        $user = Auth::user();

        $query = DB::table('employee_attendance')
            ->where('employee_id', $user->id);

        // Filter by month
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        if ($month && $year) {
            $query->whereYear('attendance_date', $year)
                  ->whereMonth('attendance_date', $month);
        }

        $attendance = $query->orderBy('attendance_date', 'desc')->paginate(20);

        // Monthly summary
        $monthlySummary = [
            'present' => $this->getMonthlyAttendanceCount($month, $year, 'present'),
            'absent' => $this->getMonthlyAttendanceCount($month, $year, 'absent'),
            'late' => $this->getMonthlyAttendanceCount($month, $year, 'late'),
            'half_day' => $this->getMonthlyAttendanceCount($month, $year, 'half_day'),
        ];

        return view('employees.attendance.index', compact('attendance', 'monthlySummary', 'month', 'year'));
    }

    /**
     * Mark Attendance
     */
    public function markAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:present,absent,late,half_day',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $user = Auth::user();
        $today = date('Y-m-d');

        // Check if attendance already marked for today
        $existingAttendance = DB::table('employee_attendance')
            ->where('employee_id', $user->id)
            ->where('attendance_date', $today)
            ->first();

        if ($existingAttendance) {
            return redirect()->back()->with('error', 'Attendance already marked for today');
        }

        DB::table('employee_attendance')->insert([
            'employee_id' => $user->id,
            'attendance_date' => $today,
            'status' => $request->status,
            'check_in_time' => $request->check_in_time ?: now()->format('H:i'),
            'check_out_time' => $request->check_out_time,
            'notes' => $request->notes,
            'created_at' => now(),
        ]);

        return redirect()->route('employee.attendance')->with('success', 'Attendance marked successfully');
    }

    /**
     * Payroll Information
     */
    public function payroll(Request $request)
    {
        $user = Auth::user();

        $query = DB::table('employee_payroll')
            ->where('employee_id', $user->id);

        // Filter by month/year
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        if ($month && $year) {
            $query->where('payroll_month', $month)
                  ->where('payroll_year', $year);
        }

        $payrollRecords = $query->orderBy('payroll_year', 'desc')
                                ->orderBy('payroll_month', 'desc')
                                ->paginate(12);

        // Current salary information
        $currentSalary = $user->employee ? $user->employee->monthly_salary : 0;

        return view('employees.payroll.index', compact('payrollRecords', 'currentSalary', 'month', 'year'));
    }

    /**
     * Employee Tasks
     */
    public function tasks(Request $request)
    {
        $user = Auth::user();

        $query = DB::table('employee_tasks')
            ->where('employee_id', $user->id);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->orderBy('due_date', 'asc')
                       ->orderBy('priority', 'desc')
                       ->paginate(15);

        // Task statistics
        $taskStats = [
            'total' => DB::table('employee_tasks')->where('employee_id', $user->id)->count(),
            'pending' => DB::table('employee_tasks')->where('employee_id', $user->id)->where('status', 'pending')->count(),
            'in_progress' => DB::table('employee_tasks')->where('employee_id', $user->id)->where('status', 'in_progress')->count(),
            'completed' => DB::table('employee_tasks')->where('employee_id', $user->id)->where('status', 'completed')->count(),
            'overdue' => DB::table('employee_tasks')
                ->where('employee_id', $user->id)
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
        ];

        return view('employees.tasks.index', compact('tasks', 'taskStats'));
    }

    /**
     * Update Task Status
     */
    public function updateTaskStatus(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid status'], 400);
        }

        $user = Auth::user();

        $updated = DB::table('employee_tasks')
            ->where('id', $taskId)
            ->where('employee_id', $user->id)
            ->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Task status updated']);
        }

        return response()->json(['error' => 'Task not found or access denied'], 404);
    }

    /**
     * Leave Management
     */
    public function leaves(Request $request)
    {
        $user = Auth::user();

        $leaves = DB::table('employee_leaves')
            ->where('employee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Leave balance
        $leaveBalance = [
            'annual' => $this->getLeaveBalance('annual'),
            'sick' => $this->getLeaveBalance('sick'),
            'casual' => $this->getLeaveBalance('casual'),
            'maternity' => $this->getLeaveBalance('maternity'),
        ];

        // Leave statistics
        $leaveStats = [
            'approved' => DB::table('employee_leaves')->where('employee_id', $user->id)->where('status', 'approved')->count(),
            'pending' => DB::table('employee_leaves')->where('employee_id', $user->id)->where('status', 'pending')->count(),
            'rejected' => DB::table('employee_leaves')->where('employee_id', $user->id)->where('status', 'rejected')->count(),
        ];

        return view('employees.leaves.index', compact('leaves', 'leaveBalance', 'leaveStats'));
    }

    /**
     * Apply for Leave
     */
    public function applyLeave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leave_type' => 'required|in:annual,sick,casual,maternity',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        // Calculate leave days
        $startDate = new \DateTime($request->start_date);
        $endDate = new \DateTime($request->end_date);
        $leaveDays = $startDate->diff($endDate)->days + 1;

        // Check leave balance
        $availableBalance = $this->getLeaveBalance($request->leave_type);
        if ($leaveDays > $availableBalance) {
            return redirect()->back()->with('error', 'Insufficient leave balance')->withInput();
        }

        DB::table('employee_leaves')->insert([
            'employee_id' => $user->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'leave_days' => $leaveDays,
            'reason' => $request->reason,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        return redirect()->route('employee.leaves')->with('success', 'Leave application submitted successfully');
    }

    /**
     * Performance Reviews
     */
    public function performance(Request $request)
    {
        $user = Auth::user();

        $reviews = DB::table('employee_performance_reviews')
            ->where('employee_id', $user->id)
            ->orderBy('review_date', 'desc')
            ->paginate(10);

        // Performance statistics
        $performanceStats = [
            'average_rating' => DB::table('employee_performance_reviews')
                ->where('employee_id', $user->id)
                ->avg('overall_rating') ?? 0,
            'total_reviews' => DB::table('employee_performance_reviews')
                ->where('employee_id', $user->id)
                ->count(),
            'last_review_date' => DB::table('employee_performance_reviews')
                ->where('employee_id', $user->id)
                ->max('review_date'),
        ];

        // Goals and objectives
        $goals = DB::table('employee_goals')
            ->where('employee_id', $user->id)
            ->where('status', 'active')
            ->get();

        return view('employees.performance.index', compact('reviews', 'performanceStats', 'goals'));
    }

    /**
     * Employee Profile
     */
    public function profile()
    {
        $user = Auth::user();
        $employee = $user->employee;

        return view('employees.profile', compact('user', 'employee'));
    }

    /**
     * Update Employee Profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update user
        $user->update($request->only(['name', 'email', 'phone']));

        // Update employee profile
        if ($user->employee) {
            $user->employee->update($request->only([
                'emergency_contact',
                'emergency_phone',
                'address',
                'bank_name',
                'account_number',
                'ifsc_code',
            ]));
        }

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    /**
     * Company Directory
     */
    public function directory(Request $request)
    {
        $query = DB::table('users')
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->select('users.*', 'employees.employee_id', 'employees.department', 'employees.designation');

        // Filter by department
        if ($request->has('department') && $request->department !== '') {
            $query->where('employees.department', $request->department);
        }

        // Search by name or email
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(20);

        // Get unique departments for filter
        $departments = DB::table('employees')
            ->select('department')
            ->distinct()
            ->whereNotNull('department')
            ->pluck('department');

        return view('employees.directory', compact('employees', 'departments'));
    }

    /**
     * Company Announcements
     */
    public function announcements()
    {
        $announcements = DB::table('company_announcements')
            ->where('is_active', true)
            ->where(function($query) {
                $query->where('target_audience', 'all')
                      ->orWhere('target_audience', 'employees');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('employees.announcements', compact('announcements'));
    }

    // Helper methods

    private function getWorkingDaysThisMonth()
    {
        $month = date('m');
        $year = date('Y');
        $firstDay = date('Y-m-01');
        $lastDay = date('Y-m-t');

        $workingDays = 0;
        $currentDate = strtotime($firstDay);

        while ($currentDate <= strtotime($lastDay)) {
            $dayOfWeek = date('N', $currentDate); // 1=Monday, 7=Sunday
            if ($dayOfWeek <= 5) { // Monday to Friday
                $workingDays++;
            }
            $currentDate = strtotime('+1 day', $currentDate);
        }

        return $workingDays;
    }

    private function getAttendanceCount($status)
    {
        $user = Auth::user();
        $month = date('m');
        $year = date('Y');

        return DB::table('employee_attendance')
            ->where('employee_id', $user->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->where('status', $status)
            ->count();
    }

    private function getMonthlyAttendanceCount($month, $year, $status)
    {
        $user = Auth::user();

        return DB::table('employee_attendance')
            ->where('employee_id', $user->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->where('status', $status)
            ->count();
    }

    private function getPendingLeaves()
    {
        $user = Auth::user();

        return DB::table('employee_leaves')
            ->where('employee_id', $user->id)
            ->where('status', 'pending')
            ->count();
    }

    private function getCompletedTasks()
    {
        $user = Auth::user();
        $month = date('m');
        $year = date('Y');

        return DB::table('employee_tasks')
            ->where('employee_id', $user->id)
            ->where('status', 'completed')
            ->whereYear('completed_at', $year)
            ->whereMonth('completed_at', $month)
            ->count();
    }

    private function getPerformanceScore()
    {
        $user = Auth::user();

        $latestReview = DB::table('employee_performance_reviews')
            ->where('employee_id', $user->id)
            ->latest('review_date')
            ->first();

        return $latestReview ? $latestReview->overall_rating : 0;
    }

    private function getLeaveBalance($leaveType)
    {
        $user = Auth::user();

        // This would typically come from employee configuration
        // For now, return default balances
        $defaultBalances = [
            'annual' => 24, // 24 days per year
            'sick' => 12,   // 12 days per year
            'casual' => 12, // 12 days per year
            'maternity' => 180, // 180 days for maternity
        ];

        $allocated = $defaultBalances[$leaveType] ?? 0;

        // Subtract used leaves
        $used = DB::table('employee_leaves')
            ->where('employee_id', $user->id)
            ->where('leave_type', $leaveType)
            ->where('status', 'approved')
            ->whereYear('start_date', date('Y'))
            ->sum('leave_days');

        return max(0, $allocated - $used);
    }
}
