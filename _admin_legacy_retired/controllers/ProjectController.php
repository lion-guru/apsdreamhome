<?php
namespace Admin\Controllers;

class ProjectController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        $stmt = $this->db->prepare("SELECT * FROM projects ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create($data) {
        $slug = $this->createSlug($data['name']);
        
        $stmt = $this->db->prepare("INSERT INTO projects (name, slug, description, location, status, 
            featured, thumbnail, amenities, specifications, floor_plans, price_range, total_units, 
            available_units, possession_date, launch_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
        $stmt->bind_param("sssssssssssiidd", 
            $data['name'],
            $slug,
            $data['description'],
            $data['location'],
            $data['status'],
            $data['featured'],
            $data['thumbnail'],
            $data['amenities'],
            $data['specifications'],
            $data['floor_plans'],
            $data['price_range'],
            $data['total_units'],
            $data['available_units'],
            $data['possession_date'],
            $data['launch_date']
        );

        return $stmt->execute();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE projects SET 
            name = ?, description = ?, location = ?, status = ?, featured = ?,
            thumbnail = ?, amenities = ?, specifications = ?, floor_plans = ?,
            price_range = ?, total_units = ?, available_units = ?, 
            possession_date = ?, launch_date = ? WHERE id = ?");
            
        $stmt->bind_param("ssssssssssiiddi",
            $data['name'],
            $data['description'],
            $data['location'],
            $data['status'],
            $data['featured'],
            $data['thumbnail'],
            $data['amenities'],
            $data['specifications'],
            $data['floor_plans'],
            $data['price_range'],
            $data['total_units'],
            $data['available_units'],
            $data['possession_date'],
            $data['launch_date'],
            $id
        );

        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function createSlug($title) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $slug;
    }
}