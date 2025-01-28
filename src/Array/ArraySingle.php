<?php

declare(strict_types=1);

namespace AbmmHasan\Bucket\Array;

/**
 * Class ArraySingle
 *
 * Provides operations for one-dimensional arrays, including
 * basic checks, slicing, and other common manipulations.
 */
class ArraySingle
{
    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array      $array The array to inspect
     * @param int|string $key   The key to check
     * @return bool True if the key exists
     */
    public static function exists(array $array, int|string $key): bool
    {
        // Note: isset() returns false for null values,
        // but array_key_exists() accounts for them.
        return isset($array[$key]) || array_key_exists($key, $array);
    }

    /**
     * Get a subset of the items from the given array by specifying keys.
     *
     * @param array        $array The array to filter
     * @param array|string $keys  A single key or multiple keys to keep
     * @return array Array containing only the specified keys
     */
    public static function only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Separate an array into two arrays: one with keys, the other with values.
     *
     * @param array $array The array to separate
     * @return array An associative array with 'keys' and 'values'
     */
    public static function separate(array $array): array
    {
        return [
            'keys'   => array_keys($array),
            'values' => array_values($array),
        ];
    }

    /**
     * Determine if an array is a sequential/list array (0-based, consecutive integer keys).
     *
     * @param array $array The array to check
     * @return bool True if the array is sequential
     */
    public static function isList(array $array): bool
    {
        // Checks if first key is 0 and keys are consecutive range from 0...count-1
        return static::exists($array, 0)
            && array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Determine if an array is associative (i.e., not a strict list).
     *
     * @param array $array The array to check
     * @return bool True if the array is associative
     */
    public static function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Push an item onto the beginning of an array, optionally with a specific key.
     *
     * @param array      $array The original array
     * @param mixed      $value The value to prepend
     * @param mixed|null $key   If specified, will be used as the key
     * @return array The modified array
     */
    public static function prepend(array $array, mixed $value, mixed $key = null): array
    {
        if ($key === null) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }
        return $array;
    }

    /**
     * Determine if all values in the array are positive numbers.
     *
     * @param array $array The array to check
     * @return bool True if every value is > 0
     */
    public static function isPositive(array $array): bool
    {
        // min() > 0 ensures all values are greater than zero
        return !empty($array) && min($array) > 0;
    }

    /**
     * Determine if all values in the array are negative numbers.
     *
     * @param array $array The array to check
     * @return bool True if every value is < 0
     */
    public static function isNegative(array $array): bool
    {
        // max() < 0 ensures all values are less than zero
        return !empty($array) && max($array) < 0;
    }

    /**
     * Shuffle the array. If a seed is provided, shuffle predictably.
     *
     * @param array    $array The array to shuffle
     * @param int|null $seed  Optional seed for randomization
     * @return array The shuffled array
     */
    public static function shuffle(array $array, ?int $seed = null): array
    {
        if ($seed === null) {
            shuffle($array);
        } else {
            mt_srand($seed);
            shuffle($array);
            mt_srand();
        }
        return $array;
    }

    /**
     * Determine if all values in the array are integers.
     *
     * @param array $array The array to check
     * @return bool True if every value is an integer
     */
    public static function isInt(array $array): bool
    {
        return $array === static::where($array, 'is_int');
    }

    /**
     * Return all non-empty values (non-null, non-empty strings, non-false) from the array.
     *
     * @param array $array The array to filter
     * @return array Filtered array of non-empty values
     */
    public static function nonEmpty(array $array): array
    {
        return array_values(static::where($array, 'strlen'));
    }

    /**
     * Calculate the average (mean) of numeric values in the array.
     *
     * @param array $array Array of numeric values
     * @return float|int The average value
     */
    public static function avg(array $array): float|int
    {
        return array_sum($array) / count($array);
    }

    /**
     * Check if the array contains only unique values.
     *
     * @param array $array The array to check
     * @return bool True if all values are unique
     */
    public static function isUnique(array $array): bool
    {
        // Compare the array count to the count of flipped keys.
        return count($array) === count(array_flip($array));
    }

    /**
     * Get only the positive numeric values from the array.
     *
     * @param array $array The array to filter
     * @return array Array containing only positive values
     */
    public static function positive(array $array): array
    {
        return static::where($array, static fn($value) => is_numeric($value) && $value > 0);
    }

    /**
     * Get only the negative numeric values from the array.
     *
     * @param array $array The array to filter
     * @return array Array containing only negative values
     */
    public static function negative(array $array): array
    {
        return static::where($array, static fn($value) => is_numeric($value) && $value < 0);
    }

    /**
     * Return every n-th element in the array, with an optional offset.
     *
     * @param array $array  The array to slice
     * @param int   $step   The step rate
     * @param int   $offset The offset index to start picking from
     * @return array The n-th elements
     */
    public static function nth(array $array, int $step, int $offset = 0): array
    {
        $results  = [];
        $position = 0;

        foreach ($array as $item) {
            if ($position % $step === $offset) {
                $results[] = $item;
            }
            $position++;
        }

        return $results;
    }

    /**
     * Retrieve an array of duplicate values (values that appear more than once).
     *
     * @param array $array The array to inspect
     * @return array An indexed array of duplicate values
     */
    public static function duplicates(array $array): array
    {
        $duplicates = [];
        foreach (array_count_values($array) as $value => $count) {
            if ($count > 1) {
                $duplicates[] = $value;
            }
        }
        return $duplicates;
    }

    /**
     * "Paginate" the array by slicing it into a smaller array segment.
     *
     * @param array $array   The array to paginate
     * @param int   $page    The current page (1-based)
     * @param int   $perPage Number of items per page
     * @return array The slice of the array for the specified page
     */
    public static function paginate(array $array, int $page, int $perPage): array
    {
        return array_slice(
            $array,
            max(0, ($page - 1) * $perPage),
            $perPage,
            true
        );
    }

    /**
     * Generate an array by using one array for keys and another for values.
     * If one array is shorter, only matches pairs up to that length.
     *
     * @param array $keys   The array of keys
     * @param array $values The array of values
     * @return array The combined array; empty if both arrays are empty
     */
    public static function combine(array $keys, array $values): array
    {
        $keyCount   = count($keys);
        $valueCount = count($values);

        if ($keyCount !== $valueCount) {
            $size   = ($keyCount > $valueCount) ? $valueCount : $keyCount;
            $keys   = array_slice($keys, 0, $size);
            $values = array_slice($values, 0, $size);
        }

        return array_combine($keys, $values) ?: [];
    }

    /**
     * Filter the array with a callback. If no callback is provided,
     * returns only the non-null, non-false values.
     *
     * @param array         $array    The array to filter
     * @param callable|null $callback The callback to apply; signature: fn($value, $key): bool
     * @return array Filtered array (preserves keys)
     */
    public static function where(array $array, ?callable $callback = null): array
    {
        $flag = ($callback !== null) ? ARRAY_FILTER_USE_BOTH : 0;
        return array_filter($array, $callback ?? fn($val) => (bool) $val, $flag);
    }

    /**
     * Search the array for the first item that matches a given condition or value.
     *
     * Usage:
     *  - If $needle is a callback, we'll check each $element until $callback($element, $key) === true.
     *  - Otherwise, if $needle is not a callable, we do a direct "value === $needle" check.
     *
     * @param array                $array  The array to search
     * @param mixed|callable       $needle The value to find or a callback
     * @return int|string|null The key if found, or null if not found
     */
    public static function search(array $array, mixed $needle): int|string|null
    {
        if (is_callable($needle)) {
            foreach ($array as $key => $value) {
                if ($needle($value, $key) === true) {
                    return $key;
                }
            }
            return null;
        }

        // Direct search for a value
        $foundKey = array_search($needle, $array, true);
        return $foundKey === false ? null : $foundKey;
    }
}
