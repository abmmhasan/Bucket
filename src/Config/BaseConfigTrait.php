<?php

declare(strict_types=1);

namespace Infocyph\ArrayKit\Config;

use Infocyph\ArrayKit\Array\DotNotation;

/**
 * Trait BaseConfigTrait
 *
 * Provides shared methods for loading and managing configuration items,
 * including checking if keys exist, retrieving values, and setting values.
 */
trait BaseConfigTrait
{
    /**
     * @var array Internal storage for config items
     */
    protected array $items = [];

    /**
     * Load configuration from a specified file path (PHP returning array).
     *
     * @param string $path The file path to load
     * @return bool True if loaded successfully, false if already loaded or file missing
     */
    public function loadFile(string $path): bool
    {
        if (count($this->items) === 0 && file_exists($path)) {
            $this->items = include $path;
            return true;
        }
        return false;
    }

    /**
     * Load configuration directly from an array resource.
     *
     * @param array $resource The array containing config items
     * @return bool True if loaded successfully, false if already loaded
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
     * Retrieve all configuration items.
     *
     * @return array The entire configuration array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Check if one or multiple keys exist in the configuration.
     *
     * @param string|array $keys Dot-notation key(s)
     * @return bool True if the key(s) exist
     */
    public function has(string|array $keys): bool
    {
        return DotNotation::has($this->items, $keys);
    }

    /**
     * Get one or multiple items from the configuration.
     *
     * @param string|int|array|null $key    Dot-notation key(s) or null for entire config
     * @param mixed|null            $default Default value if key not found
     * @return mixed The value(s) found or default
     */
    public function get(string|int|array $key = null, mixed $default = null): mixed
    {
        return DotNotation::get($this->items, $key, $default);
    }

    /**
     * Set a configuration value by dot-notation key.
     *
     * If no key is provided, replaces the entire config array with $value.
     *
     * @param string|array|null $key   Dot-notation key or [key => value] array
     * @param mixed|null        $value  The value to set
     * @return bool True on success
     */
    public function set(string|array|null $key = null, mixed $value = null): bool
    {
        return DotNotation::set($this->items, $key, $value);
    }

    /**
     * Prepend a value to a configuration array at the specified key.
     *
     * @param string $key   The dot-notation key referencing an array
     * @param mixed  $value The value to prepend
     * @return bool True on success
     */
    public function prepend(string $key, mixed $value): bool
    {
        $array = $this->get($key, []);
        array_unshift($array, $value);
        return $this->set($key, $array);
    }

    /**
     * Append a value to a configuration array at the specified key.
     *
     * @param string $key   The dot-notation key referencing an array
     * @param mixed  $value The value to append
     * @return bool True on success
     */
    public function append(string $key, mixed $value): bool
    {
        $array   = $this->get($key, []);
        $array[] = $value;
        return $this->set($key, $array);
    }
}
