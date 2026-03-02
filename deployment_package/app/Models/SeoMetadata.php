<?php
/**
 * SEO Metadata Model
 */

namespace App\Models;

use App\Core\Database\Model;

class SeoMetadata extends Model {
    public static $table = 'seo_metadata';
    
    protected array $fillable = [
        'page_name',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'canonical_url',
        'robots',
        'created_at',
        'updated_at'
    ];

    /**
     * Get metadata by page name or URL
     */
    public function getByPage($pageName) {
        return static::query()
            ->where('page_name', $pageName)
            ->first();
    }
}
