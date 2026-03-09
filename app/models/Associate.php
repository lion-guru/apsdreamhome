<?php

namespace App\Models;

use App\Core\Database;

/**
 * Associate Model - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class Associate
{
    private $database;
    private $table = 'associates';
    
    public $id;
    public $name;
    public $email;
    public $phone;
    public $address;
    public $joining_date;
    public $status;
    public $commission_rate;
    public $total_sales;
    public $created_at;
    public $updated_at;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
    }
    
    /**
     * Find associate by ID
     */
    public static function find($id)
    {
        $database = Database::getInstance();
        $result = $database->selectOne(
            "SELECT * FROM associates WHERE id = ? AND status != 'deleted'",
            [$id]
        );
        
        if ($result) {
            $associate = new self();
            $associate->fill($result);
            return $associate;
        }
        
        return null;
    }
    
    /**
     * Find associate by email
     */
    public static function findByEmail($email)
    {
        $database = Database::getInstance();
        $result = $database->selectOne(
            "SELECT * FROM associates WHERE email = ? AND status != 'deleted'",
            [$email]
        );
        
        if ($result) {
            $associate = new self();
            $associate->fill($result);
            return $associate;
        }
        
        return null;
    }
    
    /**
     * Get all active associates
     */
    public static function all()
    {
        $database = Database::getInstance();
        $results = $database->select(
            "SELECT * FROM associates WHERE status = 'active' ORDER BY name ASC"
        );
        
        $associates = [];
        foreach ($results as $result) {
            $associate = new self();
            $associate->fill($result);
            $associates[] = $associate;
        }
        
        return $associates;
    }
    
    /**
     * Get associates with pagination
     */
    public static function paginate($page = 1, $limit = 20)
    {
        $database = Database::getInstance();
        $offset = ($page - 1) * $limit;
        
        $results = $database->select(
            "SELECT * FROM associates WHERE status != 'deleted' 
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
        
        $associates = [];
        foreach ($results as $result) {
            $associate = new self();
            $associate->fill($result);
            $associates[] = $associate;
        }
        
        // Get total count
        $total = $database->selectOne(
            "SELECT COUNT(*) as count FROM associates WHERE status != 'deleted'"
        )['count'];
        
        return [
            'data' => $associates,
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => ceil($total / $limit)
        ];
    }
    
    /**
     * Create new associate
     */
    public function create(array $data)
    {
        try {
            // Validate required fields
            if (empty($data['name']) || empty($data['email'])) {
                throw new \Exception('Name and email are required');
            }
            
            // Check if email already exists
            if (self::findByEmail($data['email'])) {
                throw new \Exception('Email already exists');
            }
            
            $this->fill($data);
            
            $this->database->insert('associates', [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone ?? null,
                'address' => $this->address ?? null,
                'joining_date' => $this->joining_date ?? date('Y-m-d'),
                'status' => $this->status ?? 'active',
                'commission_rate' => $this->commission_rate ?? 0,
                'total_sales' => $this->total_sales ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->id = $this->database->lastInsertId();
            
            return $this;
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to create associate: ' . $e->getMessage());
        }
    }
    
    /**
     * Update associate
     */
    public function update(array $data)
    {
        try {
            if (!$this->id) {
                throw new \Exception('Associate ID is required for update');
            }
            
            // Update only provided fields
            $updateData = [];
            foreach ($data as $key => $value) {
                if (property_exists($this, $key) && $key !== 'id') {
                    $this->$key = $value;
                    $updateData[$key] = $value;
                }
            }
            
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            // Check email uniqueness if email is being updated
            if (isset($updateData['email'])) {
                $existing = self::findByEmail($updateData['email']);
                if ($existing && $existing->id != $this->id) {
                    throw new \Exception('Email already exists');
                }
            }
            
            $this->database->update('associates', $updateData, 'id = ?', [$this->id]);
            
            return $this;
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to update associate: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete associate (soft delete)
     */
    public function delete()
    {
        if (!$this->id) {
            throw new \Exception('Associate ID is required for delete');
        }
        
        $this->database->update('associates', [
            'status' => 'deleted',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$this->id]);
        
        return true;
    }
    
    /**
     * Activate associate
     */
    public function activate()
    {
        if (!$this->id) {
            throw new \Exception('Associate ID is required');
        }
        
        $this->status = 'active';
        return $this->update(['status' => 'active']);
    }
    
    /**
     * Deactivate associate
     */
    public function deactivate()
    {
        if (!$this->id) {
            throw new \Exception('Associate ID is required');
        }
        
        $this->status = 'inactive';
        return $this->update(['status' => 'inactive']);
    }
    
    /**
     * Get associate's sales
     */
    public function getSales($limit = 10)
    {
        if (!$this->id) {
            return [];
        }
        
        return $this->database->select(
            "SELECT s.*, p.name as property_name 
             FROM sales s 
             LEFT JOIN properties p ON s.property_id = p.id 
             WHERE s.associate_id = ? 
             ORDER BY s.created_at DESC 
             LIMIT ?",
            [$this->id, $limit]
        );
    }
    
    /**
     * Get associate's commission
     */
    public function getTotalCommission()
    {
        if (!$this->id) {
            return 0;
        }
        
        $result = $this->database->selectOne(
            "SELECT SUM(commission_amount) as total 
             FROM sales 
             WHERE associate_id = ? AND status = 'completed'",
            [$this->id]
        );
        
        return $result ? (float) $result['total'] : 0;
    }
    
    /**
     * Update commission rate
     */
    public function updateCommissionRate($rate)
    {
        if ($rate < 0 || $rate > 100) {
            throw new \Exception('Commission rate must be between 0 and 100');
        }
        
        return $this->update(['commission_rate' => $rate]);
    }
    
    /**
     * Add sale to associate
     */
    public function addSale($saleAmount, $propertyId = null)
    {
        if (!$this->id) {
            throw new \Exception('Associate ID is required');
        }
        
        $commissionAmount = ($saleAmount * $this->commission_rate) / 100;
        
        $this->database->insert('sales', [
            'associate_id' => $this->id,
            'property_id' => $propertyId,
            'sale_amount' => $saleAmount,
            'commission_amount' => $commissionAmount,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update total sales
        $this->database->query(
            "UPDATE associates SET total_sales = total_sales + ?, updated_at = NOW() WHERE id = ?",
            [$saleAmount, $this->id]
        );
        
        return $this->database->lastInsertId();
    }
    
    /**
     * Search associates
     */
    public static function search($query, $limit = 20)
    {
        $database = Database::getInstance();
        $results = $database->select(
            "SELECT * FROM associates 
             WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ?) 
             AND status != 'deleted' 
             ORDER BY name ASC 
             LIMIT ?",
            ["%$query%", "%$query%", "%$query%", $limit]
        );
        
        $associates = [];
        foreach ($results as $result) {
            $associate = new self();
            $associate->fill($result);
            $associates[] = $associate;
        }
        
        return $associates;
    }
    
    /**
     * Get active associates count
     */
    public static function getActiveCount()
    {
        $database = Database::getInstance();
        $result = $database->selectOne(
            "SELECT COUNT(*) as count FROM associates WHERE status = 'active'"
        );
        
        return $result ? (int) $result['count'] : 0;
    }
    
    /**
     * Get top performers
     */
    public static function getTopPerformers($limit = 10)
    {
        $database = Database::getInstance();
        $results = $database->select(
            "SELECT a.*, COUNT(s.id) as sales_count, SUM(s.sale_amount) as total_sales_amount
             FROM associates a
             LEFT JOIN sales s ON a.id = s.associate_id AND s.status = 'completed'
             WHERE a.status = 'active'
             GROUP BY a.id
             ORDER BY total_sales_amount DESC
             LIMIT ?",
            [$limit]
        );
        
        $associates = [];
        foreach ($results as $result) {
            $associate = new self();
            $associate->fill($result);
            $associate->sales_count = $result['sales_count'];
            $associate->total_sales_amount = $result['total_sales_amount'];
            $associates[] = $associate;
        }
        
        return $associates;
    }
    
    /**
     * Fill model with data
     */
    private function fill(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Convert to array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'joining_date' => $this->joining_date,
            'status' => $this->status,
            'commission_rate' => $this->commission_rate,
            'total_sales' => $this->total_sales,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    /**
     * Validate associate data
     */
    public static function validate(array $data)
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (isset($data['commission_rate']) && ($data['commission_rate'] < 0 || $data['commission_rate'] > 100)) {
            $errors['commission_rate'] = 'Commission rate must be between 0 and 100';
        }
        
        return $errors;
    }
}