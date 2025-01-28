<?php

declare(strict_types=1);

namespace AbmmHasan\Bucket\Array;

/**
 * Class DotNotation
 *
 * Provides utilities for working with "dot" notation on associative arrays,
 * including flattening, expanding, and accessing nested data.
 */
class DotNotation
{
    /**
     * Flatten an associative array into a single level using dot notation for nested keys.
     *
     * @param array  $array   The array to flatten
     * @param string $prepend Optional prefix to prepend to flattened keys
     * @return array Flattened array
     */
    public static function flatten(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge(
                    $results,
                    static::flatten($value, $prepend . $key . '.')
                );
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Expand a "dot" notation array into a full multi-dimensional array.
     *
     * @param array $array The flattened array in dot notation
     * @return array Expanded array
     */
    public static function expand(array $array): array
    {
        $results = [];
        foreach ($array as $key => $value) {
            static::set($results, $key, $value);
        }

        return $results;
    }

    /**
     * Determine if an item or items exist in an array using dot notation.
     *
     * @param array        $array The array to inspect
     * @param array|string $keys  One or multiple keys in dot notation
     * @return bool True if all specified keys exist
     */
    public static function has(array $array, array|string $keys): bool
    {
        if (empty($array) || empty($keys)) {
            return false;
        }

        // If the key is a string and exists at top level, return immediately.
        if (is_string($keys) && ArraySingle::exists($array, $keys)) {
            return true;
        }

        $keys = (array) $keys;
        foreach ($keys as $key) {
            // If this key is found at the top level, continue.
            if (ArraySingle::exists($array, $key)) {
                continue;
            }
            // Otherwise check dot notation segments.
            if (!static::segment($array, $key, false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if any of the provided keys exist in an array using dot notation.
     *
     * @param array        $array The array to inspect
     * @param array|string $keys  One or multiple keys in dot notation
     * @return bool True if at least one specified key exists
     */
    public static function hasAny(array $array, array|string $keys): bool
    {
        if (empty($array) || empty($keys)) {
            return false;
        }

        $keys = (array) $keys;
        foreach ($keys as $key) {
            if (static::has($array, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get an item or items from an array using dot notation.
     *
     * @param array               $array   The array to retrieve from
     * @param array|int|string|null $keys   The key(s) in dot notation or integer
     * @param mixed|null          $default Default value if the key doesn't exist
     * @return mixed The retrieved value(s) or default
     */
    public static function get(array $array, array|int|string $keys = null, mixed $default = null): mixed
    {
        if (empty($array)) {
            return $default;
        }

        // If no key specified, return the entire array.
        if ($keys === null) {
            return $array;
        }

        // If multiple keys requested, gather each value.
        if (is_array($keys)) {
            $results = [];
            foreach ($keys as $key) {
                $results[$key] = static::getValue($array, $key, $default);
            }
            return $results;
        }

        // Single key retrieval.
        return static::getValue($array, $keys, $default);
    }

    /**
     * Set an array item (or items) to a given value using dot notation.
     *
     * @param array                $array   The original array (passed by reference)
     * @param array|string|null    $keys    Key(s) in dot notation
     * @param mixed|null           $value   The value to set
     * @return bool Returns true on success
     */
    public static function set(array &$array, array|string|null $keys = null, mixed $value = null): bool
    {
        // If no key is specified, replace the entire array.
        if ($keys === null) {
            $array = (array) $value;
            return true;
        }

        // If multiple sets are requested
        $keyValueList = is_array($keys) ? $keys : [$keys => $value];
        foreach ($keyValueList as $key => $val) {
            static::setValue($array, (string) $key, $val);
        }

        return true;
    }

    /**
     * Remove one or multiple array items from a given array using dot notation.
     *
     * @param array        $array The original array (passed by reference)
     * @param array|string $keys  The key(s) to remove
     * @return void
     */
    public static function forget(array &$array, array|string $keys): void
    {
        $original = &$array;
        $keys     = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // If exists at top-level, simply unset.
            if (ArraySingle::exists($array, $key)) {
                unset($array[$key]);
                continue;
            }

            // Otherwise navigate the dot path.
            $parts = explode('.', $key);
            $array = &$original;
            $count = count($parts);

            foreach ($parts as $i => $part) {
                if ($count - $i === 1) {
                    break;
                }
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[$parts[$count - 1]]);
        }
    }

    /**
     * Add an item to the array only if it does not exist using dot notation.
     *
     * @param array             $array The original array (passed by reference)
     * @param string|int|float  $key   The dot-notation key
     * @param mixed             $value Value to add
     * @return bool Returns true if the item was added, false if it already exists
     */
    public static function add(array &$array, string|int|float $key, mixed $value): bool
    {
        if (static::get($array, $key) === null) {
            static::set($array, (string) $key, $value);
            return true;
        }
        return false;
    }

    /**
     * Get a value from the array (by dot notation), and remove it.
     *
     * @param array  $array   The original array (passed by reference)
     * @param string $key     Dot-notation key
     * @param mixed  $default Default if key not found
     * @return mixed The found value or default
     */
    public static function pull(array &$array, string $key, mixed $default = null): mixed
    {
        $value = static::get($array, $key, $default);
        static::forget($array, $key);
        return $value;
    }

    /**
     * Append a value onto the end of an array item by dot notation key.
     *
     * @param array       $array The original array (passed by reference)
     * @param mixed       $value Value to append
     * @param string|null $key   Dot-notation key (if null, appends to root array)
     * @return void
     */
    public static function append(array &$array, mixed $value, ?string $key = null): void
    {
        if ($key !== null) {
            $target = static::get($array, $key, []);
        } else {
            $target = $array;
        }

        $target[] = $value;
        static::set($array, $key, $target);
    }

    /**
     * Retrieve multiple keys from the array (by dot notation) at once.
     *
     * Example:
     *  DotNotation::pluck($array, ['user.name', 'user.email']);
     *
     * @param array        $array   The array to retrieve from
     * @param array|string $keys    One or multiple dot-notation keys
     * @param mixed        $default Default value if a key doesn't exist
     * @return array An associative array of [key => value], preserving each requested key
     */
    public static function pluck(array $array, array|string $keys, mixed $default = null): array
    {
        $keys = (array) $keys;
        $results = [];

        foreach ($keys as $key) {
            $results[$key] = static::get($array, $key, $default);
        }

        return $results;
    }

    /**
     * Internal helper to set a nested array item using dot notation.
     *
     * @param array  $array Reference to the main array
     * @param string $key   The dot-notation key
     * @param mixed  $value The value to set
     * @return void
     */
    private static function setValue(array &$array, string $key, mixed $value): void
    {
        $keys  = explode('.', $key);
        $count = count($keys);

        foreach ($keys as $i => $segment) {
            if ($count - $i === 1) {
                break;
            }
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }
            $array = &$array[$segment];
        }

        $array[$keys[$count - 1]] = $value;
    }

    /**
     * Internal helper to get a nested array item using dot notation.
     *
     * @param array               $array
     * @param array|int|string    $key
     * @param mixed|null          $default
     * @return mixed
     */
    private static function getValue(array $array, array|int|string $key, mixed $default = null): mixed
    {
        if (is_int($key) || ArraySingle::exists($array, $key)) {
            return $array[$key] ?? $default;
        }

        if (!is_string($key) || !str_contains($key, '.')) {
            return $default;
        }

        return static::segment($array, $key, $default);
    }

    /**
     * Internal helper to traverse an array by "dot" segments.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    private static function segment(array $array, string $key, mixed $default = null): mixed
    {
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && ArraySingle::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
