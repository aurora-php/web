<?php

require_once('org.octris.core/app.class.php');

namespace org\octris\core\app {
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

