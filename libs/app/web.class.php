<?php

namespace org\octris\core\app {
    use \org\octris\core\validate as validate;
    
    require_once('org.octris.core/app.class.php');

    /****c* app/web
     * NAME
     *      web
     * FUNCTION
     *      core class for web applications
     * COPYRIGHT
     *      copyright (c) 2010 by Harald Lapp
     * AUTHOR
     *      Harald Lapp <harald@octris.org>
     ****
     */

    abstract class web extends \org\octris\core\app {
        /****v* web/$headers
         * SYNOPSIS
         */
        protected $headers = array();
        /*
         * FUNCTION
         *      headers to push out when rendering website
         ****
         */
        
        /****m* web/addHeader
         * SYNOPSIS
         */
        public function addHeader($name, $value)
        /*
         * FUNCTION
         *      Adds header to output when rendering website
         * INPUTS
         *      * $name (string) -- name of header to add
         *      * $value (string) -- value to set for header
         ****
         */
        {
            $this->headers[$name] = $value;
        }
        
        /****m* web/getTemplate
         * SYNOPSIS
         */
        public function getTemplate()
        /*
         * FUNCTION
         *      create new instance of template engine and setup common stuff needed for templates of a web application
         * OUTPUTS
         *      (tpl) -- instance of template engine
         ****
         */
        {
            $tpl = new \org\octris\core\tpl(\org\octris\core\tpl::T_WEB);
            
            return $tpl;
        }
    }

    if (!defined('OCTRIS_WRAPPER')) {
        // enable validation for superglobals
        define('OCTRIS_WRAPPER', true);

        $_COOKIE  = new validate\wrapper($_COOKIE);
        $_GET     = new validate\wrapper($_GET);
        $_POST    = new validate\wrapper($_POST);
        $_SERVER  = new validate\wrapper($_SERVER);
        $_ENV     = new validate\wrapper($_ENV);
        $_REQUEST = new validate\wrapper($_REQUEST);
        $_FILES   = new validate\wrapper($_FILES);
    }
}
