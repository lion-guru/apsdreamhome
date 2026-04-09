<?php
namespace App\Http\Controllers\Admin;
use App\Core\Controller;
class BlogController extends Controller {
    public function index() { $this->render("admin/blog/index"); }
    public function create() { $this->render("admin/blog/create"); }
}