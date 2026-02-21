<?php

namespace App\Models;

class LeadCustomFieldValue extends Model
{
    protected static $table = 'lead_custom_field_values';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
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
    protected array $casts = [
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
    public function lead()
    {
        return Lead::find($this->lead_id);
    }

    /**
     * Get the custom field definition.
     */
    public function field()
    {
        return LeadCustomField::find($this->field_id);
    }

    /**
     * Get the user who last updated the field value.
     */
    public function updatedBy()
    {
        return User::find($this->updated_by);
    }

    /**
     * Get the formatted field value based on field type.
     */
    public function getFormattedValue()
    {
        $fieldValue = $this->field_value;

        if (empty($fieldValue)) {
            return null;
        }

        if (!is_array($fieldValue)) {
            return $fieldValue;
        }

        $field = $this->field();
        if (!$field) {
            return $fieldValue;
        }

        // Handle different field types
        switch ($field->field_type) {
            case 'checkbox':
                return is_array($fieldValue) ? implode(', ', $fieldValue) : $fieldValue;

            case 'multiselect':
                if (!is_array($fieldValue)) {
                    return $fieldValue;
                }

                if ($field instanceof \App\Models\LeadCustomField) {
                    $options = $field->getOptionsArray();
                } else {
                    $options = [];
                }
                $selected = [];

                foreach ($fieldValue as $value) {
                    $selected[] = $options[$value] ?? $value;
                }

                return implode(', ', $selected);

            case 'select':
            case 'radio':
                if ($field instanceof \App\Models\LeadCustomField) {
                    $options = $field->getOptionsArray();
                    $value = is_array($fieldValue) ? implode(', ', $fieldValue) : $fieldValue;
                    return $options[$value] ?? $value;
                }
                return $fieldValue;

            case 'date':
                try {
                    if (is_array($fieldValue)) {
                        return implode(', ', array_map(function($val) {
                            return date('Y-m-d', strtotime($val));
                        }, $fieldValue));
                    }
                    return date('Y-m-d', strtotime($fieldValue));
                } catch (\Exception $e) {
                    return $fieldValue;
                }

            case 'datetime':
                try {
                    if (is_array($fieldValue)) {
                        return implode(', ', array_map(function($val) {
                            return date('Y-m-d H:i:s', strtotime($val));
                        }, $fieldValue));
                    }
                    return date('Y-m-d H:i:s', strtotime($fieldValue));
                } catch (\Exception $e) {
                    return $fieldValue;
                }

            default:
                return $fieldValue;
        }
    }

    /**
     * Get values for a specific lead.
     */
    public static function forLead($leadId)
    {
        return static::where('lead_id', '=', $leadId);
    }

    /**
     * Get values for a specific field.
     */
    public static function forField($fieldId)
    {
        return static::where('field_id', '=', $fieldId);
    }
}
