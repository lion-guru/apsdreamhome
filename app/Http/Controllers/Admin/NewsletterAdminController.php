<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\AdminController;
class NewsletterAdminController extends AdminController {
    public function index() {
        $subscribers = $this->db->fetchAll("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC");
        $this->render("admin/newsletter/index", ["subscribers" => $subscribers]);
    }
}