<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadCustomFieldValue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lead_custom_field_values';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'field_id',
        'field_value',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'field_value' => 'array',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the lead that owns the custom field value.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the custom field definition.
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(LeadCustomField::class, 'field_id');
    }

    /**
     * Get the user who last updated the field value.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the formatted field value based on field type.
     *
     * @return mixed
     */
    public function getFormattedValue()
    {
        if (empty($this->field_value)) {
            return null;
        }

        if (!is_array($this->field_value)) {
            return $this->field_value;
        }

        $field = $this->field;
        if (!$field) {
            return $this->field_value;
        }

        // Handle different field types
        switch ($field->field_type) {
            case 'checkbox':
                return is_array($this->field_value) ? implode(', ', $this->field_value) : $this->field_value;
                
            case 'multiselect':
                if (!is_array($this->field_value)) {
                    return $this->field_value;
                }
                
                $options = $field->getOptionsArray();
                $selected = [];
                
                foreach ($this->field_value as $value) {
                    $selected[] = $options[$value] ?? $value;
                }
                
                return implode(', ', $selected);
                
            case 'select':
            case 'radio':
                $options = $field->getOptionsArray();
                return $options[$this->field_value] ?? $this->field_value;
                
            case 'date':
                try {
                    return \Carbon\Carbon::parse($this->field_value)->format('Y-m-d');
                } catch (\Exception $e) {
                    return $this->field_value;
                }
                
            case 'datetime':
                try {
                    return \Carbon\Carbon::parse($this->field_value)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    return $this->field_value;
                }
                
            default:
                return $this->field_value;
        }
    }

    /**
     * Scope a query to only include values for a specific lead.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $leadId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLead($query, $leadId)
    {
        return $query->where('lead_id', $leadId);
    }

    /**
     * Scope a query to only include values for a specific field.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $fieldId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForField($query, $fieldId)
    {
        return $query->where('field_id', $fieldId);
    }
}
