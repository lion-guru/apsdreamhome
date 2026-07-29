<?php

namespace App\Models;

use App\Core\UnifiedModel;

class LegalDocument extends UnifiedModel
{
    public static $table = 'legal_documents';
    public static $primaryKey = 'id';
    protected static $tenantScoped = true;
    
    protected $fillable = [
        'title',
        'description',
        'file_path',
        'category',
        'published_date',
        'status'
    ];
}
