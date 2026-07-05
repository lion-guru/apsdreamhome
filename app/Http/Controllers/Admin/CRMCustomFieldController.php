<?php
namespace App\Http\Controllers\Admin;
use App\Services\CRMCustomFieldService;

class CRMCustomFieldController extends AdminController {
    public function index() {
        $this->requireAdmin();
        $service = new CRMCustomFieldService();
        $fields = $service->getAllFields();
        return $this->render('admin/crm/custom_fields/index', ['fields' => $fields, 'page_title' => 'Custom Fields']);
    }

    public function create() {
        $this->requireAdmin();
        return $this->render('admin/crm/custom_fields/form', ['field' => null, 'page_title' => 'Add Custom Field']);
    }

    public function store() {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/crm/custom-fields');
        $service = new CRMCustomFieldService();
        $options = !empty($_POST['options']) ? array_filter(array_map('trim', explode("\n", $_POST['options']))) : [];
        $result = $service->createField([
            'field_name' => $_POST['field_name'] ?? '',
            'field_label' => $_POST['field_label'] ?? '',
            'field_type' => $_POST['field_type'] ?? 'text',
            'options' => $options,
            'is_required' => isset($_POST['is_required']) ? 1 : 0,
            'is_searchable' => isset($_POST['is_searchable']) ? 1 : 0,
            'section' => $_POST['section'] ?? 'general',
            'order_index' => (int)($_POST['order_index'] ?? 0),
        ]);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Custom field created' : 'Error: ' . ($result['error'] ?? 'Unknown'));
        return $this->redirect('/admin/crm/custom-fields');
    }

    public function edit($id) {
        $this->requireAdmin();
        $service = new CRMCustomFieldService();
        $field = $service->getFieldById((int)$id);
        if (!$field) { $this->setFlash('error', 'Field not found'); return $this->redirect('/admin/crm/custom-fields'); }
        if (!empty($field['options_json'])) $field['options_list'] = implode("\n", json_decode($field['options_json'], true) ?: []);
        return $this->render('admin/crm/custom_fields/form', ['field' => $field, 'page_title' => 'Edit Custom Field']);
    }

    public function update($id) {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/crm/custom-fields');
        $service = new CRMCustomFieldService();
        $options = !empty($_POST['options']) ? array_filter(array_map('trim', explode("\n", $_POST['options']))) : [];
        $result = $service->updateField((int)$id, [
            'field_name' => $_POST['field_name'] ?? '',
            'field_label' => $_POST['field_label'] ?? '',
            'field_type' => $_POST['field_type'] ?? 'text',
            'options' => $options,
            'is_required' => isset($_POST['is_required']) ? 1 : 0,
            'is_searchable' => isset($_POST['is_searchable']) ? 1 : 0,
            'section' => $_POST['section'] ?? 'general',
            'order_index' => (int)($_POST['order_index'] ?? 0),
        ]);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Field updated' : 'Error: ' . ($result['error'] ?? 'Unknown'));
        return $this->redirect('/admin/crm/custom-fields');
    }

    public function delete($id) {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/crm/custom-fields');
        $service = new CRMCustomFieldService();
        $result = $service->deleteField((int)$id);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Field deleted' : 'Error');
        return $this->redirect('/admin/crm/custom-fields');
    }
}
