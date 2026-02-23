<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Career;
use App\Models\CareerApplication;
use App\Helpers\SecurityHelper;

class CareerController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('csrf', ['only' => ['store', 'update', 'delete']]);
        $this->loadModel('Career');
        // $this->loadModel('CareerApplication'); // Assuming this model exists or we'll query DB directly
    }

    public function index()
    {
        $careers = Career::all();
        $this->render('admin/careers/index', [
            'careers' => $careers,
            'title' => $this->mlSupport->translate('Manage Careers')
        ]);
    }

    public function create()
    {
        $this->render('admin/careers/create', [
            'title' => $this->mlSupport->translate('Post New Job')
        ]);
    }

    public function store()
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method'));
            $this->redirect('admin/careers/create');
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token'));
            $this->redirect('admin/careers/create');
            return;
        }

        $title = SecurityHelper::sanitize($this->request->post('title'));
        $type = SecurityHelper::sanitize($this->request->post('type'));
        $location = SecurityHelper::sanitize($this->request->post('location'));
        $salary_range = SecurityHelper::sanitize($this->request->post('salary_range'));
        $status = SecurityHelper::sanitize($this->request->post('status'));
        $description = $this->request->post('description'); // Allow HTML

        $career = new Career();
        $career->title = $title;
        $career->type = $type;
        $career->location = $location;
        $career->salary_range = $salary_range;
        $career->status = $status;
        $career->description = $description;

        if ($career->save()) {
            $this->setFlash('success', $this->mlSupport->translate('Job posted successfully'));
            $this->redirect('admin/careers');
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Failed to post job'));
            $this->redirect('admin/careers/create');
        }
    }

    public function edit($id)
    {
        $career = Career::find($id);
        if (!$career) {
            $this->setFlash('error', $this->mlSupport->translate('Job not found'));
            $this->redirect('admin/careers');
            return;
        }

        $this->render('admin/careers/edit', [
            'career' => $career,
            'title' => $this->mlSupport->translate('Edit Job')
        ]);
    }

    public function update($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method'));
            $this->redirect('admin/careers/edit/' . $id);
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token'));
            $this->redirect('admin/careers/edit/' . $id);
            return;
        }

        $career = Career::find($id);
        if (!$career) {
            $this->setFlash('error', $this->mlSupport->translate('Job not found'));
            $this->redirect('admin/careers');
            return;
        }

        $career->title = SecurityHelper::sanitize($this->request->post('title'));
        $career->type = SecurityHelper::sanitize($this->request->post('type'));
        $career->location = SecurityHelper::sanitize($this->request->post('location'));
        $career->salary_range = SecurityHelper::sanitize($this->request->post('salary_range'));
        $career->status = SecurityHelper::sanitize($this->request->post('status'));
        $career->description = $this->request->post('description');

        if ($career->save()) {
            $this->setFlash('success', 'Job updated successfully');
            $this->redirect('admin/careers');
        } else {
            $this->setFlash('error', 'Failed to update job');
            $this->redirect('admin/careers/edit/' . $id);
        }
    }

    public function delete($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method'));
            $this->redirect('admin/careers');
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', 'Invalid CSRF token');
            $this->redirect('admin/careers');
            return;
        }

        $career = Career::find($id);
        if ($career) {
            $career->delete();
            $this->setFlash('success', 'Job deleted successfully');
        } else {
            $this->setFlash('error', 'Job not found');
        }
        $this->redirect('admin/careers');
    }

    public function applications($id = null)
    {
        // If ID is provided, get applications for that job
        // Otherwise get all applications
        // Assuming CareerApplication model exists, if not we'll create a basic view

        // This is a placeholder for now as I need to check CareerApplication model
        // But for migration completeness of "admin_view_applicants.php", this is needed.

        $this->render('admin/careers/applications', [
            'title' => 'Job Applications',
            'jobId' => $id
        ], 'layouts/admin');
    }
}
