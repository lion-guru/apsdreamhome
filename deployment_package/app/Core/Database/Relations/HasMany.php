<?php

namespace App\Core\Database\Relations;

use App\Core\Database\Model;
use App\Core\Database\QueryBuilder;

class HasMany extends HasOne {
    /**
     * Get the results of the relationship.
     */
    public function getResults() {
        return $this->query->get();
    }
    
    /**
     * Initialize the relation on a set of models.
     */
    public function initRelation(array $models, $relation) {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
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
                $model->setRelation(
                    $relation, 
                    $this->related->newCollection($dictionary[$key])
                );
            }
        }
        
        return $models;
    }
    
    /**
     * Add the constraints for a relationship count query.
     */
    public function getRelationExistenceCountQuery($query, $parentQuery) {
        if ($parentQuery->getQuery()->from == $query->getQuery()->from) {
            return $this->getRelationExistenceCountQueryForSelfRelation($query, $parentQuery);
        }
        
        return parent::getRelationExistenceCountQuery($query, $parentQuery);
    }
    
    /**
     * Add the constraints for a relationship query on the same table.
     */
    public function getRelationExistenceCountQueryForSelfRelation($query, $parentQuery) {
        $query->select($this->getQualifiedFirstKeyName());
        
        $query->from($query->getModel()->getTable() . ' as ' . $hash = $this->getRelationCountHash());
        
        $key = $this->wrap($this->getQualifiedParentKeyName());
        
        return $query->where($this->getHasCompareKey(), '=', new Expression($key));
    }
    
    /**
     * Get a relationship join table hash.
     */
    public function getRelationCountHash() {
        return 'laravel_reserved_' . static::$selfJoinCount++;
    }
}
