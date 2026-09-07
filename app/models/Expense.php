<?php

namespace App\Models;

use PDO;

class Expense
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $sql = "INSERT INTO expenses (associate_id, category, amount, description, expense_date, proof_file, status) 
                VALUES (:associate_id, :category, :amount, :description, :expense_date, :proof_file, :status)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'associate_id' => $data['associate_id'],
            'category' => $data['category'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'expense_date' => $data['expense_date'],
            'proof_file' => $data['proof_file'] ?? null,
            'status' => 'pending'
        ]);
    }

    public function getByAssociateId($associateId)
    {
        $sql = "SELECT * FROM expenses WHERE associate_id = :associate_id ORDER BY expense_date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['associate_id' => $associateId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats($associateId)
    {
        $sql = "SELECT 
                    SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as total_approved,
                    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending,
                    COUNT(*) as total_count
                FROM expenses WHERE associate_id = :associate_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['associate_id' => $associateId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
