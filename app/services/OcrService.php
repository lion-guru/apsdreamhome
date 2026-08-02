<?php
namespace App\Services;

use PDO;

class OcrService
{
    private $db;
    private $pdo;
    private $tenantId;

    private const DOC_TYPE_PATTERNS = [
        'aadhaar' => [
            'name' => '/(?:Name|नाम)\s*[:\-]?\s*([A-Z][A-Za-z\s\.]{2,60})/i',
            'aadhaar_number' => '/\b(\d{4}\s?\d{4}\s?\d{4})\b/',
            'dob' => '/(?:DOB|Date of Birth|जन्म तिथि)\s*[:\-]?\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
            'gender' => '/(?:Gender|Sex|लिंग)\s*[:\-]?\s*(Male|Female|पुरुष|महिला|Other)/i',
            'address' => '/(?:Address|पता)\s*[:\-]?\s*(.{10,200})/i',
        ],
        'pan' => [
            'name' => '/(?:Name|नाम)\s*[:\-]?\s*([A-Z][A-Za-z\s\.]{2,60})/i',
            'pan_number' => '/\b([A-Z]{5}\d{4}[A-Z])\b/',
            'father_name' => '/(?:Father(?:\'s)? Name|पिता का नाम)\s*[:\-]?\s*([A-Z][A-Za-z\s\.]{2,60})/i',
            'dob' => '/(?:DOB|Date of Birth|जन्म तिथि)\s*[:\-]?\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
        ],
        'passport' => [
            'name' => '/(?:Name|नाम)\s*[:\-]?\s*([A-Z][A-Za-z\s\.]{2,60})/i',
            'passport_number' => '/\b([A-Z]\d{7})\b/',
            'dob' => '/(?:DOB|Date of Birth|जन्म तिथि)\s*[:\-]?\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
            'nationality' => '/(?:Nationality|राष्ट्रीयता)\s*[:\-]?\s*(Indian|Other)/i',
            'issue_date' => '/(?:Date of Issue|जारी तिथि)\s*[:\-]?\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
            'expiry_date' => '/(?:Date of Expiry|वैधता समाप्ति)\s*[:\-]?\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
        ],
        'driving_license' => [
            'name' => '/(?:Name|नाम)\s*[:\-]?\s*([A-Z][A-Za-z\s\.]{2,60})/i',
            'dl_number' => '/\b([A-Z]{2}\s?\d{2}\s?\d{4}\s?\d{7})\b/',
            'dob' => '/(?:DOB|Date of Birth|जन्म तिथि)\s*[:\-]?\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
            'valid_upto' => '/(?:Valid Upto|वैध तक)\s*[:\-]?\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
            'vehicle_class' => '/(?:Class of Vehicle|वाहन श्रेणी)\s*[:\-]?\s*([A-Z\d\-\s,]+)/i',
        ],
        'cheque' => [
            'bank_name' => '/(?:Bank|बैंक)\s*[:\-]?\s*([A-Z][A-Za-z\s&]{2,60})/i',
            'account_number' => '/(?:A\/C|Account)\s*(?:No\.?|Number)\s*[:\-]?\s*(\d{6,18})\b/i',
            'ifsc_code' => '/\b([A-Z]{4}0[A-Z0-9]{6})\b/',
            'cheque_number' => '/(?:Cheque|Check)\s*(?:No\.?|Number)\s*[:\-]?\s*(\d{6})\b/i',
            'amount' => '/(?:₹|Rs\.?|INR)\s*([\d,]+(?:\.\d{2})?)/i',
            'payee_name' => '/(?:Pay\s*)?([A-Z][A-Za-z\s\.]{2,60}?)(?:\s+(?:Rupees|only|----))/i',
        ],
        'invoice' => [
            'invoice_number' => '/(?:Invoice|Bill)\s*(?:No\.?|Number|#)\s*[:\-]?\s*([A-Z\/\d\-]{3,30})/i',
            'date' => '/(?:Invoice|Bill)\s*Date\s*[:\-]?\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
            'vendor_name' => '/(?:From|Vendor|Supplier|Billed By)\s*[:\-]?\s*([A-Z][A-Za-z\s&\.]{2,60})/i',
            'vendor_gstin' => '/\b(\d{2}[A-Z]{5}\d{4}[A-Z]\d[Z][A-Z\d])\b/',
            'total_amount' => '/(?:Total|Grand Total|Amount Payable)\s*[:\-]?\s*(?:₹|Rs\.?)?\s*([\d,]+(?:\.\d{2})?)/i',
            'tax_amount' => '/(?:GST|Tax|CGST|SGST|IGST)\s*(?:Amount)?\s*[:\-]?\s*(?:₹|Rs\.?)?\s*([\d,]+(?:\.\d{2})?)/i',
        ],
        'contract' => [
            'parties' => '/(?:BETWEEN|Party|Parties)\s*[:\-]?\s*(.{5,150}?)(?:\s+(?:AND|hereinafter))/i',
            'property_details' => '/(?:Property|Land|Plot)\s*(?:Details?|Description|Schedule)?\s*[:\-]?\s*(.{10,200}?)(?:\s+(?:hereinafter|now|WHEREAS))/i',
            'agreement_date' => '/(?:Dated|Agreement Date|Date)\s*[:\-]?\s*(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})/i',
            'stamp_duty' => '/(?:Stamp Duty|Court Fee)\s*[:\-]?\s*(?:₹|Rs\.?)?\s*([\d,]+(?:\.\d{2})?)/i',
        ],
    ];

    private const DOC_TYPE_LABELS = [
        'aadhaar' => 'Aadhaar Card',
        'pan' => 'PAN Card',
        'passport' => 'Passport',
        'driving_license' => 'Driving License',
        'cheque' => 'Cheque',
        'invoice' => 'Invoice',
        'contract' => 'Contract',
    ];

    public function __construct($db)
    {
        $this->db = $db;
        if (is_object($db) && method_exists($db, 'getPdo')) {
            $this->pdo = $db->getPdo();
        } elseif ($db instanceof PDO) {
            $this->pdo = $db;
        } else {
            $this->pdo = $db;
        }
        $this->tenantId = (int)(\App\Core\Middleware\TenantContext::getId() ?? 0);
    }

    private function getTenantId(): int
    {
        return $this->tenantId;
    }

    private function _tWhere(string $alias = 'd'): string
    {
        return $this->tenantId > 1 ? " AND $alias.tenant_id = {$this->tenantId}" : "";
    }

    public function getDocTypeLabel(string $type): string
    {
        return self::DOC_TYPE_LABELS[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    public function getDocTypes(): array
    {
        return array_keys(self::DOC_TYPE_PATTERNS);
    }

    public function getStats(): array
    {
        $default = ['total' => 0, 'pending' => 0, 'processing' => 0, 'completed' => 0, 'failed' => 0, 'valid' => 0, 'invalid' => 0];
        try {
            $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN ocr_status = 'pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN ocr_status = 'processing' THEN 1 ELSE 0 END) AS processing,
                    SUM(CASE WHEN ocr_status = 'completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN ocr_status = 'failed' THEN 1 ELSE 0 END) AS failed,
                    SUM(CASE WHEN validation_status = 'valid' THEN 1 ELSE 0 END) AS valid,
                    SUM(CASE WHEN validation_status = 'invalid' THEN 1 ELSE 0 END) AS `invalid`
                FROM ocr_documents" . $this->_tWhere();
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            foreach ($default as $k => $v) {
                $default[$k] = (int)($row[$k] ?? $v);
            }
        } catch (\Throwable $e) { error_log($e->getMessage()); }
        return $default;
    }

    public function uploadDocument(array $file, int $userId, string $documentType): array
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxSize = 10 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload error code: ' . $file['error']];
        }
        if ($file['size'] > $maxSize) {
            return ['ok' => false, 'error' => 'File exceeds 10MB limit.'];
        }
        if (!in_array($file['type'], $allowedMimes)) {
            return ['ok' => false, 'error' => 'Only JPG, PNG, and PDF files are allowed.'];
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/apsdreamhome/uploads/ocr/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeName = 'ocr_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = $uploadDir . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['ok' => false, 'error' => 'Failed to save file to disk.'];
        }

        $relativePath = '/uploads/ocr/' . $safeName;

        try {
            $tid = $this->getTenantId();
            $stmt = $this->db->prepare("
                INSERT INTO ocr_documents (document_type, file_path, file_name, original_name, file_size, mime_type, ocr_status, validation_status, uploaded_by, tenant_id, created_at)
                VALUES (:doctype, :filepath, :filename, :origname, :filesize, :mime, 'pending', 'pending', :userid, :tid, NOW())
            ");
            $stmt->execute([
                ':doctype' => $documentType,
                ':filepath' => $relativePath,
                ':filename' => $safeName,
                ':origname' => $file['name'],
                ':filesize' => $file['size'],
                ':mime' => $file['type'],
                ':userid' => $userId,
                ':tid' => $tid,
            ]);
            $docId = (int)$this->db->lastInsertId();
            return ['ok' => true, 'id' => $docId, 'file_path' => $relativePath];
        } catch (\Throwable $e) {
            if (file_exists($dest)) {
                @unlink($dest);
            }
            return ['ok' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function processDocument(int $docId): array
    {
        try {
            $tid = $this->getTenantId();
            $stmt = $this->db->prepare("SELECT * FROM ocr_documents WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : ""));
            $params = [':id' => $docId];
            if ($tid > 1) $params[':tid'] = $tid;
            $stmt->execute($params);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$doc) {
                return ['ok' => false, 'error' => 'Document not found.'];
            }

            $this->db->prepare("UPDATE ocr_documents SET ocr_status = 'processing', updated_at = NOW() WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : ""))
                ->execute([':id' => $docId, ':tid' => $tid]);

            $fileName = $doc['original_name'] ?? $doc['file_name'] ?? '';
            $docType = $doc['document_type'] ?? 'unknown';
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/apsdreamhome' . ($doc['file_path'] ?? '');

            $extractedText = $this->simulateOcr($filePath, $fileName, $docType);

            $fields = $this->extractFields($docType, $extractedText);
            $confidence = $this->calculateConfidence($fields);
            $jsonFields = json_encode($fields, JSON_UNESCAPED_UNICODE);

            $this->db->prepare("
                UPDATE ocr_documents
                SET ocr_status = 'completed',
                    extracted_text = :text,
                    structured_data = :data,
                    confidence_score = :conf,
                    processed_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id" . $this->_tWhere() . "
            ")->execute([':text' => $extractedText, ':data' => $jsonFields, ':conf' => $confidence, ':id' => $docId]);

            $this->db->prepare("DELETE FROM ocr_extracted_fields WHERE ocr_document_id = :oid")
                ->execute([':oid' => $docId]);

            foreach ($fields as $fName => $fVal) {
                if ($fVal !== null && $fVal !== '') {
                    $this->db->prepare("
                        INSERT INTO ocr_extracted_fields (ocr_document_id, field_name, field_value, confidence, created_at)
                        VALUES (:oid, :fname, :fval, :conf, NOW())
                    ")->execute([':oid' => $docId, ':fname' => $fName, ':fval' => $fVal, ':conf' => $confidence]);
                }
            }

            $this->classifyDocument($docId, $docType, $confidence);

            return ['ok' => true, 'fields' => $fields, 'confidence' => $confidence];
        } catch (\Throwable $e) {
            $this->db->prepare("
                UPDATE ocr_documents SET ocr_status = 'failed', error_message = :err, updated_at = NOW() WHERE id = :id" . $this->_tWhere() . "
            ")->execute([':err' => $e->getMessage(), ':id' => $docId]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function simulateOcr(string $filePath, string $fileName, string $docType): string
    {
        $base = pathinfo($fileName, PATHINFO_FILENAME);
        $text = str_replace(['_', '-'], ' ', $base) . "\n";

        $samples = [
            'aadhaar' => "Name: Rajesh Kumar Sharma\nDOB: 15/08/1990\nGender: Male\nAadhaar Number: 4523 6789 0123\nAddress: 45, Rajaji Nagar, Lucknow, Uttar Pradesh - 226001",
            'pan' => "Name: Priya Singh\nFather Name: Mr. Vijay Singh\nDOB: 22/03/1988\nPAN: BVGPS1234K",
            'passport' => "Name: Amit Kumar Verma\nPassport No: R7894561\nDOB: 10/12/1985\nNationality: Indian\nDate of Issue: 15/06/2022\nDate of Expiry: 14/06/2032",
            'driving_license' => "Name: Sunita Devi\nDL No: UP32 2019 0012345\nDOB: 05/11/1992\nValid Upto: 04/11/2032\nClass of Vehicle: MCWG, LMV",
            'cheque' => "State Bank of India\nCheque No: 987654\nA/C No: 30214567890\nIFSC: SBIN0005678\nPay to: Raj Construction\nRupees Two Lakh Fifty Thousand Only ₹2,50,000.00",
            'invoice' => "Tax Invoice\nInvoice No: INV-2024-0456\nInvoice Date: 18/09/2024\nBilled To: Sharma Builders Pvt Ltd\nGSTIN: 09AABCS1234F1Z5\nTotal: ₹3,75,000.00\nGST Amount: ₹67,500.00",
            'contract' => "This Agreement is entered into between Mr. Praveen Prabhat (Party A) and Mrs. Sunita Sharma (Party B)\nProperty Details: Plot No. 12, Suryoday Colony, Sector 7, Lucknow\nDated: 01/03/2024\nStamp Duty: ₹75,000",
        ];

        $text .= $samples[$docType] ?? "Document Type: {$docType}\nFile: {$fileName}\nThis is a sample OCR extracted text for processing.";

        return $text;
    }

    public function extractFields(string $documentType, string $extractedText): array
    {
        $fields = [];
        $patterns = self::DOC_TYPE_PATTERNS[$documentType] ?? null;

        if ($patterns) {
            foreach ($patterns as $fieldName => $regex) {
                if (preg_match($regex, $extractedText, $m)) {
                    $fields[$fieldName] = trim($m[1]);
                } else {
                    $fields[$fieldName] = null;
                }
            }
        }

        try {
            $tid = $this->getTenantId();
            $tplSql = "SELECT field_definitions FROM ocr_templates WHERE document_type = :dt AND is_active = 1" . ($tid > 1 ? " AND tenant_id = :tid" : "") . " LIMIT 1";
            $stmt = $this->db->prepare($tplSql);
            $params = [':dt' => $documentType];
            if ($tid > 1) $params[':tid'] = $tid;
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['field_definitions'])) {
                $tmplFields = json_decode($row['field_definitions'], true);
                if (is_array($tmplFields)) {
                    foreach ($tmplFields as $def) {
                        $fName = $def['name'] ?? $def['field_name'] ?? '';
                        if ($fName && !isset($fields[$fName]) && !empty($def['pattern'])) {
                            if (preg_match($def['pattern'], $extractedText, $tm)) {
                                $fields[$fName] = trim($tm[1]);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) { error_log($e->getMessage()); }

        return $fields;
    }

    private function calculateConfidence(array $fields): float
    {
        if (empty($fields)) {
            return 0.0;
        }
        $filled = 0;
        $total = count($fields);
        foreach ($fields as $v) {
            if ($v !== null && $v !== '') {
                $filled++;
            }
        }
        return round($filled / $total, 2);
    }

    public function classifyDocument(int $documentId, string $category, float $confidence = 0.0, array $metadata = []): array
    {
        $tid = $this->getTenantId();
        try {
            $insertCols = "(document_id, classified_category, classified_type, confidence, manual_override, classified_at)";
            $insertVals = "VALUES (:d, :c, :t, :co, 0, NOW())";
            if ($tid > 1) {
                $insertCols = "(document_id, classified_category, classified_type, confidence, manual_override, classified_at, tenant_id)";
                $insertVals = "VALUES (:d, :c, :t, :co, 0, NOW(), :tid)";
            }
            $this->db->prepare("INSERT INTO document_classification $insertCols $insertVals")->execute([
                ':d' => $documentId,
                ':c' => $category,
                ':t' => $metadata['method'] ?? $category,
                ':co' => $confidence,
                ':tid' => $tid,
            ]);
            return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function autoClassify(int $documentId, string $fileName, string $content = ''): array
    {
        $lower = strtolower($fileName . ' ' . $content);
        $category = 'unknown';
        $confidence = 0.5;

        $map = [
            'aadhar|aadhaar|uid' => ['identity', 0.95],
            'pan|pan card' => ['identity', 0.95],
            'passport' => ['identity', 0.95],
            'license|driving' => ['identity', 0.9],
            'agreement|contract' => ['legal', 0.9],
            'invoice|bill' => ['financial', 0.9],
            'receipt' => ['financial', 0.85],
            'bank|statement' => ['financial', 0.9],
            'property|deed|registry' => ['property', 0.9],
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

    public function getDocument(int $id): ?array
    {
        $tid = $this->getTenantId();
        try {
            $docSql = "SELECT * FROM ocr_documents WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : "");
            $stmt = $this->db->prepare($docSql);
            $params = [':id' => $id];
            if ($tid > 1) $params[':tid'] = $tid;
            $stmt->execute($params);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$doc) {
                return null;
            }

            $fstmt = $this->db->prepare("SELECT * FROM ocr_extracted_fields WHERE ocr_document_id = :oid" . ($tid > 1 ? " AND tenant_id = :tid" : "") . " ORDER BY id");
            $fstmt->execute([':oid' => $id, ':tid' => $tid]);
            $doc['fields'] = $fstmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($doc['structured_data']) && is_string($doc['structured_data'])) {
                $decoded = json_decode($doc['structured_data'], true);
                $doc['structured_data'] = $decoded ?? [];
            }

            return $doc;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function listDocuments(int $page = 1, int $perPage = 20, string $status = '', string $doctype = '', string $search = ''): array
    {
        $tid = $this->getTenantId();
        $where = ['1=1'];
        $params = [];
        if ($tid > 1) {
            $where[] = "tenant_id = :tid";
            $params[':tid'] = $tid;
        }

        if ($status !== '') {
            $where[] = "ocr_status = :status";
            $params[':status'] = $status;
        }
        if ($doctype !== '') {
            $where[] = "document_type = :doctype";
            $params[':doctype'] = $doctype;
        }
        if ($search !== '') {
            $where[] = "(original_name LIKE :search OR file_name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $whereSQL = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM ocr_documents WHERE {$whereSQL}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT * FROM ocr_documents WHERE {$whereSQL} ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'documents' => $docs,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    public function approveDocument(int $id, int $adminId): array
    {
        try {
            $tid = $this->getTenantId();
            $extra = $tid > 1 ? " AND tenant_id = :tid" : "";
            $this->db->prepare("UPDATE ocr_documents SET validation_status = 'valid', is_verified = 1, verified_by = :uid, updated_at = NOW() WHERE id = :id" . $extra)
                ->execute([':uid' => $adminId, ':id' => $id, ':tid' => $tid]);
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function rejectDocument(int $id, string $reason): array
    {
        try {
            $tid = $this->getTenantId();
            $extra = $tid > 1 ? " AND tenant_id = :tid" : "";
            $this->db->prepare("UPDATE ocr_documents SET validation_status = 'invalid', rejection_reason = :reason, is_verified = 0, updated_at = NOW() WHERE id = :id" . $extra)
                ->execute([':reason' => $reason, ':id' => $id, ':tid' => $tid]);
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteDocument(int $id): array
    {
        $tid = $this->getTenantId();
        try {
            $extraTid = $tid > 1 ? " AND tenant_id = :tid" : "";
            $docSql = "SELECT * FROM ocr_documents WHERE id = :id" . $extraTid;
            $docStmt = $this->db->prepare($docSql);
            $docStmt->execute([':id' => $id, ':tid' => $tid]);
            $doc = $docStmt->fetch(PDO::FETCH_ASSOC);
            if (!$doc) {
                return ['ok' => false, 'error' => 'Document not found.'];
            }

            $this->db->prepare("DELETE FROM ocr_extracted_fields WHERE ocr_document_id = :oid" . $extraTid)->execute([':oid' => $id, ':tid' => $tid]);
            $this->db->prepare("DELETE FROM document_classification WHERE document_id = :did" . $extraTid)->execute([':did' => $id, ':tid' => $tid]);
            $this->db->prepare("DELETE FROM ocr_documents WHERE id = :id" . $extraTid)->execute([':id' => $id, ':tid' => $tid]);

            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/apsdreamhome' . ($doc['file_path'] ?? '');
            if (!empty($doc['file_path']) && file_exists($filePath)) {
                @unlink($filePath);
            }

            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function listTemplates(string $doctype = ''): array
    {
        $tid = $this->getTenantId();
        try {
            $sql = "SELECT * FROM ocr_templates WHERE 1=1" . ($tid > 1 ? " AND tenant_id = :tid" : "");
            $params = [];
            if ($tid > 1) $params[':tid'] = $tid;
            if ($doctype !== '') {
                $sql .= " AND document_type = :dt";
                $params[':dt'] = $doctype;
            }
            $sql .= " ORDER BY template_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($templates as &$t) {
                if (!empty($t['field_definitions']) && is_string($t['field_definitions'])) {
                    $t['field_definitions'] = json_decode($t['field_definitions'], true) ?? [];
                }
            }
            return $templates;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getTemplate(int $id): ?array
    {
        $tid = $this->getTenantId();
        try {
            $sql = "SELECT * FROM ocr_templates WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : "");
            $stmt = $this->db->prepare($sql);
            $params = [':id' => $id];
            if ($tid > 1) $params[':tid'] = $tid;
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['field_definitions']) && is_string($row['field_definitions'])) {
                $row['field_definitions'] = json_decode($row['field_definitions'], true) ?? [];
            }
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function saveTemplate(array $data, int $id = 0): array
    {
        $tid = $this->getTenantId();
        try {
            $fieldsJson = json_encode($data['field_definitions'] ?? [], JSON_UNESCAPED_UNICODE);
            if ($id > 0) {
                $this->db->prepare("
                    UPDATE ocr_templates
                    SET template_name = :name, document_type = :doctype, field_definitions = :fields, is_active = :active" . ($tid > 1 ? ", tenant_id = :tid" : "") . "
                    WHERE id = :id" . ($tid > 1 ? " AND tenant_id = :tid" : "") . "
                ")->execute([
                    ':name' => $data['template_name'],
                    ':doctype' => $data['document_type'],
                    ':fields' => $fieldsJson,
                    ':active' => $data['is_active'] ?? 1,
                    ':id' => $id,
                    ':tid' => $tid,
                ]);
            } else {
                $this->db->prepare("
                    INSERT INTO ocr_templates (template_name, document_type, field_definitions, is_active" . ($tid > 1 ? ", tenant_id" : "") . ", created_at)
                    VALUES (:name, :doctype, :fields, :active" . ($tid > 1 ? ", :tid" : "") . ", NOW())
                ")->execute([
                    ':name' => $data['template_name'],
                    ':doctype' => $data['document_type'],
                    ':fields' => $fieldsJson,
                    ':active' => $data['is_active'] ?? 1,
                    ':tid' => $tid,
                ]);
                $id = (int)$this->db->lastInsertId();
            }
            return ['ok' => true, 'id' => $id];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteTemplate(int $id): array
    {
        $tid = $this->getTenantId();
        $extra = $tid > 1 ? " AND tenant_id = :tid" : "";
        try {
            $this->db->prepare("DELETE FROM ocr_templates WHERE id = :id" . $extra)->execute([':id' => $id, ':tid' => $tid]);
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function initSchema(): void
    {
        $stmts = [
            "CREATE TABLE IF NOT EXISTS ocr_documents (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                document_type VARCHAR(50) NOT NULL DEFAULT 'unknown',
                file_path VARCHAR(500) NULL,
                file_name VARCHAR(255) NULL,
                original_name VARCHAR(255) NULL,
                file_size INT UNSIGNED DEFAULT 0,
                mime_type VARCHAR(100) NULL,
                ocr_status ENUM('pending','processing','completed','failed') DEFAULT 'pending',
                extracted_text LONGTEXT NULL,
                structured_data JSON NULL,
                confidence_score DECIMAL(5,2) DEFAULT 0.00,
                validation_status ENUM('pending','valid','invalid') DEFAULT 'pending',
                rejection_reason TEXT NULL,
                is_verified TINYINT(1) DEFAULT 0,
                verified_by INT UNSIGNED NULL,
                uploaded_by INT UNSIGNED NULL,
                error_message TEXT NULL,
                processed_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_status (ocr_status),
                INDEX idx_type (document_type),
                INDEX idx_validation (validation_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS ocr_templates (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                template_name VARCHAR(100) NOT NULL,
                document_type VARCHAR(50) NOT NULL,
                field_definitions JSON NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_type (document_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS ocr_extracted_fields (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ocr_document_id BIGINT UNSIGNED NOT NULL,
                field_name VARCHAR(100) NOT NULL,
                field_value TEXT NULL,
                confidence DECIMAL(5,2) DEFAULT 0.00,
                verified TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ocr (ocr_document_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS document_classification (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                document_id BIGINT UNSIGNED NOT NULL,
                classified_category VARCHAR(50) NULL,
                classified_type VARCHAR(50) NULL,
                confidence DECIMAL(5,2) DEFAULT 0.00,
                manual_override TINYINT(1) DEFAULT 0,
                classified_by INT UNSIGNED NULL,
                classified_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_doc (document_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];

        foreach ($stmts as $sql) {
            try {
                $this->db->prepare($sql)->execute();
            } catch (\Throwable $e) { error_log($e->getMessage()); }
        }

        $columnsToCheck = [
            'ocr_documents' => [
                'document_type' => "ALTER TABLE ocr_documents ADD COLUMN document_type VARCHAR(50) NOT NULL DEFAULT 'unknown' AFTER id",
                'file_path' => "ALTER TABLE ocr_documents ADD COLUMN file_path VARCHAR(500) NULL AFTER document_type",
                'file_name' => "ALTER TABLE ocr_documents ADD COLUMN file_name VARCHAR(255) NULL AFTER file_path",
                'original_name' => "ALTER TABLE ocr_documents ADD COLUMN original_name VARCHAR(255) NULL AFTER file_name",
                'file_size' => "ALTER TABLE ocr_documents ADD COLUMN file_size INT UNSIGNED DEFAULT 0 AFTER original_name",
                'mime_type' => "ALTER TABLE ocr_documents ADD COLUMN mime_type VARCHAR(100) NULL AFTER file_size",
                'ocr_status' => "ALTER TABLE ocr_documents ADD COLUMN ocr_status ENUM('pending','processing','completed','failed') DEFAULT 'pending' AFTER mime_type",
                'extracted_text' => "ALTER TABLE ocr_documents ADD COLUMN extracted_text LONGTEXT NULL AFTER ocr_status",
                'structured_data' => "ALTER TABLE ocr_documents ADD COLUMN structured_data JSON NULL AFTER extracted_text",
                'confidence_score' => "ALTER TABLE ocr_documents ADD COLUMN confidence_score DECIMAL(5,2) DEFAULT 0.00 AFTER structured_data",
                'validation_status' => "ALTER TABLE ocr_documents ADD COLUMN validation_status ENUM('pending','valid','invalid') DEFAULT 'pending' AFTER confidence_score",
                'rejection_reason' => "ALTER TABLE ocr_documents ADD COLUMN rejection_reason TEXT NULL AFTER validation_status",
                'is_verified' => "ALTER TABLE ocr_documents ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER rejection_reason",
                'verified_by' => "ALTER TABLE ocr_documents ADD COLUMN verified_by INT UNSIGNED NULL AFTER is_verified",
                'uploaded_by' => "ALTER TABLE ocr_documents ADD COLUMN uploaded_by INT UNSIGNED NULL AFTER verified_by",
                'error_message' => "ALTER TABLE ocr_documents ADD COLUMN error_message TEXT NULL AFTER uploaded_by",
                'processed_at' => "ALTER TABLE ocr_documents ADD COLUMN processed_at DATETIME NULL AFTER error_message",
                'updated_at' => "ALTER TABLE ocr_documents ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
            ],
        ];

        foreach ($columnsToCheck as $table => $cols) {
            try {
                $existing = $this->db->fetchAll("SHOW COLUMNS FROM {$table}");
                $existingNames = array_column($existing, 'Field');
                foreach ($cols as $col => $sql) {
                    if (!in_array($col, $existingNames)) {
                        try {
                            $this->db->prepare($sql)->execute();
                        } catch (\Throwable $e) { error_log($e->getMessage()); }
                    }
                }
            } catch (\Throwable $e) { error_log($e->getMessage()); }
        }
    }
}
