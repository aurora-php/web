<?php

require_once('org.octris.core/app.class.php');

namespace org\octris\core\app {
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

    class web extends \org\octris\core\app {
    }

    // enable validation for superglobals
    $_COOKIE  = new \org\octris\core\validate\wrapper($_COOKIE);
    $_GET     = new \org\octris\core\validate\wrapper($_GET);
    $_POST    = new \org\octris\core\validate\wrapper($_POST);
    $_SERVER  = new \org\octris\core\validate\wrapper($_SERVER);
    $_ENV     = new \org\octris\core\validate\wrapper($_ENV);
    $_REQUEST = new \org\octris\core\validate\wrapper($_REQUEST);
}

