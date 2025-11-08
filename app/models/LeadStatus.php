<?php

namespace App\Models;

class LeadStatus extends Model
{
    protected static string $table = 'lead_statuses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'name',
        'color',
        'is_default',
        'is_active',
        'description',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected array $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Find a lead status by ID.
     */
    public static function find($id)
    {
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_statuses WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? new static($result) : null;
    }

    /**
    public function leads()
    {
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM leads WHERE status = ?");
        $stmt->execute([$this->name]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $leads = [];
        foreach ($results as $result) {
            $leads[] = new Lead($result);
        }

        return $leads;
    }

    /**
     * Get the status history entries for this status.
     */
    public function statusHistory()
    {
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_status_history WHERE status_id = ?");
        $stmt->execute([$this->id]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $histories = [];
        foreach ($results as $result) {
            $histories[] = new LeadStatusHistory($result);
        }

        return $histories;
    }

    /**
     * Get active statuses only.
     */
    public static function active()
    {
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_statuses WHERE is_active = 1 ORDER BY sort_order ASC");
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $statuses = [];
        foreach ($results as $result) {
            $statuses[] = new LeadStatus($result);
        }

        return $statuses;
    }

    /**
     * Get the default status.
     */
    public static function getDefault()
    {
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_statuses WHERE is_default = 1 LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ? new LeadStatus($result) : null;
    }
}
