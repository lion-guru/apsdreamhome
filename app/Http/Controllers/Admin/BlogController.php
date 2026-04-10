<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\AdminController;
class BlogController extends AdminController {
    public function index() { $this->render("admin/blog/index"); }
    public function create() { $this->render("admin/blog/create"); }
}