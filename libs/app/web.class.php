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
        /****d* web/T_REQ_POST, T_REQ_GET
         * SYNOPSIS
         */
        const T_REQ_POST = 'POST';
        const T_REQ_GET  = 'GET';
        /*
         * FUNCTION
         *      restuest methods
         ****
         */
        
        /****v* web/$headers
         * SYNOPSIS
         */
        protected $headers = array();
        /*
         * FUNCTION
         *      headers to push out when rendering website
         ****
         */

        /****m* web/process
         * SYNOPSIS
         */
        public function process()
        /*
         * FUNCTION
         *      Main application processor. This is the only method that needs to
         *      be called to invoke an application. Internally this method determines
         *      the last visited page and handles everything required to determine
         *      the next page to display.
         * EXAMPLE
         *      simple example to invoke an application, assuming that "test" implements
         *      an application base on lima_apps
         *
         *      ..  source: php
         *
         *          $app = new test();
         *          $app->process();
         ****
         */
        {
            $module = self::getModule();
            $action = self::getAction();

            $last_page = $this->getLastPage();
            $last_page->validate($this, $action);

            $next_page = $last_page->getNextPage($this, $this->entry_page);

            $max = 3;

            do {
                $redirect_page = $next_page->prepareRender($this, $last_page, $action);

                if (is_object($redirect_page) && $next_page != $redirect_page) {
                    $next_page = $redirect_page;
                } else {
                    break;
                }
            } while (--$max);

            // fix security context
            $secure = $next_page->isSecure();

            if ($secure != $this->isSSL() && $this->getRequestMethod() == 'GET') {
                $this->redirectHttp(($secure ? $this->getSSLUrl() : $this->getNonSSLUrl()));
                exit;
            }

            // process with page
            $this->setLastPage($next_page);

            $next_page->prepareMessages($this);
            $next_page->sendHeaders($this->headers);
            $next_page->render($this);
        }
        
        /****m* web/negotiateLanguage
         * SYNOPSIS
         */
        public function negotiateLanguage($supported, $default) 
        /*
         * FUNCTION
         *      uses HTTP_ACCEPT_LANGUAGE to negotiate accepted language
         * INPUTS
         *      * $supported (array) -- array of supported languages
         *      * $default (string) -- default language to use (fallback if no accepted language matches)
         * OUTPUTS
         *      (string) -- language
         ****
         */
        {
            // generate language array
            $lc_supported = explode(',', $supported);

            $keys = explode(',', str_replace('_', '-', strtolower($supported)));
            $lc_supported = array_combine($keys, $lc_supported);

            $short = explode(',', preg_replace('/_[A-Z0-9]+/', '', $supported));
            $lc_supported = array_merge(
                $lc_supported, 
                array_flip(array_combine(array_reverse($lc_supported), $short))
            );

            // parse HTTP_ACCEPT_LANGUAGE
            $http_accept_language = $_SERVER->import('HTTP_ACCEPT_LANGUAGE', new lima_validate_print());

            $langs = ($http_accept_language->isSet && $http_accept_language->isValid 
                      ? explode(',', $http_accept_language->value) 
                      : array());

            $lc_accepted = array();

            foreach ($langs as $lang) if (preg_match('/([a-z]{1,2})(-([a-z0-9]+))?(;q=([0-9\.]+))?/', $lang, $match)) {
                $code = $match[1];
                $morecode = (array_key_exists(3, $match) ? $match[3] : '');
                $fullcode = ($morecode ? $code . '-' . $morecode : $code);

                $coef = sprintf('%3.1f', (array_key_exists(5, $match) && $match[5] ? $match[5] : '1'));

                $key = $coef . '-' . $code;

                $lc_accepted[$key] = array(
                    'code' => $code,
                    'coef' => $coef,
                    'morecode' => $morecode,
                    'fullcode' => $fullcode
                );
            }

            krsort($lc_accepted);

            // negotiate language
            $lc_specified = $default;

            foreach ($lc_accepted as $q => $lc) {
                if (array_key_exists($lc['fullcode'], $lc_supported)) {
                    $lc_specified = $lc_supported[$lc['fullcode']];
                    break;
                } elseif (array_key_exists($lc['code'], $lc_supported)) {
                    $lc_specified = $lc_supported[$lc['code']];                    
                    break;
                }
            }

            return $lc_specified;
        }

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
        
        /****m* page/getModule
         * SYNOPSIS
         */
        public static function getModule($action)
        /*
         * FUNCTION
         *      Determine requested module with specified action. If a module was determined but the action is not
         *      valid, this method will return default application module. The module must be reachable from inside
         *      the application.
         * OUTPUTS
         *      (string) -- name of module requested
         ****
         */
        {
            static $module = '';
            
            if ($module != '') {
                return $module;
            }

            $method = app::getRequestMethod();

            if ($method == 'POST' || $method == 'GET') {
                // try to determine action from a request parameter named ACTION
                $method = ($method == 'POST' ? $_POST : $_GET);
            
                if ($method->validate('MODULE', validate::T_ALPHANUM)) {
                    $module = $method['MODULE']->value;
                }

                if ($module == '') {
                    // try to determine action from a request parameter named ACTION_...
                    foreach ($method as $k => $v) {
                        if (preg_match('/^MODULE_([a-zA-Z]+)$/', $k, $match)) {
                            $module = $match[1];
                            break;
                        }
                    }
                }
            }

            if ($module == '') {
                $module = 'default';
            } elseif (isset(self::$modules[$module])) {
                $class = self::$modules[$module];
                $class::entry
            }

            return $action;
        }

        /****m* page/getAction
         * SYNOPSIS
         */
        public static function getAction()
        /*
         * FUNCTION
         *      determine the action the page called with
         * OUTPUTS
         *      (string) -- name of action
         ****
         */
        {
            static $action = '';

            if ($action != '') {
                return $action;
            }

            $method = app::getRequestMethod();

            if ($method == 'POST' || $method == 'GET') {
                // try to determine action from a request parameter named ACTION
                $method = ($method == 'POST' ? $_POST : $_GET);
            
                if ($method->validate('ACTION', validate::T_ALPHANUM)) {
                    $action = $method['ACTION']->value;
                }

                if ($action == '') {
                    // try to determine action from a request parameter named ACTION_...
                    foreach ($method as $k => $v) {
                        if (preg_match('/^ACTION_([a-zA-Z]+)$/', $k, $match)) {
                            $action = $match[1];

                            return $action;
                        }
                    }
                }
            }

            if ($action == '') {
                $action = 'default';
            }

            return $action;
        }

        /****m* web/getRequestMethod
         * SYNOPSIS
         */
        public static function getRequestMethod()
        /*
         * FUNCTION
         *      determine the method of the current request
         * INPUTS
         *      
         * OUTPUTS
         *      (int) -- type of request method
         ****
         */
        {
            static $method = '';
            
            if ($method == '' && $_SERVER->validate('REQUEST_METHOD', validate::T_ALPHA)) {
                $method = strtoupper($_SERVER['REQUEST_METHOD']->value);
            }

            return $method;
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
