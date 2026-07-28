<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class FAQController extends BaseController
{
    use TenantAwareTrait;
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY display_order ASC, created_at DESC");
            $faqs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $faqs = [];
        }
        $this->render('pages/faq_list', [
            'page_title' => 'Frequently Asked Questions',
            'faqs' => $faqs
        ]);
    }

    public function show($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM faqs WHERE id = ? AND status = 'active'");
            $stmt->execute([$id]);
            $faq = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $faq = null;
        }
        if (!$faq) {
            $this->redirect('/faq-list');
            return;
        }
        $this->render('pages/faq_detail', [
            'page_title' => $faq['question'] ?? 'FAQ',
            'faq' => $faq
        ]);
    }
}
