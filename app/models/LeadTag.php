<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Models;

use App\Core\Database\Model;
use App\Models\Lead\Lead;
use App\Models\User\User;

class LeadTag extends Model
{
    protected static $table = 'lead_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'name',
        'color',
        'created_by',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected array $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get all tags
     */
    public static function all()
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_tags ORDER BY name ASC");
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $tags = [];
        foreach ($results as $result) {
            $tags[] = new LeadTag($result);
        }

        return $tags;
    }

    /**
     * Get leads that belong to this tag.
     */
    public function leads()
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT l.* FROM leads l
            INNER JOIN lead_tag_mapping ltm ON l.id = ltm.lead_id
            WHERE ltm.tag_id = ?
        ");
        $stmt->execute([$this->id]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $leads = [];
        foreach ($results as $result) {
            $leads[] = new Lead($result);
        }

        return $leads;
    }

    /**
     * Get the user who created the tag.
     */
    public function createdBy()
    {
        return User::find($this->created_by);
    }

    /**
     * Get system tags only.
     */
    public static function system()
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_tags WHERE is_system = 1");
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $tags = [];
        foreach ($results as $result) {
            $tags[] = new LeadTag($result);
        }

        return $tags;
    }

    /**
     * Get user-created tags only.
     */
    public static function userCreated()
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_tags WHERE is_system = 0");
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $tags = [];
        foreach ($results as $result) {
            $tags[] = new LeadTag($result);
        }

        return $tags;
    }
}
