<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Models;

/**
 * CoreFunctions Model
 * Represents CoreFunctions data
 */
class CoreFunctions
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $function_name;

    /**
     * @var mixed
     */
    protected $description;

    /**
     * @var mixed
     */
    protected $parameters;

    /**
     * @var mixed
     */
    protected $created_at;

    /**
     * @var mixed
     */
    protected $updated_at;

    /**
     * Constructor
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['id', 'function_name', 'description', 'parameters', 'created_at', 'updated_at'])) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Get id
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get function_name
     * @return mixed
     */
    public function getFunctionname()
    {
        return $this->function_name;
    }

    /**
     * Get description
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get parameters
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get created_at
     * @return mixed
     */
    public function getCreatedat()
    {
        return $this->created_at;
    }

    /**
     * Get updated_at
     * @return mixed
     */
    public function getUpdatedat()
    {
        return $this->updated_at;
    }

}
