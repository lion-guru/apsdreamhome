<?php

namespace App\Http\Controllers\Admin;

use App\Services\Legal\LegalDocumentService;
use App\Services\Legal\LegalAIService;
use Exception;

class LegalDocumentController extends AdminController
{
    protected $docService;
    protected $aiService;

    public function __construct()
    {
        parent::__construct();
        try {
            $db = \App\Core\Database::getInstance();
            $pdo = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
            $this->docService = new LegalDocumentService($pdo);
            $this->aiService = new LegalAIService($this->docService);
        } catch (Exception $e) {
            $this->docService = null;
            $this->aiService = null;
        }
    }

    // ===================== DASHBOARD =====================

    public function index()
    {
        $this->requireAdmin();
        $stats = $this->docService ? $this->docService->getDashboardStats() : [];
        $this->render('admin/legal/index', [
            'page_title' => 'Legal Documentation Management',
            'stats' => $stats,
            'categories' => $this->docService ? $this->docService->getCategories() : [],
        ]);
    }

    // ===================== CATEGORIES =====================

    public function categories()
    {
        $this->requireAdmin();
        $cats = $this->docService ? $this->docService->getCategories() : [];
        $this->render('admin/legal/categories', [
            'page_title' => 'Document Categories',
            'categories' => $cats,
        ]);
    }

    public function categoryCreate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->createCategory($_POST) : ['success' => false, 'error' => 'Service unavailable'];
        if ($result['success']) {
            $this->setFlash('success', 'Category created successfully');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to create category');
        }
        $this->redirect('/admin/legal/categories');
    }

    public function categoryUpdate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->updateCategory((int)$id, $_POST) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Category updated' : ($result['error'] ?? 'Update failed'));
        $this->redirect('/admin/legal/categories');
    }

    public function categoryDelete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->deleteCategory((int)$id) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Category deleted' : ($result['error'] ?? 'Delete failed'));
        $this->redirect('/admin/legal/categories');
    }

    // ===================== TEMPLATES =====================

    public function templates()
    {
        $this->requireAdmin();
        $filters = [];
        if (!empty($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        $templates = $this->docService ? $this->docService->getTemplates($filters) : [];
        $this->render('admin/legal/templates', [
            'page_title' => 'Document Templates',
            'templates' => $templates,
            'categories' => $this->docService ? $this->docService->getCategories() : [],
            'merge_fields' => $this->docService ? $this->docService->getAvailableMergeFields() : [],
        ]);
    }

    public function templateCreate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = $_POST;
        $data['created_by'] = $_SESSION['admin_id'] ?? null;
        $result = $this->docService ? $this->docService->createTemplate($data) : ['success' => false, 'error' => 'Service unavailable'];
        if ($result['success']) {
            $this->setFlash('success', 'Template created successfully');
            $this->redirect('/admin/legal/templates/' . $result['id'] . '/edit');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to create template');
            $this->redirect('/admin/legal/templates');
        }
    }

    public function templateEdit($id)
    {
        $this->requireAdmin();
        $template = $this->docService ? $this->docService->getTemplateById((int)$id) : null;
        if (!$template) {
            $this->setFlash('error', 'Template not found');
            $this->redirect('/admin/legal/templates');
            return;
        }
        $versions = $this->docService ? $this->docService->getTemplateVersions((int)$id) : [];
        $this->render('admin/legal/template_edit', [
            'page_title' => 'Edit Template: ' . $template['name'],
            'template' => $template,
            'versions' => $versions,
            'categories' => $this->docService ? $this->docService->getCategories() : [],
            'merge_fields' => $this->docService ? $this->docService->getAvailableMergeFields() : [],
        ]);
    }

    public function templateUpdate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->updateTemplate((int)$id, $_POST) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Template updated' : ($result['error'] ?? 'Update failed'));
        $this->redirect('/admin/legal/templates/' . $id . '/edit');
    }

    public function templateDelete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->deleteTemplate((int)$id) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Template archived' : ($result['error'] ?? 'Delete failed'));
        $this->redirect('/admin/legal/templates');
    }

    public function templateRestore($id, $version)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->restoreTemplateVersion((int)$id, (int)$version) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Version restored' : ($result['error'] ?? 'Restore failed'));
        $this->redirect('/admin/legal/templates/' . $id . '/edit');
    }

    // ===================== CLAUSE LIBRARY =====================

    public function clauses()
    {
        $this->requireAdmin();
        $filters = [];
        if (!empty($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['tag'])) $filters['tag'] = $_GET['tag'];
        $clauses = $this->docService ? $this->docService->getClauses($filters) : [];
        $this->render('admin/legal/clauses', [
            'page_title' => 'Clause Library',
            'clauses' => $clauses,
            'categories' => $this->docService ? $this->docService->getCategories() : [],
        ]);
    }

    public function clauseCreate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = $_POST;
        $data['created_by'] = $_SESSION['admin_id'] ?? null;
        $result = $this->docService ? $this->docService->createClause($data) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Clause created' : ($result['error'] ?? 'Failed'));
        $this->redirect('/admin/legal/clauses');
    }

    public function clauseUpdate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->updateClause((int)$id, $_POST) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Clause updated' : ($result['error'] ?? 'Update failed'));
        $this->redirect('/admin/legal/clauses');
    }

    public function clauseDelete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->deleteClause((int)$id) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Clause deleted' : ($result['error'] ?? 'Delete failed'));
        $this->redirect('/admin/legal/clauses');
    }

    // ===================== DOCUMENTS =====================

    public function documents()
    {
        $this->requireAdmin();
        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['entity_type'])) $filters['entity_type'] = $_GET['entity_type'];
        if (!empty($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
        $documents = $this->docService ? $this->docService->getDocuments($filters) : [];
        $this->render('admin/legal/documents', [
            'page_title' => 'Legal Documents',
            'documents' => $documents,
            'categories' => $this->docService ? $this->docService->getCategories() : [],
        ]);
    }

    public function documentCreate()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->render('admin/legal/document_create', [
                'page_title' => 'Create Document',
                'templates' => $this->docService ? $this->docService->getTemplates(['status' => 'active']) : [],
                'customers' => $this->docService ? $this->docService->getCustomers() : [],
                'bookings' => $this->docService ? $this->docService->getBookings() : [],
                'plots' => $this->docService ? $this->docService->getPlots() : [],
                'associates' => $this->docService ? $this->docService->getAssociates() : [],
                'colonies' => $this->docService ? $this->docService->getColonies() : [],
                'merge_fields' => $this->docService ? $this->docService->getAvailableMergeFields() : [],
            ]);
            return;
        }
        $this->validateCsrfOrFail();
        $data = $_POST;
        $data['merge_data'] = $_POST;
        $data['created_by'] = $_SESSION['admin_id'] ?? null;
        $result = $this->docService ? $this->docService->createDocument($data) : ['success' => false, 'error' => 'Service unavailable'];
        if ($result['success']) {
            $this->setFlash('success', 'Document created: ' . ($result['document_number'] ?? ''));
            $this->redirect('/admin/legal/documents/' . $result['id']);
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to create document');
            $this->redirect('/admin/legal/documents/create');
        }
    }

    public function documentDetail($id)
    {
        $this->requireAdmin();
        $doc = $this->docService ? $this->docService->getDocumentById((int)$id) : null;
        if (!$doc) {
            $this->setFlash('error', 'Document not found');
            $this->redirect('/admin/legal/documents');
            return;
        }
        $uploads = $this->docService ? $this->docService->getUploads((int)$id) : [];
        $this->render('admin/legal/document_detail', [
            'page_title' => 'Document: ' . ($doc['document_number'] ?? $doc['title']),
            'doc' => $doc,
            'uploads' => $uploads,
        ]);
    }

    public function documentUpdate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->updateDocument((int)$id, $_POST) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Document updated' : ($result['error'] ?? 'Update failed'));
        $this->redirect('/admin/legal/documents/' . $id);
    }

    public function documentUpdateStatus($id, $status)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $allowed = ['draft', 'final', 'signed', 'expired', 'cancelled', 'archived'];
        if (!in_array($status, $allowed)) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('/admin/legal/documents/' . $id);
            return;
        }
        $result = $this->docService ? $this->docService->updateDocumentStatus((int)$id, $status) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Status updated to ' . $status : ($result['error'] ?? 'Update failed'));
        $this->redirect('/admin/legal/documents/' . $id);
    }

    public function documentDelete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->deleteDocument((int)$id) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Document archived' : ($result['error'] ?? 'Delete failed'));
        $this->redirect('/admin/legal/documents');
    }

    public function documentMarkOnline($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->markSubmittedOnline((int)$id) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Marked as submitted online' : ($result['error'] ?? 'Failed'));
        $this->redirect('/admin/legal/documents/' . $id);
    }

    public function documentMarkPhysical($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->markSubmittedPhysically((int)$id) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Marked as submitted physically' : ($result['error'] ?? 'Failed'));
        $this->redirect('/admin/legal/documents/' . $id);
    }

    public function documentKycVerify($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        $result = $this->docService ? $this->docService->markKycVerified((int)$id, $adminId) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'KYC verified successfully' : ($result['error'] ?? 'Failed'));
        $this->redirect('/admin/legal/documents/' . $id);
    }

    // ===================== UPLOADS =====================

    public function uploadVerify($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $status = $_POST['status'] ?? 'verified';
        $reason = $_POST['rejection_reason'] ?? null;
        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        $result = $this->docService ? $this->docService->verifyUpload((int)$id, $adminId, $status, $reason) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Upload ' . $status : ($result['error'] ?? 'Failed'));
        $this->redirectToReferrer('/admin/legal/documents');
    }

    public function uploadDelete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->deleteUpload((int)$id) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Upload deleted' : ($result['error'] ?? 'Delete failed'));
        $this->redirectToReferrer('/admin/legal/documents');
    }

    // ===================== AI DOCUMENT GENERATION =====================

    public function aiComposer()
    {
        $this->requireAdmin();
        $this->render('admin/legal/ai_composer', [
            'page_title' => 'AI Document Composer',
            'prompts' => $this->docService ? $this->docService->getAiPrompts() : [],
            'categories' => $this->docService ? $this->docService->getCategories() : [],
            'customers' => $this->docService ? $this->docService->getCustomers() : [],
            'merge_fields' => $this->docService ? $this->docService->getAvailableMergeFields() : [],
        ]);
    }

    public function aiGenerate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $promptId = (int)($_POST['prompt_id'] ?? 0);
        $mergeData = $_POST;
        if ($promptId > 0) {
            $result = $this->aiService ? $this->aiService->generateDocument($promptId, $mergeData) : ['success' => false, 'error' => 'AI service unavailable'];
        } else {
            $customPrompt = $_POST['custom_prompt'] ?? '';
            if (empty($customPrompt)) {
                $this->setFlash('error', 'Please select a prompt template or enter a custom prompt');
                $this->redirect('/admin/legal/ai-composer');
                return;
            }
            $temp = (float)($_POST['temperature'] ?? 0.30);
            $tokens = (int)($_POST['max_tokens'] ?? 2048);
            $result = $this->aiService ? $this->aiService->generateFromCustomPrompt($customPrompt, $mergeData, $temp, $tokens) : ['success' => false, 'error' => 'AI service unavailable'];
        }

        if (!$result['success']) {
            $this->setFlash('error', $result['error'] ?? 'AI generation failed');
            $this->redirect('/admin/legal/ai-composer');
            return;
        }

        $title = $result['title'] ?? 'AI Generated Document';
        $content = $result['content'] ?? '';
        $entityType = $_POST['entity_type'] ?? 'general';
        $entityId = !empty($_POST['entity_id']) ? (int)$_POST['entity_id'] : null;
        $customerId = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;

        $docResult = $this->docService ? $this->docService->createDocument([
            'template_id' => null,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'customer_id' => $customerId,
            'title' => $title,
            'content' => $content,
            'status' => 'draft',
            'created_by' => $_SESSION['admin_id'] ?? null,
        ]) : ['success' => false, 'error' => 'Service unavailable'];

        if ($docResult['success']) {
            $this->setFlash('success', 'AI document generated: ' . ($docResult['document_number'] ?? ''));
            $this->redirect('/admin/legal/documents/' . $docResult['id']);
        } else {
            $this->setFlash('error', 'Document generated but could not be saved: ' . ($docResult['error'] ?? ''));
            $this->redirect('/admin/legal/ai-composer');
        }
    }

    // ===================== AI PROMPT MANAGEMENT =====================

    public function aiPrompts()
    {
        $this->requireAdmin();
        $prompts = $this->docService ? $this->docService->getAiPrompts() : [];
        $this->render('admin/legal/ai_prompts', [
            'page_title' => 'AI Prompt Templates',
            'prompts' => $prompts,
        ]);
    }

    public function aiPromptCreate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = $_POST;
        $data['created_by'] = $_SESSION['admin_id'] ?? null;
        $result = $this->docService ? $this->docService->createAiPrompt($data) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Prompt created' : ($result['error'] ?? 'Failed'));
        $this->redirect('/admin/legal/ai-prompts');
    }

    public function aiPromptUpdate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->updateAiPrompt((int)$id, $_POST) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Prompt updated' : ($result['error'] ?? 'Update failed'));
        $this->redirect('/admin/legal/ai-prompts');
    }

    public function aiPromptDelete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->docService ? $this->docService->deleteAiPrompt((int)$id) : ['success' => false, 'error' => 'Service unavailable'];
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Prompt deleted' : ($result['error'] ?? 'Delete failed'));
        $this->redirect('/admin/legal/ai-prompts');
    }

    // ===================== RENDER / PREVIEW =====================

    public function documentPreview($id)
    {
        $this->requireAdmin();
        $doc = $this->docService ? $this->docService->getDocumentById((int)$id) : null;
        if (!$doc) {
            $this->setFlash('error', 'Document not found');
            $this->redirect('/admin/legal/documents');
            return;
        }
        $this->render('admin/legal/document_preview', [
            'page_title' => 'Preview: ' . ($doc['document_number'] ?? $doc['title']),
            'doc' => $doc,
        ]);
    }

    protected function redirectToReferrer(string $default): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? $default;
        $this->redirect($ref);
    }
}
