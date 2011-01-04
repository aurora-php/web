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
    }
}
