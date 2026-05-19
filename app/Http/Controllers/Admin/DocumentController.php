<?php

namespace App\Http\Controllers\Admin;

class DocumentController extends AdminController
{
    public function index()
    {
        $this->data['page_title'] = 'Documents';
        $this->data['documents'] = [];
        $this->render('admin/documents/index');
    }

    public function upload()
    {
        $this->data['page_title'] = 'Upload Document';
        $this->render('admin/documents/upload');
    }

    public function show($id)
    {
        $this->data['page_title'] = 'Document Details';
        $this->data['document_id'] = $id;
        $this->render('admin/documents/show');
    }

    public function store()
    {
        $this->middleware('admin.auth');
        $this->redirect('/admin/documents');
    }

    public function delete($id)
    {
        $this->middleware('admin.auth');
        $this->redirect('/admin/documents');
    }

    public function download($id)
    {
        $this->middleware('admin.auth');
    }
}