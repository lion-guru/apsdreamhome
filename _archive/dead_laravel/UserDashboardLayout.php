<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDashboardLayout extends Model
{
    protected $table = 'user_dashboard_layouts';
    protected $fillable = [
        'user_id',
        'layout_name',
        'layout_config',
        'is_default',
        'widgets_order',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'widgets_order' => 'array',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}