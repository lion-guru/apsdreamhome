<?php
namespace App\Http\Controllers\Admin;
class ActivityLogController extends AdminController {
    public function index() { $this->render("admin/activity-log/index"); }
}
