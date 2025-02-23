<?php

declare(strict_types=1);

namespace Infocyph\ArrayKit\Array;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Class ArrayMulti
 *
 * Provides operations for multi-dimensional (often 2D) arrays.
 * Includes collapsing, flattening, recursive sorting, chunking,
 * advanced filtering (whereIn, etc.), groupBy, partition, etc.
 */
class ArrayMulti
{
    /**
     * Get a subset of each sub-array from the given 2D array based on specified keys.
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
     * Get the maximum depth of a nested array.
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

        return $depth + 1;  // getDepth is zero-based
    }

    /**
     * Recursively flatten a multi-dimensional array into a single-level array.
     */
    public static function flatten(array $array, float|int $depth = INF): array
    {
        $result = [];

        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } else {
                // If depth is 1, flatten only one level
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
     */
    public static function flattenByKey(array $array): array
    {
        return iterator_to_array(
            new RecursiveIteratorIterator(new RecursiveArrayIterator($array)),
            false
        );
    }

    /**
     * Recursively sort an array by its keys and values (associative vs. sequential).
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
     * Get the first "row" in a 2D array that optionally matches a callback.
     */
    public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
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
     * Get the last "row" in a 2D array that optionally matches a callback.
     */
    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return empty($array) ? $default : end($array);
        }

        // Reverse array with preserve_keys = true
        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Return rows where a certain key is between two values (inclusive).
     */
    public static function between(array $array, string $key, float|int $from, float|int $to): array
    {
        $new = [];

        foreach ($array as $index => $item) {
            if (
                ArraySingle::exists($item, $key) &&
                compare($item[$key], $from, '>=') &&
                compare($item[$key], $to, '<=')
            ) {
                $new[$index] = $item;
            }
        }

        return $new;
    }

    /**
     * Filter a 2D array using a callback on each row.
     */
    public static function whereCallback(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return empty($array) ? $default : $array;
        }

        return array_filter($array, fn($item, $index) => $callback($item, $index), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Filter a 2D array by a single key's comparison (like "where 'age' > 18").
     */
    public static function where(array $array, string $key, mixed $operator = null, mixed $value = null): array
    {
        if ($value === null && $operator !== null) {
            $value    = $operator;
            $operator = null;
        }

        $new = [];
        foreach ($array as $index => $item) {
            if (ArraySingle::exists($item, $key) && compare($item[$key], $value, $operator)) {
                $new[$index] = $item;
            }
        }

        return $new;
    }

    /********************************************************************************
     *                      New -Inspired / Extended Methods                  *
     ********************************************************************************/

    /**
     * Break the top-level array into multiple smaller arrays (chunks).
     * Each chunk is still an array of "rows".
     */
    public static function chunk(array $array, int $size, bool $preserveKeys = false): array
    {
        if ($size <= 0) {
            // Could throw an exception, or just return single chunk:
            return [$array];
        }
        return array_chunk($array, $size, $preserveKeys);
    }

    /**
     * Apply a callback to each "row" and return a new array of transformed rows.
     * Equivalent to "map" for a 2D array's top-level dimension.
     */
    public static function map(array $array, callable $callback): array
    {
        $results = [];
        foreach ($array as $key => $row) {
            $results[$key] = $callback($row, $key);
        }
        return $results;
    }

    /**
     * Execute a callback on each "row", returning the original array (for chaining).
     */
    public static function each(array $array, callable $callback): array
    {
        foreach ($array as $key => $row) {
            // If callback returns false explicitly, break early
            if ($callback($row, $key) === false) {
                break;
            }
        }
        return $array;
    }

    /**
     * Reduce the top-level array to a single value (e.g. summation, etc.).
     */
    public static function reduce(array $array, callable $callback, mixed $initial = null): mixed
    {
        $accumulator = $initial;
        foreach ($array as $key => $row) {
            $accumulator = $callback($accumulator, $row, $key);
        }
        return $accumulator;
    }

    /**
     * Determine if at least one row passes a truth test.
     */
    public static function some(array $array, callable $callback): bool
    {
        foreach ($array as $key => $row) {
            if ($callback($row, $key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if all rows pass a truth test.
     */
    public static function every(array $array, callable $callback): bool
    {
        foreach ($array as $key => $row) {
            if (!$callback($row, $key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the array (of rows) contains at least one row matching a condition or a direct "row" in_array check.
     *
     * - If $valueOrCallback is callable => we pass each row into it until true.
     * - Otherwise => checks in_array($valueOrCallback, $array).
     */
    public static function contains(array $array, mixed $valueOrCallback, bool $strict = false): bool
    {
        if (is_callable($valueOrCallback)) {
            return static::some($array, $valueOrCallback);
        }
        // Attempt direct search for row value (works if each row is a scalar or the same shape).
        return in_array($valueOrCallback, $array, $strict);
    }

    /**
     * Return only the unique rows. (2D de-dup by comparing entire row with strict = false/true).
     */
    public static function unique(array $array, bool $strict = false): array
    {
        // If each "row" is array, strict in_array() might be trickier, but we can still handle it:
        // We'll track a list of "serialized" rows or something to compare them.
        $seen = [];
        $results = [];

        foreach ($array as $key => $row) {
            // If the row is itself an array, serialize it
            $compareValue = is_array($row) ? serialize($row) : $row;
            if (!in_array($compareValue, $seen, $strict)) {
                $seen[] = $compareValue;
                $results[$key] = $row;
            }
        }

        return $results;
    }

    /**
     * Filter out rows for which a callback is true (i.e. keep only "falsey" rows).
     * Opposite of a "whereCallback" style filter. (similar to Collection::reject)
     *
     * If $callback is not callable, we do a basic != comparison.
     */
    public static function reject(array $array, mixed $callback = true): array
    {
        if (is_callable($callback)) {
            return array_filter($array, fn($row, $key) => !$callback($row, $key), ARRAY_FILTER_USE_BOTH);
        }
        // Otherwise, do direct != comparison
        return array_filter($array, fn($row) => $row != $callback);
    }

    /**
     * Partition the array into two arrays [passed, failed] based on a callback.
     * Each part is a sub-array of rows.
     */
    public static function partition(array $array, callable $callback): array
    {
        $passed = [];
        $failed = [];

        foreach ($array as $key => $row) {
            if ($callback($row, $key)) {
                $passed[$key] = $row;
            } else {
                $failed[$key] = $row;
            }
        }

        return [$passed, $failed];
    }

    /**
     * "Skip" the first $count rows (like array_slice).
     */
    public static function skip(array $array, int $count): array
    {
        return array_slice($array, $count, null, true);
    }

    /**
     * Skip rows while the callback returns true; once it returns false, the remainder are kept.
     */
    public static function skipWhile(array $array, callable $callback): array
    {
        $result = [];
        $skipping = true;

        foreach ($array as $key => $row) {
            if ($skipping && !$callback($row, $key)) {
                $skipping = false;
            }
            if (!$skipping) {
                $result[$key] = $row;
            }
        }
        return $result;
    }

    /**
     * Skip rows until the callback returns true, then return the remainder.
     */
    public static function skipUntil(array $array, callable $callback): array
    {
        return static::skipWhile($array, fn($row, $key) => !$callback($row, $key));
    }

    /**
     * Summation across all rows in a 2D array, optionally by callback or a key.
     * If $keyOrCallback is a string, sum that column. If callable, sum callback($row).
     */
    public static function sum(array $array, string|callable|null $keyOrCallback = null): float|int
    {
        $total = 0;

        foreach ($array as $row) {
            if ($keyOrCallback === null) {
                // If row is numeric, add it. But in 2D, row may be sub-array => skip or handle
                if (is_numeric($row)) {
                    $total += $row;
                }
            } elseif (is_callable($keyOrCallback)) {
                $total += $keyOrCallback($row);
            } else {
                // Assume string key => sum that column if it exists
                if (isset($row[$keyOrCallback]) && is_numeric($row[$keyOrCallback])) {
                    $total += $row[$keyOrCallback];
                }
            }
        }

        return $total;
    }

    /**
     * Filter rows where "column" matches one of the given $values. (like "WHERE col IN (...)")
     *
     * @param array $array
     * @param string $key     The sub-item key to compare
     * @param array  $values  The allowed values
     * @param bool   $strict  Use strict comparison
     * @return array
     */
    public static function whereIn(array $array, string $key, array $values, bool $strict = false): array
    {
        return array_filter($array, function ($row) use ($key, $values, $strict) {
            return isset($row[$key]) && in_array($row[$key], $values, $strict);
        });
    }

    /**
     * Filter rows where "column" is NOT in the given $values.
     */
    public static function whereNotIn(array $array, string $key, array $values, bool $strict = false): array
    {
        return array_filter($array, function ($row) use ($key, $values, $strict) {
            return !isset($row[$key]) || !in_array($row[$key], $values, $strict);
        });
    }

    /**
     * Filter rows where a column is null. (similar to whereNull)
     */
    public static function whereNull(array $array, string $key): array
    {
        return array_filter($array, fn($row) => !empty($row) && array_key_exists($key, $row) && $row[$key] === null);
    }

    /**
     * Filter rows where a column is NOT null.
     */
    public static function whereNotNull(array $array, string $key): array
    {
        return array_filter($array, fn($row) => isset($row[$key]) && $row[$key] !== null);
    }

    /**
     * Group a 2D array by a given column or a callback. Similar to 's groupBy.
     */
    public static function groupBy(array $array, string|callable $groupBy, bool $preserveKeys = false): array
    {
        $results = [];

        foreach ($array as $key => $row) {
            $gKey = null;
            if (is_callable($groupBy)) {
                $gKey = $groupBy($row, $key);
            } elseif (isset($row[$groupBy])) {
                $gKey = $row[$groupBy];
            } else {
                $gKey = '_undefined';
            }

            if ($preserveKeys) {
                $results[$gKey][$key] = $row;
            } else {
                $results[$gKey][] = $row;
            }
        }

        return $results;
    }

    /**
     * Sort the array of rows by a column or callback (similar to Collection::sortBy).
     * By default ascending, pass $desc=true for descending.
     */
    public static function sortBy(array $array, string|callable $by, bool $desc = false, int $options = SORT_REGULAR): array
    {
        uasort($array, function ($a, $b) use ($by, $desc, $options) {
            $valA = is_callable($by) ? $by($a) : ($a[$by] ?? null);
            $valB = is_callable($by) ? $by($b) : ($b[$by] ?? null);

            if ($valA === $valB) {
                return 0;
            }
            // Asc compare
            $comparison = ($valA < $valB) ? -1 : 1;
            return $desc ? -$comparison : $comparison;
        });

        return $array;
    }

    /**
     * Sort the array of rows by a column or callback in descending order (shortcut).
     */
    public static function sortByDesc(array $array, string|callable $by, int $options = SORT_REGULAR): array
    {
        return static::sortBy($array, $by, true, $options);
    }
}
