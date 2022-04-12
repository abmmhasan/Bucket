<?php


namespace AbmmHasan\Bucket\Array;


use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class MultiDimension
{
    /**
     * Get a subset of the items from the given array (2D).
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    public static function only(array $array, array|string $keys): array
    {
        $result = [];
        $pick = array_flip((array)$keys);
        foreach ($array as $item) {
            $result[] = array_intersect_key($item, $pick);
        }
        return $result;
    }

    /**
     * Collapse an array of arrays into a single array (2D).
     *
     * @param array $array
     * @return array
     */
    public static function collapse(array $array): array
    {
        $results = [];

        foreach ($array as $values) {
            if (!is_array($values)) {
                continue;
            }
            $results = array_merge($results, $values);
        }
        return $results;
    }

    /**
     * Get depth of an array
     *
     * @param array $array
     * @return int
     */
    public static function depth(array $array): int
    {
        if (empty($array)) {
            return 0;
        }
        $depth = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
        foreach ($iterator as $ignored) {
            $depth = max($iterator->getDepth(), $depth);
        }
        return $depth + 1;
    }

    /**
     * Flatten into a single level
     *
     * @param array $array
     * @param $depth
     * @return array
     */
    public static function flatten(array $array, $depth = INF): array
    {
        $result = [];
        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : self::flatten($item, (int)$depth - 1);
                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Flatten a single level (by keeping keys intact)
     *
     * @param array $array
     * @return array
     */
    public static function flattenByKey(array $array): array
    {
        return iterator_to_array(
            new RecursiveIteratorIterator(new RecursiveArrayIterator($array))
        );
    }

    /**
     * Recursively sort an array by keys and values.
     *
     * @param array $array
     * @param int $options
     * @param bool $descending
     * @return array
     */
    public static function sortRecursive(array $array, int $options = SORT_REGULAR, bool $descending = false): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::sortRecursive($value, $options, $descending);
            }
        }

        if (OneDimension::isAssoc($array)) {
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
     * Get the first element in an array (2D).
     * or
     * Get the first item by the given key value pair (2D).
     *
     * @param array $array
     * @param callable|null $callback
     * @param mixed|null $default
     * @return mixed
     */
    public static function first(array $array, callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            if (empty($array)) {
                return $default;
            }
            foreach ($array as $item) {
                return $item;
            }
        }
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * Get the last element in an array (2D).
     * or
     * Get the last item by the given key value pair (2D).
     *
     * @param array $array
     * @param callable|null $callback
     * @param mixed|null $default
     * @return mixed
     */
    public static function last(array $array, callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return empty($array) ? $default : end($array);
        }
        return self::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Filter items such that the value of the given key is between the given values (2D).
     *
     * @param array $array
     * @param string $key
     * @param float|int $from
     * @param float|int $to
     * @return array
     */
    public static function between(array $array, string $key, float|int $from, float|int $to): array
    {
        $new = [];
        if (!empty($array)) {
            foreach ($array as $index => $item) {
                if (
                    OneDimension::exists($item, $key) &&
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
     * Returns a multidimensional array with given key-value filter (2D).
     *
     * @param array $array
     * @param callable|null $callback
     * @param mixed|null $default
     * @return mixed
     */
    public static function whereCallback(array $array, callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            if (empty($array)) {
                return $default;
            }
            return $array;
        }
        $new = [];
        foreach ($array as $index => $item) {
            if ($callback($item, $index)) {
                $new[$index] = $item;
            }
        }
        return $new;
    }

    /**
     * Returns a multidimensional array with given key-value filter (2D).
     *
     * @param array $array
     * @param string $key | $operator
     * @param null|string $operator | $value
     * @param null|string $value
     * @return array
     */
    public static function where(array $array, string $key, mixed $operator = null, mixed $value = null): array
    {
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = null;
        }
        $new = [];
        if (!empty($array)) {
            foreach ($array as $index => $item) {
                if (
                    OneDimension::exists($item, $key) &&
                    compare($item[$key], $value, $operator)
                ) {
                    $new[$index] = $item;
                }
            }
        }
        return $new;
    }

    /**
     * Get an array of all elements that pass a given test.
     *
     * @param array $array
     * @param string|callable|null $column
     * @param callable|mixed $callback
     * @return array
     */
    public static function accept(array $array, string|callable $column = null, string|callable $callback = null): array
    {
        if (empty($callback) && !empty($column)) {
            $callback = $column;
            $column = null;
        }
        $callable = isCallable($callback);
        return self::filter(
            $array, $column,
            function ($value) use ($callback, $callable) {
                return $callable
                    ? $callback($value)
                    : $value == $callback;
            }
        );
    }

    /**
     * Get an array of all elements that do not pass a given test.
     *
     * @param array $array
     * @param string|callable|null $column
     * @param callable|mixed $callback
     * @return array
     */
    public static function except(array $array, string|callable $column = null, string|callable $callback = null): array
    {
        if (empty($callback) && !empty($column)) {
            $callback = $column;
            $column = null;
        }
        $callable = isCallable($callback);
        return self::filter(
            $array, $column,
            function ($value) use ($callback, $callable) {
                return $callable
                    ? !$callback($value)
                    : $value != $callback;
            }
        );
    }

    /**
     * Returns Array with given callback,
     * if no callback then returns non-null, non-false values (preserves keys)
     *
     * @param array $array
     * @param String $column
     * @param callable|null $callback
     * @return array
     */
    public static function filter(array $array, string $column, callable $callback = null): array
    {
        $flag = null;
        if ($callback) {
            $flag = ARRAY_FILTER_USE_BOTH;
        }
        $process = $return = [];
        foreach ($array as $key => $value) {
            $process[$key] = $value[$column] ?? null;
        }
        $process = array_filter($process, $callback, $flag);
        foreach ($process as $key => $value) {
            $return[$key] = $array[$key];
        }
        return $return;
    }
}
