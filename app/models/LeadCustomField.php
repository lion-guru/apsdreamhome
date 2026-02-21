<?php

namespace App\Models;

class LeadCustomField extends Model
{
    protected static $table = 'lead_custom_fields';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'field_name',
        'field_label',
        'field_type',
        'field_group',
        'field_options',
        'default_value',
        'is_required',
        'is_active',
        'validation_rules',
        'sort_order',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected array $casts = [
        'field_options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the field values associated with the custom field.
     */
    public function fieldValues()
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM lead_custom_field_values WHERE field_id = :id");
        $stmt->execute(['id' => $this->id]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $fieldValues = [];
        foreach ($results as $result) {
            $fieldValues[] = new LeadCustomFieldValue($result);
        }

        return $fieldValues;
    }

    /**
     * Get the user who created the custom field.
     */
    public function creator()
    {
        return User::find($this->created_by);
    }

    /**
     * Get the human-readable field type label.
     */
    public function getFieldTypeLabelAttribute()
    {
        $types = [
            'text' => 'Text',
            'textarea' => 'Text Area',
            'number' => 'Number',
            'email' => 'Email',
            'phone' => 'Phone',
            'date' => 'Date',
            'datetime' => 'Date & Time',
            'select' => 'Dropdown',
            'multiselect' => 'Multi-Select',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Buttons',
            'url' => 'URL',
            'file' => 'File Upload',
            'color' => 'Color Picker',
        ];

        return $types[$this->field_type] ?? ucfirst($this->field_type);
    }

    /**
     * Get the field options as an array.
     */
    public function getOptionsArray()
    {
        $fieldOptions = $this->field_options;

        if (empty($fieldOptions)) {
            return [];
        }

        if (is_array($fieldOptions)) {
            return $fieldOptions;
        }

        if (is_string($fieldOptions)) {
            $options = [];
            $lines = explode("\n", $fieldOptions);

            foreach ($lines as $line) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $options[trim($parts[0])] = trim($parts[1]);
                } else {
                    $options[trim($line)] = trim($line);
                }
            }

            return $options;
        }

        return [];
    }

    /**
     * Get active fields.
     */
    public static function active()
    {
        return static::where('is_active', '=', 1);
    }

    /**
     * Get fields in a specific group.
     */
    public static function inGroup($group)
    {
        return static::where('field_group', '=', $group);
    }

    /**
     * Get the validation rules for the custom field.
     */
    public function getValidationRules()
    {
        $rules = [];

        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Add field type specific rules
        switch ($this->field_type) {
            case 'email':
                $rules[] = 'email';
                break;

            case 'number':
                $rules[] = 'numeric';
                break;

            case 'date':
                $rules[] = 'date';
                break;

            case 'datetime':
                $rules[] = 'date';
                break;

            case 'url':
                $rules[] = 'url';
                break;
        }

        // Add custom validation rules if any
        if (!empty($this->validation_rules) && is_array($this->validation_rules)) {
            $rules = array_merge($rules, $this->validation_rules);
        }

        return $rules;
    }
}
