<?php

namespace App\Core\Database\Relations;

use App\Core\Database\Model;
use App\Core\Database\QueryBuilder;

class BelongsToMany extends Relation {
    /**
     * The intermediate table for the relation.
     */
    protected $table;
    
    /**
     * The foreign key of the parent model.
     */
    protected $foreignPivotKey;
    
    /**
     * The associated key of the relation.
     */
    protected $relatedPivotKey;
    
    /**
     * The key name of the parent model.
     */
    protected $parentKey;
    
    /**
     * The key name of the related model.
     */
    protected $relatedKey;
    
    /**
     * The "name" of the relationship.
     */
    protected $relationName;
    
    /**
     * The pivot table columns to retrieve.
     */
    protected $pivotColumns = [];
    
    /**
     * Create a new belongs to many relationship instance.
     */
    public function __construct(
        QueryBuilder $query,
        Model $parent,
        $table,
        $foreignPivotKey,
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null
    ) {
        $this->table = $table;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        $this->relationName = $relationName;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        
        parent::__construct($query, $parent);
    }
    
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints() {
        $this->performJoin();
        
        if (static::$constraints) {
            $this->addWhereConstraints();
        }
    }
    
    /**
     * Set the join clause for the relation query.
     */
    protected function performJoin($query = null) {
        $query = $query ?: $this->query;
        
        $baseTable = $this->related->getTable();
        
        $key = $baseTable . '.' . $this->relatedKey;
        
        $query->join($this->table, $key, '=', $this->getQualifiedRelatedPivotKeyName());
        
        return $this;
    }
    
    /**
     * Set the where clause for the relation query.
     */
    protected function addWhereConstraints() {
        $this->query->where(
            $this->getQualifiedForeignPivotKeyName(), '=', $this->parent->{$this->parentKey}
        );
        
        return $this;
    }
    
    /**
     * Set the constraints for an eager load of the relation.
     */
    public function addEagerConstraints(array $models) {
        $this->query->whereIn(
            $this->getQualifiedForeignPivotKeyName(),
            $this->getKeys($models, $this->parentKey)
        );
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
            $key = $model->getAttribute($this->parentKey);
            
            if (isset($dictionary[$key])) {
                $model->setRelation(
                    $relation, $this->related->newCollection($dictionary[$key])
                );
            }
        }
        
        return $models;
    }
    
    /**
     * Build model dictionary keyed by the relation's foreign key.
     */
    protected function buildDictionary($results) {
        $dictionary = [];
        
        foreach ($results as $result) {
            $dictionary[$result->pivot->{$this->foreignPivotKey}][] = $result;
        }
        
        return $dictionary;
    }
    
    /**
     * Get the results of the relationship.
     */
    public function getResults() {
        return $this->get();
    }
    
    /**
     * Execute the query as a "select" statement.
     */
    public function get($columns = ['*']) {
        $columns = $this->query->getQuery()->columns ? [] : $columns;
        
        $builder = $this->query->applyScopes();
        
        $columns = $builder->getQuery()->columns ? [] : $columns;
        
        if ($columns == ['*']) {
            $columns = [$this->related->getTable() . '.'];
        }
        
        $models = $builder->addSelect(
            $this->shouldSelect($columns)
        )->getModels();
        
        $this->hydratePivotRelation($models);
        
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }
        
        return $this->related->newCollection($models);
    }
    
    /**
     * Hydrate the pivot table relationship on the models.
     */
    protected function hydratePivotRelation(array $models) {
        foreach ($models as $model) {
            $model->setRelation('pivot', $this->newPivot([
                $this->foreignPivotKey => $model->{$this->parentKey},
                $this->relatedPivotKey => $model->getKey(),
            ]));
        }
    }
    
    /**
     * Create a new pivot model instance.
     */
    public function newPivot(array $attributes = []) {
        return new Pivot(
            $this->parent,
            $attributes,
            $this->table,
            $this->foreignPivotKey,
            $this->relatedPivotKey,
            $this->parentKey,
            $this->relatedKey,
            $this->relationName
        );
    }
    
    /**
     * Get the select columns for the relation query.
     */
    protected function shouldSelect(array $columns = ['*']) {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable() . '.'];
        }
        
        return array_merge($columns, $this->aliasedPivotColumns());
    }
    
    /**
     * Get the pivot columns for the relation.
     */
    protected function aliasedPivotColumns() {
        $defaults = [$this->foreignPivotKey, $this->relatedPivotKey];
        
        $columns = [];
        
        foreach (array_merge($defaults, $this->pivotColumns) as $column) {
            $columns[] = $this->table . '.' . $column . ' as pivot_' . $column;
        }
        
        return array_unique($columns);
    }
    
    /**
     * Get the fully qualified foreign key for the relation.
     */
    public function getQualifiedForeignPivotKeyName() {
        return $this->table . '.' . $this->foreignPivotKey;
    }
    
    /**
     * Get the fully qualified "related key" for the relation.
     */
    public function getQualifiedRelatedPivotKeyName() {
        return $this->table . '.' . $this->relatedPivotKey;
    }
    
    /**
     * Get the intermediate table for the relationship.
     */
    public function getTable() {
        return $this->table;
    }
    
    /**
     * Get the foreign key for the relation.
     */
    public function getForeignPivotKeyName() {
        return $this->foreignPivotKey;
    }
    
    /**
     * Get the related key for the relation.
     */
    public function getRelatedPivotKeyName() {
        return $this->relatedPivotKey;
    }
    
    /**
     * Get the key for comparing against the parent key in "has" query.
     */
    public function getExistenceCompareKey() {
        return $this->getQualifiedForeignPivotKeyName();
    }
    
    /**
     * Get the key for comparing against the parent key in "has" query.
     */
    public function getParentKey() {
        return $this->parentKey;
    }
    
    /**
     * Get the name of the relationship.
     */
    public function getRelationName() {
        return $this->relationName;
    }
}
