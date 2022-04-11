<?php

namespace AbmmHasan\Bucket\Config;

use AbmmHasan\Bucket\Array\Dotted;

class Config
{
    protected static array $items = [];

    /**
     * Load Configuration array or Configuration file from a path
     *
     * @param array|string $pathOrResource
     * @return bool
     */
    public static function load(array|string $pathOrResource): bool
    {
        if (count(self::$items) === 0) {
            self::$items = is_array($pathOrResource)
                ? $pathOrResource
                : include($pathOrResource);
            return true;
        }
        return false;
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
     * Get all the configuration
     *
     * @return array
     */
    public function all(): array
    {
        return self::$items;
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