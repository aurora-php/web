<?php

namespace org\octris\core {
    require_once('app/autoloader.class.php');
    
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

    abstract class app {
        /****d* app/T_CONTEXT_UNDEFINED, T_CONTEXT_CLI, T_CONTEXT_WEB, T_CONTEXT_TEST
         * SYNOPSIS
         */
        const T_CONTEXT_UNDEFINED = 0;
        const T_CONTEXT_CLI       = 1;
        const T_CONTEXT_WEB       = 2;
        const T_CONTEXT_TEST      = 3;
        /*
         * FUNCTION
         *      context the application is running in
         ****
         */
        
        /****v* app/$instance
         * SYNOPSIS
         */
        private static $instance = null;
        /*
         * FUNCTION
         *      application instance
         ****
         */
        
        /****v* app/$context
         * SYNOPSIS
         */
        protected static $context = self::T_CONTEXT_UNDEFINED;
        /*
         * FUNCTION
         *      application context
         ****
         */
        
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
            if (!$_ENV['OCTRIS_APP']->isSet || !$_ENV['OCTRIS_BASE']->isSet) {
                die("unable to import OCTRIS_APP or OCTRIS_BASE!\n");
            }

            if (!$_ENV->validate('OCTRIS_APP', validate::T_ALPHANUM) || !$_ENV->validate('OCTRIS_BASE', validate::T_PRINT)) {
                die("unable to import OCTRIS_APP or OCTRIS_BASE - invalid settings!\n");
            }
    
            $_ENV['OCTRIS_DEVEL']->value = ($_ENV->validate('OCTRIS_DEVEL', validate::T_BOOL) && $_ENV['OCTRIS_DEVEL']->value);
        }

        /****m* app/process
         * SYNOPSIS
         */
        abstract public function process();
        /*
         * FUNCTION
         *      methods to be implemented by application controller
         ****
         */
        
        /****m* app/triggerError
         * SYNOPSIS
         */
        public static function triggerError($code, $string, $file, $line, $context)
        /*
         * FUNCTION
         *      catches non OO errors and convert them to real exceptions
         * INPUTS
         *      * $code (int) -- error code
         *      * $string (string) -- the error message
         *      * $file (string) -- the name of the file the error was raised
         *      * $line (int) -- the line number in which the error was raised
         *      * $context (array) -- array of the active symbol table, when error was raised
         ****
         */
        {
            // TODO: implementation
        }
        
        /****m* app/getContext
         * SYNOPSIS
         */
        public static final function getContext()
        /*
         * FUNCTION
         *      Return context the application is running in.
         * OUTPUTS
         *      (int) -- application context
         ****
         */
        {
            return static::$context;
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
            if (is_null(self::$instance)) {
                self::$instance = new static();
            }
            
            return self::$instance;
        }
    }

    set_error_handler(array('\org\octris\core\app', 'triggerError'), E_ALL);
}
