<?php

namespace App\Models\Lead;

use App\Core\Database\Model;
use App\Models\User\User;

class LeadFile extends Model
{
    protected static $table = 'lead_files';

    protected array $fillable = [
        'lead_id',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    public function uploader()
    {
        return User::find($this->uploaded_by);
    }
}
