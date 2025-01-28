<?php

declare(strict_types=1);

namespace AbmmHasan\Bucket\Array;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Class ArrayMulti
 *
 * Provides operations for multidimensional arrays, including:
 * collapsing, flattening, recursive sorting, and more advanced
 * 2D or multidimensional filtering.
 */
class ArrayMulti
{
    /**
     * Get a subset of each sub-array from the given 2D (or multidimensional) array
     * based on specified keys.
     *
     * @param array        $array A multidimensional array (array of arrays)
     * @param array|string $keys  One or multiple keys to keep
     * @return array An array of arrays, each containing only the specified keys
     */
    public static function only(array $array, array|string $keys): array
    {
        $result = [];
        $pick   = array_flip((array) $keys);

        foreach ($array as $item) {
            if (is_array($item)) {
                $result[] = array_intersect_key($item, $pick);
            }
        }

        return $result;
    }

    /**
     * Collapse an array of arrays into a single (1D) array.
     *
     * @param array $array A multi-dimensional array
     * @return array The collapsed 1D array
     */
    public static function collapse(array $array): array
    {
        $results = [];

        foreach ($array as $values) {
            if (is_array($values)) {
                $results = array_merge($results, $values);
            }
        }

        return $results;
    }

    /**
     * Get the maximum depth of a multidimensional array.
     *
     * @param array $array The array to measure
     * @return int The maximum nesting depth
     */
    public static function depth(array $array): int
    {
        if (empty($array)) {
            return 0;
        }

        $depth    = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

        foreach ($iterator as $unused) {
            $depth = max($iterator->getDepth(), $depth);
        }

        // getDepth() is zero-based, so add 1 to get the actual depth
        return $depth + 1;
    }

    /**
     * Recursively flatten a multidimensional array into a single-level array.
     *
     * @param array   $array The array to flatten
     * @param float|int $depth The maximum depth to flatten (default: INF, meaning fully flatten)
     * @return array Flattened array
     */
    public static function flatten(array $array, float|int $depth = INF): array
    {
        $result = [];

        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } else {
                // If depth is 1, only flatten one level
                $values = ($depth === 1)
                    ? array_values($item)
                    : static::flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Flatten an array into a single level but preserve keys (recursive).
     *
     * @param array $array The array to flatten
     * @return array The flattened array with keys
     */
    public static function flattenByKey(array $array): array
    {
        return iterator_to_array(
            new RecursiveIteratorIterator(new RecursiveArrayIterator($array)),
            false
        );
    }

    /**
     * Recursively sort an array by its keys and values.
     *
     * - Uses ksort/krsort if the array is associative.
     * - Uses sort/rsort if the array is sequential.
     *
     * @param array $array      The array to sort
     * @param int   $options    Sorting options
     * @param bool  $descending If true, sort in descending order
     * @return array The sorted array
     */
    public static function sortRecursive(array $array, int $options = SORT_REGULAR, bool $descending = false): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value, $options, $descending);
            }
        }

        if (ArraySingle::isAssoc($array)) {
            $descending
                ? krsort($array, $options)
                : ksort($array, $options);
        } else {
            $descending
                ? rsort($array, $options)
                : sort($array, $options);
        }

        return $array;
    }

    /**
     * Get the first element in a 2D (or multidimensional) array.
     * Optionally pass a callback to find the first item that matches.
     *
     * @param array         $array    A 2D or multi-dimensional array
     * @param callable|null $callback If provided, must return true for the first matching item
     * @param mixed|null    $default  Fallback if no item or no match is found
     * @return mixed The found item or the default
     */
    public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            // Return the first item in the array if it exists
            return empty($array) ? $default : reset($array);
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Get the last element in a 2D (or multidimensional) array.
     * Optionally pass a callback to find the last item that matches.
     *
     * @param array         $array    A 2D or multi-dimensional array
     * @param callable|null $callback If provided, must return true for the last matching item
     * @param mixed|null    $default  Fallback if no match is found
     * @return mixed The found item or the default
     */
    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            // Return the last item in the array if it exists
            return empty($array) ? $default : end($array);
        }

        // Reverse array with preserve_keys = true, to find last matching
        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Filter items such that the value of the given key is between two values.
     * (Intended for 2D arrays, e.g. each sub-item has a certain key to compare.)
     *
     * @param array      $array A 2D or multi-dimensional array
     * @param string     $key   The sub-item key to compare
     * @param float|int  $from  Lower bound (inclusive)
     * @param float|int  $to    Upper bound (inclusive)
     * @return array A filtered array of items
     */
    public static function between(array $array, string $key, float|int $from, float|int $to): array
    {
        $new = [];

        if (!empty($array)) {
            foreach ($array as $index => $item) {
                // check that sub-item has the key
                if (ArraySingle::exists($item, $key) &&
                    compare($item[$key], $from, '>=') &&
                    compare($item[$key], $to, '<=')
                ) {
                    $new[$index] = $item;
                }
            }
        }

        return $new;
    }

    /**
     * Filter a multidimensional array using a callback.
     * Returns only items for which the callback returns true.
     *
     * @param array         $array    The array to filter
     * @param callable|null $callback Callback signature: fn($value, $key): bool
     * @param mixed|null    $default  Value returned if callback is null or array is empty
     * @return array Filtered array of items
     */
    public static function whereCallback(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return empty($array) ? $default : $array;
        }

        return array_filter($array, function ($item, $index) use ($callback) {
            return $callback($item, $index);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Return a multidimensional array filtered by a specified condition on one key.
     * - If $operator is null, uses "==" by default in 'compare' method.
     *
     * @param array       $array    The array to filter (2D or multi-dimensional)
     * @param string      $key      The key to compare
     * @param mixed|null  $operator Comparison operator or value if $value is null
     * @param mixed|null  $value    The value to compare against
     * @return array Filtered array of items
     */
    public static function where(array $array, string $key, mixed $operator = null, mixed $value = null): array
    {
        // If only two arguments are passed, treat it as $operator => $value
        if ($value === null && $operator !== null) {
            $value    = $operator;
            $operator = null;
        }

        $new = [];
        if (!empty($array)) {
            foreach ($array as $index => $item) {
                if (ArraySingle::exists($item, $key)
                    && compare($item[$key], $value, $operator)) {
                    $new[$index] = $item;
                }
            }
        }

        return $new;
    }

    /**
     * Get an array of items that pass a given test (callback) on a specific column/key.
     *
     * If $callback is not a callable, it’s treated as a value to match (==).
     * This is for 2D arrays (list of records/rows).
     *
     * @param array                  $array   The array to filter
     * @param string|callable|null   $column  If string, the sub-item key to check;
     *                                        if callable, used as the callback.
     * @param callable|mixed         $callback The callback or value for matching
     * @return array Filtered array of items
     */
    public static function accept(array $array, string|callable $column = null, string|callable $callback = null): array
    {
        if (empty($callback) && !empty($column)) {
            $callback = $column;
            $column   = null;
        }

        $isCallable = is_callable($callback);

        return static::filter(
            $array,
            $column,
            static function ($value) use ($callback, $isCallable) {
                return $isCallable
                    ? $callback($value)
                    : $value == $callback;
            }
        );
    }

    /**
     * Get an array of items that do NOT pass a given test (callback) on a specific column/key.
     *
     * If $callback is not a callable, it’s treated as a value to match (values != $callback).
     * This is for 2D arrays (list of records/rows).
     *
     * @param array                  $array    The array to filter
     * @param string|callable|null   $column   If string, the sub-item key to check;
     *                                         if callable, used as the callback.
     * @param callable|mixed         $callback The callback or value for matching
     * @return array Filtered array of items that do NOT match
     */
    public static function except(array $array, string|callable $column = null, string|callable $callback = null): array
    {
        if (empty($callback) && !empty($column)) {
            $callback = $column;
            $column   = null;
        }

        $isCallable = is_callable($callback);

        return static::filter(
            $array,
            $column,
            static function ($value) use ($callback, $isCallable) {
                return $isCallable
                    ? !$callback($value)
                    : $value != $callback;
            }
        );
    }

    /**
     * Filter the array by a specific column/key using a custom callback.
     * If no callback is provided, returns all non-null, non-false column values.
     *
     * This method effectively checks the column's values
     * and returns the entire row if that column's value passes the callback.
     *
     * @param array         $array    The 2D array to filter
     * @param string        $column   The column to inspect
     * @param callable|null $callback The function to apply to each column value
     * @return array Filtered rows (preserving keys)
     */
    public static function filter(array $array, string $column, ?callable $callback = null): array
    {
        if (empty($column)) {
            return $array;
        }

        // We'll build an array of just the column's values:
        // If the row is missing this column, we default to null
        $temp = array_map(function ($row) use ($column) {
            return $row[$column] ?? null;
        }, $array);

        // If callback is null, filter out falsey values
        $filtered = array_filter(
            $temp,
            $callback ?? static fn($val) => (bool) $val,
            ARRAY_FILTER_USE_BOTH
        );

        // Rebuild final results by picking only the rows that survived the filter
        $return = [];
        foreach ($filtered as $key => $_value) {
            $return[$key] = $array[$key];
        }

        return $return;
    }
}
