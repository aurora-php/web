<?php

namespace org\octris\core\app\web {
    use \org\octris\core\validate as validate;

    /****c* web/request
     * NAME
     *      web
     * FUNCTION
     *      request helper functions
     * COPYRIGHT
     *      copyright (c) 2010 by Harald Lapp
     * AUTHOR
     *      Harald Lapp <harald@octris.org>
     ****
     */

    class request {
        /****d* request/T_POST, T_GET
         * SYNOPSIS
         */
        const T_POST = 'POST';
        const T_GET  = 'GET';
        /*
         * FUNCTION
         *      request methods
         ****
         */
        
        /****m* request/getRequestMethod
         * SYNOPSIS
         */
        public static function getRequestMethod()
        /*
         * FUNCTION
         *      determine request method
         * INPUTS
         *      (string) -- type of request method
         ****
         */
        {
            static $method = NULL;

            if (is_null($method)) {
                if ($_SERVER['REQUEST_METHOD']->isSet) {
                    $method = strtoupper($_SERVER->validate('REQUEST_METHOD', validate::T_PRINT)->value);

                    if ($method != 'POST' && $method != 'GET') {
                        $method = 'GET';
                    }
                }
            }

            return $method;
        }

        /****m* request/isSSL
         * SYNOPSIS
         */
        public static function isSSL()
        /*
         * FUNCTION
         *      determine whether request uses https or not
         * OUTPUTS
         *      (bool) -- returns true, if request is SSL secured
         ****
         */
        {
            static $isSSL = NULL;

            if (is_null($isSSL)) {
                $isSSL = ($_SERVER['HTTP_HOST']->isSet && $_SERVER->validate('HTTPS', validate::T_PATTERN, array('pattern' => '/on/i')));
            }

            return $isSSL;
        }

        /****m* request/getHostname
         * SYNOPSIS
         */
        public static function getHostname()
        /*
         * FUNCTION
         *      return hostname of current request
         * OUTPUTS
         *      (string) -- hostname
         ****
         */
        {
            $host = '';
            
            if ($_SERVER['HTTP_HOST']->isSet && $_SERVER->validate('HTTP_HOST', validate::T_PRINT)) {
                $host = $_SERVER['HTTP_HOST']->value;
            }
            
            return $host;
        }
        
        /****m* request/getHost
         * SYNOPSIS
         */
        public static function getHost()
        /*
         * FUNCTION
         *      return host of request
         * OUTPUTS
         *      (string) -- host
         ****
         */
        {
            $host = static::getHostname();
            
            return sprintf('http%s://%s', (static::isSSL() ? 's' : ''), $host);
            
        }
        
        /****m* request/getSSLHost
         * SYNOPSIS
         */
        public static function getSSLHost()
        /*
         * FUNCTION
         *      return current host forced to https
         * OUTPUTS
         *      (string) -- application host
         ****
         */
        {
            return preg_replace('|^http://|i', 'https://', static::getHost());
        }

        /****m* request/getNonSSLHost
         * SYNOPSIS
         */
        public static function getNonSSLHost()
        /*
         * FUNCTION
         *      return current host forced to http
         * OUTPUTS
         *      (string) -- application host
         ****
         */
        {
            return preg_replace('|^https://|i', 'http://', static::getHost());
        }
        
        /****m* request/getUrl
         * SYNOPSIS
         */
        public static function getUrl()
        /*
         * FUNCTION
         *      determine current URL of application and return it.
         * OUTPUTS
         *      returns current application URL
         * TODO
         *      this method is not fully tested with all webservers, but it 
         *      seems to work for apache and lighttpd
         ****
         */
        {
            $uri = static::getHost();

            if ($_SERVER['PHP_SELF']->isSet && $_SERVER['REQUEST_URI']->isSet) {
                if ($_SERVER->validate('REQUEST_URI', validate::T_PRINT)) {
                    $uri .= $_SERVER['REQUEST_URI']->value;
                }
            } else {
                // for IIS
                if ($_SERVER->validate('SCRIPT_NAME', validate::T_PRINT)) {
                    $uri .= $_SERVER['SCRIPT_NAME']->value;
                }

                if ($_SERVER->validate('QUERY_STRING', validate::T_PRINT) && $_SERVER['QUERY_STRING']->value != '') {
                    $uri .= '?' . $_SERVER['QUERY_STRING']->value;
                }
            }

            return $uri;
        }
        
        /****m* request/getSSLUrl
         * SYNOPSIS
         */
        public static function getSSLUrl()
        /*
         * FUNCTION
         *      return current URL forced to https
         * OUTPUTS
         *      (string) -- SSL secured URL
         ****
         */
        {
            return preg_replace('|^http://|i', 'https://', static::getUrl());
        }

        /****m* request/getNonSSLHost
         * SYNOPSIS
         */
        public static function getNonSSLHost()
        /*
         * FUNCTION
         *      return current URL forced to http
         * OUTPUTS
         *      (string) -- unsecured URL
         ****
         */
        {
            return preg_replace('|^https://|i', 'http://', static::getUrl());
        }
    }
}
