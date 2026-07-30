<?php

namespace App\Services;

class CompanyCredentialsService
{
    private $db;

    public function __construct($db = null)
    {
        if ($db !== null) {
            $this->db = $db;
            return;
        }
        try {
            $this->db = \App\Core\Database\Database::getInstance()->getConnection();
        } catch (\Exception $e) {
            error_log("[CompanyCredentials] DB connection failed: " . $e->getMessage());
            $this->db = null;
        }
    }

    public function getAll(): array
    {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->query("SELECT * FROM company_credentials ORDER BY credential_type, is_primary DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] getAll: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM company_credentials WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] getById: " . $e->getMessage());
            return null;
        }
    }

    public function getByType(string $type): array
    {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM company_credentials WHERE credential_type = ? ORDER BY is_primary DESC");
            $stmt->execute([$type]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] getByType: " . $e->getMessage());
            return [];
        }
    }

    public function getActive(): array
    {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->query("SELECT * FROM company_credentials WHERE status = 'active' ORDER BY credential_type");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] getActive: " . $e->getMessage());
            return [];
        }
    }

    public function getGstin(): ?string
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->query("SELECT credential_value FROM company_credentials WHERE credential_type = 'gst' AND is_primary = 1 AND status = 'active' LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['credential_value'] : null;
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] getGstin: " . $e->getMessage());
            return null;
        }
    }

    public function getPan(): ?string
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->query("SELECT credential_value FROM company_credentials WHERE credential_type = 'pan' AND is_primary = 1 AND status = 'active' LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['credential_value'] : null;
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] getPan: " . $e->getMessage());
            return null;
        }
    }

    public function getReraNumber(): ?string
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->query("SELECT credential_value FROM company_credentials WHERE credential_type = 'rera' AND is_primary = 1 AND status = 'active' LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['credential_value'] : null;
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] getReraNumber: " . $e->getMessage());
            return null;
        }
    }

    public function create(array $data): int
    {
        if (!$this->db) return 0;
        try {
            $stmt = $this->db->prepare("
                INSERT INTO company_credentials
                (credential_type, credential_label, credential_value, issuer, issue_date, expiry_date, document_path, is_primary, status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['credential_type'],
                $data['credential_label'],
                $data['credential_value'],
                $data['issuer'] ?? null,
                !empty($data['issue_date']) ? $data['issue_date'] : null,
                !empty($data['expiry_date']) ? $data['expiry_date'] : null,
                $data['document_path'] ?? null,
                isset($data['is_primary']) ? (int)$data['is_primary'] : 1,
                $data['status'] ?? 'active',
                $data['notes'] ?? null,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] create: " . $e->getMessage());
            return 0;
        }
    }

    public function update(int $id, array $data): bool
    {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare("
                UPDATE company_credentials SET
                    credential_type = ?,
                    credential_label = ?,
                    credential_value = ?,
                    issuer = ?,
                    issue_date = ?,
                    expiry_date = ?,
                    document_path = ?,
                    is_primary = ?,
                    status = ?,
                    notes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['credential_type'],
                $data['credential_label'],
                $data['credential_value'],
                $data['issuer'] ?? null,
                !empty($data['issue_date']) ? $data['issue_date'] : null,
                !empty($data['expiry_date']) ? $data['expiry_date'] : null,
                $data['document_path'] ?? null,
                isset($data['is_primary']) ? (int)$data['is_primary'] : 1,
                $data['status'] ?? 'active',
                $data['notes'] ?? null,
                $id,
            ]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] update: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare("DELETE FROM company_credentials WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] delete: " . $e->getMessage());
            return false;
        }
    }

    public function getExpiringSoon(int $days = 30): array
    {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM company_credentials
                WHERE expiry_date IS NOT NULL
                AND expiry_date <= DATE_ADD(NOW(), INTERVAL ? DAY)
                AND status = 'active'
                ORDER BY expiry_date ASC
            ");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] getExpiringSoon: " . $e->getMessage());
            return [];
        }
    }

    public function getDashboardStats(): array
    {
        if (!$this->db) return ['total' => 0, 'active' => 0, 'expiring' => 0, 'expired' => 0, 'by_type' => []];
        try {
            $stats = [];
            $stats['total'] = (int)$this->db->query("SELECT COUNT(*) FROM company_credentials")->fetchColumn();
            $stats['active'] = (int)$this->db->query("SELECT COUNT(*) FROM company_credentials WHERE status = 'active'")->fetchColumn();
            $stats['expired'] = (int)$this->db->query("SELECT COUNT(*) FROM company_credentials WHERE status = 'expired'")->fetchColumn();
            $stats['expiring'] = (int)$this->db->query("SELECT COUNT(*) FROM company_credentials WHERE expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND status = 'active'")->fetchColumn();

            $byType = $this->db->query("SELECT credential_type, COUNT(*) as cnt FROM company_credentials GROUP BY credential_type ORDER BY credential_type")->fetchAll(PDO::FETCH_ASSOC);
            $stats['by_type'] = [];
            foreach ($byType as $row) {
                $stats['by_type'][$row['credential_type']] = (int)$row['cnt'];
            }
            return $stats;
        } catch (\Throwable $e) {
            error_log("[CompanyCredentials] getDashboardStats: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'expiring' => 0, 'expired' => 0, 'by_type' => []];
        }
    }
}
