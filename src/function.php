<?php

use AbmmHasan\Bucket\Config\Config;
use AbmmHasan\Bucket\Config\Formation;

if (!function_exists('compare')) {
    /**
     * Get compared according to the operator.
     *
     * @param $retrieved
     * @param $value
     * @param string|null $operator
     * @return bool.
     */
    function compare($retrieved, $value, string $operator = null): bool
    {
        return match ($operator) {
            '!=', '<>', 'ne' => $retrieved != $value,
            '<', 'lt' => $retrieved < $value,
            '>', 'gt' => $retrieved > $value,
            '<=', 'lte' => $retrieved <= $value,
            '>=', 'gte' => $retrieved >= $value,
            '===' => $retrieved === $value,
            '!==' => $retrieved !== $value,
            default => $retrieved == $value,
        };
    }
}
if (!function_exists('isCallable')) {
    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param mixed $value
     * @return bool
     */
    function isCallable(mixed $value): bool
    {
        return !is_string($value) && is_callable($value);
    }
}
if (!function_exists('config')) {
    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param array|int|string|null $keys
     * @param mixed|null $default
     * @return mixed
     */
    function config(array|int|string $keys = null, mixed $default = null): mixed
    {
        if ($keys === null) {
            return Config::class;
        }
        if (is_array($keys)) {
            return Config::set($keys);
        }
        return Config::get($keys, $default);
    }
}
if (!function_exists('formation')) {
    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param array|int|string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    function formation(array|int|string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return Formation::class;
        }
        if (is_array($key)) {
            return Formation::set(key($key), current($key));
        }
        return Formation::get($key, $default);
    }
}