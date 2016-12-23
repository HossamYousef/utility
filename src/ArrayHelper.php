<?php

namespace Avoxx\Utility;

/*
 * AVOXX - PHP Framework Packages
 *
 * @author    Merlin Christen <merloxx@avoxx.org>
 * @copyright Copyright (c) 2016 Merlin Christen
 * @license   The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
use ArrayAccess;

class ArrayHelper
{
    /**
     * Determine whether the given value is an array.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in array.
     *
     * @param array|\ArrayAccess $array
     * @param string|int         $key
     *
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Return an item from a given array.
     *
     * @param array|\ArrayAccess $array
     * @param string             $key
     * @param mixed              $default
     *
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (! static::accessible($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Return the first item from a given array.
     *
     * @param array|\ArrayAccess $array
     * @param callable|null      $callback
     * @param mixed              $default
     *
     * @return mixed
     */
    public static function first($array, $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return $default;
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Return the last item from a given array.
     *
     * @param array|\ArrayAccess $array
     * @param callable|null      $callback
     * @param mixed              $default
     *
     * @return mixed
     */
    public static function last($array, $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return $default;
            }

            return end($array);
        }

        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Check if an item or items exists in a given array.
     *
     * @param array|\ArrayAccess $array
     * @param string|array       $key
     *
     * @return boolean
     */
    public static function has($array, $key)
    {
        if (is_null($key)) {
            return false;
        }

        $keys = (array) $key;

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Filter the array using the given callback.
     *
     * @param array|\ArrayAccess $array
     * @param callable           $callback
     *
     * @return array
     */
    public static function where($array, callable $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param array|\ArrayAccess $array
     * @param array|string       $key
     *
     * @return array
     */
    public static function only($array, $key)
    {
        return array_intersect_key($array, array_flip((array) $key));
    }

    /**
     * Remove one ore more items from a given array.
     *
     * @param array|\ArrayAccess &$array
     * @param array|string       $keys
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;
        $keys = (array) $keys;

        foreach ($keys as $key) {
            if (static::exists($array, $key)) {
                unset($array[$key]);
                continue;
            }

            $parts = explode('.', $key);
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (static::accessible($array) && static::exists($array, $part)) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }
}