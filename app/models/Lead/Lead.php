<?php

namespace App\Models\Lead;

use App\Core\Database\Model;
use App\Models\User\User;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property string $mobile
 * @property string $company
 * @property string $job_title
 * @property string $website
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $postal_code
 * @property string $country
 * @property string $source
 * @property string $status
 * @property int $rating
 * @property float $estimated_value
 * @property string $description
 * @property string $last_contact_date
 * @property string $next_followup_date
 * @property int $assigned_to
 * @property int $created_by
 * @property int $updated_by
 * @property array $custom_fields
 * @property bool $is_deleted
 * @property string $created_at
 * @property string $updated_at
 */
class Lead extends Model
{
    protected static $table = 'leads';
    protected static $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'company',
        'job_title',
        'website',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'source',
        'status',
        'rating',
        'estimated_value',
        'description',
        'last_contact_date',
        'next_followup_date',
        'assigned_to',
        'created_by',
        'updated_by',
        'custom_fields',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected array $casts = [
        'estimated_value' => 'float',
        'rating' => 'integer',
        'last_contact_date' => 'string',
        'next_followup_date' => 'string',
        'custom_fields' => 'array',
        'is_deleted' => 'boolean',
    ];

    /**
     * Find a lead by ID using custom database (for compatibility)
     */
    public static function find($id)
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM leads WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? new static($result) : null;
    }

    /**
     * Get the user assigned to this lead.
     */
    public function assignedTo()
    {
        return User::find($this->assigned_to);
    }

    /**
     * Get the user who created this lead.
     */
    public function createdBy()
    {
        return User::find($this->created_by);
    }

    /**
     * Get the user who last updated this lead.
     */
    public function updatedBy()
    {
        return User::find($this->updated_by);
    }

    /**
     * Get the full name of the lead.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
