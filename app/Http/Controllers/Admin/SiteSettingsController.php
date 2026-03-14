<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\SiteSettings;

class SiteSettingsController extends BaseController
{
    public function edit()
    {
        $this->requireAdmin();
        $data = [
            'page_title' => 'Appearance Settings',
            'active_page' => 'settings',
            'settings' => SiteSettings::get(),
        ];
        $this->render('admin/settings/appearance', $data, 'admin/layouts/header');
        include __DIR__ . '/../../views/admin/layouts/footer.php';
    }

    public function update()
    {
        $this->requireAdmin();
        $payload = [
            'brand_name' => $_POST['brand_name'] ?? null,
            'logo_url' => $_POST['logo_url'] ?? null,
            'favicon_url' => $_POST['favicon_url'] ?? null,
            'nav_json' => $_POST['nav_json'] ?? null,
            'social_json' => $_POST['social_json'] ?? null,
            'footer_html' => $_POST['footer_html'] ?? null,
        ];
        SiteSettings::update($payload);
        $this->redirect('/admin/settings/appearance');
    }
}

