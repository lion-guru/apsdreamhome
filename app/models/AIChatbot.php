<?php

namespace App\Models;

/**
 * AIChatbot Model
 * Represents AIChatbot data
 */
class AIChatbot
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $description;

    /**
     * @var mixed
     */
    protected $status;

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
            if (in_array($key, ['id', 'name', 'description', 'status', 'created_at', 'updated_at'])) {
                $this-> = $value;
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
     * Get name
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
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
     * Get status
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
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
