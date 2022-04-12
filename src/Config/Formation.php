<?php

namespace AbmmHasan\Bucket\Config;

use AbmmHasan\Bucket\Array\Dotted;

class Formation
{
    protected static array $items = [];
    protected static array $hooks = [];

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
        return static::$items;
    }

    /**
     * Check if configuration exist
     *
     * @param array|string $keys
     * @return bool
     */
    public static function has(array|string $keys): bool
    {
        return Dotted::has(static::$items, $keys);
    }

    /**
     * Get configuration by key
     *
     * @param int|string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(int|string $key = null, mixed $default = null): mixed
    {
        return static::processValue(
            $key,
            Dotted::get(static::$items, $key, $default),
            'get'
        );
    }

    /**
     * Set configuration value by key
     *
     * @param string|null $key
     * @param mixed|null $value
     * @return bool
     */
    public static function set(string $key = null, mixed $value = null): bool
    {
        return Dotted::set(
            static::$items,
            $key,
            static::processValue($key, $value, 'set')
        );
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
        $array = static::get($key, []);
        array_unshift($array, $value);
        return static::set($key, $array);
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
        $array = static::get($key, []);
        $array[] = $value;
        return static::set($key, $array);
    }

    /**
     * Set on-get rule
     *
     * @param string $key
     * @param callable $callback
     */
    public static function onGet(string $key, callable $callback)
    {
        static::addHook($key, 'get', $callback);
    }

    /**
     * Set on-set rule
     *
     * @param string $key
     * @param callable $callback
     */
    public static function onSet(string $key, callable $callback)
    {
        static::addHook($key, 'set', $callback);
    }

    /**
     * Add callable hook
     *
     * @param mixed $offset
     * @param string $direction
     * @param callable $callback
     */
    protected static function addHook(mixed $offset, string $direction, callable $callback)
    {

        $name = static::getHookName($offset, $direction);
        if (!in_array($callback, static::$hooks[$name] ?? [])) {
            static::$hooks[$name][] = $callback;
        }
    }

    /**
     * Get the value after processing
     *
     * @param mixed $offset
     * @param mixed $value
     * @param string $direction
     * @return mixed
     */
    protected static function processValue(mixed $offset, mixed $value, string $direction): mixed
    {
        $hooks = static::$hooks[static::getHookName($offset, $direction)] ?? [];
        foreach ($hooks as $hook) {
            $value = $hook($value);
        }
        return $value;
    }

    /**
     * Get hook name
     *
     * @param string $hook
     * @param string $direction
     * @return string
     */
    protected static function getHookName(string $hook, string $direction): string
    {
        return $hook . "-" . $direction;
    }
}