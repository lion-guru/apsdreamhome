<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\BaseController;
use Exception;

/**
 * DatabaseSeederController
 * 
 * Handles seeding sample data for testing and demonstration purposes.
 */
class DatabaseSeederController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:admin');
    }

    /**
     * Seed all sample data
     */
    public function seedAll()
    {
        $results = [];
        $results['associates'] = $this->seedAssociates();
        $results['commissions'] = $this->seedCommissions();

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode($results);
            exit;
        }

        echo "<h1>Seeding Complete</h1>";
        echo "<pre>" . print_r($results, true) . "</pre>";
    }

    /**
     * Seed extensive MLM associate data for 7-level tree demo
     */
    public function seedAssociates()
    {
        try {
            // Level 1 (root)
            $this->insertAssociate(1, 'You', 'you@example.com', '9000000001', null, 10, 1, 'active');

            $nextId = 2;
            $parentIds = [1];
            $count = 1;

            for ($level = 2; $level <= 7; $level++) {
                $newParentIds = [];
                foreach ($parentIds as $parent) {
                    $numChildren = ($level <= 3) ? 5 : 2; // Wider at top, then binary style
                    for ($j = 1; $j <= $numChildren; $j++) {
                        $name = 'L' . $level . '-' . $parent . '-' . $j;
                        $email = strtolower($name) . '@example.com';
                        $phone = '90000' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
                        $this->insertAssociate($nextId, $name, $email, $phone, $parent, 10, $level, 'active');
                        $newParentIds[] = $nextId;
                        $nextId++;
                        $count++;
                    }
                }
                $parentIds = $newParentIds;
            }
            return "Successfully seeded $count associates (7 levels).";
        } catch (Exception $e) {
            return "Error seeding associates: " . $e->getMessage();
        }
    }

    /**
     * Seed sample commission_transactions for demo/testing
     */
    public function seedCommissions()
    {
        try {
            $sample = [
                [1, 10000, 2500, '2025-04-01', 'Initial Sale'],
                [2, 20000, 5000, '2025-04-02', 'Level 2 Sale'],
                [3, 30000, 7500, '2025-04-03', 'Level 3 Sale'],
                [4, 40000, 10000, '2025-04-04', 'Level 4 Sale'],
                [5, 50000, 12500, '2025-04-05', 'Level 5 Sale'],
                [6, 60000, 15000, '2025-03-15', 'Older Sale'],
                [7, 70000, 17500, '2025-02-20', 'Oldest Sale'],
            ];

            $count = 0;
            foreach ($sample as $row) {
                $stmt = $this->db->prepare("INSERT INTO commission_transactions (associate_id, amount, commission_amount, transaction_date, description, status) VALUES (:associate_id, :amount, :commission_amount, :transaction_date, :description, 'approved')");
                if ($stmt->execute([
                    'associate_id' => $row[0],
                    'amount' => $row[1],
                    'commission_amount' => $row[2],
                    'transaction_date' => $row[3],
                    'description' => $row[4]
                ])) {
                    $count++;
                }
            }
            return "Successfully seeded $count commission transactions.";
        } catch (Exception $e) {
            return "Error seeding commissions: " . $e->getMessage();
        }
    }

    private function insertAssociate($id, $name, $email, $phone, $parent_id, $commission_percent, $level, $status)
    {
        $check = $this->db->prepare("SELECT id FROM associates WHERE id = :id");
        $check->execute(['id' => $id]);

        if (!$check->fetch()) {
            $stmt = $this->db->prepare("INSERT INTO associates (id, name, email, phone, parent_id, commission_percent, level, status) VALUES (:id, :name, :email, :phone, :parent_id, :commission_percent, :level, :status)");
            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'parent_id' => $parent_id,
                'commission_percent' => $commission_percent,
                'level' => $level,
                'status' => $status
            ]);
        }
    }
}
