<?php
// Add missing methods to HomeController for about, contact, properties
class HomeController extends \App\Http\Controllers\BaseController
{
    // ... existing methods ...

    public function about()
    {
        $this->data['title'] = 'About APS Dream Home';
        $this->data['description'] = 'Learn about our company and mission';
        $this->render('home/about', [], 'layouts/base', false);
    }

    public function contact()
    {
        $this->data['title'] = 'Contact APS Dream Home';
        $this->data['description'] = 'Get in touch with our team';
        $this->render('home/contact', [], 'layouts/base', false);
    }

    public function properties()
    {
        $this->data['title'] = 'Properties - APS Dream Home';
        $this->data['description'] = 'Browse our premium properties';
        $this->render('home/properties', [], 'layouts/base', false);
    }
}
?>
