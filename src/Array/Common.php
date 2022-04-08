<?php


namespace AbmmHasan\Bucket\Array;

class Common
{

    /**
     * Checks if array is multidimensional
     *
     * @param $array
     * @return bool
     */
    public static function isMultiDimensional($array): bool
    {
        return is_array($array) && count($array) !== count($array, COUNT_RECURSIVE);
    }

    /**
     * If the given value is not an array and not empty, wrap it in one.
     *
     * @param mixed $value
     * @return array
     */
    public static function wrap(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }


}
