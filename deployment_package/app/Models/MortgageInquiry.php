<?php
/**
 * Mortgage Inquiry Model
 */

namespace App\Models;

class MortgageInquiry extends Model {
    public static $table = 'mortgage_inquiries';
    
    protected array $fillable = [
        'name',
        'email',
        'phone',
        'property_value',
        'down_payment',
        'loan_amount',
        'loan_tenure',
        'employment_type',
        'monthly_income',
        'existing_loans',
        'property_location',
        'urgency_level',
        'additional_info',
        'loan_to_value_ratio',
        'status',
        'created_at',
        'updated_at'
    ];
}
