<?php

namespace App\Core\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * The items contained in the collection.
     */
    protected $items = [];

    /**
     * Create a new collection.
     */
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Create a new collection instance if the value isn't one already.
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * Get all of the items in the collection.
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get the first item from the collection.
     */
    public function first()
    {
        return isset($this->items[0]) ? $this->items[0] : null;
    }

    /**
     * Get the last item from the collection.
     */
    public function last()
    {
        $count = $this->count();
        return $count > 0 ? $this->items[$count - 1] : null;
    }

    /**
     * Get the number of items in the collection.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Get an iterator for the items.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Convert the collection to its string representation.
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Get the collection of items as JSON.
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize(): mixed
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif (method_exists($value, 'toArray')) {
                return $value->toArray();
            }
            return $value;
        }, $this->items);
    }

    /**
     * Get the collection of items as a plain array.
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * Get an item from the collection by key.
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }
        return $default;
    }

    /**
     * Execute a callback over each item.
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }

    /**
     * Run a filter over each of the items.
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }
        return new static(array_filter($this->items));
    }

    /**
     * Get the first item from the collection.
     */
    public function firstWhere($key, $operator = null, $value = null)
    {
        return $this->filter($this->operatorForWhere(...func_get_args()))->first();
    }

    /**
     * Get an operator checker callback.
     */
    protected function operatorForWhere($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            $value = true;
            $operator = '=';
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return function ($item) use ($key, $operator, $value) {
            $retrieved = $item;
            
            if (is_array($item) && isset($item[$key])) {
                $retrieved = $item[$key];
            } elseif (is_object($item) && isset($item->{$key})) {
                $retrieved = $item->{$key};
            }

            switch ($operator) {
                default:
                case '=':
                case '==':  return $retrieved == $value;
                case '!=':
                case '<>':  return $retrieved != $value;
                case '<':   return $retrieved < $value;
                case '>':   return $retrieved > $value;
                case '<=':  return $retrieved <= $value;
                case '>=':  return $retrieved >= $value;
                case '===': return $retrieved === $value;
                case '!==': return $retrieved !== $value;
            }
        };
    }

    /**
     * Get the values of a given key.
     */
    public function pluck($value, $key = null)
    {
        $results = [];
        
        foreach ($this->items as $item) {
            $itemValue = $this->dataGet($item, $value);
            
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = $this->dataGet($item, $key);
                $results[$itemKey] = $itemValue;
            }
        }
        
        return new static($results);
    }

    /**
     * Get an item from an array or object using "dot" notation.
     */
    protected function dataGet($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return $this->value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = $this->dataGet($item, $key);
                }

                return in_array('*', $key) ? $this->collapse($result) : $result;
            }

            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return $this->value($default);
            }
        }

        return $target;
    }

    /**
     * Return the default value of the given value.
     */
    protected function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }

    /**
     * Collapse the collection of items into a single array.
     */
    protected function collapse($array)
    {
        $results = [];

        foreach ($array as $values) {
            if ($values instanceof self) {
                $values = $values->all();
            } elseif (!is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }

    /**
     * Get the arrayable items from an array or collection.
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    /**
     * Dynamically access collection items.
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set an item in the collection.
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Dynamically check if an item exists in the collection.
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically unset an item in the collection.
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }
}
