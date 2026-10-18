<?php

namespace App\Services;

use App\Core\Database\Database;

class EmployeeSkillService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getEmployeeSkills($employeeId)
    {
        return $this->db->fetchAll(
            "SELECT es.*, s.category 
             FROM employee_skills es
             JOIN skills s ON s.name = es.skill_name
             WHERE es.employee_id = ? AND s.is_active = 1
             ORDER BY s.category, es.skill_name",
            [$employeeId]
        );
    }

    public function getEmployeeSkillMap($employeeId)
    {
        $skills = $this->getEmployeeSkills($employeeId);
        $map = [];
        foreach ($skills as $s) {
            $map[$s['skill_name']] = [
                'proficiency' => $s['proficiency_level'],
                'years' => $s['years_experience'],
                'certified' => $s['certified'],
                'category' => $s['category']
            ];
        }
        return $map;
    }

    public function addSkill($employeeId, $skillName, $level = 'beginner', $years = 0, $certified = false, $certName = null, $certExpiry = null)
    {
        $skill = $this->db->fetchOne("SELECT id, category FROM skills WHERE name = ? AND is_active = 1", [$skillName]);
        if (!$skill) {
            throw new \Exception("Skill '$skillName' not found in skills catalog");
        }

        try {
            $this->db->execute(
                "INSERT INTO employee_skills (tenant_id, employee_id, skill_name, skill_category, proficiency_level, years_experience, certified, certification_name, certification_expiry) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [1, $employeeId, $skillName, $skill['category'], $level, $years, $certified ? 1 : 0, $certName, $certExpiry]
            );
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'uniq_emp_skill') !== false) {
                return ['success' => false, 'message' => "Skill already exists for this employee"];
            }
            throw $e;
        }
    }

    public function updateSkill($employeeId, $skillName, $level = null, $years = null, $certified = null, $certName = null, $certExpiry = null)
    {
        $updates = [];
        $params = [];

        if ($level !== null) {
            $updates[] = "proficiency_level = ?";
            $params[] = $level;
        }
        if ($years !== null) {
            $updates[] = "years_experience = ?";
            $params[] = $years;
        }
        if ($certified !== null) {
            $updates[] = "certified = ?";
            $params[] = $certified ? 1 : 0;
        }
        if ($certName !== null) {
            $updates[] = "certification_name = ?";
            $params[] = $certName;
        }
        if ($certExpiry !== null) {
            $updates[] = "certification_expiry = ?";
            $params[] = $certExpiry;
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'No updates provided'];
        }

        $updates[] = "updated_at = NOW()";
        $params[] = $employeeId;
        $params[] = $skillName;

        $this->db->execute(
            "UPDATE employee_skills SET " . implode(', ', $updates) . " WHERE employee_id = ? AND skill_name = ?",
            $params
        );

        return ['success' => true];
    }

    public function removeSkill($employeeId, $skillName)
    {
        $this->db->execute("DELETE FROM employee_skills WHERE employee_id = ? AND skill_name = ?", [$employeeId, $skillName]);
        return ['success' => true];
    }

    public function getSkillGap($employeeId, $requiredSkills)
    {
        $empSkills = $this->getEmployeeSkillMap($employeeId);
        $gaps = [];

        foreach ($requiredSkills as $skill => $requiredLevel) {
            $empLevel = $empSkills[$skill]['proficiency'] ?? 'none';
            $levelOrder = ['none' => 0, 'beginner' => 1, 'intermediate' => 2, 'advanced' => 3, 'expert' => 4];
            
            if (($levelOrder[$empLevel] ?? 0) < ($levelOrder[$requiredLevel] ?? 1)) {
                $gaps[$skill] = [
                    'required' => $requiredLevel,
                    'current' => $empLevel,
                    'gap' => ($levelOrder[$requiredLevel] ?? 1) - ($levelOrder[$empLevel] ?? 0)
                ];
            }
        }
        return $gaps;
    }

    public function getAllSkills($category = null)
    {
        $query = "SELECT * FROM skills WHERE is_active = 1";
        $params = [];
        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }
        $query .= " ORDER BY category, name";
        return $this->db->fetchAll($query, $params);
    }

    public function getEmployeesWithSkill($skillName, $minLevel = 'beginner')
    {
        $levelOrder = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3, 'expert' => 4];
        $minLevelNum = $levelOrder[$minLevel] ?? 1;

        return $this->db->fetchAll(
            "SELECT e.id, e.name, e.email, e.role, es.proficiency_level, es.years_experience
             FROM employee_skills es
             JOIN users e ON e.id = es.employee_id
             WHERE es.skill_name = ? 
             AND e.status = 'active'
             ORDER BY FIELD(es.proficiency_level, 'expert', 'advanced', 'intermediate', 'beginner') DESC",
            [$skillName]
        );
    }
}