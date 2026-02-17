<?php

namespace App\Models;

class LeadSource extends Model
{
    protected static string $table = 'lead_sources';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
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
    protected array $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the leads associated with this source.
     */
    public function leads()
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM leads WHERE source = :source");
        $stmt->execute(['source' => $this->name]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $leads = [];
        foreach ($results as $result) {
            $leads[] = new Lead($result);
        }

        return $leads;
    }

    /**
     * Get active sources only.
     */
    public static function active()
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_sources WHERE is_active = 1 ORDER BY sort_order ASC");
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $sources = [];
        foreach ($results as $result) {
            $sources[] = new LeadSource($result);
        }

        return $sources;
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
