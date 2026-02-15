<?php
namespace Admin\Models;

class LayoutTemplate {
    private $db;
    private $table = 'layout_templates';
    private $version_table = 'layout_template_versions';
    private $category_table = 'layout_template_categories';
    private $dependencies_table = 'layout_template_dependencies';
    private $inheritance_table = 'layout_template_inheritance';

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, description, content, is_active, created_by) 
                VALUES (:name, :description, :content, :is_active, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? '',
            ':content' => $data['content'],
            ':is_active' => $data['is_active'] ?? true,
            ':created_by' => $data['user_id']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                name = :name,
                description = :description,
                content = :content,
                is_active = :is_active
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':content' => $data['content'],
            ':is_active' => $data['is_active'],
            ':id' => $id
        ]);
    }

    public function delete($id) {
        // Check if template is in use
        $sql = "SELECT COUNT(*) FROM pages WHERE layout = (SELECT name FROM {$this->table} WHERE id = :id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            throw new \Exception('Cannot delete template that is in use by pages');
        }

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getByName($name) {
        $sql = "SELECT * FROM {$this->table} WHERE name = :name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $name]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAll($activeOnly = true) {
        $sql = "SELECT * FROM {$this->table}";
        if ($activeOnly) {
            $sql .= " WHERE is_active = true";
        }
        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function toggleActive($id) {
        // Check if template is in use before deactivating
        $template = $this->getById($id);
        if ($template && $template['is_active']) {
            $sql = "SELECT COUNT(*) FROM pages WHERE layout = :name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':name' => $template['name']]);
            if ($stmt->fetchColumn() > 0) {
                throw new \Exception('Cannot deactivate template that is in use by pages');
            }
        }

        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function duplicate($id) {
        $template = $this->getById($id);
        if (!$template) return false;

        $template['name'] = $template['name'] . ' (Copy)';
        unset($template['id']);
        unset($template['created_at']);
        unset($template['updated_at']);

        return $this->create($template);
    }

    public function getPagesUsingTemplate($templateName) {
        $sql = "SELECT id, title, slug FROM pages WHERE layout = :template_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':template_name' => $templateName]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function saveVersion($templateId, $data) {
        $template = $this->getById($templateId);
        if (!$template) return false;

        $sql = "INSERT INTO {$this->version_table} (template_id, content, version_number, created_by, comment) 
                VALUES (:template_id, :content, :version_number, :created_by, :comment)";
        
        // Get the latest version number
        $versionSql = "SELECT MAX(version_number) FROM {$this->version_table} WHERE template_id = :template_id";
        $stmt = $this->db->prepare($versionSql);
        $stmt->execute([':template_id' => $templateId]);
        $latestVersion = $stmt->fetchColumn() ?: 0;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':template_id' => $templateId,
            ':content' => $data['content'],
            ':version_number' => $latestVersion + 1,
            ':created_by' => $data['user_id'],
            ':comment' => $data['comment'] ?? ''
        ]);
    }

    public function getVersions($templateId) {
        $sql = "SELECT * FROM {$this->version_table} WHERE template_id = :template_id ORDER BY version_number DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':template_id' => $templateId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getVersion($templateId, $versionNumber) {
        $sql = "SELECT * FROM {$this->version_table} WHERE template_id = :template_id AND version_number = :version_number";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':template_id' => $templateId,
            ':version_number' => $versionNumber
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function restoreVersion($templateId, $versionNumber) {
        $version = $this->getVersion($templateId, $versionNumber);
        if (!$version) return false;

        $sql = "UPDATE {$this->table} SET content = :content WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':content' => $version['content'],
            ':id' => $templateId
        ]);
    }

    public function setCategory($templateId, $categoryId) {
        $sql = "UPDATE {$this->table} SET category_id = :category_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':category_id' => $categoryId,
            ':id' => $templateId
        ]);
    }

    public function getCategories() {
        $sql = "SELECT * FROM {$this->category_table} ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createCategory($data) {
        $sql = "INSERT INTO {$this->category_table} (name, description) VALUES (:name, :description)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? ''
        ]);
    }

    public function previewTemplate($content, $data = []) {
        // Replace placeholders in template with actual data
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', htmlspecialchars($value), $content);
        }
        return $content;
    }

    public function setParentTemplate($templateId, $parentId) {
        if ($templateId === $parentId) {
            throw new \Exception('A template cannot inherit from itself');
        }
        
        // Check for circular inheritance
        if ($this->hasCircularInheritance($templateId, $parentId)) {
            throw new \Exception('Circular template inheritance detected');
        }

        $sql = "INSERT INTO {$this->inheritance_table} (template_id, parent_id) VALUES (:template_id, :parent_id)
                ON DUPLICATE KEY UPDATE parent_id = :parent_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':template_id' => $templateId,
            ':parent_id' => $parentId
        ]);
    }

    private function hasCircularInheritance($templateId, $parentId, $visited = []) {
        if (in_array($parentId, $visited)) {
            return true;
        }
        
        $visited[] = $parentId;
        $sql = "SELECT parent_id FROM {$this->inheritance_table} WHERE template_id = :parent_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':parent_id' => $parentId]);
        $grandParentId = $stmt->fetchColumn();
        
        if (!$grandParentId) {
            return false;
        }
        
        return $this->hasCircularInheritance($templateId, $grandParentId, $visited);
    }

    public function addDependency($templateId, $componentId, $type = 'component') {
        $sql = "INSERT INTO {$this->dependencies_table} (template_id, component_id, dependency_type) 
                VALUES (:template_id, :component_id, :type)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':template_id' => $templateId,
            ':component_id' => $componentId,
            ':type' => $type
        ]);
    }

    public function removeDependency($templateId, $componentId) {
        $sql = "DELETE FROM {$this->dependencies_table} 
                WHERE template_id = :template_id AND component_id = :component_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':template_id' => $templateId,
            ':component_id' => $componentId
        ]);
    }

    public function getDependencies($templateId) {
        $sql = "SELECT d.*, t.name as component_name 
                FROM {$this->dependencies_table} d 
                JOIN {$this->table} t ON d.component_id = t.id 
                WHERE d.template_id = :template_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':template_id' => $templateId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function validateTemplate($content) {
        // Check for basic syntax errors
        if (!$this->checkSyntax($content)) {
            throw new \Exception('Template contains syntax errors');
        }

        // Check for required sections
        if (!$this->hasRequiredSections($content)) {
            throw new \Exception('Template is missing required sections');
        }

        return true;
    }

    private function checkSyntax($content) {
        // Check for matching brackets
        $openBrackets = substr_count($content, '{{');
        $closeBrackets = substr_count($content, '}}');
        if ($openBrackets !== $closeBrackets) {
            return false;
        }

        // Check for invalid placeholder syntax
        if (preg_match('/{{[^}]*{{|}}[^{]*}}/m', $content)) {
            return false;
        }

        return true;
    }

    private function hasRequiredSections($content) {
        // Check for minimum required template sections
        $requiredSections = ['content', 'header', 'footer'];
        foreach ($requiredSections as $section) {
            if (!preg_match('/{{\s*' . $section . '\s*}}/i', $content)) {
                return false;
            }
        }
        return true;
    }

    public function compileTemplate($templateId) {
        $template = $this->getById($templateId);
        if (!$template) {
            throw new \Exception('Template not found');
        }

        $content = $template['content'];
        
        // Process inheritance
        $sql = "SELECT parent_id FROM {$this->inheritance_table} WHERE template_id = :template_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':template_id' => $templateId]);
        $parentId = $stmt->fetchColumn();
        
        if ($parentId) {
            $parent = $this->getById($parentId);
            $content = $this->mergeWithParent($content, $parent['content']);
        }

        // Process dependencies
        $dependencies = $this->getDependencies($templateId);
        foreach ($dependencies as $dependency) {
            $component = $this->getById($dependency['component_id']);
            $content = $this->injectComponent($content, $component);
        }

        return $content;
    }

    private function mergeWithParent($childContent, $parentContent) {
        // Replace parent blocks with child blocks
        preg_match_all('/{{block\s+([^}]+)}}([^{]*){{\/block}}/s', $childContent, $childBlocks, PREG_SET_ORDER);
        
        foreach ($childBlocks as $block) {
            $blockName = trim($block[1]);
            $blockContent = $block[2];
            $parentContent = preg_replace(
                '/{{block\s+' . preg_quote($blockName) . '}}[^{]*{{\/block}}/s',
                $blockContent,
                $parentContent
            );
        }
        
        return $parentContent;
    }

    private function injectComponent($content, $component) {
        // Replace component placeholders with actual component content
        $componentName = preg_quote($component['name']);
        $content = preg_replace(
            '/{{component\s+' . $componentName . '}}/',
            $component['content'],
            $content
        );
        
        return $content;
    }
}