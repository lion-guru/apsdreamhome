<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Core\Contracts;

interface Arrayable
{
    /**
     * Get the instance as an array.
     */
    public function toArray();
}
