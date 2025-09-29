<?php

namespace App\Core\Database\Relations;

use App\Core\Database\Model;
use App\Core\Database\QueryBuilder;

class HasOne extends Relation {
    /**
     * The foreign key of the parent model.
     */
    protected $foreignKey;
    
    /**
     * The local key of the parent model.
     */
    protected $localKey;
    
    /**
     * Create a new has one relationship instance.
     */
    public function __construct(QueryBuilder $query, Model $parent, $foreignKey, $localKey) {
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        
        parent::__construct($query, $parent);
    }
    
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints() {
        if (static::$constraints) {
            $this->query->where($this->foreignKey, '=', $this->getParentKey());
        }
    }
    
    /**
     * Set the constraints for an eager load of the relation.
     */
    public function addEagerConstraints(array $models) {
        $this->query->whereIn(
            $this->foreignKey, 
            $this->getKeys($models, $this->localKey)
        );
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
        $dictionary = $this->buildDictionary($results);
        
        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);
            
            if (isset($dictionary[$key])) {
                $model->setRelation($relation, $dictionary[$key][0]);
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
     * Build model dictionary keyed by the relation's foreign key.
     */
    protected function buildDictionary($results) {
        $dictionary = [];
        
        foreach ($results as $result) {
            $dictionary[$result->{$this->foreignKey}][] = $result;
        }
        
        return $dictionary;
    }
    
    /**
     * Get the key value of the parent's local key.
     */
    protected function getParentKey() {
        return $this->parent->getAttribute($this->localKey);
    }
    
    /**
     * Get the key for comparing against the parent key in "has" query.
     */
    public function getHasCompareKey() {
        return $this->foreignKey;
    }
    
    /**
     * Get the name of the "where in" method for eager loading.
     */
    protected function whereInMethod(Model $model, $key) {
        return 'whereIn';
    }
}
