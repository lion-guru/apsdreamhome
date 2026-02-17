<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\BaseController;

class PropertyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of properties.
     */
    public function index()
    {
        $this->data['page_title'] = 'Properties - ' . APP_NAME;
        return $this->render('property/index');
    }

    /**
     * Display the specified property.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        $this->data['page_title'] = 'Property Details - ' . APP_NAME;
        $this->data['property_id'] = $id;
        return $this->render('property/show');
    }

    /**
     * Search for properties.
     */
    public function search()
    {
        $this->data['page_title'] = 'Search Properties - ' . APP_NAME;
        return $this->render('property/search');
    }
}
