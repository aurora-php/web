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

/**
 * Response class.
 *
 * @copyright   copyright (c) 2015-2016 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Response
{
    /**
     * HTTP Status codes.
     *
     * @type    array
     */
    protected static $status_phrases = array(
        // Informational - Request received, continuing process
        100 => 'Continue',                         // [RFC7231, Section 6.2.1]
        101 => 'Switching Protocols',              // [RFC7231, Section 6.2.2]
        102 => 'Processing',                       // [RFC2518]

        // Success - The action was successfully received, understood, and accepted
        200 => 'OK',                               // [RFC7231, Section 6.3.1]
        201 => 'Created',                          // [RFC7231, Section 6.3.2]
        202 => 'Accepted',                         // [RFC7231, Section 6.3.3]
        203 => 'Non-Authoritative Information',    // [RFC7231, Section 6.3.4]
        204 => 'No Content',                       // [RFC7231, Section 6.3.5]
        205 => 'Reset Content',                    // [RFC7231, Section 6.3.6]
        206 => 'Partial Content',                  // [RFC7233, Section 4.1]
        207 => 'Multi-Status',                     // [RFC4918]
        208 => 'Already Reported',                 // [RFC5842]
        226 => 'IM Used',                          // [RFC3229]

        // Redirection - Further action must be taken in order to complete the request
        300 => 'Multiple Choices',                 // [RFC7231, Section 6.4.1]
        301 => 'Moved Permanently',                // [RFC7231, Section 6.4.2]
        302 => 'Found',                            // [RFC7231, Section 6.4.3]
        303 => 'See Other',                        // [RFC7231, Section 6.4.4]
        304 => 'Not Modified',                     // [RFC7232, Section 4.1]
        305 => 'Use Proxy',                        // [RFC7231, Section 6.4.5]
        306 => '(Unused)',                         // [RFC7231, Section 6.4.6]
        307 => 'Temporary Redirect',               // [RFC7231, Section 6.4.7]
        308 => 'Permanent Redirect',               // [RFC7538]

        // Client Error - The request contains bad syntax or cannot be fulfilled
        400 => 'Bad Request',                      // [RFC7231, Section 6.5.1]
        401 => 'Unauthorized',                     // [RFC7235, Section 3.1]
        402 => 'Payment Required',                 // [RFC7231, Section 6.5.2]
        403 => 'Forbidden',                        // [RFC7231, Section 6.5.3]
        404 => 'Not Found',                        // [RFC7231, Section 6.5.4]
        405 => 'Method Not Allowed',               // [RFC7231, Section 6.5.5]
        406 => 'Not Acceptable',                   // [RFC7231, Section 6.5.6]
        407 => 'Proxy Authentication Required',    // [RFC7235, Section 3.2]
        408 => 'Request Timeout',                  // [RFC7231, Section 6.5.7]
        409 => 'Conflict',                         // [RFC7231, Section 6.5.8]
        410 => 'Gone',                             // [RFC7231, Section 6.5.9]
        411 => 'Length Required',                  // [RFC7231, Section 6.5.10]
        412 => 'Precondition Failed',              // [RFC7232, Section 4.2]
        413 => 'Payload Too Large',                // [RFC7231, Section 6.5.11]
        414 => 'URI Too Long',                     // [RFC7231, Section 6.5.12]
        415 => 'Unsupported Media Type',           // [RFC7231, Section 6.5.13]
        416 => 'Range Not Satisfiable',            // [RFC7233, Section 4.4]
        417 => 'Expectation Failed',               // [RFC7231, Section 6.5.14]
        421 => 'Misdirected Request',              // [RFC-ietf-httpbis-http2-17, Section 9.1.2]
        422 => 'Unprocessable Entity',             // [RFC4918]
        423 => 'Locked',                           // [RFC4918]
        424 => 'Failed Dependency',                // [RFC4918]
        425 => 'Unassigned',
        426 => 'Upgrade Required',                 // [RFC7231, Section 6.5.15]
        427 => 'Unassigned',
        428 => 'Precondition Required',            // [RFC6585]
        429 => 'Too Many Requests',                // [RFC6585]
        430 => 'Unassigned',
        431 => 'Request Header Fields Too Large',  // [RFC6585]

        // Server Error - The server failed to fulfill an apparently valid request
        500 => 'Internal Server Error',            // [RFC7231, Section 6.6.1]
        501 => 'Not Implemented',                  // [RFC7231, Section 6.6.2]
        502 => 'Bad Gateway',                      // [RFC7231, Section 6.6.3]
        503 => 'Service Unavailable',              // [RFC7231, Section 6.6.4]
        504 => 'Gateway Timeout',                  // [RFC7231, Section 6.6.5]
        505 => 'HTTP Version Not Supported',       // [RFC7231, Section 6.6.6]
        506 => 'Variant Also Negotiates',          // [RFC2295]
        507 => 'Insufficient Storage',             // [RFC4918]
        508 => 'Loop Detected',                    // [RFC5842]
        509 => 'Unassigned',
        510 => 'Not Extended',                     // [RFC2774]
        511 => 'Network Authentication Required'   // [RFC6585]
    );

    /**
     * Default HTTP version.
     *
     * @type    string
     */
    protected $http_version = '1.0';

    /**
     * Default status code.
     *
     * @type    int
     */
    protected $status_code = 200;

    /**
     * Instance of headers object.
     *
     * @type    \Octris\Core\App\Web\Headers
     */
    protected $headers;

    /**
     * Content of response.
     *
     * @type    string
     */
    protected $content;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->headers = new Headers();
    }

    /**
     * Access protected/private properties.
     *
     * @param   string                  $name               Name of property to access.
     * @return  mixed                                       Value of property.
     */
    public function __get($name)
    {
        $return = null;

        switch ($name) {
            case 'headers':
                $return = $this->headers;
                break;
            default:
                throw new \Exception('Invalid access to property "' . $name . '"');
        }

        return $return;
    }

    /**
     * Set status code.
     *
     * @param   int                     $code               Status code to set.
     */
    public function setStatusCode($code)
    {
        if (!isset(static::$status_phrases[$code])) {
            throw new \InvalidArgumentException('Invalid status code "' . $code . '"');
        }

        $this->status_code = $code;
    }

    /**
     * Return currently set status code.
     *
     * @return  int                                         Status code.
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * Return currently set status text.
     *
     * @return  string                                      Status text.
     */
    public function getStatusText()
    {
        return static::$status_phrases[$this->status_code];
    }

    /**
     * Set content of response.
     *
     * @param   string                  $content            Content of response.
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Set HTTP version.
     *
     * @param   string                  $version            HTTP Version to set.
     */
    public function setVersion($version)
    {
        if ($version != '1.0' && $version != '1.1') {
            throw new \InvalidArgumentException('Invalid HTTP version "' . $version . '"');
        }

        $this->http_version = $version;
    }

    /**
     * Return currently set HTTP version.
     *
     * @return  string                                      HTTP Version.
     */
    public function getVersion()
    {
        return $this->http_version;
    }

    /**
     * Send headers.
     */
    public function sendHeaders()
    {
        if (!headers_sent()) {
            header(
                sprintf(
                    'HTTP/%s %s %s',
                    $this->http_version,
                    $this->status_code,
                    static::$status_phrases[$this->status_code]
                ),
                true,
                $this->status_code
            );

            foreach ($this->headers as $name => $value) {
                header(sprintf('%s: %s', $name, $value));
            }
        }
    }

    /**
     * Send content of response.
     */
    public function sendContent()
    {
        print $this->content;
    }

    /**
     * Send headers and content of response.
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
}
