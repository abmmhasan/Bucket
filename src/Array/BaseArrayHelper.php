<?php

declare(strict_types=1);

namespace Infocyph\ArrayKit\Array;

/**
 * Class BaseArrayHelper
 *
 * Provides basic utility methods for array operations,
 * including checks for multidimensionality, wrapping,
 * and some general-purpose helpers akin to  Arr.
 */
class BaseArrayHelper
{
    /**
     * Determine if a variable is truly a multidimensional array.
     *
     * We compare count($array) with count($array, COUNT_RECURSIVE):
     * if they differ, there's at least one nested array.
     *
     * @param mixed $array Value to check
     * @return bool True if multidimensional, otherwise false
     */
    public static function isMultiDimensional(mixed $array): bool
    {
        return is_array($array) && count($array) !== count($array, COUNT_RECURSIVE);
    }

    /**
     * Wrap a value in an array if it isn't already an array (and not empty).
     *
     * If the given value is empty, return an empty array.
     *
     * @param mixed $value The value to wrap
     * @return array
     */
    public static function wrap(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Unwrap an array if it is exactly one single element; otherwise return the array as is.
     *
     * (This is not a standard  method, but sometimes helpful if you'd
     * like to "reduce" single-element arrays to just the element. Adapt as needed.)
     *
     * @param mixed $value The value to potentially unwrap
     * @return mixed
     */
    public static function unWrap(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        // If exactly one element, return it; else return the whole array
        return (count($value) === 1) ? reset($value) : $value;
    }

    /**
     * Check if at least one item in the array passes the given truth test.
     * (-like "some"/"any")
     *
     * @param array    $array    The array to inspect
     * @param callable $callback fn($value, $key): bool
     * @return bool True if the callback returns true for any item
     */
    public static function haveAny(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key) === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if all items in the array pass the given truth test.
     * (-like "every")
     *
     * @param array    $array    The array to inspect
     * @param callable $callback fn($value, $key): bool
     * @return bool True if the callback returns true for every item
     */
    public static function isAll(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Find the first key in the array for which the callback returns true.
     *
     * @param array    $array    The array to search
     * @param callable $callback fn($value, $key): bool
     * @return int|string|null The key if found, or null if no match
     */
    public static function findKey(array $array, callable $callback): int|string|null
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key) === true) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Determine whether the given value is "array accessible."
     * (In , checks if $value is an array or implements ArrayAccess.)
     *
     * @param mixed $value
     * @return bool
     */
    public static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Check if *all* of the given keys exist in the array (using "dot" notation not included here).
     * For a simple approach, if $keys is an array, we ensure each key is array_key_exists.
     *
     * @param array         $array
     * @param int|string|array $keys Single key or array of keys
     * @return bool
     */
    public static function has(array $array, int|string|array $keys): bool
    {
        $keys = (array) $keys;
        if (empty($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if *any* of the given keys exist in the array.
     *
     * @param array                $array
     * @param int|string|array     $keys
     * @return bool
     */
    public static function hasAny(array $array, int|string|array $keys): bool
    {
        $keys = (array) $keys;
        if (empty($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create a simple array of integers between $start and $end (inclusive),
     * with an optional $step, similar to range($start, $end, $step).
     *
     * @param int $start
     * @param int $end
     * @param int $step
     * @return array<int, int>
     */
    public static function range(int $start, int $end, int $step = 1): array
    {
        if ($step === 0) {
            // Could throw an exception, or return empty array
            return [];
        }
        return range($start, $end, $step);
    }

    /**
     * Invoke a callback a given number of times, collecting the results in an array.
     * (Similar to Collection::times in .)
     *
     * @param int              $number
     * @param callable|null    $callback fn(int $iteration) => mixed
     * @return array
     */
    public static function times(int $number, ?callable $callback = null): array
    {
        $results = [];
        if ($number < 1) {
            return $results;
        }

        for ($i = 1; $i <= $number; $i++) {
            $results[] = $callback ? $callback($i) : $i;
        }

        return $results;
    }

    /**
     * An alias for haveAny()—syntactic sugar for "any."
     *
     * @param array    $array
     * @param callable $callback
     * @return bool
     */
    public static function any(array $array, callable $callback): bool
    {
        return static::haveAny($array, $callback);
    }

    /**
     * An alias for isAll()—syntactic sugar for "all."
     *
     * @param array    $array
     * @param callable $callback
     * @return bool
     */
    public static function all(array $array, callable $callback): bool
    {
        return static::isAll($array, $callback);
    }
}
