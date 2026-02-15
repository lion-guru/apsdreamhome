<?php

namespace App\Models;

use App\Models\Model;

class Payout extends Model
{
    public static $table = 'payouts';
    public static $primaryKey = 'id';

    /**
     * Create a new payout record
     */
    public function createPayout($data)
    {
        return $this->create([
            'associate_id' => $data['associate_id'],
            'sale_id' => $data['sale_id'],
            'payout_amount' => $data['payout_amount'],
            'payout_percent' => $data['payout_percent'],
            'period' => $data['period'],
            'status' => $data['status'] ?? 'pending',
            'created_at' => \date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get payouts for a specific sale
     */
    public function getPayoutsBySale($saleId)
    {
        return $this->where('sale_id', $saleId)->get();
    }
}
