<?php

namespace App\Models;

use PDO;

class Project extends Model
{
    protected static string $table = 'projects';
    protected array $fillable = [
        'project_name',
        'project_code',
        'project_type',
        'location',
        'city',
        'state',
        'pincode',
        'description',
        'short_description',
        'total_area',
        'total_plots',
        'available_plots',
        'price_per_sqft',
        'base_price',
        'project_status',
        'possession_date',
        'rera_number',
        'is_featured',
        'is_active',
        'latitude',
        'longitude',
        'address',
        'highlights',
        'amenities',
        'layout_map',
        'brochure',
        'gallery_images',
        'virtual_tour',
        'booking_amount',
        'emi_available',
        'developer_name',
        'developer_contact',
        'developer_email',
        'project_head',
        'project_manager',
        'sales_manager',
        'contact_number',
        'contact_email',
        'website',
        'social_facebook',
        'social_instagram',
        'social_twitter',
        'social_youtube',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'meta_image',
        'created_by',
        'created_at',
        'updated_at'
    ];

    /**
     * Get all active projects
     */
    public function getAllActiveProjects($limit = null, $offset = 0)
    {
        try {
            $db = \App\Core\Database::getInstance();

            $sql = "SELECT * FROM projects WHERE is_active = 1 ORDER BY is_featured DESC, created_at DESC";
            $params = [];

            if ($limit) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $params['limit'] = (int)$limit;
                $params['offset'] = (int)$offset;
            }

            $stmt = $db->prepare($sql);
            if ($limit) {
                $stmt->bindValue(':limit', $params['limit'], PDO::PARAM_INT);
                $stmt->bindValue(':offset', $params['offset'], PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt->execute();
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in getAllActiveProjects: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get featured projects
     */
    public function getFeaturedProjects($limit = 6)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare(
                "SELECT * FROM projects
                 WHERE is_featured = 1 AND is_active = 1
                 ORDER BY created_at DESC LIMIT :limit"
            );
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in getFeaturedProjects: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project by ID
     */
    public function getProjectById($id)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM projects WHERE project_id = :id AND is_active = 1");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in getProjectById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get project by code
     */
    public function getProjectByCode($code)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM projects WHERE project_code = :code AND is_active = 1");
            $stmt->execute(['code' => $code]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in getProjectByCode: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get projects by location
     */
    public function getProjectsByLocation($location)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare(
                "SELECT * FROM projects
                 WHERE (location LIKE :location OR city LIKE :city) AND is_active = 1
                 ORDER BY is_featured DESC, created_at DESC"
            );
            $stmt->execute([
                'location' => "%{$location}%",
                'city' => "%{$location}%"
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in getProjectsByLocation: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new project
     */
    public function createProject($data)
    {
        try {
            $db = \App\Core\Database::getInstance();

            // Handle JSON fields
            if (isset($data['amenities']) && is_array($data['amenities'])) {
                $data['amenities'] = json_encode($data['amenities']);
            }
            if (isset($data['highlights']) && is_array($data['highlights'])) {
                $data['highlights'] = json_encode($data['highlights']);
            }
            if (isset($data['gallery_images']) && is_array($data['gallery_images'])) {
                $data['gallery_images'] = json_encode($data['gallery_images']);
            }

            // Set defaults
            $data['is_active'] = $data['is_active'] ?? 1;
            $data['is_featured'] = $data['is_featured'] ?? 0;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $columns = implode(', ', array_keys($data));
            $placeholders = [];
            foreach (array_keys($data) as $key) {
                $placeholders[] = ":{$key}";
            }
            $placeholdersStr = implode(', ', $placeholders);

            $sql = "INSERT INTO projects ($columns) VALUES ($placeholdersStr)";
            $stmt = $db->prepare($sql);
            $stmt->execute($data);

            return $db->lastInsertId();
        } catch (\Exception $e) {
            error_log("Error in createProject: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update project
     */
    public function updateProject($id, $data)
    {
        try {
            $db = \App\Core\Database::getInstance();

            // Handle JSON fields
            if (isset($data['amenities']) && is_array($data['amenities'])) {
                $data['amenities'] = json_encode($data['amenities']);
            }
            if (isset($data['highlights']) && is_array($data['highlights'])) {
                $data['highlights'] = json_encode($data['highlights']);
            }
            if (isset($data['gallery_images']) && is_array($data['gallery_images'])) {
                $data['gallery_images'] = json_encode($data['gallery_images']);
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            $setParts = [];
            $params = [];
            foreach ($data as $key => $value) {
                $setParts[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $params['id'] = $id;

            $sql = "UPDATE projects SET " . implode(', ', $setParts) . " WHERE project_id = :id";
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (\Exception $e) {
            error_log("Error in updateProject: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete project (soft delete)
     */
    public function deleteProject($id)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("UPDATE projects SET is_active = 0, updated_at = NOW() WHERE project_id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (\Exception $e) {
            error_log("Error in deleteProject: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search projects
     */
    public function searchProjects($searchTerm, $filters = [])
    {
        try {
            $db = \App\Core\Database::getInstance();

            $sql = "SELECT * FROM projects WHERE is_active = 1 AND (";
            $sql .= "project_name LIKE :search OR ";
            $sql .= "project_code LIKE :search OR ";
            $sql .= "location LIKE :search OR ";
            $sql .= "city LIKE :search OR ";
            $sql .= "description LIKE :search)";
            $params = [
                'search' => "%{$searchTerm}%"
            ];

            // Add filters
            if (!empty($filters['city'])) {
                $sql .= " AND city = :city";
                $params['city'] = $filters['city'];
            }

            if (!empty($filters['project_type'])) {
                $sql .= " AND project_type = :project_type";
                $params['project_type'] = $filters['project_type'];
            }

            if (!empty($filters['min_price'])) {
                $sql .= " AND base_price >= :min_price";
                $params['min_price'] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $sql .= " AND base_price <= :max_price";
                $params['max_price'] = $filters['max_price'];
            }

            $sql .= " ORDER BY is_featured DESC, created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in searchProjects: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project statistics
     */
    public function getProjectStats()
    {
        try {
            $db = \App\Core\Database::getInstance();

            $sql = "SELECT
                COUNT(*) as total_projects,
                SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_projects,
                SUM(CASE WHEN project_status = 'completed' THEN 1 ELSE 0 END) as completed_projects,
                SUM(CASE WHEN project_status = 'ongoing' THEN 1 ELSE 0 END) as ongoing_projects,
                SUM(total_plots) as total_plots,
                SUM(available_plots) as available_plots
                FROM projects WHERE is_active = 1";

            $stmt = $db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in getProjectStats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get projects by city
     */
    public function getProjectsByCity($city)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $sql = "SELECT * FROM projects
                 WHERE city = :city AND is_active = 1
                 ORDER BY is_featured DESC, created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute(['city' => $city]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error in getProjectsByCity: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unique cities
     */
    public function getUniqueCities()
    {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->query("SELECT DISTINCT city FROM projects WHERE is_active = 1 ORDER BY city");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            error_log("Error in getUniqueCities: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unique project types
     */
    public function getUniqueProjectTypes()
    {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->query("SELECT DISTINCT project_type FROM projects WHERE is_active = 1 ORDER BY project_type");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            error_log("Error in getUniqueProjectTypes: " . $e->getMessage());
            return [];
        }
    }
}
