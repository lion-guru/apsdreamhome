<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

/**
 * Public legal document portal (/legal)
 * Lists published policies/terms, shows details, tracks acceptance.
 */
class LegalDocumentController extends BaseController
{
    public const CATEGORIES = [
        'company' => 'Company Policies',
        'associate' => 'Associate/MLM',
        'agent' => 'Agent/Sales',
        'booking' => 'Booking & Reservations',
        'general' => 'General',
    ];

    /**
     * List all published legal documents, optionally filtered by category/slug.
     */
    public function index($category = null)
    {
        $docs = [];
        try {
            if ($category !== null && $category !== '') {
                // Treat as slug first (footer links point to /legal/{slug});
                // fall back to category grouping when it matches a known category.
                $stmt = $this->db->prepare(
                    "SELECT id, slug, title, category, document_type, summary, version, is_mandatory, published_at
                     FROM legal_documents
                     WHERE slug = ? AND status = 'published' AND deleted_at IS NULL
                     LIMIT 1"
                );
                $stmt->execute([$category]);
                $doc = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($doc) {
                    return $this->show($category);
                }

                $stmt = $this->db->prepare(
                    "SELECT id, slug, title, category, document_type, summary, version, is_mandatory, published_at
                     FROM legal_documents
                     WHERE category = ? AND status = 'published' AND deleted_at IS NULL
                     ORDER BY document_type, title"
                );
                $stmt->execute([$category]);
            } else {
                $stmt = $this->db->query(
                    "SELECT id, slug, title, category, document_type, summary, version, is_mandatory, published_at
                     FROM legal_documents
                     WHERE status = 'published' AND deleted_at IS NULL
                     ORDER BY category, document_type, title"
                );
            }

            $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
            foreach ($rows as $row) {
                $cat = $row['category'] ?: 'general';
                $docs[$cat][] = $row;
            }
        } catch (\Exception $e) {
            error_log('LegalDocumentController@index: ' . $e->getMessage());
        }

        $this->render('legal/index', [
            'page_title' => __('legal_documentation') ?: 'Legal Documentation',
            'documents' => $docs,
            'categories' => self::CATEGORIES,
            'active_category' => $category,
        ]);
    }

    /**
     * Display a single legal document by slug.
     */
    public function show($slug)
    {
        $document = null;
        $related = [];
        $accepted = false;

        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM legal_documents
                 WHERE slug = ? AND status = 'published' AND deleted_at IS NULL
                 LIMIT 1"
            );
            $stmt->execute([$slug]);
            $document = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('LegalDocumentController@show: ' . $e->getMessage());
        }

        if (!$document) {
            $this->render('errors/404', ['page_title' => 'Page Not Found']);
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT id, slug, title, category, document_type FROM legal_documents
                 WHERE category = ? AND id != ? AND status = 'published' AND deleted_at IS NULL
                 ORDER BY published_at DESC LIMIT 5"
            );
            $stmt->execute([$document['category'], $document['id']]);
            $related = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $userId = (int)($_SESSION['user_id'] ?? 0);
            if ($userId > 0) {
                $stmt = $this->db->prepare(
                    "SELECT COUNT(*) AS c FROM legal_document_acceptances
                     WHERE legal_document_id = ? AND user_id = ? AND version = ?"
                );
                $stmt->execute([$document['id'], $userId, $document['version'] ?? '']);
                $accepted = ((int)$stmt->fetch(\PDO::FETCH_ASSOC)['c']) > 0;
            }
        } catch (\Exception $e) {
            error_log('LegalDocumentController@show related/acceptance: ' . $e->getMessage());
        }

        $this->render('legal/show', [
            'page_title' => $document['title'],
            'document' => $document,
            'accepted' => $accepted,
            'related' => $related,
        ]);
    }

    /**
     * Accept a mandatory legal document (AJAX POST).
     */
    public function accept()
    {
        header('Content-Type: application/json');

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $documentId = (int)($_POST['document_id'] ?? 0);
        if ($documentId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'document_id is required']);
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT id, title, version, is_mandatory FROM legal_documents
                 WHERE id = ? AND status = 'published' AND deleted_at IS NULL LIMIT 1"
            );
            $stmt->execute([$documentId]);
            $document = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$document) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Document not found.']);
                return;
            }

            if (!(int)$document['is_mandatory']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'This document is not mandatory.']);
                return;
            }

            $stmt = $this->db->prepare(
                "SELECT COUNT(*) AS c FROM legal_document_acceptances
                 WHERE legal_document_id = ? AND user_id = ? AND version = ?"
            );
            $stmt->execute([$documentId, $userId, $document['version'] ?? '']);
            if (((int)$stmt->fetch(\PDO::FETCH_ASSOC)['c']) > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Document already accepted.',
                    'redirect' => $_POST['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? BASE_URL),
                ]);
                return;
            }

            $stmt = $this->db->prepare(
                "INSERT INTO legal_document_acceptances
                    (legal_document_id, user_id, user_type, ip_address, user_agent, version, created_at)
                 VALUES (?, ?, 'user', ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $documentId,
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? '',
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                $document['version'] ?? '',
            ]);

            error_log("Legal document #{$documentId} accepted by user #{$userId}");

            echo json_encode([
                'success' => true,
                'message' => 'Document accepted successfully.',
                'redirect' => $_POST['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? BASE_URL),
            ]);
        } catch (\Exception $e) {
            error_log('LegalDocumentController@accept: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to record acceptance.']);
        }
    }

    /**
     * Unaccepted mandatory documents for current user (JSON, for middleware/prompt).
     */
    public function getUnaccepted()
    {
        header('Content-Type: application/json');

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            echo json_encode([]);
            return;
        }

        $out = [];
        try {
            $stmt = $this->db->prepare(
                "SELECT d.id, d.slug, d.title, d.category, d.document_type, d.version, d.file_path
                 FROM legal_documents d
                 LEFT JOIN legal_document_acceptances a
                    ON a.legal_document_id = d.id AND a.user_id = ? AND a.version = d.version
                 WHERE d.status = 'published' AND d.deleted_at IS NULL AND d.is_mandatory = 1
                   AND a.id IS NULL"
            );
            $stmt->execute([$userId]);
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $doc) {
                $out[] = [
                    'id' => (int)$doc['id'],
                    'slug' => $doc['slug'],
                    'title' => $doc['title'],
                    'category' => $doc['category'],
                    'document_type' => $doc['document_type'],
                    'version' => $doc['version'],
                    'url' => BASE_URL . '/legal/' . $doc['slug'],
                ];
            }
        } catch (\Exception $e) {
            error_log('LegalDocumentController@getUnaccepted: ' . $e->getMessage());
        }

        echo json_encode($out);
    }
}
