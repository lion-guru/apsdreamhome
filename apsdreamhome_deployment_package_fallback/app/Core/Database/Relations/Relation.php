<?php

namespace App\Core\Database\Relations;

use App\Core\Database\QueryBuilder;
use App\Core\Database\Model;

abstract class Relation {
    /**
     * The underlying query builder instance.
     */
    protected $query;
    
    /**
     * The parent model instance.
     */
    protected $parent;
    
    /**
     * The related model instance.
     */
    protected $related;
    
    /**
     * Create a new relation instance.
     */
    public function __construct(QueryBuilder $query, Model $parent) {
        $this->query = $query;
        $this->parent = $parent;
        $this->related = $query->getModel();
        
        $this->addConstraints();
    }
    
    /**
     * Set the base constraints on the relation query.
     */
    abstract public function addConstraints();
    
    /**
     * Set the constraints for an eager load of the relation.
     */
    abstract public function addEagerConstraints(array $models);
    
    /**
     * Initialize the relation on a set of models.
     */
    abstract public function initRelation(array $models, $relation);
    
    /**
     * Match the eagerly loaded results to their parents.
     */
    abstract public function match(array $models, $results, $relation);
    
    /**
     * Get the results of the relationship.
     */
    abstract public function getResults();
    
    /**
     * Get the underlying query for the relation.
     */
    public function getQuery() {
        return $this->query;
    }
    
    /**
     * Get the parent model of the relation.
     */
    public function getParent() {
        return $this->parent;
    }
    
    /**
     * Get the related model of the relation.
     */
    public function getRelated() {
        return $this->related;
    }
    
    /**
     * Handle dynamic method calls to the relationship.
     */
    public function __call($method, $parameters) {
        $result = $this->query->$method(...$parameters);
        
        if ($result === $this->query) {
            return $this;
        }
        
        return $result;
    }
}
