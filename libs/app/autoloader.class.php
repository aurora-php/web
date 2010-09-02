<?php

namespace org\octris\core\app {
    /****c* app/autoloader
     * NAME
     *      autoloader
     * FUNCTION
     *      Static class which provides the autoloader.
     * COPYRIGHT
     *      copyright (c) 2010 by Harald Lapp
     * AUTHOR
     *      Harald Lapp <harald@octris.org>
     ****
     */

    class autoloader {
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
            $pkg = preg_replace('|\\\\|', '/', preg_replace('|\\\\|', '.', ltrim($classpath, '\\\\'), 2)) . '.class.php';

            require_once($pkg);
        }
    }

    spl_autoload_register(array('\org\octris\core\app\autoloader', 'autoload'));
}