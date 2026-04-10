<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class MLMController extends AdminController
{
    public function index() 
    {
        $this->data['page_title'] = 'MLM Dashboard';
        return $this->render('admin/mlm/dashboard');
    }
    
    public function associates() 
    {
        $this->data['page_title'] = 'Associates';
        return $this->render('admin/mlm/associates/index');
    }
    
    public function createAssociate() 
    {
        $this->data['page_title'] = 'Create Associate';
        return $this->render('admin/mlm/associates/create');
    }
    
    public function commission() 
    {
        $this->data['page_title'] = 'Commission';
        return $this->render('admin/mlm/commission/index');
    }
    
    public function network() 
    {
        $this->data['page_title'] = 'Network';
        return $this->render('admin/mlm/network/tree');
    }
    
    public function payouts() 
    {
        $this->data['page_title'] = 'Payouts';
        return $this->render('admin/mlm/payouts/index');
    }
    
    public function tree() 
    {
        $this->data['page_title'] = 'Network Tree';
        return $this->render('admin/mlm/tree');
    }
    
    public function genealogy() 
    {
        $this->data['page_title'] = 'Genealogy';
        return $this->render('admin/mlm/genealogy');
    }
    
    public function ranks() 
    {
        $this->data['page_title'] = 'Ranks';
        return $this->render('admin/mlm/ranks');
    }
}