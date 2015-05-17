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
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Response {
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
        switch ($name) {
            case 'headers':
                $return = $this->headers;
                break;
            default:
                throw new \Exception('Invalid access to property "' . $name . '"');
        }
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
     * Send headers.
     */
    public function sendHeaders()
    {
        if (!headers_sent()) {
            foreach ($this->headers as $name => $value) {
                header($name, $value);
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
    }
}
