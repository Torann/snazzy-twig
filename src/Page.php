<?php

namespace Torann\SnazzyTwig;

use ArrayAccess;
use JsonSerializable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Page implements ArrayAccess, JsonSerializable
{
    /**
     * The page's attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Create a new page instance.
     *
     * @param array|Model $attributes
     */
    public function __construct($attributes = [])
    {
        // Convert a model into an array
        if ($attributes instanceof Model) {
            $attributes = $attributes->toArray();
        }

        foreach ($attributes as $key=>$value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * Set a given attribute on the page.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        $value = $this->getAttributeFromArray($key, $default);

        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set.
        if (method_exists($this, 'get' . Str::studly($key) . 'Attribute')) {
            $method = 'get' . Str::studly($key) . 'Attribute';

            return $this->{$method}($value);
        }

        return $value;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getAttributeFromArray($key, $default = null)
    {
        return isset($this->attributes[$key])
            ? $this->attributes[$key]
            : $default;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->attributes;
    }

    /**
     * Get the page's attribute
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Set the page's attribute
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * Check if the page's attribute is set
     *
     * @param $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Unset an attribute on the page.
     *
     * @param string $key
     *
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }
}