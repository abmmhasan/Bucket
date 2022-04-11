<?php


namespace AbmmHasan\Bucket\Array;


class Dotted
{

    /**
     * Flatten associative array with dots.
     *
     * @param array $array
     * @param string $prepend
     * @return array
     */
    public static function flatten(array $array, string $prepend = ''): array
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, self::flatten($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Convert flattened "dot" notation array into an expanded array.
     *
     * @param array $array
     * @return array
     */
    public static function expand(array $array): array
    {
        $results = [];
        foreach ($array as $key => $value) {
            self::set($results, $key, $value);
        }
        return $results;
    }

    /**
     * Check if an item or items exist in an array
     *
     * @param array $array
     * @param array|string $keys
     * @return bool
     */
    public static function has(array $array, array|string $keys): bool
    {
        if (empty($array) || empty($keys)) {
            return false;
        }
        if (is_string($keys) && OneDimension::exists($array, $keys)) {
            return true;
        }
        $keys = (array)$keys;
        foreach ($keys as $key) {
            if (OneDimension::exists($array, $key)) {
                continue;
            }
            if (!self::segment($array, $key, false)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Determine if any of the keys exist in an array
     *
     * @param array $array
     * @param array|string $keys
     * @return bool
     */
    public static function hasAny(array $array, array|string $keys): bool
    {
        if (empty($array) || empty($keys)) {
            return false;
        }
        $keys = (array)$keys;
        foreach ($keys as $key) {
            if (self::has($array, $key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get item(s) from an array.
     *
     * @param array $array
     * @param array|int|string|null $keys
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(array $array, array|int|string $keys = null, mixed $default = null): mixed
    {
        if (empty($array)) {
            return $default;
        }
        if ($keys === null) {
            return $array;
        }
        if (is_array($keys)) {
            $asset = [];
            foreach ($keys as $key => $value) {
                $asset[$key] = self::getValue($array, $key, $value);
            }
            return $asset;
        }
        return self::getValue($array, $keys, $default);
    }

    /**
     * Set an array item to a given value
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array $array
     * @param array|string|null $keys
     * @param mixed $default
     * @return bool
     */
    public static function set(array &$array, array|string $keys = null, mixed $default = null): bool
    {
        if (is_null($keys)) {
            return $array = $default;
        }
        $keys = is_array($keys) ? $keys : [$keys => $default];
        foreach ($keys as $key => $item) {
            self::setValue($array, $key, $item);
        }
        return true;
    }

    /**
     * Remove one or many array items from a given array
     *
     * @param array $array
     * @param array|string $keys
     * @return void
     */
    public static function forget(array &$array, array|string $keys)
    {
        $original = &$array;
        $keys = (array)$keys;
        if (count($keys) === 0) {
            return;
        }
        foreach ($keys as $key) {
            if (OneDimension::exists($array, $key)) {
                unset($array[$key]);
                continue;
            }
            $parts = explode('.', $key);
            $array = &$original;
            $count = count($parts);
            foreach ($parts as $i => $del) {
                if ($count - $i === 1) {
                    break;
                }
                if (isset($array[$del]) || is_array($array[$del])) {
                    $array = &$array[$del];
                } else {
                    continue 2;
                }
            }
            unset($array[$parts[$count - 1]]);
        }
    }

    /**
     * Add an element to an array if it doesn't exist.
     *
     * @param array $array
     * @param string|int|float $key
     * @param mixed $value
     * @return bool
     */
    public static function add(array &$array, string|int|float $key, mixed $value): bool
    {
        if (self::get($array, $key) === null) {
            self::set($array, $key, $value);
            return true;
        }
        return false;
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function pull(array &$array, string $key, mixed $default = null): mixed
    {
        $value = self::get($array, $key, $default);
        self::forget($array, $key);
        return $value;
    }

    /**
     * Add an item in the end of an array
     *
     * @param array $array
     * @param mixed $value
     * @param string|null $key
     */
    public static function append(array &$array, mixed $value, string $key = null)
    {
        if ($key !== null) {
            $process = self::get($array, $key);
        } else {
            $process = $array;
        }
        $process[] = $value;
        self::set($array, $key, $process);
    }

    /**
     * @param $array
     * @param $key
     * @param $value
     */
    private static function setValue(&$array, $key, $value)
    {
        $keys = explode('.', $key);
        $count = count($keys);
        foreach ($keys as $i => $key) {
            if ($count - $i === 1) {
                break;
            }
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[$keys[$count - 1]] = $value;
    }

    /**
     * @param array $array
     * @param array|int|string|null $key
     * @param mixed $default
     * @return array|mixed|null
     */
    private static function getValue(array $array, array|int|string $key = null, mixed $default = null): mixed
    {
        if (OneDimension::exists($array, $key)) {
            return $array[$key];
        }
        if (!str_contains($key, '.')) {
            return $default;
        }
        return self::segment($array, $key, $default);
    }

    /**
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private static function segment(array $array, string $key, mixed $default): mixed
    {
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && OneDimension::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }
        return $array;
    }
}
