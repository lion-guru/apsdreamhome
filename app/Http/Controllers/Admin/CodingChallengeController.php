<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;
use Exception;

class CodingChallengeController extends AdminController
{
    use TenantAwareTrait;

    public function index()
    {
        $this->requireAdmin();
        try {
            $tid = $this->tenantId();
            $where = '';
            $params = [];
            if ($tid > 1) {
                $where = 'WHERE tenant_id = ?';
                $params = [$tid];
            }

            $difficulty = $_GET['difficulty'] ?? '';
            if ($difficulty && in_array($difficulty, ['easy', 'medium', 'hard'])) {
                $where .= ($where ? ' AND ' : 'WHERE ') . 'difficulty = ?';
                $params[] = $difficulty;
            }

            $search = trim($_GET['search'] ?? '');
            if ($search !== '') {
                $where .= ($where ? ' AND ' : 'WHERE ') . '(title LIKE ? OR slug LIKE ?)';
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;

            $stmt = Database::getInstance()->getConnection()->prepare("
                SELECT * FROM coding_challenges $where ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}
            ");
            $stmt->execute($params);
            $challenges = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $countStmt = Database::getInstance()->getConnection()->prepare("SELECT COUNT(*) FROM coding_challenges $where");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $totalPages = max(1, (int)ceil($total / $perPage));

            $stats = [
                'total' => 0,
                'easy' => 0,
                'medium' => 0,
                'hard' => 0,
            ];
            $stmt = Database::getInstance()->getConnection()->query("SELECT difficulty, COUNT(*) as cnt FROM coding_challenges GROUP BY difficulty");
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $stats[$row['difficulty']] = (int)$row['cnt'];
                $stats['total'] += (int)$row['cnt'];
            }

            $this->render('admin/coding-challenges/index', [
                'page_title' => 'Coding Challenges',
                'challenges' => $challenges,
                'stats' => $stats,
                'search' => $search,
                'difficulty' => $difficulty,
                'page' => $page,
                'totalPages' => $totalPages,
                'totalRows' => $total,
                'success' => $this->getFlash('success'),
                'error' => $this->getFlash('error'),
            ]);
        } catch (Exception $e) {
            error_log("CodingChallengeController::index error: " . $e->getMessage());
            $this->render('admin/coding-challenges/index', [
                'page_title' => 'Coding Challenges',
                'challenges' => [],
                'stats' => ['total' => 0, 'easy' => 0, 'medium' => 0, 'hard' => 0],
                'search' => '',
                'difficulty' => '',
                'page' => 1,
                'totalPages' => 1,
                'totalRows' => 0,
                'success' => null,
                'error' => 'Failed to load challenges',
            ]);
        }
    }

    public function create()
    {
        $this->requireAdmin();
        $this->render('admin/coding-challenges/form', [
            'page_title' => 'Create Challenge',
            'challenge' => null,
            'categories' => ['arrays', 'strings', 'stack', 'search', 'dynamic-programming', 'graph', 'tree', 'math', 'greedy', 'bit-manipulation', 'other'],
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        try {
            $data = $_POST;
            $required = ['title', 'slug', 'difficulty', 'category', 'problem_statement'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->setFlash('error', "Missing required field: {$field}");
                    $this->redirect(BASE_URL . '/admin/coding-challenges/create');
                    return;
                }
            }

            $tid = $this->tenantId();
            $stmt = Database::getInstance()->getConnection()->prepare("
                INSERT INTO coding_challenges (title, slug, description, difficulty, category, problem_statement, input_format, output_format, constraints, sample_input, sample_output, explanation, starter_code, solution_code, time_limit_seconds, memory_limit_mb, points, is_active, created_by, tenant_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['title'], $data['slug'], $data['description'] ?? '', $data['difficulty'], $data['category'],
                $data['problem_statement'], $data['input_format'] ?? '', $data['output_format'] ?? '',
                $data['constraints'] ?? '', $data['sample_input'] ?? '', $data['sample_output'] ?? '',
                $data['explanation'] ?? '', $data['starter_code'] ?? '', $data['solution_code'] ?? '',
                (int)($data['time_limit_seconds'] ?? 2), (int)($data['memory_limit_mb'] ?? 256), (int)($data['points'] ?? 10),
                (int)($data['is_active'] ?? 1), $this->getUserId(), $tid
            ]);

            $this->setFlash('success', 'Challenge created successfully');
            $this->redirect(BASE_URL . '/admin/coding-challenges');
        } catch (Exception $e) {
            error_log("CodingChallengeController::store error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to create challenge');
            $this->redirect(BASE_URL . '/admin/coding-challenges/create');
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        try {
            $tid = $this->tenantId();
            $where = 'id = ?';
            $params = [$id];
            if ($tid > 1) {
                $where .= ' AND tenant_id = ?';
                $params[] = $tid;
            }

            $stmt = Database::getInstance()->getConnection()->prepare("SELECT * FROM coding_challenges WHERE $where");
            $stmt->execute($params);
            $challenge = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$challenge) {
                $this->setFlash('error', 'Challenge not found');
                $this->redirect(BASE_URL . '/admin/coding-challenges');
                return;
            }

            $this->render('admin/coding-challenges/form', [
                'page_title' => 'Edit Challenge',
                'challenge' => $challenge,
                'categories' => ['arrays', 'strings', 'stack', 'search', 'dynamic-programming', 'graph', 'tree', 'math', 'greedy', 'bit-manipulation', 'other'],
            ]);
        } catch (Exception $e) {
            error_log("CodingChallengeController::edit error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load challenge');
            $this->redirect(BASE_URL . '/admin/coding-challenges');
        }
    }

    public function update($id)
    {
        $this->requireAdmin();
        try {
            $tid = $this->tenantId();
            $where = 'id = ?';
            $params = [$id];
            if ($tid > 1) {
                $where .= ' AND tenant_id = ?';
                $params[] = $tid;
            }

            $data = $_POST;
            $fields = ['title', 'slug', 'description', 'difficulty', 'category', 'problem_statement', 'input_format', 'output_format', 'constraints', 'sample_input', 'sample_output', 'explanation', 'starter_code', 'solution_code', 'time_limit_seconds', 'memory_limit_mb', 'points', 'is_active'];
            $setParts = [];
            $values = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $setParts[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }
            $values[] = $id;
            if ($tid > 1) $values[] = $tid;

            $sql = "UPDATE coding_challenges SET " . implode(', ', $setParts) . " WHERE $where";
            $stmt = Database::getInstance()->getConnection()->prepare($sql);
            $stmt->execute($values);

            $this->setFlash('success', 'Challenge updated successfully');
            $this->redirect(BASE_URL . '/admin/coding-challenges');
        } catch (Exception $e) {
            error_log("CodingChallengeController::update error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update challenge');
            $this->redirect(BASE_URL . '/admin/coding-challenges/edit/' . $id);
        }
    }

    public function destroy($id)
    {
        $this->requireAdmin();
        try {
            $tid = $this->tenantId();
            $where = 'id = ?';
            $params = [$id];
            if ($tid > 1) {
                $where .= ' AND tenant_id = ?';
                $params[] = $tid;
            }

            $stmt = Database::getInstance()->getConnection()->prepare("DELETE FROM coding_challenges WHERE $where");
            $stmt->execute($params);

            $this->setFlash('success', 'Challenge deleted successfully');
            $this->redirect(BASE_URL . '/admin/coding-challenges');
        } catch (Exception $e) {
            error_log("CodingChallengeController::destroy error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to delete challenge');
            $this->redirect(BASE_URL . '/admin/coding-challenges');
        }
    }
}