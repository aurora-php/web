<?php

namespace org\octris\core\app {
    require_once('org.octris.core/app/autoloader.class.php');
    
    use \org\octris\core\validate as validate;
    
    /**
     * Test base class. The main purpose of this class is to include the
     * OCTRiS autoloader and to provide some helper methods useful for
     * writing test cases.
     *
     * @octdoc      c:app/test
     * @copyright   copyright (c) 2010-2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    class test
    /**/
    {
        /**
         * This is a helper method to unit tests to enable access to
         * a method which is protected / private and make it possible
         * to write a testcase for it.
         *
         * @octdoc  m:test/getMethod
         * @param   mixed           $class              Name or instance of class
         *                                              the method is located in.
         * @param   string          $name               Name of method to enable access to.
         * @return  ReflectionMethod                    Method object.
         */
        public static function getMethod($class, $name)
        /**/
        {
            $class = new \ReflectionClass($class);
            $method = $class->getMethod($name);
            $method->setAccessible(true);

            return $method;
        }
        
        /**
         * Implements the same as ~getMethod~ for object properties.
         *
         * @octdoc  m:test/getProperty
         * @param   mixed           $class              Name or instance of class
         *                                              the property is located in.
         * @param   string          $name               Name of property to enable access to.
         * @return  ReflectionProperty                  Property object.
         */
        public static function getProperty($class, $name)
        /**/
        {
            $class = new \ReflectionClass($class);
            $property = $class->getProperty($name);
            $property->setAccessible(true);

            return $property;
        }
    }

    if (!defined('OCTRIS_WRAPPER')) {
        // enable validation for superglobals
        define('OCTRIS_WRAPPER', true);

        $_COOKIE  = new test\wrapper($_COOKIE);
        $_GET     = new test\wrapper($_GET);
        $_POST    = new test\wrapper($_POST);
        $_SERVER  = new test\wrapper($_SERVER);
        $_ENV     = new test\wrapper($_ENV);
        $_REQUEST = new test\wrapper($_REQUEST);
        $_FILES   = new test\wrapper($_FILES);
        
        if (!$_ENV->validate('OCTRIS_BASE', validate::T_PATH)) {
            die("OCTRIS_BASE is not set\n");
        }
    }
}