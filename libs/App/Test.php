<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\App;

require_once(__DIR__ . '/Autoloader.php');

use \Octris\Core\Validate as validate;
use \Octris\Core\Provider as provider;

provider::setIfUnset('server', $_SERVER, provider::T_READONLY);
provider::setIfUnset('env', $_ENV, provider::T_READONLY);
provider::setIfUnset('request', $_REQUEST, provider::T_READONLY);
provider::setIfUnset('post', $_POST, provider::T_READONLY);
provider::setIfUnset('get', $_GET, provider::T_READONLY);
provider::setIfUnset('cookie', $_COOKIE, provider::T_READONLY);
provider::setIfUnset('files', $_FILES, provider::T_READONLY);

/**
 * Test base class. The main purpose of this class is to include the
 * OCTRiS autoloader and to provide some helper methods useful for
 * writing test cases.
 *
 * @copyright   copyright (c) 2010-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Test
{
    /**
     * This is a helper method to unit tests to enable access to
     * a method which is protected / private and make it possible
     * to write a testcase for it.
     *
     * @param   mixed           $class              Name or instance of class
     *                                              the method is located in.
     * @param   string          $name               Name of method to enable access to.
     * @return  ReflectionMethod                    Method object.
     */
    public static function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Implements the same as ~getMethod~ for object properties.
     *
     * @param   mixed           $class              Name or instance of class
     *                                              the property is located in.
     * @param   string          $name               Name of property to enable access to.
     * @return  ReflectionProperty                  Property object.
     */
    public static function getProperty($class, $name)
    {
        $class = new \ReflectionClass($class);
        $property = $class->getProperty($name);
        $property->setAccessible(true);

        return $property;
    }
}
