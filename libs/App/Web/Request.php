<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\App\Web;

use \Octris\Core\Validate as validate;
use \Octris\Core\Provider as provider;

/**
 * Request helper functions
 *
 * @copyright   copyright (c) 2010-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Request
{
    /**
     * Request types.
     */
    const T_POST = 'POST';
    const T_GET  = 'GET';

    /**
     * Base64 for URLs encoding.
     *
     * @param   string          $data                   Data to encode.
     * @return  string                                  Encoded data.
     */
    public static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 for URLs decoding.
     *
     * @param   string          $data                   Data to decode.
     * @param   string                                  Decoded data.
     */
    public static function base64UrlDecode($data)
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
     * @return  string                                  Type of request.
     */
    public static function getRequestMethod()
    {
        static $method = null;

        if (is_null($method)) {
            $server = provider::access('server');

            if ($server->isExist('REQUEST_METHOD') && $server->isValid('REQUEST_METHOD', validate::T_PRINTABLE)) {
                $method = strtoupper($server->getValue('REQUEST_METHOD'));

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
     * @return  bool                                    Returns true if request is SSL secured.
     */
    public static function isSSL()
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
     * @return  string                                  Hostname.
     */
    public static function getHostname()
    {
        static $host = false;

        if ($host === false) {
            $server = provider::access('server');

            if ($server->isExist('HTTP_HOST') && $server->isValid('HTTP_HOST', validate::T_PRINTABLE)) {
                $host = $server->getValue('HTTP_HOST');
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
     * @return  string                                  Host.
     */
    public static function getHost()
    {
        $host = static::getHostname();

        return sprintf('http%s://%s', (static::isSSL() ? 's' : ''), $host);
    }

    /**
     * Return current host forced to https.
     *
     * @param   string                                  SSL secured host.
     */
    public static function getSSLHost()
    {
        return preg_replace('|^http://|i', 'https://', static::getHost());
    }

    /**
     * Return URI of request.
     *
     * @return  string                                  URI.
     */
    public static function getUri()
    {
        $server = provider::access('server');

        return ($server->isExist('REQUEST_URI') && $server->isValid('REQUEST_URI', validate::T_PRINTABLE)
                ? $server->getValue('REQUEST_URI')
                : '/');
    }

    /**
     * Determine current URL of application and return it.
     *
     * @todo    This method is not fully tested with all webservers, but it works for apache, lighttpd, nginx and IIS.
     * @return  string                                  URL.
     */
    public static function getUrl()
    {
        $uri = static::getHost();

        $server = provider::access('server');

        if ($server->isExist('PHP_SELF') && $server->isExist('REQUEST_URI') && $server->isValid('REQUEST_URI', validate::T_PRINTABLE)) {
            // for 'good' servers
            $uri .= $server->getValue('REQUEST_URI');
        } else {
            // for IIS
            if ($server->isValid('SCRIPT_NAME', validate::T_PRINTABLE)) {
                $uri .= $server->getValue('SCRIPT_NAME');
            }

            if ($server->isValid('QUERY_STRING', validate::T_PRINTABLE)) {
                $uri .= '?' . $server->getValue('QUERY_STRING');
            }
        }

        return $uri;
    }

    /**
     * Return current URL forced to https.
     *
     * @return  string                                  SSL secured URL.
     */
    public static function getSSLUrl()
    {
        return preg_replace('|^http://|i', 'https://', static::getUrl());
    }

    /**
     * Return current URL non-SSL secured.
     *
     * @return  string                                  Non-SSL secured URL.
     */
    public static function getNonSSLHost()
    {
        return preg_replace('|^https://|i', 'http://', static::getUrl());
    }

    /**
     * Uses HTTP's "Accept-Language" header to negotiate accepted language.
     *
     * @param   array           $supported              Optional supported languages.
     * @param   string          $default                Optional default language to use if no accepted language matches.
     * @return  string                                  Determined language.
     */
    public static function negotiateLanguage(array $supported = array(), $default = '')
    {
        $server = provider::access('server');

        if (!$server->isExist('HTTP_ACCEPT_LANGUAGE') || !$server->isValid('HTTP_ACCEPT_LANGUAGE', validate::T_PRINTABLE)) {
            return $default;
        }

        $accepted = $server->getValue('HTTP_ACCEPT_LANGUAGE', validate::T_PRINTABLE);

        // generate language array
        $supported = array_combine(array_map(function ($v) {
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

        return $determined;
    }
}
