<?php

namespace App\Models;

use App\Core\UnifiedModel;

class BankAccount extends UnifiedModel
{
    public static $table = 'bank_accounts';
    public static $primaryKey = 'id';
    
    protected array $fillable = [
        'bank_name',
        'account_name',
        'account_number',
        'ifsc_code',
        'branch_name',
        'status'
    ];
}
