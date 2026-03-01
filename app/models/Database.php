<?php

namespace App\Models;

/**
 * Database Model
 * Represents Database data
 */
class Database
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
    protected $host;

    /**
     * @var mixed
     */
    protected $username;

    /**
     * @var mixed
     */
    protected $password;

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
            if (in_array($key, ['id', 'name', 'host', 'username', 'password', 'created_at', 'updated_at'])) {
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
     * Get host
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get username
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get password
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
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
