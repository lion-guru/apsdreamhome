<?php
/**
 * Public Customer Model (for leads/visits)
 */

namespace App\Models;

class PublicCustomer extends Model {
    public static $table = 'customers';
    
    protected array $fillable = [
        'name',
        'email',
        'phone',
        'created_at'
    ];
    
    /**
     * Find or create customer by email
     */
    public function findOrCreate(array $data) {
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
