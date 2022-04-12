<?php

namespace AbmmHasan\Bucket\Config;

use AbmmHasan\Bucket\Array\Dotted;

class Config
{
    protected static array $items = [];

    /**
     * Load Configuration file from a path
     *
     * @param string $path
     * @return bool
     */
    public static function loadFile(string $path): bool
    {
        if (count(static::$items) === 0 && file_exists($path)) {
            static::$items = include($path);
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
    public static function loadArray(array $resource): bool
    {
        if (count(static::$items) === 0) {
            static::$items = $resource;
            return true;
        }
        return false;
    }

    /**
     * Get all the configuration
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$items;
    }

    /**
     * Check if configuration exist
     *
     * @param array|string $keys
     * @return bool
     */
    public static function has(array|string $keys): bool
    {
        return Dotted::has(self::$items, $keys);
    }

    /**
     * Get configuration by key(s)
     *
     * @param array|int|string|null $keys
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(array|int|string $keys = null, mixed $default = null): mixed
    {
        return Dotted::get(self::$items, $keys, $default);
    }

    /**
     * Set configuration value by key(s)
     *
     * @param array|string|null $keys
     * @param mixed|null $value
     * @return bool
     */
    public static function set(array|string $keys = null, mixed $value = null): bool
    {
        return Dotted::set(self::$items, $keys, $value);
    }

    /**
     * Prepend a value to a configuration
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function prepend(string $key, mixed $value): bool
    {
        $array = self::get($key, []);
        array_unshift($array, $value);
        return self::set($key, $array);
    }

    /**
     * Append a value to a configuration
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function append(string $key, mixed $value): bool
    {
        $array = self::get($key, []);
        $array[] = $value;
        return self::set($key, $array);
    }
}