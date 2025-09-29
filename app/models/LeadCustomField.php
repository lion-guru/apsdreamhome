<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadCustomField extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
    protected $casts = [
        'field_options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['field_type_label'];

    /**
     * Get the field values associated with the custom field.
     */
    public function fieldValues(): HasMany
    {
        return $this->hasMany(LeadCustomFieldValue::class, 'field_id');
    }

    /**
     * Get the user who created the custom field.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the human-readable field type label.
     *
     * @return string
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
     *
     * @return array
     */
    public function getOptionsArray()
    {
        if (empty($this->field_options)) {
            return [];
        }

        if (is_array($this->field_options)) {
            return $this->field_options;
        }

        if (is_string($this->field_options)) {
            $options = [];
            $lines = explode("\n", $this->field_options);
            
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
     * Scope a query to only include active fields.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include fields of a specific group.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $group
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInGroup($query, $group)
    {
        return $query->where('field_group', $group);
    }

    /**
     * Get the validation rules for the custom field.
     *
     * @return array
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
