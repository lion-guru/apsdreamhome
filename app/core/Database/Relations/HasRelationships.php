<?php

namespace App\Core\Database\Relations;

use App\Core\Database\Model;
use App\Core\Database\QueryBuilder;
use Closure;

/**
 * @mixin \App\Core\Database\Model
 */
trait HasRelationships {
    /**
     * The loaded relationships for the model.
     */
    protected $relations = [];
    
    /**
     * Define a one-to-one relationship.
     */
    public function hasOne($related, $foreignKey = null, $localKey = null) {
        $instance = $this->newRelatedInstance($related);
        
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();
        
        return $this->newHasOne(
            $instance->newQuery(),
            $this,
            $instance->getTable() . '.' . $foreignKey,
            $localKey
        );
    }
    
    /**
     * Define a one-to-many relationship.
     */
    public function hasMany($related, $foreignKey = null, $localKey = null) {
        $instance = $this->newRelatedInstance($related);
        
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();
        
        return $this->newHasMany(
            $instance->newQuery(),
            $this,
            $instance->getTable() . '.' . $foreignKey,
            $localKey
        );
    }
    
    /**
     * Define an inverse one-to-one or many relationship.
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null) {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }
        
        $instance = $this->newRelatedInstance($related);
        
        if (is_null($foreignKey)) {
            $foreignKey = snake_case($relation) . '_' . $instance->getKeyName();
        }
        
        $ownerKey = $ownerKey ?: $instance->getKeyName();
        
        return $this->newBelongsTo(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $ownerKey,
            $relation
        );
    }
    
    /**
     * Define a many-to-many relationship.
     */
    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null) {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToManyRelation();
        }
        
        $instance = $this->newRelatedInstance($related);
        
        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();
        
        if (is_null($table)) {
            $table = $this->joiningTable($related);
        }
        
        return $this->newBelongsToMany(
            $instance->newQuery(),
            $this,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(),
            $relation
        );
    }
    
    /**
     * Get the default foreign key name for the model.
     */
    public function getForeignKey() {
        return snake_case(class_basename($this)) . '_id';
    }
    
    /**
     * Get the joining table name for a many-to-many relation.
     */
    public function joiningTable($related) {
        $models = [
            str_replace('\\', '', Str::snake(class_basename($this))),
            str_replace('\\', '', Str::snake(class_basename($related))),
        ];
        
        sort($models);
        
        return strtolower(implode('_', $models));
    }
    
    /**
     * Create a new model instance for a related model.
     */
    protected function newRelatedInstance($class) {
        return new $class;
    }
    
    /**
     * Get a relationship value from a method.
     */
    protected function getRelationshipFromMethod($method) {
        $relation = $this->$method();
        
        if (!$relation instanceof Relation) {
            throw new \LogicException('Relationship method must return an object of type ' . Relation::class);
        }
        
        return tap($relation->getResults(), function($results) use ($method) {
            $this->setRelation($method, $results);
        });
    }
    
    /**
     * Set the specific relationship in the model.
     */
    public function setRelation($relation, $value) {
        $this->relations[$relation] = $value;
        return $this;
    }
    
    /**
     * Get a relationship.
     */
    public function getRelation($relation) {
        return $this->relations[$relation] ?? null;
    }
    
    /**
     * Get all the loaded relations for the instance.
     */
    public function getRelations() {
        return $this->relations;
    }
    
    /**
     * Determine if the given relation is loaded.
     */
    public function relationLoaded($key) {
        return array_key_exists($key, $this->relations);
    }
    
    /**
     * Create a new HasOne relationship.
     */
    protected function newHasOne(QueryBuilder $query, Model $parent, $foreignKey, $localKey) {
        return new HasOne($query, $parent, $foreignKey, $localKey);
    }
    
    /**
     * Create a new HasMany relationship.
     */
    protected function newHasMany(QueryBuilder $query, Model $parent, $foreignKey, $localKey) {
        return new HasMany($query, $parent, $foreignKey, $localKey);
    }
    
    /**
     * Create a new BelongsTo relationship.
     */
    protected function newBelongsTo(QueryBuilder $query, Model $child, $foreignKey, $ownerKey, $relation) {
        return new BelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
    }
    
    /**
     * Create a new BelongsToMany relationship.
     */
    protected function newBelongsToMany(QueryBuilder $query, Model $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation) {
        return new BelongsToMany($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation);
    }
    
    /**
     * Guess the "belongs to" relationship name.
     */
    protected function guessBelongsToRelation() {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2];
        return $caller['function'];
    }
    
    /**
     * Guess the "belongs to many" relationship name.
     */
    protected function guessBelongsToManyRelation() {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2];
        return $caller['function'];
    }
    
    /**
     * Handle dynamic method calls into the model.
     */
    public function __call($method, $parameters) {
        if (method_exists($this, 'get' . ucfirst($method) . 'Attribute')) {
            return $this->{'get' . ucfirst($method) . 'Attribute'}();
        }
        
        if (method_exists($this, $method)) {
            return $this->forwardCallTo($this, $method, $parameters);
        }
        
        if (in_array($method, ['increment', 'decrement'])) {
            return $this->$method(...$parameters);
        }
        
        if (strpos($method, 'where') === 0) {
            return $this->dynamicWhere($method, $parameters);
        }
        
        return $this->newQuery()->$method(...$parameters);
    }
    
    /**
     * Handle dynamic method calls into the model for relationships.
     */
    public function __get($key) {
        if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }
        
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
        
        return null;
    }
    
    /**
     * Determine if a get mutator exists for an attribute.
     */
    public function hasGetMutator($key) {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute');
    }
}
