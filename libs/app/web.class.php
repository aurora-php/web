<?php

namespace org\octris\core\app {
    class web extends \org\octris\core\app {
    }

    // enable validation for superglobals
    $_COOKIE  = new wrapper($_COOKIE);
    $_GET     = new wrapper($_GET);
    $_POST    = new wrapper($_POST);
    $_SERVER  = new wrapper($_SERVER);
    $_ENV     = new wrapper($_ENV);
    $_REQUEST = new wrapper($_REQUEST);
}

