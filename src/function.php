<?php

use AbmmHasan\Bucket\Config\Config;

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
        if (is_null($keys)) {
            return Config::class;
        }
        if (is_array($keys)) {
            return Config::set($keys);
        }
        return Config::get($keys, $default);
    }
}