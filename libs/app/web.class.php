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
        /****m* web/getTemplate
         * SYNOPSIS
         */
        function getTemplate()
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

    // enable validation for superglobals
    $_COOKIE  = new validate\wrapper($_COOKIE);
    $_GET     = new validate\wrapper($_GET);
    $_POST    = new validate\wrapper($_POST);
    $_SERVER  = new validate\wrapper($_SERVER);
    $_ENV     = new validate\wrapper($_ENV);
    $_REQUEST = new validate\wrapper($_REQUEST);
    $_FILES   = new validate\wrapper($_FILES);
}
