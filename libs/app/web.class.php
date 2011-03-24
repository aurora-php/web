<?php

namespace org\octris\core\app {
    use \org\octris\core\app\web\request as request;
    use \org\octris\core\validate as validate;
    use \org\octris\core\provider as provider;
    
    require_once('org.octris.core/app.class.php');

    /**
     * Core class for Web applications.
     *
     * @octdoc      c:app/web
     * @copyright   copyright (c) 2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    abstract class web extends \org\octris\core\app
    /**/
    {
        /**
         * Initialization of web application.
         *
         * @octdoc  m:web/initialize
         */
        protected function initialize()
        /**/
        {
            $request = provider::access('request');
            
            if ($request->isExist('state')) {
                // TODO: handling of state
            }
        }
        
        /**
         * Main application processor. This is the only method that needs to be called to
         * invoke an application. Internally this method determines the last visited page
         * and handles everything required to determine the next page to display.
         *
         * The following example shows how to invoke an application, assuming that 'test'
         * implements an application based on \org\octris\core\app.
         *
         * <code>
         * $app = test::getInstance();
         * $app->process();
         * </code>
         *
         * @octdoc  m:cli/process
         */
        public function process()
        /**/
        {
            $last_page = $this->getLastPage();
            $action    = $last_page->getAction();
            // $module = self::getModule();

            $last_page->validate($action);

            $next_page = $last_page->getNextPage($action, $this->entry_page);

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
            
            if ($secure != request::isSSL() && request::getRequestMethod() == 'GET') {
                $this->redirectHttp(($secure ? request::getSSLUrl() : request::getNonSSLUrl()));
                exit;
            }

            // process with page
            $this->setLastPage($next_page);

            // $next_page->prepareMessages($this);
            // $next_page->sendHeaders($this->headers);
            $next_page->render($this);
        }

        /**
         * Adds header to output when rendering web site.
         *
         * @octdoc  m:web/addHeader
         * @param   string          $name               Name of header to add.
         * @param   string          $value              Value to set for header.
         */
        public function addHeader($name, $value)
        /**/
        {
            $this->headers[$name] = $value;
        }

        /**
         * Create new instance of template engine and setup common stuff needed for templates of a web application.
         *
         * @octdoc  m:web/getTemplate
         * @return  \org\octris\core\tpl                Instance of template class.
         */
        public function getTemplate()
        /**/
        {
            $tpl = new \org\octris\core\tpl();
            
            $tpl->
            
            return $tpl;
        }
    }

    if (!defined('OCTRIS_WRAPPER')) {
        // enable validation for superglobals
        define('OCTRIS_WRAPPER', true);

        $_ENV['OCTRIS_DEVEL'] = (isset($_ENV['OCTRIS_DEVEL']) && !!$_ENV['OCTRIS_DEVEL']);
        
        provider::set('server',  $_SERVER,  provider::T_READONLY);
        provider::set('env',     $_ENV,     provider::T_READONLY);
        provider::set('request', $_REQUEST, provider::T_READONLY);
        provider::set('post',    $_POST,    provider::T_READONLY);
        provider::set('get',     $_GET,     provider::T_READONLY);
        provider::set('cookie',  $_COOKIE,  provider::T_READONLY);
        provider::set('session', $_SESSION, provider::T_READONLY);
        provider::set('files',   $_FILES,   provider::T_READONLY);
        
        unset($_SERVER);
        unset($_ENV);
        unset($_REQUEST);
        unset($_POST);
        unset($_GET);
        unset($_COOKIE);
        unset($_SESSION);
        unset($_FILES);
        
        if (!provider::access('env')->isValid('OCTRIS_BASE', validate::T_PATH)) {
            die("OCTRIS_BASE is not set\n");
        }
    }
}
