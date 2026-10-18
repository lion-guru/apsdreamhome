<?php
/**
 * Admin Holiday Calendar Controller
 */

namespace App\Http\Controllers\Admin;

use App\Services\HolidayCalendarService;
use App\Http\Controllers\BaseController;

class HolidayCalendarController extends BaseController
{
    private $holidayService;

    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        $this->holidayService = new HolidayCalendarService();
    }

    /**
     * Holiday calendar list
     */
    public function index()
    {
        $filters = [
            'year' => (int)($_GET['year'] ?? date('Y')),
            'type' => $_GET['type'] ?? 'all',
            'search' => $_GET['search'] ?? '',
        ];

        $holidays = $this->holidayService->getHolidays($filters);
        $stats = $this->holidayService->getStats();

        return $this->render('admin/holiday_calendar/index', [
            'page_title' => 'Holiday Calendar',
            'holidays' => $holidays,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return $this->render('admin/holiday_calendar/create', [
            'page_title' => 'Add Holiday',
            'types' => $this->holidayService->getHolidayTypes(),
        ]);
    }

    /**
     * Store new holiday
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/holiday-calendar');
            return;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'date' => $_POST['date'] ?? '',
            'type' => $_POST['type'] ?? 'national',
            'description' => $_POST['description'] ?? '',
            'is_recurring' => isset($_POST['is_recurring']) ? 1 : 0,
            'applicable_states' => $_POST['applicable_states'] ?? '',
            'is_optional' => isset($_POST['is_optional']) ? 1 : 0,
        ];

        $result = $this->holidayService->createHoliday($data);

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        if ($result['success']) {
            $this->redirect(BASE_URL . '/admin/holiday-calendar');
        } else {
            $this->redirect(BASE_URL . '/admin/holiday-calendar/create');
        }
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $holiday = $this->holidayService->getHoliday($id);
        if (!$holiday) {
            $_SESSION['error'] = 'Holiday not found';
            $this->redirect(BASE_URL . '/admin/holiday-calendar');
            return;
        }

        return $this->render('admin/holiday_calendar/edit', [
            'page_title' => 'Edit Holiday',
            'holiday' => $holiday,
            'types' => $this->holidayService->getHolidayTypes(),
        ]);
    }

    /**
     * Update holiday
     */
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/holiday-calendar');
            return;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'date' => $_POST['date'] ?? '',
            'type' => $_POST['type'] ?? 'national',
            'description' => $_POST['description'] ?? '',
            'is_recurring' => isset($_POST['is_recurring']) ? 1 : 0,
            'applicable_states' => $_POST['applicable_states'] ?? '',
            'is_optional' => isset($_POST['is_optional']) ? 1 : 0,
        ];

        $result = $this->holidayService->updateHoliday($id, $data);

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        if ($result['success']) {
            $this->redirect(BASE_URL . '/admin/holiday-calendar');
        } else {
            $this->redirect(BASE_URL . '/admin/holiday-calendar/' . $id . '/edit');
        }
    }

    /**
     * Delete holiday
     */
    public function destroy($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/holiday-calendar');
            return;
        }

        $result = $this->holidayService->deleteHoliday($id);

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/admin/holiday-calendar');
    }

    /**
     * Bulk import holidays
     */
    public function bulkImport()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/holiday-calendar');
            return;
        }

        $holidays = [];
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === 0) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $holidays[] = array_combine($header, $row);
            }
            fclose($handle);
        } elseif (!empty($_POST['holidays'])) {
            $holidays = json_decode($_POST['holidays'], true);
        }

        if (empty($holidays)) {
            $_SESSION['error'] = 'No holiday data provided';
            $this->redirect(BASE_URL . '/admin/holiday-calendar');
            return;
        }

        $result = $this->holidayService->bulkImport($holidays);

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/admin/holiday-calendar');
    }
}