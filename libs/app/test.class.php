<?php

namespace org\octris\core\app {
    require_once('PHPUnit/Framework.php');
    
    /****c* app/test
     * NAME
     *      test
     * FUNCTION
     *      Test base class. The main purpose of this class is to include the
     *      OCTRiS autoloader and to include the base class of the PHPUnit 
     *      framework. Additionally the class provides some helper methods 
     *      useful for writing test cases.
     * COPYRIGHT
     *      copyright (c) 2010 by Harald Lapp
     * AUTHOR
     *      Harald Lapp <harald@octris.org>
     ****
     */

    class test {
        /****m* test/autoload
         * SYNOPSIS
         */
        public static function autoload($classpath)
        /*
         * FUNCTION
         *      class autoloader
         * INPUTS
         *      * $classpath (string) -- path of class to load
         ****
         */
        {
            $class = substr($classpath, strrpos($classpath, '\\') + 1);
            $pkg   = preg_replace('|\\\\|', '/', preg_replace('|\\\\|', '.', ltrim($classpath, '\\\\'), 2)) . '.class.php';
            
            require_once($pkg);
        }
        
        /****m* test/getMethod
         * SYNOPSIS
         */
        public static function getMethod($class, $name)
        /*
         * FUNCTION
         *      This is a helper method to unit tests to enable access to
         *      a method which is protected / private and make it possible
         *      to write a testcase for it.
         * INPUTS
         *      * $class (mixed) -- name or instance of class the method is located in
         *      * $name (string) -- name of method to enable access to
         * OUTPUTS
         *      (ReflectionMethod) -- method object
         * REFERENCE
         *      http://stackoverflow.com/questions/249664/best-practices-to-test-protected-methods-with-phpunit/2798203#2798203
         ****
         */
        {
            $class = new \ReflectionClass($class);
            $method = $class->getMethod($name);
            $method->setAccessible(true);

            return $method;
        }
        
        /****m* test/getProperty
         * SYNOPSIS
         */
        public static function getProperty($class, $name)
        /*
         * FUNCTION
         *      Implements the same as ~getMethod~ for object properties.
         * INPUTS
         *      * $class (mixed) -- name or instance of class the property is located in
         *      * $name (string) -- name of property to enable access to
         * OUTPUTS
         *      (ReflectionProperty) -- property object
         ****
         */
        {
            $class = new \ReflectionClass($class);
            $property = $class->getProperty($name);
            $property->setAccessible(true);

            return $property;
        }
    }

    spl_autoload_register(array('\org\octris\core\app\test', 'autoload'));

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