<?php

namespace App\Models\Lead;

use App\Core\Database\Model;

class LeadTag extends Model
{
    protected static $table = 'lead_tags';

    protected array $fillable = [
        'name',
        'color',
        'created_by',
    ];

    public function creator()
    {
        return \App\Models\User\User::find($this->created_by);
    }
}
