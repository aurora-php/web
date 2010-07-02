<?php

namespace org\octris\core\app {
    require_once('org.octris.core/app.class.php');
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

    class test extends \org\octris\core\app {
        /****m* test/getMethod
         * SYNOPSIS
         */
        static function getMethod($class, $name)
        /*
         * FUNCTION
         *      This is a helper method to unit tests to enable access to
         *      a method which is protected / private and make it possible
         *      to write a testcase for it.
         * INPUTS
         *      * $class (string) -- name of class the method is located in
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
        function getProperty($class, $name)
        /*
         * FUNCTION
         *      Implements the same as ~getMethod~ for object properties.
         * INPUTS
         *      * $class (string) -- name of class the property is located in
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
}