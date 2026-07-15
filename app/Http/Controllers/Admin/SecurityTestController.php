<?php

namespace App\Http\Controllers\Admin;

use App\Services\Security\SecurityTestSuite;

class SecurityTestController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();

        $lastRun = null;
        $results = null;
        $overallScore = 0;
        $recommendations = [];

        try {
            $row = $this->db->fetch("SELECT results, overall_score, created_at FROM security_test_runs ORDER BY id DESC LIMIT 1");
            if ($row) {
                $results = json_decode($row['results'], true);
                $overallScore = (int) ($row['overall_score'] ?? 0);
                $lastRun = $row['created_at'];
                $suite = new SecurityTestSuite();
                $recommendations = $suite->getRecommendations($results);
            }
        } catch (\Exception $e) {
            error_log('SecurityTest index error: ' . $e->getMessage());
        }

        return $this->render('admin/security-test/index', [
            'page_title'     => 'Security Test Suite',
            'results'        => $results,
            'overall_score'  => $overallScore,
            'last_run'       => $lastRun,
            'recommendations' => $recommendations,
        ]);
    }

    public function runTests()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/security-test');
            return;
        }

        $suite = new SecurityTestSuite();
        $results = $suite->runAllTests();
        $overallScore = $suite->getOverallScore($results);

        try {
            $this->db->execute(
                "INSERT INTO security_test_runs (results, overall_score, created_by, created_at) VALUES (?, ?, ?, NOW())",
                [json_encode($results), $overallScore, $_SESSION['admin_id'] ?? null]
            );
        } catch (\Exception $e) {
            try {
                $this->db->execute("CREATE TABLE IF NOT EXISTS security_test_runs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    results JSON NOT NULL,
                    overall_score INT NOT NULL DEFAULT 0,
                    created_by INT NULL,
                    created_at DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $this->db->execute(
                    "INSERT INTO security_test_runs (results, overall_score, created_by, created_at) VALUES (?, ?, ?, NOW())",
                    [json_encode($results), $overallScore, $_SESSION['admin_id'] ?? null]
                );
            } catch (\Exception $e2) {
                error_log('SecurityTest runTests DB error: ' . $e2->getMessage());
            }
        }

        $this->setFlash('success', 'Security tests completed. Overall score: ' . $overallScore . '/100');
        $this->redirect('/admin/security-test');
    }

    public function report()
    {
        $this->requireAdmin();

        $results = null;
        $overallScore = 0;

        try {
            $row = $this->db->fetch("SELECT results, overall_score FROM security_test_runs ORDER BY id DESC LIMIT 1");
            if ($row) {
                $results = json_decode($row['results'], true);
                $overallScore = (int) ($row['overall_score'] ?? 0);
            }
        } catch (\Exception $e) {
            error_log('SecurityTest report error: ' . $e->getMessage());
        }

        if (!$results) {
            $this->setFlash('error', 'No test results found. Run tests first.');
            $this->redirect('/admin/security-test');
            return;
        }

        $suite = new SecurityTestSuite();
        $html = $suite->generateReport($results);

        echo $html;
        exit;
    }
}
