<?php

namespace AbmmHasan\Bucket\traits;

use AbmmHasan\Bucket\Array\Dotted;

trait ConfigTrait
{
    protected array $items = [];

    /**
     * Load Configuration file from a path
     *
     * @param string $path
     * @return bool
     */
    public function loadFile(string $path): bool
    {
        if (count($this->items) === 0 && file_exists($path)) {
            $this->items = include($path);
            return true;
        }
        return false;
    }

    /**
     * Load Configuration array
     *
     * @param array $resource
     * @return bool
     */
    public function loadArray(array $resource): bool
    {
        if (count($this->items) === 0) {
            $this->items = $resource;
            return true;
        }
        return false;
    }

    /**
     * Get all the configuration
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Check if configuration exist
     *
     * @param array|string $keys
     * @return bool
     */
    public function has(array|string $keys): bool
    {
        return Dotted::has($this->items, $keys);
    }

    /**
     * Get configuration by key(s)
     *
     * @param array|int|string|null $keys
     * @param mixed|null $default
     * @return mixed
     */
    public function get(array|int|string $keys = null, mixed $default = null): mixed
    {
        return Dotted::get($this->items, $keys, $default);
    }

    /**
     * Set configuration value by key(s)
     *
     * @param array|string|null $keys
     * @param mixed|null $value
     * @return bool
     */
    public function set(array|string $keys = null, mixed $value = null): bool
    {
        return Dotted::set($this->items, $keys, $value);
    }

    /**
     * Prepend a value to a configuration
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function prepend(string $key, mixed $value): bool
    {
        $array = $this->get($key, []);
        array_unshift($array, $value);
        return $this->set($key, $array);
    }

    /**
     * Append a value to a configuration
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function append(string $key, mixed $value): bool
    {
        $array = $this->get($key, []);
        $array[] = $value;
        return $this->set($key, $array);
    }
}
