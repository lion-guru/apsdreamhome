<?php
  
namespace App\Models;

class ResellPropertyImage extends Model
{
    public static $table = 'resell_property_images';

    protected array $fillable = [
        'property_id',
        'image_path'
    ];
}
