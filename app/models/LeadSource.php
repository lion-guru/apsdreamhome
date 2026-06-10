<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Models;

use App\Core\Database\Model;
use App\Models\Lead\Lead;

class LeadSource extends Model
{
    protected static $table = 'lead_sources';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'color',
        'icon',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the leads associated with this source.
     */
    public function leads()
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM leads WHERE source = ?");
        $stmt->execute([$this->name ?? $this->data['name'] ?? '']);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get active sources only.
     */
    public static function active()
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM lead_sources WHERE is_active = 1 ORDER BY sort_order ASC");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get active sources as id/name pairs for dropdowns.
     */
    public static function getActiveNames()
    {
        return static::active();
    }

    /**
     * Get the default icon if none is set.
     */
    public function getIconAttribute($value)
    {
        return $value ?? 'fa fa-question-circle';
    }

    /**
     * Get the default color if none is set.
     */
    public function getColorAttribute($value)
    {
        return $value ?? '#6c757d';
    }
}
