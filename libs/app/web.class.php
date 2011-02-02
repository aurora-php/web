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

        /**
         * Initialization of web application.
         *
         * @octdoc  m:web/initialize
         */
        protected function initialize()
        /**/
        {
            if (isset($_REQUEST['state'])) {
                // $this->state = \org\octris\core\app\state::thaw(...)
            }
            
            // $registry = \org\octris\core\registry::getInstance();
            
            // setup settings
            // $settings = \org\octris\core\settings::getInstance();
            // $settings->setStorage(function($instance) use ($registry) {
            //     $name     = $registry['config']['common.settings.cookie'];
            //     $lifetime = $registry['config']['common.settings.lifetime'];
            // 
            //     $cookie   = \org\octris\core\storage\cookie::getInstance($name, $lifetime);
            // });
            //     $depend = new \org\octris\core\dependency();
            //     $depend->set('setting.storage', function($dep) use ($injection) {
            // 
            // 
            //         return $cookie;
            //     }, true);
        }
        

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
            $last_page->validate($action);

            $next_page = $last_page->getNextPage($this->entry_page);

            $max = 3;

            do {
                $redirect_page = $next_page->prepare($last_page, $action);

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

            // $next_page->prepareMessages($this);
            // $next_page->sendHeaders($this->headers);
            // $next_page->render($this);
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
                // $class::entry
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
        
        if (!$_ENV->validate('OCTRIS_BASE', validate::T_PATH)) {
            die("OCTRIS_BASE is not set\n");
        }
    }
}
