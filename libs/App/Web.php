<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\App;

use \Octris\Core\App\Web\Request as request;
use \Octris\Core\Validate as validate;
use \Octris\Core\Provider as provider;

provider::setIfUnset('server', $_SERVER, provider::T_READONLY);
provider::setIfUnset('env', $_ENV, provider::T_READONLY);
provider::setIfUnset('request', $_REQUEST, provider::T_READONLY);
provider::setIfUnset('post', $_POST, provider::T_READONLY);
provider::setIfUnset('get', $_GET, provider::T_READONLY);
provider::setIfUnset('cookie', $_COOKIE, provider::T_READONLY);
provider::setIfUnset('files', $_FILES, provider::T_READONLY);

/**
 * Core class for Web applications.
 *
 * @copyright   copyright (c) 2011-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class Web extends \Octris\Core\App
{
    /**
     * Instance of request object.
     *
     * @type    \Octris\Core\App\Web\Request.
     */
    protected $request = null;

    /**
     * Instance of response object.
     *
     * @type    \Octris\Core\App\Web\Response.
     */
    protected $response = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns instance of request object.
     *
     * @return  \Octris\Core\App\Web\Request                Instance of request object.
     */
    public function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = new \Octris\Core\App\Web\Request();
        }

        return $this->request;
    }

    /**
     * Returns instance of response object.
     *
     * @return  \Octris\Core\App\Web\Response               Instance of response object.
     */
    public function getResponse()
    {
        if (is_null($this->response)) {
            $this->response = new \Octris\Core\App\Web\Response();
        }

        return $this->response;
    }

    /**
     * Initialization of web application.
     */
    protected function initialize()
    {
        $request = provider::access('request');

        if ($request->isExist('state') && $request->isValid('state', validate::T_BASE64)) {
            $this->state = state::thaw($request->getValue('state', validate::T_BASE64));
        }

        if (!is_object($this->state)) {
            $this->state = new state();
        }
    }

    /**
     * Application initial routing.
     *
     * @return  \Octris\Core\App\Page           Returns instance of next page to render.
     */
    protected function routing()
    {
        $last_page = $this->getLastPage();
        $action = $last_page->getAction();

        $last_page->validate($action);

        $next_page = $last_page->getNextPage($action, $this->entry_page);
    }

    /**
     * Application rerouting.
     *
     * @param   \Octris\Core\App\Page           Expected page to render.
     * @return  \Octris\Core\App\Page           Actual page to render.
     */
    protected function rerouting($next_page)
    {
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
            header('Location: ' . ($secure ? request::getSSLUrl() : request::getNonSSLUrl()));
            exit;
        }

        return $next_page;
    }

    /**
     * Main application processor. This is the only method that needs to be called to
     * invoke an application. Internally this method determines the last visited page
     * and handles everything required to determine the next page to display.
     *
     * The following example shows how to invoke an application, assuming that 'test'
     * implements an application based on \Octris\Core\App.
     *
     * <code>
     * $app = test::getInstance();
     * $app->process();
     * </code>
     */
    public function process()
    {
        $response = $this->getResponse();

        $response->headers->setHeader(
            'Content-Type',
            'text/html; charset="UTF-8"'
        );

        $this->initialize();

        $next_page = $this->routing()
        $next_page = $this->rerouting($next_page);

        $this->setLastPage($next_page);

        $response->setContent($next_page->render());

        $response->send();
    }

    /**
     * Create new instance of template engine and setup common stuff needed for templates of a web application.
     *
     * @return  \Octris\Core\Tpl                Instance of template class.
     * @todo    set correct path (cache, resources, output, ...)
     */
    public function getTemplate()
    {
        $tpl = \Octris\Core\Registry::getInstance()->createTemplate;

        // register common template methods
        $tpl->registerMethod('getState', function (array $data = array()) {
            return $this->getState()->freeze($data);
        }, array('min' => 0, 'max' => 1));
        $tpl->registerMethod('isAuthenticated', function () {
            return \Octris\Core\Auth::getInstance()->isAuthenticated();
        }, array('min' => 0, 'max' => 0));

        return $tpl;
    }
}
