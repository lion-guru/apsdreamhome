<?php

namespace App\Http\Controllers\Admin;

class MeetingController extends AdminController
{
    public function index()
    {
        $this->data['page_title'] = 'Meetings';
        $this->data['meetings'] = [];
        $this->render('admin/meetings/index');
    }

    public function schedule()
    {
        $this->data['page_title'] = 'Schedule Meeting';
        $this->render('admin/meetings/schedule');
    }

    public function show($id)
    {
        $this->data['page_title'] = 'Meeting Details';
        $this->data['meeting_id'] = $id;
        $this->render('admin/meetings/show');
    }

    public function store()
    {
        $this->middleware('admin.auth');
        $this->redirect('/admin/meetings');
    }

    public function cancel($id)
    {
        $this->middleware('admin.auth');
        $this->redirect('/admin/meetings');
    }
}