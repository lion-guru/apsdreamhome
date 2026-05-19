<?php
namespace App\Http\Controllers\Admin;
class ExpensesController extends AdminController {
    public function index() { $this->render("admin/expenses/index"); }
    public function create() { $this->render("admin/expenses/create"); }
}
