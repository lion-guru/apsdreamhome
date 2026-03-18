<?php

namespace App\Models;

use App\Core\UnifiedModel;

/**
 * User Model
 * Represents user data
 */
class User extends UnifiedModel
{
    public static $table = 'users';
    public static $primaryKey = 'id';

    protected array $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Update user data
     */
    public function update($id, $data)
    {
        return $this->where('id', $id)->update($data);
    }

    /**
     * Find user by ID
     */
    public function findById($id)
    {
        return $this->find($id);
    }
}
