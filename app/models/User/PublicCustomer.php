<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Models;

use Exception;

/**
 * Public Customer Model (for leads/visits)
 */

class PublicCustomer extends Model
{
    public static $table = 'customers';
    public $id;
    protected array $fillable = [
        'name',
        'email',
        'phone',
        'created_at'
    ];

    /**
     * Save customer record
     * @return bool Success status
     */
    public function save()
    {
        try {
            $data = [
                'name' => $this->name ?? '',
                'email' => $this->email ?? '',
                'phone' => $this->phone ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $columns = implode(', ', array_keys($data));
            $placeholders = str_repeat('?,', count($data));
            $values = array_values($data);

            $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";
            $result = static::getDb()->query($sql, $values);

            if ($result) {
                $this->id = static::getDb()->lastInsertId();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            error_log("PublicCustomer save error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find or create customer by email
     */
    public function findOrCreate(array $data)
    {
        if (empty($data['email'])) return false;

        $existing = static::query()
            ->select(['id'])
            ->where('email', $data['email'])
            ->first();

        if ($existing) {
            return $existing['id'];
        }

        $customer = new self($data);
        if ($customer->save()) {
            return $customer->id;
        }

        return false;
    }
}
