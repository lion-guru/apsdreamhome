<?php

namespace App\Models;

use App\Models\Model;
use PDO;

/**
 * Admin Model
 * Handles all admin panel related database operations
 */
class Admin extends Model
{
    protected static string $table = 'admin';
    protected $primaryKey = 'id';
    protected $db;

    protected array $fillable = [
        'username',
        'auser',
        'email',
        'password',
        'apass',
        'role',
        'status',
        'permissions',
        'last_login',
        'created_at',
        'updated_at'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    /**
     * Find admin by username or email
     */
    public function findByUsernameOrEmail($username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE auser = :u1 OR username = :u2 OR email = :e1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['u1' => $username, 'u2' => $username, 'e1' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? new static($result) : null;
    }

    /**
     * Get admin by ID
     */
    public function getAdminById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get admin by email
     */
    public function getAdminByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Authenticate admin
     */
    public function authenticateAdmin($email, $password)
    {
        $admin = $this->getAdminByEmail($email);

        if ($admin && password_verify($password, $admin['apass'])) {
            return $admin;
        }

        return false;
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        $stats = [];

        // Total users
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_users'] = $result ? (int)$result['total'] : 0;

        // Total properties
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM properties");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_properties'] = $result ? (int)$result['total'] : 0;

        // Total leads
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM leads");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_leads'] = $result ? (int)$result['total'] : 0;

        // Total farmers
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM farmers WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_farmers'] = $result ? (int)$result['total'] : 0;

        return $stats;
    }

    /**
     * Get comprehensive admin analytics
     */
    public function getAdminAnalytics()
    {
        $analytics = [];

        // System Overview
        $stmt = $this->db->prepare("
            SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active') as total_customers,
                (SELECT COUNT(*) FROM users WHERE role = 'agent' AND status = 'active') as total_agents,
                (SELECT COUNT(*) FROM associates WHERE status = 'active') as total_associates,
                (SELECT COUNT(*) FROM properties WHERE status = 'available') as available_properties,
                (SELECT COUNT(*) FROM properties WHERE status = 'sold') as sold_properties,
                (SELECT COUNT(*) FROM bookings WHERE status = 'confirmed') as confirmed_bookings,
                (SELECT COUNT(*) FROM payments WHERE status = 'completed') as completed_payments,
                (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed') as total_revenue,
                (SELECT COUNT(*) FROM leads WHERE status = 'new') as new_leads,
                (SELECT COUNT(*) FROM leads WHERE status = 'converted') as converted_leads
        ");
        $stmt->execute();
        $analytics['system_overview'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Monthly Trends
        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(CASE WHEN role = 'customer' THEN 1 END) as new_customers,
                COUNT(CASE WHEN role = 'agent' THEN 1 END) as new_agents,
                COUNT(CASE WHEN role = 'associate' THEN 1 END) as new_associates,
                COUNT(properties.id) as new_properties,
                COALESCE(SUM(CASE WHEN payments.status = 'completed' THEN payments.amount ELSE 0 END), 0) as monthly_revenue
            FROM users
            LEFT JOIN associates ON users.id = associates.user_id
            LEFT JOIN properties ON DATE_FORMAT(properties.created_at, '%Y-%m') = DATE_FORMAT(users.created_at, '%Y-%m')
            LEFT JOIN payments ON DATE_FORMAT(payments.created_at, '%Y-%m') = DATE_FORMAT(users.created_at, '%Y-%m')
            WHERE users.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ");
        $stmt->execute();
        $analytics['monthly_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $analytics;
    }
}
