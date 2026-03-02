<?php

namespace App\Core\Database;

use App\Core\Database\Model;
use App\Core\Database\QueryBuilder;

class Builder
{
    /**
     * The base query builder instance.
     */
    protected $query;

    /**
     * The model being queried.
     */
    protected $model;

    /**
     * The methods that should be returned from query builder.
     */
    protected $passthru = [
        'insert',
        'insertGetId',
        'getBindings',
        'toSql',
        'exists',
        'count',
        'min',
        'max',
        'avg',
        'sum',
        'getConnection',
        'raw',
        'getGrammar'
    ];

    /**
     * Create a new query builder instance.
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * Set a model instance for the model being queried.
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        $this->query->from($model->getTable());

        return $this;
    }

    /**
     * Get the underlying query builder instance.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set the underlying query builder instance.
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get the model instance being queried.
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Find a model by its primary key.
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }

        return $this->where($this->model->getQualifiedKeyName(), '=', $id)
            ->first($columns);
    }

    /**
     * Find multiple models by their primary keys.
     */
    public function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return $this->model->newCollection();
        }

        return $this->whereIn($this->model->getQualifiedKeyName(), $ids)
            ->get($columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (!is_null($result)) {
            return $result;
        }

        throw new \Exception("No query results for model [" . get_class($this->model) . "]");
    }

    /**
     * Execute the query and get the first result.
     */
    public function first($columns = ['*'])
    {
        return $this->take(1)->get($columns)->first();
    }

    /**
     * Execute the query and get the first result or throw an exception.
     */
    public function firstOrFail($columns = ['*'])
    {
        if (!is_null($model = $this->first($columns))) {
            return $model;
        }

        throw new \Exception("No query results for model [" . get_class($this->model) . "]");
    }

    /**
     * Execute the query as a "select" statement.
     */
    public function get($columns = ['*'])
    {
        $builder = $this->applyScopes();

        if (count($models = $builder->getModels($columns)) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->newCollection($models);
    }

    /**
     * Get the hydrated models without eager loading.
     */
    public function getModels($columns = ['*'])
    {
        return $this->model->hydrate(
            $this->query->get($columns)
        )->all();
    }

    /**
     * Eager load the relationships for the models.
     */
    public function eagerLoadRelations(array $models)
    {
        foreach ($this->eagerLoad as $name => $constraints) {
            if (!str_contains($name, '.')) {
                $models = $this->eagerLoadRelation($models, $name, $constraints);
            }
        }

        return $models;
    }

    /**
     * Eagerly load the relationship on a set of models.
     */
    protected function eagerLoadRelation(array $models, $name, $constraints)
    {
        $relation = $this->getRelation($name);

        $relation->addEagerConstraints($models);

        $models = $relation->initRelation($models, $name);

        $results = $relation->getEager();

        return $relation->match($models, $results, $name);
    }

    /**
     * Get the relation instance for the given relation name.
     */
    public function getRelation($name)
    {
        $relation = $this->getModel()->$name();

        if (!$relation instanceof Relation) {
            throw new \RuntimeException("Relationship method must return an object of type " . Relation::class);
        }

        $relation->addEagerConstraints($this->getModels());

        return $relation;
    }

    /**
     * Create a new instance of the model being queried.
     */
    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes);
    }

    /**
     * Apply the scopes to the query builder.
     */
    protected function applyScopes()
    {
        if (isset($this->scopes)) {
            foreach ($this->scopes as $scope) {
                $this->callScope($scope);
            }
        }

        return $this;
    }

    /**
     * Call the given scope on the underlying model.
     */
    protected function callScope($scope, $parameters = [])
    {
        array_unshift($parameters, $this);

        return $this->model->callScope(
            $scope,
            $parameters
        ) ?: $this;
    }

    /**
     * Dynamically handle calls into the query instance.
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->model, $scope = 'scope' . ucfirst($method))) {
            return $this->callScope([$this->model, $scope], $parameters);
        }

        if (in_array($method, $this->passthru)) {
            return $this->toBase()->$method(...$parameters);
        }

        $this->query->$method(...$parameters);

        return $this;
    }

    /**
     * Get the underlying query builder instance.
     */
    public function toBase()
    {
        return $this->applyScopes()->getQuery();
    }
}
