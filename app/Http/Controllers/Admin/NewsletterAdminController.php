<?php
namespace App\Http\Controllers\Admin;
use App\Core\Controller;
class NewsletterAdminController extends Controller {
    public function index() {
        $subscribers = $this->db->fetchAll("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC");
        $this->render("admin/newsletter/index", ["subscribers" => $subscribers]);
    }
}