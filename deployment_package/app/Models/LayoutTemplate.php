<?php
namespace App\Models;

use App\Models\Model;

class LayoutTemplate extends Model {
    public static $table = 'layout_templates';
    private $version_table = 'layout_template_versions';
    private $category_table = 'layout_template_categories';
    private $dependencies_table = 'layout_template_dependencies';
    private $inheritance_table = 'layout_template_inheritance';

    public static function create(array $data) {
        return static::query()->insert([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'content' => $data['content'],
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $data['user_id']
        ]);
    }

    public function update($id, $data) {
        return static::query()
            ->where('id', '=', $id)
            ->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'content' => $data['content'],
                'is_active' => $data['is_active']
            ]);
    }

    public function delete($id = null): bool {
        if ($id === null) {
            return parent::delete();
        }
        // Check if template is in use
        $template = $this->getById($id);
        if (!$template) return false;

        $inUse = static::query()
            ->from('pages')
            ->where('layout', '=', $template['name'])
            ->count();

        if ($inUse > 0) {
            throw new \Exception('Cannot delete template that is in use by pages');
        }

        return static::query()
            ->where('id', '=', $id)
            ->delete() > 0;
    }

    public function getById($id) {
        return static::query()
            ->where('id', '=', $id)
            ->first();
    }

    public function getByName($name) {
        return static::query()
            ->where('name', '=', $name)
            ->first();
    }

    public function getAll($activeOnly = true) {
        $query = static::query();
        if ($activeOnly) {
            $query->where('is_active', '=', true);
        }
        return $query->orderBy('name', 'ASC')->get();
    }

    public function toggleActive($id) {
        // Check if template is in use before deactivating
        $template = $this->getById($id);
        if ($template && $template['is_active']) {
            $inUse = static::query()
                ->from('pages')
                ->where('layout', '=', $template['name'])
                ->count();

            if ($inUse > 0) {
                throw new \Exception('Cannot deactivate template that is in use by pages');
            }
        }

        return static::query()
            ->getConnection()
            ->execute("UPDATE " . static::$table . " SET is_active = NOT is_active WHERE id = ?", [$id]);
    }

    public function duplicate($id) {
        $template = $this->getById($id);
        if (!$template) return false;

        $template['name'] = $template['name'] . ' (Copy)';
        $userId = $template['created_by'] ?? null;

        return $this->create([
            'name' => $template['name'],
            'description' => $template['description'],
            'content' => $template['content'],
            'is_active' => $template['is_active'],
            'user_id' => $userId
        ]);
    }

    public function getPagesUsingTemplate($templateName) {
        return static::query()
            ->from('pages')
            ->select(['id', 'title', 'slug'])
            ->where('layout', '=', $templateName)
            ->get();
    }

    public function saveVersion($templateId, $data) {
        $template = $this->getById($templateId);
        if (!$template) return false;

        // Get the latest version number
        $latestVersion = static::query()
            ->from($this->version_table)
            ->where('template_id', '=', $templateId)
            ->max('version_number') ?: 0;

        return static::query()
            ->from($this->version_table)
            ->insert([
                'template_id' => $templateId,
                'content' => $data['content'],
                'version_number' => $latestVersion + 1,
                'created_by' => $data['user_id'],
                'comment' => $data['comment'] ?? ''
            ]);
    }

    public function getVersions($templateId) {
        return static::query()
            ->from($this->version_table)
            ->where('template_id', '=', $templateId)
            ->orderBy('version_number', 'DESC')
            ->get();
    }

    public function getVersion($templateId, $versionNumber) {
        return static::query()
            ->from($this->version_table)
            ->where('template_id', '=', $templateId)
            ->where('version_number', '=', $versionNumber)
            ->first();
    }

    public function restoreVersion($templateId, $versionNumber) {
        $version = $this->getVersion($templateId, $versionNumber);
        if (!$version) return false;

        return static::query()
            ->where('id', '=', $templateId)
            ->update(['content' => $version['content']]);
    }

    public function setCategory($templateId, $categoryId) {
        return static::query()
            ->where('id', '=', $templateId)
            ->update(['category_id' => $categoryId]);
    }

    public function getCategories() {
        return static::query()
            ->from($this->category_table)
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function createCategory($data) {
        return static::query()
            ->from($this->category_table)
            ->insert([
                'name' => $data['name'],
                'description' => $data['description'] ?? ''
            ]);
    }

    public function previewTemplate($content, $data = []) {
        // Replace placeholders in template with actual data
        foreach ($data as $key => $value) {
            $content = \str_replace('{{' . $key . '}}', h($value), $content);
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

        return static::query()
            ->from($this->inheritance_table)
            ->getConnection()
            ->execute("INSERT INTO " . $this->inheritance_table . " (template_id, parent_id) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE parent_id = ?", [$templateId, $parentId, $parentId]);
    }

    private function hasCircularInheritance($templateId, $parentId, $visited = []) {
        if (\in_array($parentId, $visited)) {
            return true;
        }

        $visited[] = $parentId;
        $grandParentId = static::query()
            ->from($this->inheritance_table)
            ->where('template_id', '=', $parentId)
            ->select(['parent_id'])
            ->first();

        if (!$grandParentId) {
            return false;
        }

        return $this->hasCircularInheritance($templateId, $grandParentId['parent_id'], $visited);
    }

    public function addDependency($templateId, $componentId, $type = 'component') {
        return static::query()
            ->from($this->dependencies_table)
            ->insert([
                'template_id' => $templateId,
                'component_id' => $componentId,
                'dependency_type' => $type
            ]);
    }

    public function removeDependency($templateId, $componentId) {
        return static::query()
            ->from($this->dependencies_table)
            ->where('template_id', '=', $templateId)
            ->where('component_id', '=', $componentId)
            ->delete();
    }

    public function getDependencies($templateId) {
        return static::query()
            ->from($this->dependencies_table . ' as d')
            ->select(['d.*', 't.name as component_name'])
            ->join(static::$table . ' as t', 'd.component_id', '=', 't.id')
            ->where('d.template_id', '=', $templateId)
            ->get();
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
        $openBrackets = \substr_count($content, '{{');
        $closeBrackets = \substr_count($content, '}}');
        if ($openBrackets !== $closeBrackets) {
            return false;
        }

        // Check for invalid placeholder syntax
        if (\preg_match('/{{[^}]*{{|}}[^{]*}}/m', $content)) {
            return false;
        }

        return true;
    }

    private function hasRequiredSections($content) {
        // Check for minimum required template sections
        $requiredSections = ['content', 'header', 'footer'];
        foreach ($requiredSections as $section) {
            if (!\preg_match('/{{\s*' . $section . '\s*}}/i', $content)) {
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
        $parentId = static::query()
            ->from($this->inheritance_table)
            ->where('template_id', '=', $templateId)
            ->select(['parent_id'])
            ->first();

        if ($parentId) {
            $parent = $this->getById($parentId['parent_id']);
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
        \preg_match_all('/{{block\s+([^}]+)}}([^{]*){{\/block}}/s', $childContent, $childBlocks, PREG_SET_ORDER);

        foreach ($childBlocks as $block) {
            $blockName = \trim($block[1]);
            $blockContent = $block[2];
            $parentContent = \preg_replace(
                '/{{block\s+' . \preg_quote($blockName) . '}}[^{]*{{\/block}}/s',
                $blockContent,
                $parentContent
            );
        }

        return $parentContent;
    }

    private function injectComponent($content, $component) {
        // Replace component placeholders with actual component content
        $componentName = \preg_quote($component['name']);
        $content = \preg_replace(
            '/{{component\s+' . $componentName . '}}/',
            $component['content'],
            $content
        );

        return $content;
    }
}
