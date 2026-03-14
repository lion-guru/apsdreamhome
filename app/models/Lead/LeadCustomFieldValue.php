<?php

namespace App\Models\Lead;

use App\Core\Database\Model;

class LeadCustomFieldValue extends Model
{
    protected static $table = 'lead_custom_field_values';

    protected array $fillable = [
        'lead_id',
        'field_id',
        'value',
    ];

    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    public function field()
    {
        return LeadCustomField::find($this->field_id);
    }
}
