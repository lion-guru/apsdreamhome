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
        'name',
        'is_active'
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
        $existing = self::findByEmail($email);
        if ($existing) {
            return $existing['id'] ?? false;
        }
        // subscribed_at auto-defaults to CURRENT_TIMESTAMP in the schema
        return static::create([
            'email' => $email,
            'is_active' => 1,
        ]);
    }
}
