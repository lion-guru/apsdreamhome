<?php
  
namespace App\Models;

class ResellPropertyImage extends Model
{
    public static $table = 'resell_property_images';

    protected $fillable = [
        'property_id',
        'image_path'
    ];
}
