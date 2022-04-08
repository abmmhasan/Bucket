<?php


namespace AbmmHasan\Bucket\Array;


class OneDimension
{
    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array $array
     * @param int|string $key
     * @return bool
     */
    public static function exists(array $array, int|string $key): bool
    {
        return isset($array[$key]) || array_key_exists($key, $array);
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    public static function only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }

    /**
     * Separate an array into two arrays. One with keys and the other with values.
     *
     * @param array $array
     * @return array
     */
    public static function separate(array $array): array
    {
        return ["keys" => array_keys($array), "values" => array_values($array)];
    }

    /**
     * Determines if an array is sequential/non-associative/list.
     *
     * @param array $array
     * @return bool
     */
    public static function isList(array $array): bool
    {
        return self::exists($array, 0) && array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Determines if an array is associative.
     *
     * @param array $array
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Push an item onto the beginning of an array.
     *
     * @param array $array
     * @param mixed $value
     * @param mixed|null $key
     * @return array
     */
    public static function prepend(array $array, mixed $value, mixed $key = null): array
    {
        if (func_num_args() == 2) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }
        return $array;
    }

    /**
     * Checks if all the array values are positive value
     *
     * @param array $array
     * @return bool
     */
    public static function isPositive(array $array): bool
    {
        return min($array) > 0;
    }

    /**
     * Checks if all the array values are negative
     *
     * @param array $array
     * @return bool
     */
    public static function isNegative(array $array): bool
    {
        return max($array) < 0;
    }

    /**
     * Shuffle the given array and return the result.
     *
     * @param array $array
     * @param int|null $seed
     * @return array
     */
    public static function shuffle(array $array, int $seed = null): array
    {
        if (is_null($seed)) {
            shuffle($array);
        } else {
            mt_srand($seed);
            shuffle($array);
            mt_srand();
        }
        return $array;
    }

    /**
     * Checks if all the array values are integer
     *
     * @param array $array
     * @return bool
     */
    public static function isInt(array $array): bool
    {
        return $array === self::where($array, 'is_int');
    }

    /**
     * Returns all the values of an array except null, false and empty strings
     *
     * @param array $array
     * @return array
     */
    public static function nonEmpty(array $array): array
    {
        return array_values(self::where($array, 'strlen'));
    }

    /**
     * Returns average of values in an array
     *
     * @param array $array
     * @return float|int
     */
    public static function avg(array $array)
    {
        return array_sum($array) / count($array);
    }

    /**
     * Check if the array is unique
     *
     * @param array $array
     * @return bool
     */
    public static function isUnique(array $array): bool
    {
        return count($array) === count(array_flip($array));
    }

    /**
     * Get only Positive numeric values from an array
     *
     * @param array $array
     * @return array
     */
    public static function positive(array $array): array
    {
        return self::where($array, function ($v) {
            return (is_numeric($v) && $v > 0) + 0;
        });
    }

    /**
     * Get only Negative numeric values from an array
     *
     * @param array $array
     * @return array
     */
    public static function negative(array $array): array
    {
        return self::where($array, function ($v) {
            return (is_numeric($v) && $v < 0) + 0;
        });
    }

    /**
     * Return an array consisting of every n-th element.
     *
     * @param array $array
     * @param int $step
     * @param int $offset
     * @return array
     */
    public function nth(array $array, int $step, int $offset = 0): array
    {
        $return = [];
        $position = 0;
        foreach ($array as $item) {
            if ($position % $step === $offset) {
                $return[] = $item;
            }
            $position++;
        }
        return $return;
    }

    /**
     * Retrieve duplicate items
     *
     * @param array $array
     * @return array
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
     * "Paginate" the array by slicing it into a smaller array.
     *
     * @param array $array
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function paginate(array $array, int $page, int $perPage): array
    {
        return array_slice(
            $array,
            max(0, ($page - 1) * $perPage),
            $perPage,
            true
        );
    }

    /**
     * Generates an array by using one array for keys and another for its values
     * If arrays (keys and values) are of unequal length it will take minimal size in-between.
     *
     * @param array $keys
     * @param array $values
     * @return array|false
     */
    public static function combine(array $keys, array $values): bool|array
    {
        $keyCount = count($keys);
        $valueCount = count($values);
        if ($keyCount !== $valueCount) {
            $size = ($keyCount > $valueCount) ? $valueCount : $keyCount;
            $keys = array_slice($keys, 0, $size);
            $values = array_slice($values, 0, $size);
        }
        return array_combine($keys, $values);
    }

    /**
     * Returns Array with given callback,
     * if no callback then returns non-null, non-false values (preserves keys)
     *
     * @param array $array
     * @param callable|null $callback
     * @return array
     */
    public static function where(array $array, callable $callback = null): array
    {
        $flag = null;
        if ($callback !== null) {
            $flag = ARRAY_FILTER_USE_BOTH;
        }
        return array_filter($array, $callback, $flag);
    }
}
