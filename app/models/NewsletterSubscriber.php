<?php
/**
 * Newsletter Subscriber Model
 */

namespace App\Models;

class NewsletterSubscriber extends Model {
    public static $table = 'newsletter_subscribers';
    
    protected $fillable = [
        'email',
        'status',
        'created_at'
    ];
}
