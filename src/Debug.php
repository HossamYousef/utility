<?php

namespace Avoxx\Utility;

/*
 * AVOXX - PHP Framework Packages
 *
 * @author    Merlin Christen <merloxx@avoxx.org>
 * @copyright Copyright (c) 2016 - 2017 Merlin Christen
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
use ReflectionClass;

class Debug
{

    /**
     * The current debug output environment
     *
     * @var string
     */
    protected static $sapi = null;

    /**
     * Set the current debug output environment.
     *
     * @param string $sapi
     */
    public static function setSapi($sapi)
    {
        static::$sapi = $sapi;
    }

    /**
     * Get the current debug output environment.
     *
     * @return string
     */
    public static function getSapi()
    {
        if (is_null(static::$sapi)) {
            static::$sapi = PHP_SAPI;
        }

        return static::$sapi;
    }

    /**
     * Dumps information about a variable.
     *
     * @param mixed  $value
     * @param string $title
     * @param bool   $plain
     * @param bool   $echo
     *
     * @return string
     */
    public static function dump($value, $title = null, $plain = false, $echo = true)
    {
        if (is_null($title)) {
            $title = 'AVOXX DEBUGGER';
        } else {
            $title = trim($title);
        }

        $dump = self::renderValue($value, 0);

        if (static::$sapi == 'cli' || $plain) {
            $output = $title . PHP_EOL . $dump . PHP_EOL . PHP_EOL;
        } else {
            $output = '<pre dir="ltr" ';
            $output .= 'style="padding:5px;font:14px/2 monospace;color:#e0245e;background:#f6f6f6;overflow-x:scroll;">';
            $output .= $title . '<br>' . htmlspecialchars($dump, ENT_QUOTES);
            $output .= '</pre>';
        }

        if ($echo) {
            echo $output;
        }

        return $output;
    }

    /**
     * Renders a dump of the given value.
     *
     * @param mixed $value
     * @param int   $level
     *
     * @return string
     */
    protected static function renderValue($value, $level)
    {
        if (is_string($value)) {
            $output = self::renderString($value);
        } elseif (is_numeric($value)) {
            $output = sprintf('%s: %s', gettype($value), $value);
        } elseif (is_bool($value)) {
            $output = sprintf('boolean: %s', $value ? 'true' : 'false');
        } elseif (is_null($value) || is_resource($value)) {
            $output = self::renderNullOrResource($value);
        } elseif (is_array($value)) {
            $output = self::renderArray($value, $level + 1);
        } elseif (is_object($value)) {
            $output = self::renderObject($value, $level + 1);
        }

        return $output;
    }

    /**
     * Render a string value.
     *
     * @param string $string
     *
     * @return string
     */
    protected static function renderString($string)
    {
        $length = strlen($string);
        $stringValue = $length > 150 ? substr($string, 0, 150) . '...' : $string;
        $stringLength = $length > 1 ? "{$length} chars" : "{$length} char";

        return sprintf('string: "%s" (%s)', $stringValue, $stringLength);
    }

    /**
     * Render a null value or resource.
     *
     * @param null|resource $nullOrResource
     *
     * @return string
     */
    protected static function renderNullOrResource($nullOrResource)
    {
        if (is_null($nullOrResource)) {
            $output = 'NULL';
        } else {
            $output = sprintf('resource: %s', get_resource_type($nullOrResource));
        }

        return $output;
    }

    /**
     * Render an array.
     *
     * @param array $array
     * @param int   $level
     *
     * @return string
     */
    protected static function renderArray(array $array, $level)
    {
        $count = count($array);
        $header = $count > 0 ? '(' . $count .  ' item'  . ($count > 1 ? 's' : null) . ')' : '(empty)';
        $content = '';

        foreach ($array as $key => $value) {
            $content .= PHP_EOL . str_repeat('   ', $level) . "\"{$key}\" => " . self::renderValue($value, $level);
        }

        return sprintf('array: %s %s', $header, $content);
    }

    /**
     * Render an object.
     *
     * @param object $object
     * @param int    $level
     *
     * @return string
     */
    protected static function renderObject($object, $level)
    {
        $reflectionClass = new ReflectionClass($object);

        $className = $reflectionClass->getName();
        $content = '';

        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);

            if ($property->isPublic()) {
                $visibility = 'public';
            } elseif ($property->isProtected()) {
                $visibility = 'protected';
            } elseif ($property->isPrivate()) {
                $visibility = 'private';
            }

            $content .= PHP_EOL . str_repeat('   ', $level) . "{$visibility} \"{$property->getName()}\" => ";
            $content .= self::renderValue($property->getValue($object), $level);
        }

        return sprintf('object: %s %s', $className, $content);
    }
}
