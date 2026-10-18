<?php
namespace App\Services;

use App\Traits\ServiceTenantTrait;

class LabourLawComplianceService
{
    use ServiceTenantTrait;

    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    public function getAll(array $filters = []): array
    {
        $sql = "SELECT * FROM labour_law_compliances WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['law_name'])) {
            $sql .= " AND law_name LIKE ?";
            $params[] = '%' . $filters['law_name'] . '%';
        }
        $sql .= " ORDER BY due_date ASC, id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM labour_law_compliances WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO labour_law_compliances (tenant_id, law_name, compliance_type, registration_no, authority, applicable_unit, frequency, due_date, last_filed_date, next_due_date, status, document_path, penalty_amount, assigned_to, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['tenant_id'] ?? 1,
            $data['law_name'],
            $data['compliance_type'],
            $data['registration_no'] ?? null,
            $data['authority'] ?? null,
            $data['applicable_unit'] ?? null,
            $data['frequency'] ?? 'annual',
            $data['due_date'] ?: null,
            $data['last_filed_date'] ?: null,
            $data['next_due_date'] ?: null,
            $data['status'] ?? 'pending',
            $data['document_path'] ?? null,
            $data['penalty_amount'] ?? 0,
            $data['assigned_to'] ?: null,
            $data['remarks'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("UPDATE labour_law_compliances SET law_name = ?, compliance_type = ?, registration_no = ?, authority = ?, applicable_unit = ?, frequency = ?, due_date = ?, last_filed_date = ?, next_due_date = ?, status = ?, document_path = ?, penalty_amount = ?, assigned_to = ?, remarks = ? WHERE id = ?");
        return $stmt->execute([
            $data['law_name'],
            $data['compliance_type'],
            $data['registration_no'] ?? null,
            $data['authority'] ?? null,
            $data['applicable_unit'] ?? null,
            $data['frequency'] ?? 'annual',
            $data['due_date'] ?: null,
            $data['last_filed_date'] ?: null,
            $data['next_due_date'] ?: null,
            $data['status'] ?? 'pending',
            $data['document_path'] ?? null,
            $data['penalty_amount'] ?? 0,
            $data['assigned_to'] ?: null,
            $data['remarks'] ?? null,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM labour_law_compliances WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getStats(): array
    {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'filed' => 0,
            'overdue' => 0,
        ];
        $row = $this->pdo->query("SELECT COUNT(*) AS total, SUM(status='pending') AS pending, SUM(status='filed') AS filed, SUM(status='overdue') AS overdue FROM labour_law_compliances")->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $stats['total'] = (int)$row['total'];
            $stats['pending'] = (int)$row['pending'];
            $stats['filed'] = (int)$row['filed'];
            $stats['overdue'] = (int)$row['overdue'];
        }
        return $stats;
    }
}
