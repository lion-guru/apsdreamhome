<?php

namespace App\Models;

use App\Models\Model;
use \Exception;

class Sale extends Model
{
    public static $table = 'property_sales';
    public static $primaryKey = 'id';

    protected array $fillable = [
        'property_id',
        'buyer_id',
        'agent_id',
        'sale_amount',
        'commission_amount',
        'commission_distributed',
        'sale_date',
        'created_at'
    ];

    /**
     * Process property sale and distribute MLM commissions
     */
    public function processPropertySale($propertyId, $buyerId, $saleAmount, $agentId = null)
    {
        try {
            // Get buyer information
            $buyer = static::query()
                ->from('user')
                ->where('uid', $buyerId)
                ->first();

            if (!$buyer) {
                throw new \Exception("Buyer not found");
            }

            // Get property information
            $propertyModel = new Property();
            $property = $propertyModel->getPropertyById($propertyId);
            if (!$property) {
                throw new \Exception("Property not found");
            }

            // Check if buyer is an MLM associate
            $associate = static::query()
                ->from('associates')
                ->where('user_id', $buyerId)
                ->first();

            // Calculate total commission (default 7% if not specified by settings)
            $commissionRate = 0.07;
            $totalCommission = $saleAmount * $commissionRate;

            $commissionsDistributed = [];
            if ($associate) {
                // Use MLM distribution logic - pool is based on total commission, not sale amount
                $distribution = $this->calculateMLMDistribution($associate['id'], $totalCommission);

                foreach ($distribution as $level => $info) {
                    $uplineId = $info['upline_id'];
                    $amount = $info['commission'];

                    if ($amount > 0) {
                        // Record commission
                        static::query()
                            ->from('mlm_commissions')
                            ->insert([
                                'associate_id' => $uplineId,
                                'property_id' => $propertyId,
                                'level' => $level,
                                'commission_amount' => $amount,
                                'commission_type' => 'property_sale',
                                'status' => 'pending',
                                'created_at' => \date('Y-m-d H:i:s')
                            ]);

                        // Update upline balance (using uid from user table)
                        $uplineUser = static::query()
                            ->from('associates')
                            ->select(['user_id'])
                            ->where('id', $uplineId)
                            ->first();

                        if ($uplineUser) {
                            static::query()
                                ->from('user')
                                ->where('uid', $uplineUser['user_id'])
                                ->increment('ubalance', $amount);
                        }

                        $commissionsDistributed[] = [
                            'associate_id' => $uplineId,
                            'level' => $level,
                            'amount' => $amount
                        ];
                    }
                }
            }

            // Record the property sale
            static::query()->insert([
                'property_id' => $propertyId,
                'buyer_id' => $buyerId,
                'agent_id' => $agentId,
                'sale_amount' => $saleAmount,
                'commission_amount' => $totalCommission,
                'commission_distributed' => !empty($commissionsDistributed) ? 1 : 0,
                'sale_date' => \date('Y-m-d H:i:s'),
                'created_at' => \date('Y-m-d H:i:s')
            ]);

            // Update property status
            static::query()
                ->from('properties')
                ->where('id', $propertyId)
                ->update([
                    'status' => 'sold',
                    'sold_date' => \date('Y-m-d H:i:s'),
                    'sold_to' => $buyerId
                ]);

            return [
                'success' => true,
                'message' => 'Property sale processed successfully',
                'sale_amount' => $saleAmount,
                'commission_amount' => $totalCommission,
                'commissions_distributed' => $commissionsDistributed
            ];

        } catch (\Exception $e) {
            \error_log("Error in processPropertySale: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error processing property sale: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate MLM commission distribution
     */
    private function calculateMLMDistribution($associateId, $saleAmount)
    {
        // Get company share
        $companyShareRow = static::query()
            ->from('mlm_company_share')
            ->select(['share_percent'])
            ->first();

        $companyShare = $companyShareRow ? \floatval($companyShareRow['share_percent']) : 25.0;

        $totalCommissionPool = ($companyShare / 100.0) * $saleAmount;
        $distribution = [];
        $currentId = $associateId;

        for ($level = 1; $level <= 10; $level++) {
            $setting = static::query()
                ->from('mlm_commission_settings')
                ->select(['percent'])
                ->where('level', $level)
                ->first();

            if (!$setting || \floatval($setting['percent']) <= 0) break;

            $percent = \floatval($setting['percent']);

            // Find upline
            $row = static::query()
                ->from('associates')
                ->select(['parent_id'])
                ->where('id', $currentId)
                ->first();

            if (!$row || !$row['parent_id']) break;

            $uplineId = $row['parent_id'];
            $commission = ($percent / 100.0) * $totalCommissionPool;

            $distribution[$level] = [
                'upline_id' => $uplineId,
                'commission' => $commission
            ];
            $currentId = $uplineId;
        }

        return $distribution;
    }

    /**
     * Get recent sales with commission details
     */
    public function getRecentSales($associateId, $limit = 10)
    {
        return static::query()
            ->select([
                'ps.*',
                'p.title as property_title',
                'p.location as property_location',
                'u.uname as buyer_name',
                'mc.commission_amount as my_commission',
                'mc.level as commission_level',
                'mc.status as commission_status'
            ])
            ->from(static::$table . ' as ps')
            ->leftJoin('properties as p', 'ps.property_id', '=', 'p.id')
            ->leftJoin('user as u', 'ps.buyer_id', '=', 'u.uid')
            ->leftJoin('mlm_commissions as mc', function($q) use ($associateId) {
                $q->on('ps.property_id', '=', 'mc.property_id')
                  ->where('mc.associate_id', '=', $associateId);
            })
            ->where(function($q) use ($associateId) {
                $q->where('mc.associate_id', '=', $associateId)
                  ->orWhere('ps.buyer_id', '=', $associateId);
            })
            ->orderBy('ps.sale_date', 'DESC')
            ->limit($limit)
            ->get();
    }
}
