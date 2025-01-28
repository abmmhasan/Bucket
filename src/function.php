<?php

declare(strict_types=1);

use AbmmHasan\Bucket\Config\Config;
use AbmmHasan\Bucket\Config\DynamicConfig;

if (!function_exists('compare')) {
    /**
     * Compare two values using a specified operator.
     *
     * @param mixed       $retrieved The value to compare
     * @param mixed       $value     The reference value
     * @param string|null $operator  Supported operators:
     *                               '!=', '<>', 'ne', '<', 'lt', '>', 'gt',
     *                               '<=', 'lte', '>=', 'gte', '===', '!=='
     *                               or null/default for '=='.
     * @return bool True if comparison holds, false otherwise
     */
    function compare(mixed $retrieved, mixed $value, ?string $operator = null): bool
    {
        return match ($operator) {
            '!=', '<>', 'ne' => $retrieved != $value,
            '<', 'lt'        => $retrieved < $value,
            '>', 'gt'        => $retrieved > $value,
            '<=', 'lte'      => $retrieved <= $value,
            '>=', 'gte'      => $retrieved >= $value,
            '==='            => $retrieved === $value,
            '!=='            => $retrieved !== $value,
            default          => $retrieved == $value,
        };
    }
}

if (!function_exists('isCallable')) {
    /**
     * Determine if the given value is callable (but not a string).
     *
     * @param mixed $value
     * @return bool
     */
    function isCallable(mixed $value): bool
    {
        return !\is_string($value) && \is_callable($value);
    }
}

if (!function_exists('config')) {
    /**
     * Get/Set configuration via the Config class, or retrieve its singleton instance.
     *
     * Usage:
     *  - config(): returns the global Config instance.
     *  - config(['key' => 'value']): sets the config for 'key' to 'value'.
     *  - config('key'): gets the value of 'key'.
     *  - config('key', 'default'): gets the value of 'key', or 'default' if not found.
     *
     * @param array|int|string|null $keys    If null, returns the Config instance.
     *                                       If array, sets those key/value pairs.
     *                                       If string|int, retrieves that key's value.
     * @param mixed|null            $default Default value if key not found
     * @return Config|mixed
     */
    function config(array|int|string $keys = null, mixed $default = null)
    {
        // If no arguments, return the Config instance
        if ($keys === null) {
            return Config::instance();
        }

        // If an array is passed, set each key => value
        if (\is_array($keys)) {
            return Config::instance()->set($keys);
        }

        // Otherwise, retrieve the value for the given key
        return Config::instance()->get($keys, $default);
    }
}

if (!function_exists('formation')) {
    /**
     * Get/Set configuration using DynamicConfig or get its singleton instance.
     *
     * Usage:
     *  - formation(): returns the global DynamicConfig instance.
     *  - formation(['key' => 'value']): sets 'key' to 'value'.
     *  - formation('key'): retrieves the value of 'key'.
     *  - formation('key', 'default'): retrieves the value of 'key', or 'default' if missing.
     *
     * @param array|int|string|null $key     The key(s) or null for the instance
     * @param mixed|null            $default Default value if key not found
     * @return DynamicConfig|mixed
     */
    function formation(array|int|string $key = null, mixed $default = null)
    {
        // If no arguments, return the DynamicConfig instance
        if ($key === null) {
            return DynamicConfig::instance();
        }

        // If an array is passed, we assume a single key => value pair
        if (\is_array($key)) {
            // for consistency, let's set them all if it has multiple keys
            // or if you only want to set the first key, preserve the old logic
            return DynamicConfig::instance()->set($key);
        }

        // Otherwise retrieve the key's value
        return DynamicConfig::instance()->get($key, $default);
    }
}
