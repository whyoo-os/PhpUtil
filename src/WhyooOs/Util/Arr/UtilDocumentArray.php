<?php

namespace WhyooOs\Util\Arr;

use WhyooOs\Util\UtilDebug;
use WhyooOs\Util\UtilDict;
use WhyooOs\Util\UtilDocument;

/**
 * utility functions for handling lists (aka arrays) of documents (aka objects)
 *
 * 05/2021 created
 */
class UtilDocumentArray
{
    /**
     * 02/2018 unused
     * 08/2020 used by cloudlister
     * 08/2020 used by tldr-to-anki
     * 09/2020 used by language immerser
     * 05/2021 moved from UtilArray to UtilDocumentArray
     *
     * @param array $items
     * @param string $getterName eg "getId"
     * @return array
     */
    public static function arrayColumnByGetter(array $items, string $getterName): array
    {
        return array_map(function ($f) use ($getterName) {
            return $f->$getterName();
        }, $items);
    }


    /**
     * 02/2018 unused
     * 08/2020 used by cloudlister
     * 08/2020 used by tldr-to-anki
     * 09/2020 used by language immerser
     * 05/2021 moved from UtilArray to UtilDocumentArray
     *
     * @param array $items
     * @param string $columnName eg "id" ... the gettername (eg "getId") is generated automatically
     * @return array
     */
    public static function arrayColumn(array $items, string $columnName): array
    {
        $getterName = 'get' . ucfirst($columnName);
        return array_map(function ($f) use ($getterName) {
            return $f->$getterName();
        }, $items);
    }

    /**
     * get single property of documents in an array using getters,
     * also by sub-documents like 'userProfile.birthday' are possible
     *
     * example:
     *
     * $posts = {'a' => post1, 'b' => post2, 'c' => post3]
     * arrayColumnDeep($posts, 'id', false) returns [1,2,3]
     * arrayColumnDeep($posts, 'id', true) returns { a:1, b:2, c:3 }
     *
     * used in marketer
     *
     * 05/2021 moved from UtilArray::getObjectProperty() to UtilDocumentArray::arrayColumnDeep()
     *
     * @param array $arr
     * @param string $path also sub-documents like 'userProfile.birthday' are possible
     * @param bool $bKeepOriginalKeys the thing with $bKeepOriginalKeys` is if array is associative to keep the old keys not to cretae new numeric array
     * @return array
     */
    public static function arrayColumnDeep(array $arr, string $path, bool $bKeepOriginalKeys = false)
    {
        $subfields = explode('.', $path);

        $getterNames = [];
        foreach ($subfields as $subfield) {
            $getterNames[] = 'get' . ucfirst($subfield);
        }

        // $methodName = "get" . ucfirst($propertyName);

        if ($bKeepOriginalKeys) {
            // keep original keys
            array_walk($arr, function (&$item, $key) use ($getterNames) {
                foreach ($getterNames as $getterName) {
                    $item = $item->$getterName();
                }
            });
            $newArray = $arr;
        } else {
            // new keys (create ordinary numeric array)
            $newArray = array_map(function ($item) use ($getterNames) {
                foreach ($getterNames as $getterName) {
                    $item = $item->$getterName();
                }
                return $item;
            }, $arr);
        }

        return $newArray;
    }

    /**
     * 05/2021 moved from UtilArray::arrayOfDocumentsToAssoc() to UtilDocumentArray::documentArrayToDict()
     *
     * @param array $array
     * @param string $keyName eg 'id'
     * @return array assoc array aka dict
     */
    public static function documentArrayToDict(array $array, string $keyName): array
    {
        $values = array_values($array);
        $keys = [];
        foreach ($values as &$doc) {
            $getter = "get" . ucfirst($keyName);
            $keys[] = $doc->$getter();
        }

        return array_combine($keys, $values);
    }



    /**
     * search for one document by an attribute (eg id)
     * TODO: better name (eg findOneXXX)
     *
     * 05/2021 moved from UtilArray::searchObjectByAttribute() to UtilDocumentArray::searchDocumentByAttribute()
     *
     * @param $arr
     * @param $attributeName
     * @param $attributeValue
     * @return object|null
     */
    public static function searchDocumentByAttribute($arr, string $attributeName, $attributeValue)
    {
        if (empty($arr)) {
            return null;
        }

        foreach ($arr as &$obj) {
            $getter = "get" . ucfirst($attributeName);
            if ($obj->$getter() == $attributeValue) {
                return $obj;
            }
        }
        return null;
    }


    /**
     * TODO: find better name
     * used by eqipoo
     *
     * 05/2021 moved from UtilArray::searchObjectByAttribute() to UtilDocumentArray::searchDocumentByAttribute()
     *
     * @param $arr
     * @param string $attributeName
     * @param array $attributeValues
     */
    public static function moveElementsToBeginning(array $arr, $attributeName, array $attributeValues)
    {
        $new = [];
        foreach ($attributeValues as $val) {
            $newElem = self::searchDocumentByAttribute($arr, $attributeName, $val);
            if ($newElem) {
                $new[] = $newElem;
            }
        }
        $new = array_merge($new, array_diff($arr, $new));

        return $new;
    }



    /**
     * used in eqipoo
     *
     * 05/2021 moved from UtilArray::getObjectProperties() to UtilDocumentArray::getDocumentProperties()
     *
     * @param array $arr
     * @param string[] $propertyNames
     * @return array
     */
    public static function getDocumentProperties(array $arr, array $propertyNames)
    {
        $methodNames = [];
        foreach ($propertyNames as $propertyName) {
            $methodNames[$propertyName] = "get" . ucfirst($propertyName);
        }
        // new keys (create ordinary numeric array)
        $newArray = array_map(function ($item) use ($methodNames) {
            $ret = [];
            foreach ($methodNames as $key => $getterName) {
                $ret[$key] = $item->$getterName();
            }
            return $ret;
        }, $arr);

        return $newArray;
    }


    /**
     * 05/2021 created, used by push4 graph building
     *
     * @param array $arr
     * @param string $path eg 'function', also deepPaths are possible 'xx.yy.zz'
     * @param array $whitelist eg ['LicMapScalar', 'RotateWarpMapScalar']
     * @return array
     */
    public static function whitelist(array $arr, string $path, array $whitelist): array
    {
        return array_filter($arr, function ($item) use ($path, $whitelist) {
            try {
                return in_array(UtilDocument::deepGet($item, $path, True), $whitelist);
            } catch (\Exception $e) {
                return false;
            }
        });

    }


    /**
     * 05/2021 created, used by push4 graph building
     *
     * @param array $arr
     * @param string $path eg 'function', also deepPaths are possible 'xx.yy.zz'
     * @param array $blacklist eg ['DivisionRaster', 'DrawRectangles']
     * @return array
     */
    public static function blacklist(array $arr, string $path, array $blacklist): array
    {
        return array_filter($arr, function ($item) use ($path, $blacklist) {
            try {
                # UtilDebug::dd("needle: " . UtilDocument::deepGet($item, $path, True));
                return !in_array(UtilDocument::deepGet($item, $path, True), $blacklist);
            } catch (\Exception $e) {
                # UtilDebug::dd("FAIL", $path, $item, $e);
                return True;
            }
        });
    }



}
