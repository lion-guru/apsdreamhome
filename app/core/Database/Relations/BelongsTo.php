<?php

namespace App\Core\Database\Relations;

use App\Core\Database\Model;
use App\Core\Database\QueryBuilder;

class BelongsTo extends Relation {
    /**
     * The child model instance of the relation.
     */
    protected $child;
    
    /**
     * The foreign key of the parent model.
     */
    protected $foreignKey;
    
    /**
     * The associated key on the parent model.
     */
    protected $ownerKey;
    
    /**
     * The name of the relationship.
     */
    protected $relation;
    
    /**
     * Create a new belongs to relationship instance.
     */
    public function __construct(QueryBuilder $query, Model $child, $foreignKey, $ownerKey, $relation) {
        $this->child = $child;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
        $this->relation = $relation;
        
        parent::__construct($query, $child);
    }
    
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints() {
        if (static::$constraints) {
            $table = $this->related->getTable();
            $this->query->where($table . '.' . $this->ownerKey, '=', $this->child->{$this->foreignKey});
        }
    }
    
    /**
     * Set the constraints for an eager load of the relation.
     */
    public function addEagerConstraints(array $models) {
        $key = $this->related->getTable() . '.' . $this->ownerKey;
        $this->query->whereIn($key, $this->getEagerModelKeys($models));
    }
    
    /**
     * Gather the keys from an array of related models.
     */
    protected function getEagerModelKeys(array $models) {
        $keys = [];
        
        foreach ($models as $model) {
            if (!is_null($value = $model->{$this->foreignKey})) {
                $keys[] = $value;
            }
        }
        
        return array_unique($keys);
    }
    
    /**
     * Initialize the relation on a set of models.
     */
    public function initRelation(array $models, $relation) {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }
        
        return $models;
    }
    
    /**
     * Match the eagerly loaded results to their parents.
     */
    public function match(array $models, $results, $relation) {
        $foreign = $this->foreignKey;
        $other = $this->ownerKey;
        
        $dictionary = [];
        
        foreach ($results as $result) {
            $dictionary[$result->$other] = $result;
        }
        
        foreach ($models as $model) {
            if (isset($dictionary[$model->$foreign])) {
                $model->setRelation($relation, $dictionary[$model->$foreign]);
            }
        }
        
        return $models;
    }
    
    /**
     * Get the results of the relationship.
     */
    public function getResults() {
        return $this->query->first();
    }
    
    /**
     * Associate the model instance to the given parent.
     */
    public function associate($model) {
        $this->child->setAttribute($this->foreignKey, $model->getAttribute($this->ownerKey));
        
        return $this->child->setRelation($this->relation, $model);
    }
    
    /**
     * Dissociate the model from the given parent.
     */
    public function dissociate() {
        $this->child->setAttribute($this->foreignKey, null);
        
        return $this->child->setRelation($this->relation, null);
    }
    
    /**
     * Update the parent model on the relationship.
     */
    public function update(array $attributes) {
        $instance = $this->getResults();
        
        return $instance->fill($attributes)->save();
    }
    
    /**
     * Get the foreign key of the relationship.
     */
    public function getForeignKey() {
        return $this->foreignKey;
    }
    
    /**
     * Get the fully qualified foreign key of the relationship.
     */
    public function getQualifiedForeignKey() {
        return $this->child->getTable() . '.' . $this->foreignKey;
    }
    
    /**
     * Get the associated key of the relationship.
     */
    public function getOwnerKey() {
        return $this->ownerKey;
    }
    
    /**
     * Get the fully qualified associated key of the relationship.
     */
    public function getQualifiedOwnerKeyName() {
        return $this->related->getTable() . '.' . $this->ownerKey;
    }
    
    /**
     * Get the name of the relationship.
     */
    public function getRelation() {
        return $this->relation;
    }
}
