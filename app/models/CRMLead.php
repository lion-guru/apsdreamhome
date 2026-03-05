<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Models;

/**
 * CRMLead Model
 * Represents CRMLead data
 */
class CRMLead
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
    protected $email;

    /**
     * @var mixed
     */
    protected $phone;

    /**
     * @var mixed
     */
    protected $status;

    /**
     * @var mixed
     */
    protected $source;

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
            if (in_array($key, ['id', 'name', 'email', 'phone', 'status', 'source', 'created_at', 'updated_at'])) {
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
     * Get name
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get email
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get phone
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
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
     * Get source
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
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
