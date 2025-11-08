<?php

namespace App\Core\Database\Relations;

use App\Core\Database\Model;

class Pivot extends Model {
    /**
     * The parent model of the relationship.
     */
    public $pivotParent;
    
    /**
     * The name of the foreign key column.
     */
    protected $foreignKey;
    
    /**
     * The name of the "other key" column.
     */
    protected $relatedKey;
    
    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];
    
    /**
     * Create a new pivot model instance.
     */
    public function __construct(
        $parent,
        array $attributes,
        $table,
        $foreignKey,
        $relatedKey,
        $parentKey,
        $relatedKeyName,
        $relationName = null
    ) {
        $this->setTable($table);
        $this->pivotParent = $parent;
        $this->foreignKey = $foreignKey;
        $this->relatedKey = $relatedKey;
        
        parent::__construct($attributes);
    }
    
    /**
     * Get the foreign key column name.
     */
    public function getForeignKey() {
        return $this->foreignKey;
    }
    
    /**
     * Get the "related key" column name.
     */
    public function getRelatedKey() {
        return $this->relatedKey;
    }
    
    /**
     * Get the table associated with the model.
     */
    public function getTable() {
        return $this->table ?? parent::getTable();
    }
    
    /**
     * Set the table associated with the model.
     */
    public function setTable($table) {
        $this->table = $table;
        
        return $this;
    }
    
    /**
     * Get the queueable identity for the entity.
     */
    public function getQueueableId() {
        return $this->getKey();
    }
    
    /**
     * Get the queueable relationships for the entity.
     */
    public function getQueueableRelations() {
        return [];
    }
    
    /**
     * Get the queueable connection for the entity.
     */
    public function getQueueableConnection() {
        return $this->getConnectionName();
    }
    
    /**
     * Get the connection name for the model.
     */
    public function getConnectionName() {
        return $this->connection ?? config('database.default');
    }
}
