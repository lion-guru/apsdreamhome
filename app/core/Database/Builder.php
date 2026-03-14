<?php

// TODO: Add proper error handling with try-catch blocks


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
        if (is_array($id)) {
            return $this->findMany($id, $columns);
        }

        return $this->where($this->model->getPrimaryKey(), '=', $id)
            ->first($columns);
    }

    /**
     * Find multiple models by their primary keys.
     */
    public function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return new \App\Core\Support\Collection();
        }

        return $this->whereIn($this->model->getPrimaryKey(), $ids)
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

        $models = $builder->getModels($columns);
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->hydrate($models);
    }

    /**
     * Paginate the given query.
     */
    public function paginate($perPage = 15, $page = null)
    {
        $page = $page ?: (int)($_GET['page'] ?? 1);
        $total = $this->query->count();
        
        $results = $this->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     */
    public function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     */
    public function oldest($column = 'created_at')
    {
        return $this->orderBy($column, 'asc');
    }

    /**
     * Add a "where has" clause to the query.
     */
    public function whereHas($relation, $callback = null)
    {
        return $this;
    }

    /**
     * Add a "with count" clause to the query.
     */
    public function withCount($relations)
    {
        return $this;
    }

    /**
     * Get the hydrated models without eager loading.
     */
    public function getModels($columns = ['*'])
    {
        return $this->query->get($columns);
    }

    /**
     * Eager load the relationships for the models.
     */
    public function eagerLoadRelations(array $models)
    {
        return $models;
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
        return $this;
    }

    /**
     * Dynamically handle calls into the query instance.
     */
    public function __call($method, $parameters)
    {
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
        return $this->getQuery();
    }
}
