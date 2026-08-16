<?php

namespace App\Models;

use App\Core\UnifiedModel;

/**
 * LegalDocumentVersion Model
 * Stores version history for legal documents
 */
class LegalDocumentVersion extends \App\Core\UnifiedModel
{
    public static $table = 'legal_document_versions';
    public static $primaryKey = 'id';
    protected static $tenantScoped = false;

    protected $fillable = [
        'legal_document_id',
        'version',
        'content',
        'change_summary',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the parent document.
     */
    public function document()
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    /**
     * Get the creator of this version.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}