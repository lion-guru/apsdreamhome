<?php

namespace App\Http\Controllers\Admin;

class TestimonialController extends AdminController
{
    public function manage()
    {
        $this->requireAdmin();
        try {
            $this->data['page_title'] = 'Testimonials Management - APS Dream Home';
            $this->render('admin/testimonials/index', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load testimonials');
            $this->redirect('/admin/dashboard');
        }
    }
}
