<?php

declare(strict_types=1);

namespace Infocyph\ArrayKit\Array;

/**
 * Class BaseArrayHelper
 *
 * Provides basic utility methods for array operations,
 * including checks for multidimensionality and wrapping
 * single values into arrays.
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
     * Check if at least one item in the array passes the given truth test.
     *
     * @param array $array The array to inspect
     * @param callable $callback A callback with signature: fn($value, $key): bool
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
     *
     * @param array $array The array to inspect
     * @param callable $callback A callback with signature: fn($value, $key): bool
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
     * @param array $array The array to search
     * @param callable $callback Callback with signature: fn($value, $key): bool
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
}
