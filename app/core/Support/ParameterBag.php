<?php

namespace App\Core\Support;

/**
 * ParameterBag is a container for key/value pairs.
 */
class ParameterBag implements \Countable, \IteratorAggregate, \ArrayAccess {
    /**
     * Parameter storage.
     */
    protected $parameters;

    /**
     * @param array $parameters An array of parameters (keys must be strings or integers)
     * 
     * @throws \InvalidArgumentException If any key is not a string or integer
     */
    public function __construct(array $parameters = []) {
        foreach ($parameters as $key => $value) {
            if (!is_string($key) && !is_int($key)) {
                throw new \InvalidArgumentException('Parameter keys must be strings or integers');
            }
        }
        $this->parameters = $parameters;
    }

    /**
     * Returns the parameters.
     *
     * @return array An array of parameters
     */
    public function all(): array {
        return $this->parameters;
    }

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    public function keys(): array {
        return array_keys($this->parameters);
    }

    /**
     * Replaces the current parameters by a new set.
     *
     * @param array $parameters An array of parameters (keys must be strings or integers)
     * 
     * @throws \InvalidArgumentException If any key is not a string or integer
     */
    public function replace(array $parameters = []): void {
        foreach ($parameters as $key => $value) {
            if (!is_string($key) && !is_int($key)) {
                throw new \InvalidArgumentException('Parameter keys must be strings or integers');
            }
        }
        $this->parameters = $parameters;
    }

    /**
     * Adds parameters.
     *
     * @param array $parameters An array of parameters (keys must be strings or integers)
     * 
     * @throws \InvalidArgumentException If any key is not a string or integer
     */
    public function add(array $parameters = []): void {
        foreach ($parameters as $key => $value) {
            if (!is_string($key) && !is_int($key)) {
                throw new \InvalidArgumentException('Parameter keys must be strings or integers');
            }
        }
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    /**
     * Returns a parameter by name.
     *
     * @param mixed $key     The key
     * @param mixed $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get(mixed $key, mixed $default = null): mixed {
        return $this->has($key) ? $this->parameters[$key] : $default;
    }

    /**
     * Sets a parameter by name.
     *
     * @param mixed $key   The key (must be string or int)
     * @param mixed $value The value
     * 
     * @throws \InvalidArgumentException If the key is not a string or integer
     */
    public function set(mixed $key, mixed $value): void {
        if (!is_string($key) && !is_int($key)) {
            throw new \InvalidArgumentException('Parameter key must be a string or integer');
        }
        $this->parameters[$key] = $value;
    }

    /**
     * Returns true if the parameter is defined.
     *
     * @param mixed $key The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function has(mixed $key): bool {
        return is_string($key) || is_int($key) ? array_key_exists($key, $this->parameters) : false;
    }

    /**
     * Removes a parameter.
     *
     * @param mixed $key The key
     */
    public function remove(mixed $key): void {
        if ($this->has($key)) {
            unset($this->parameters[$key]);
        }
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count(): int {
        return \count($this->parameters);
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \Traversable An \ArrayIterator instance
     */
    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->parameters);
    }

    /**
     * Returns true if the parameter exists.
     *
     * @param mixed $offset The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function offsetExists(mixed $offset): bool {
        return $this->has($offset);
    }

    /**
     * Returns a parameter by name.
     *
     * @param mixed $offset The key
     *
     * @return mixed The parameter value or null if not set
     */
    public function offsetGet(mixed $offset): mixed {
        return $this->get($offset);
    }

    /**
     * Sets a parameter by name.
     *
     * @param mixed $offset The key
     * @param mixed $value  The value
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        $this->set($offset, $value);
    }

    /**
     * Removes a parameter.
     *
     * @param mixed $offset The key
     */
    public function offsetUnset(mixed $offset): void {
        $this->remove($offset);
    }
}
