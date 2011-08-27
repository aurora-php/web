<?php

/*
 * This file is part of the 'org.octris.core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\octris\core\app\web {
    use \org\octris\core\validate as validate;
    use \org\octris\core\provider as provider;

    /**
     * Request helper functions
     *
     * @octdoc      c:web/request
     * @copyright   copyright (c) 2010-2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    class request
    /**/
    {
        /**
         * Request types.
         *
         * @octdoc  d:request/T_POST, T_GET
         */
        const T_POST = 'post';
        const T_GET  = 'get';
        /**/

        /**
         * Base64 for URLs encoding.
         *
         * @octdoc  m:request/base64UrlEncode
         * @param   string          $data                   Data to encode.
         * @return  string                                  Encoded data.
         */
        public static function base64UrlEncode($data)
        /**/
        {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        /**
         * Base64 for URLs decoding.
         *
         * @octdoc  m:request/base64UrlDecode
         * @param   string          $data                   Data to decode.
         * @param   string                                  Decoded data.
         */
        public static function base64UrlDecode($data)
        /**/
        {
            return base64_decode(
                str_pad(
                    strtr($data, '-_', '+/'),
                    strlen($data) % 4,
                    '=',
                    STR_PAD_RIGHT
                )
            );
        }

        /**
         * Determine and return method of the request.
         *
         * @octdoc  m:request/getRequestMehot
         * @return  string                                  Type of request.
         */
        public static function getRequestMethod()
        /**/
        {
            static $method = null;

            if (is_null($method)) {
                $server = provider::access('server');

                if ($server->isExist('REQUEST_METHOD')) {
                    $method = strtolower($server->getValue('REQUEST_METHOD', validate::T_PRINTABLE));

                    if ($method != self::T_POST && $method != self::T_GET) {
                        $method = self::T_GET;
                    }
                }
            }

            return $method;
        }

        /**
         * Determine whether request is SSL secured.
         *
         * @octdoc  m:request/isSSL
         * @return  bool                                    Returns true if request is SSL secured.
         */
        public static function isSSL()
        /**/
        {
            static $isSSL = null;

            if (is_null($isSSL)) {
                $server = provider::access('server');

                $isSSL = (
                    $server->isExist('HTTP_HOST') &&
                    $server->isExist('HTTPS') &&
                    $server->isValid('HTTPS', validate::T_PATTERN, array('pattern' => '/on/i'))
                );
            }

            return $isSSL;
        }

        /**
         * Return hostname of current request.
         *
         * @octdoc  m:request/getHostname
         * @param   string                                  Hostname.
         */
        public static function getHostname()
        /**/
        {
            static $host = false;

            if ($host === false) {
                $server = provider::access('server');

                if ($server->isExist('HTTP_HOST')) {
                    $host = $server->getValue('HTTP_HOST', validate::T_PRINTABLE);
                }

                if ($host === false) {
                    $host = '';
                }
            }

            return $host;
        }

        /**
         * Return host of request.
         *
         * @octdoc  m:request/getHost
         * @param   string                                  Host.
         */
        public function getHost()
        /**/
        {
            $host = static::getHostname();

            return sprintf('http%s://%s', (static::isSSL() ? 's' : ''), $host);
        }

        /**
         * Return current host forced to https.
         *
         * @octdoc  m:request/getSSLHost
         * @param   string                                  SSL secured host.
         */
        public static function getSSLHost()
        /**/
        {
            return preg_replace('|^http://|i', 'https://', static::getHost());
        }

        /**
         * Determine current URL of application and return it.
         *
         * @octdoc  m:request/getUrl
         * @todo    This method is not fully tested with all webservers, but it works for apache, lighttpd, nginx and IIS.
         * @return  string                                  URL.
         */
        public static function getUrl()
        /**/
        {
            $uri = static::getHost();

            $server = provider::access('server');

            if ($server->isExist('PHP_SELF') && $server->isExist('REQUEST_URI')) {
                // for 'good' servers
                if (($tmp = $server->getValue('REQUEST_URI', validate::T_PRINTABLE)) !== false) {
                    $uri .= $tmp;
                }
            } else {
                // for IIS
                if (($tmp = $server->getValue('SCRIPT_NAME', validate::T_PRINTABLE)) !== false) {
                    $uri .= $tmp;
                }

                if (($tmp = $server->getValue('QUERY_STRING', validate::T_PRINTABLE)) !== '') {
                    $uri .= '?' . $tmp;
                }
            }

            return $uri;
        }

        /**
         * Return current URL forced to https.
         *
         * @octdoc  m:request/getSSLUrl
         * @return  string                                  SSL secured URL.
         */
        public static function getSSLUrl()
        /**/
        {
            return preg_replace('|^http://|i', 'https://', static::getUrl());
        }

        /**
         * Return current URL non-SSL secured.
         *
         * @octdoc  m:request/getNonSSLHost
         * @return  string                                  Non-SSL secured URL.
         */
        public static function getNonSSLHost()
        /**/
        {
            return preg_replace('|^https://|i', 'http://', static::getUrl());
        }

        /**
         * Uses HTTP's "Accept-Language" header to negotiate accepted language.
         *
         * @octdoc  m:request/negotiateLanguage
         * @param   array           $supported              Optional supported languages.
         * @param   string          $default                Optional default language to use if no accepted language matches.
         * @return  string                                  Determined language.
         */
        public static function negotiateLanguage(array $supported = array(), $default = '')
        /**/
        {
            $server = provider::access('server');

            if (!$server->isExist('HTTP_ACCEPT_LANGUAGE') || !($accepted = $server->getValue('HTTP_ACCEPT_LANGUAGE', validate::T_PRINTABLE))) {
                return $default;
            }

            // generate language array
            $supported = array_combine(array_map(function($v) {
                return str_replace('_', '-', $v);
            }, $supported), $supported);

            // parse "Accept-Language" header
            $languages = explode(',', $accepted);
            $accepted  = array();

            foreach ($languages as $l) {
                if (preg_match('/([a-z]{1,2})(-([a-z0-9]+))?(;q=([0-9\.]+))?/', $l, $match)) {
                    $code = $match[1];
                    $morecode = (array_key_exists(3, $match) ? $match[3] : '');
                    $fullcode = ($morecode ? $code . '-' . $morecode : $code);

                    $coef = sprintf('%3.1f', (array_key_exists(5, $match) && $match[5] ? $match[5] : '1'));

                    $key = $coef . '-' . $code;

                    $accepted[$key] = array(
                        'code'     => $code,
                        'coef'     => $coef,
                        'morecode' => $morecode,
                        'fullcode' => $fullcode
                    );
                }
            }

            krsort($accepted);

            // negotiate language
            $determined = $default;

            foreach ($accepted as $q => $lc) {
                if (array_key_exists($lc['fullcode'], $supported)) {
                    $determined = $supported[$lc['fullcode']];
                    break;
                } elseif (array_key_exists($lc['code'], $supported)) {
                    $determined = $supported[$lc['code']];
                    break;
                }
            }

            return $lc_specified;
        }
    }
}
