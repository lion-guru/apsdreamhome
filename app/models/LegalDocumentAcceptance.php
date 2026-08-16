<?php

namespace App\Models;

use App\Core\UnifiedModel;

/**
 * LegalDocumentAcceptance Model
 * Tracks user acceptance of legal documents
 */
class LegalDocumentAcceptance extends \App\Core\UnifiedModel
{
    public static $table = 'legal_document_acceptances';
    public static $primaryKey = 'id';
    protected static $tenantScoped = false;

    protected $fillable = [
        'legal_document_id',
        'user_id',
        'user_type',
        'ip_address',
        'user_agent',
        'version',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    /**
     * Get the legal document.
     */
    public function document()
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    /**
     * Get the user who accepted (polymorphic).
     */
    public function user()
    {
        // Manual polymorphic relationship
        $userType = $this->user_type;
        $userId = $this->user_id;
        
        // Map user types to classes
        $classMap = [
            'App\Models\User' => \App\Models\User::class,
            'App\Models\Associate' => \App\Models\Associate::class,
            'App\Models\Agent' => \App\Models\Agent::class,
            'App\Models\Admin' => \App\Models\Admin::class,
            'App\Models\Employee' => \App\Models\Employee::class,
        ];
        
        $class = $classMap[$this->user_type] ?? null;
        if ($class) {
            return $class::find($this->user_id);
        }
        return null;
    }

    /**
     * Scope: For a specific user
     */
    public function scopeForUser($query, $user)
    {
        $userId = is_object($user) ? $user->id : $user;
        $userType = is_object($user) ? get_class($user) : 'App\Models\User';
        
        return $query->where('user_id', $userId)
            ->where('user_type', $userType);
    }

    /**
     * Scope: For a specific document
     */
    public function scopeForDocument($query, $documentId)
    {
        return $query->where('legal_document_id', $documentId);
    }
}