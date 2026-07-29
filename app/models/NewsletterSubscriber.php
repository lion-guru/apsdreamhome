<?php
/**
 * Newsletter Subscriber Model
 */

namespace App\Models;

class NewsletterSubscriber extends \App\Models\Model {
    protected static $table = 'newsletter_subscribers';
    protected static $tenantScoped = true;
    
    protected $fillable = [
        'email',
        'is_active',
        'created_at'
    ];

    /**
     * Find subscriber by email
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Subscribe a new email
     */
    public static function subscribe($email)
    {
        return static::create([
            'email' => $email,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
