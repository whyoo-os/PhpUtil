<?php

namespace WhyooOs\Util;

/**
 * utility functions for handling associative arrays (aka dicts)
 *
 * 12/2019
 */
class UtilDict
{



    /**
     * 12/2019
     */
    public static function getOneFilteredByWhitelist(array $one, $whitelistedKeys)
    {
        $newDict = [];
        foreach($whitelistedKeys as $key) {
            $newDict[$key] = $one[$key];
        }

        return $newDict;
    }


    /**
     * 12/2019 unimplemented
     *
     * @param array $arr
     * @param array $blacklistedKeys
     * @throws \Exception
     */
    public static function getManyFilteredByBlacklist($arr, array $blacklistedKeys)
    {
        throw new \Exception("TODO...");
    }


    /**
     * 12/2019
     *
     * @param array[] $many
     * @param array $whitelistedKeys
     * @return array[]
     */
    public static function getManyFilteredByWhitelist(iterable $many, array $whitelistedKeys)
    {
        $newList = [];
        foreach($many as &$item) {
            $newList[] = self::getOneFilteredByWhitelist($item, $whitelistedKeys);
        }

        return $newList;
    }


    /**
     * 12/2019 moved from UtilArray to here .. because list of dicts is sorted
     *
     * @param array $array
     * @param string $key
     * @param array $order
     * @return array
     */
    public static function sortManyByCustomOrder(array $array, string $key, array $order)
    {
        usort($array, function ($a, $b) use ($order, $key) {
            $pos_a = array_search($a[$key], $order);
            $pos_b = array_search($b[$key], $order);
            return $pos_a - $pos_b;
        });

        return $array;
    }


    /**
     * 08/2020
     *
     * @param array $arr
     * @param string $prefix
     * @return array|false
     */
    public static function prependToKeys(array $arr, string $prefix)
    {
        $keys = array_map(function($key) use ($prefix){
            return $prefix . $key;
        }, array_keys($arr));

        return array_combine($keys, array_values($arr));
    }


    /**
     * 08/2020
     *
     * @param array $arr
     * @param string $prefix
     * @return array|false
     */
    public static function appendToKeys(array $arr, string $suffix)
    {
        $keys = array_map(function($key) use ($suffix){
            return $key . $suffix;
        }, array_keys($arr));

        return array_combine($keys, array_values($arr));
    }

    /**
     * filters assoc array
     *
     * was used by cloudlister
     * 05/2021 moved from UtilArray::filterArrayByKeys() to UtilDict::filterByKeys()
     * 05/2021 unused (TODO? remove)
     *
     *
     * @param array $arr
     * @param string[] $allowedKeys
     * @return array
     */
    public static function filterByKeys(array $arr, array $allowedKeys)
    {
        $new = [];
        foreach ($arr as $key => &$val) {
            if (in_array($key, $allowedKeys, true)) {
                $new[$key] = $val;
            }
        }

        return $new;
    }

    /**
     * Filters dict ("assoc array") by its keys and convert it to a list ("numeric array")
     *
     * example:
     *
     * UtilDict::toList(['aaa' => 123, 'bbb' => 456], ['bbb', 'ccc']) -->
     *
     * array:2 [
     *   0 => 456
     *   1 => null
     * ]
     *
     *
     * 09/2017 from scrapers
     * 12/2019 merged UtilArray::filterByKey and UtilArray::extractByKeys to this
     * 05/2021 moved from UtilCsv::dictToList to UtilDict::toList()
     *
     * used by cloudlister(exportOrdersToExcel)
     *
     * @return array numeric(!) array
     */
    public static function toList(array $dict, array $keys)
    {
        return array_map(function ($key) use ($dict) {
            return @$dict[$key];
        }, $keys);
    }

}
