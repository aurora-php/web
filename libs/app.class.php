<?php

namespace org\octris\core {
    /****c* core/app
     * NAME
     *      app
     * FUNCTION
     *      Core application class. This class needs to be the first
     *      one included in an application, because it set's up a function
     *      which will try to automatically load all required classes.
     * COPYRIGHT
     *      copyright (c) 2010 by Harald Lapp
     * AUTHOR
     *      Harald Lapp <harald@octris.org>
     ****
     */

    class app {
        /****m* app/__construct
         * SYNOPSIS
         */
        protected function __construct()
        /*
         * FUNCTION
         *      constructor
         ****
         */
        {
        }
        
        /****m* app/autoload
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
            $pkg = preg_replace('|\\\\|', '/', preg_replace('|\\\\|', '.', $classpath, 2)) . '.class.php';

            require_once($pkg);
        }
        
        /****m* app/getInstance
         * SYNOPSIS
         */
        public static function getInstance()
        /*
         * FUNCTION
         *      return instance of main application class
         * OUTPUTS
         *      (app) -- instance of main application class
         ****
         */
        {
            return new static();
        }
    }

    spl_autoload_register(array('\org\octris\core\app', 'autoload'));
}

