<?php

namespace App\Models;

/**
 * Associate Model
 * Represents Associate data
 */
class Associate
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $user_id;

    /**
     * @var mixed
     */
    protected $code;

    /**
     * @var mixed
     */
    protected $level;

    /**
     * @var mixed
     */
    protected $commission_rate;

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
            if (in_array($key, ['id', 'user_id', 'code', 'level', 'commission_rate', 'created_at', 'updated_at'])) {
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
     * Get user_id
     * @return mixed
     */
    public function getUserid()
    {
        return $this->user_id;
    }

    /**
     * Get code
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get level
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Get commission_rate
     * @return mixed
     */
    public function getCommissionrate()
    {
        return $this->commission_rate;
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
