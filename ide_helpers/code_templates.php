<?php
/**
 * Code templates for IDE enhancement
 */

class CodeTemplates {
    private $templates = [];
    
    public function __construct() {
        $this->loadTemplates();
    }
    
    private function loadTemplates() {
        $this->templates = [
            'controller' => '<?php
namespace App\\Controllers;

class {ClassName} extends Controller {
    public function index() {
        // Index method implementation
    }
    
    public function create() {
        // Create method implementation
    }
    
    public function store() {
        // Store method implementation
    }
    
    public function edit($id) {
        // Edit method implementation
    }
    
    public function update($id) {
        // Update method implementation
    }
    
    public function destroy($id) {
        // Destroy method implementation
    }
}',
            'model' => '<?php
namespace App\\Models;

class {ClassName} extends Model {
    protected $table = \'{tableName}\';
    
    protected $fillable = [
        // Fillable fields
    ];
    
    protected $hidden = [
        // Hidden fields
    ];
}',
            'view' => '<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    <h1>{heading}</h1>
    <div class="content">
        {content}
    </div>
</body>
</html>'
        ];
    }
    
    public function getTemplate($type) {
        return $this->templates[$type] ?? '';
    }
    
    public function addTemplate($type, $template) {
        $this->templates[$type] = $template;
    }
}
