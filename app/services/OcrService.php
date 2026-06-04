<?php
namespace App\Services;

use PDO;

/**
 * OcrService - document OCR + classification + report execution engine
 */
class OcrService
{
    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function classifyDocument(int $documentId, string $category, float $confidence = 0.0, array $metadata = []): array
    {
        $st = $this->db->prepare("INSERT INTO document_classification (document_id, category, confidence, metadata, classified_at) VALUES (:d, :c, :co, :m, NOW())");
        $st->execute([':d' => $documentId, ':c' => $category, ':co' => $confidence, ':m' => json_encode($metadata, JSON_UNESCAPED_UNICODE)]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function autoClassify(int $documentId, string $fileName, string $content = ''): array
    {
        $lower = strtolower($fileName . ' ' . $content);
        $category = 'unknown'; $confidence = 0.5;

        $map = [
            'aadhar|aadhaar|uid' => ['identity', 0.95],
            'pan|pan card' => ['identity', 0.95],
            'passport' => ['identity', 0.95],
            'license|driving' => ['identity', 0.9],
            'agreement|contract' => ['legal', 0.9],
            'invoice|bill' => ['financial', 0.9],
            'receipt' => ['financial', 0.85],
            'salary|payslip' => ['financial', 0.85],
            'bank|statement' => ['financial', 0.9],
            'property|deed|registry' => ['property', 0.9],
            'rera' => ['property', 0.95],
            'tax|gst' => ['tax', 0.9],
            'report|analysis' => ['report', 0.8],
        ];

        foreach ($map as $pattern => $cat) {
            if (preg_match('/' . $pattern . '/i', $lower)) {
                $category = $cat[0];
                $confidence = $cat[1];
                break;
            }
        }

        return $this->classifyDocument($documentId, $category, $confidence, ['file_name' => $fileName, 'method' => 'pattern_matching']);
    }

    public function listClassifications(string $category = ''): array
    {
        $sql = "SELECT * FROM document_classification WHERE 1=1";
        $params = [];
        if ($category) { $sql .= " AND category = :c"; $params[':c'] = $category; }
        $sql .= " ORDER BY classified_at DESC LIMIT 200";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function processOcr(int $documentId, string $extractedText, array $fields = []): array
    {
        $st = $this->db->prepare("INSERT INTO ocr_documents (document_id, extracted_text, status, processed_at, created_at) VALUES (:d, :t, 'completed', NOW(), NOW())");
        $st->execute([':d' => $documentId, ':t' => $extractedText]);
        $ocrId = (int)$this->db->lastInsertId();

        foreach ($fields as $fieldName => $fieldValue) {
            $st2 = $this->db->prepare("INSERT INTO ocr_extracted_fields (ocr_document_id, field_name, field_value, confidence, created_at) VALUES (:o, :n, :v, 1.0, NOW())");
            $st2->execute([':o' => $ocrId, ':n' => $fieldName, ':v' => $fieldValue]);
        }

        return ['ok' => true, 'ocr_id' => $ocrId, 'fields_extracted' => count($fields)];
    }

    public function getOcrDocument(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM ocr_documents WHERE id = :id");
        $st->execute([':id' => $id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;
        $st2 = $this->db->prepare("SELECT * FROM ocr_extracted_fields WHERE ocr_document_id = :o");
        $st2->execute([':o' => $id]);
        $r['fields'] = $st2->fetchAll(PDO::FETCH_ASSOC);
        return $r;
    }

    public function listOcrDocuments(int $limit = 100): array
    {
        $st = $this->db->prepare("SELECT * FROM ocr_documents ORDER BY created_at DESC LIMIT :lim");
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listTemplates(string $category = ''): array
    {
        $sql = "SELECT * FROM ocr_templates WHERE active = 1";
        $params = [];
        if ($category) { $sql .= " AND category = :c"; $params[':c'] = $category; }
        $sql .= " ORDER BY name";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveTemplate(string $name, string $category, array $fields, string $regex = ''): array
    {
        $st = $this->db->prepare("INSERT INTO ocr_templates (name, category, fields, regex_pattern, active, created_at) VALUES (:n, :c, :f, :r, 1, NOW())
                                  ON DUPLICATE KEY UPDATE category = VALUES(category), fields = VALUES(fields), regex_pattern = VALUES(regex_pattern), active = 1");
        $st->execute([':n' => $name, ':c' => $category, ':f' => json_encode($fields, JSON_UNESCAPED_UNICODE), ':r' => $regex]);
        return ['ok' => true];
    }

    public function executeReport(int $userId, string $reportType, array $params, string $format = 'json'): array
    {
        $startTime = microtime(true);
        $st = $this->db->prepare("INSERT INTO report_executions (user_id, report_type, parameters, status, started_at) VALUES (:u, :t, :p, 'running', NOW())");
        $st->execute([':u' => $userId, ':t' => $reportType, ':p' => json_encode($params, JSON_UNESCAPED_UNICODE)]);
        $execId = (int)$this->db->lastInsertId();

        try {
            $data = $this->generateReportData($reportType, $params);
            $duration = (int)((microtime(true) - $startTime) * 1000);
            $st2 = $this->db->prepare("UPDATE report_executions SET status = 'completed', result_data = :d, duration_ms = :ms, completed_at = NOW(), output_format = :f WHERE id = :id");
            $st2->execute([':d' => json_encode($data, JSON_UNESCAPED_UNICODE), ':ms' => $duration, ':f' => $format, ':id' => $execId]);
            return ['ok' => true, 'id' => $execId, 'data' => $data, 'duration_ms' => $duration];
        } catch (\Throwable $e) {
            $st2 = $this->db->prepare("UPDATE report_executions SET status = 'failed', error_message = :e, completed_at = NOW() WHERE id = :id");
            $st2->execute([':e' => $e->getMessage(), ':id' => $execId]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function generateReportData(string $type, array $params): array
    {
        switch ($type) {
            case 'leads':
                $st = $this->db->query("SELECT status, COUNT(*) as count FROM leads WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY status");
                return ['leads_by_status' => $st->fetchAll(PDO::FETCH_ASSOC)];
            case 'sales':
                $st = $this->db->query("SELECT DATE(created_at) as day, SUM(amount) as total FROM payments WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day");
                return ['sales_trend' => $st->fetchAll(PDO::FETCH_ASSOC)];
            case 'plots':
                $st = $this->db->query("SELECT status, COUNT(*) as count FROM plots GROUP BY status");
                return ['plots_by_status' => $st->fetchAll(PDO::FETCH_ASSOC)];
            case 'agents':
                $st = $this->db->query("SELECT u.id, u.name, COUNT(b.id) as bookings, COALESCE(SUM(b.amount), 0) as revenue FROM users u LEFT JOIN bookings b ON b.agent_id = u.id WHERE u.role = 'agent' GROUP BY u.id, u.name ORDER BY revenue DESC LIMIT 20");
                return ['agent_performance' => $st->fetchAll(PDO::FETCH_ASSOC)];
            case 'associates':
                $st = $this->db->query("SELECT u.id, u.name, COALESCE(SUM(c.amount), 0) as commission FROM users u LEFT JOIN hybrid_commission_records c ON c.agent_id = u.id WHERE u.role = 'associate' GROUP BY u.id, u.name ORDER BY commission DESC LIMIT 20");
                return ['associate_performance' => $st->fetchAll(PDO::FETCH_ASSOC)];
            case 'colonies':
                $st = $this->db->query("SELECT c.name, COUNT(p.id) as plot_count, COALESCE(SUM(p.total_price), 0) as total_value FROM colonies c LEFT JOIN plots p ON p.colony_id = c.id GROUP BY c.id, c.name ORDER BY total_value DESC");
                return ['colony_performance' => $st->fetchAll(PDO::FETCH_ASSOC)];
            case 'financial':
                $st = $this->db->query("SELECT
                    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)) as revenue_30d,
                    (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)) as expenses_30d,
                    (SELECT COUNT(*) FROM bookings WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)) as bookings_30d");
                return $st->fetch(PDO::FETCH_ASSOC) ?: [];
            default:
                return ['info' => 'Unknown report type: ' . $type, 'params' => $params];
        }
    }

    public function listExecutions(int $userId = 0, int $limit = 50): array
    {
        $sql = "SELECT * FROM report_executions WHERE 1=1";
        $params = [];
        if ($userId) { $sql .= " AND user_id = :u"; $params[':u'] = $userId; }
        $sql .= " ORDER BY started_at DESC LIMIT :lim";
        $st = $this->db->prepare($sql);
        foreach ($params as $k => $v) $st->bindValue($k, $v);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
