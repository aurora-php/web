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

provider::set('server', $_SERVER);
provider::set('env', $_ENV);
provider::set('request', $_REQUEST);
provider::set('post', $_POST);
provider::set('get', $_GET);
provider::set('cookie', $_COOKIE);
provider::set('files', $_FILES);

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
     * Instance of fastroute dispatcher.
     *
     * @type    \FastRoute\Dispatcher|null
     */
    protected $router_dispatcher = null;

    /**
     * Storage container for GET request data.
     *
     * @type    \ArrayObject
     */
    protected $router_storage;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->router_storage = new ArrayObject();

        provider::setIfUnset('get', $_GET, provider::T_READONLY, $this->router_storage);

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
     * Setup router.
     *
     * @param   callable            $cb                 A callback for defining the router definitions.
     * @param   string|null         $file               Optional filename for route caching.
     */
    public function setupRouter(callable $cb, $file = null)
    {
        if (is_null($file)) {
            $this->router_dispatcher = \FastRoute\simpleDispatcher(
                $cb,
                [
                    'routeCollector' => '\\Octris\\Core\\App\\Web\\RuleCollector'
                ]
            );
        } else {
            $this->router_dispatcher = \FastRoute\cachedDispatcher(
                $cb,
                [
                    'cacheFile' => $file,
                    'routeCollector' => '\\Octris\\Core\\App\\Web\\RuleCollector'
                ]
            );
        }
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
     * Page-based routing.
     *
     * @return  \Octris\Core\App\Page           Returns instance of next page to render.
     */
    protected function pageRouter()
    {
        $last_page = $this->getLastPage();
        $action = $last_page->getAction();

        $last_page->validate($action);

        $next_page = $last_page->getNextPage($action, $this->entry_page);

        return $next_page;
    }

    /**
     * URL-based routing.
     *
     * @return  \Octris\Core\App\Page           Returns instance of next page to render.
     */
    public function urlRouter()
    {
        do {
            $result = $this->router_dispatcher->dispatch($this->request->getRequestMethod(), $this->request->getUri());

            switch ($result[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    $next_page = new \Octris\Core\App\Web\Page\Error($this, '404');
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $next_page = new \Octris\Core\App\Web\Page\Error($this, '405');
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $result[1];
                    $vars = $result[2];

                    foreach ($vars as $name => $var) {
                        $this->router_storage[$name] = $var;
                    }

                    if (is_null($handler[1])) {
                        // no handler provided use default routing
                        $next_page = $this->pageRouter();
                    } elseif (is_callable($handler)) {
                        // handler is callable, directly call it and provide router arguments as parameter.
                        $next_page = $handler($this, $vars);

                        if (!($next_page instanceof \Octris\Core\App\Page)) {
                            // callback did not return any page to route to, exit silently.
                            exit();
                        }
                    } elseif (class_exists($handler) && is_subclass_of($handler, '\Octris\Core\App\Web\Page')) {
                        // handler is a page class
                        $next_page = new $handler($this);
                    } else {
                        throw new \Exception('Either a callable or a page is required as route handler');
                    }

                    break;
            }
        } while (false);
    }

    /**
     * Application initial routing.
     *
     * @return  \Octris\Core\App\Page           Returns instance of next page to render.
     */
    protected function routing()
    {
        if (!is_null($this->router_dispatcher)) {
            $next_page = $this->urlRouter();
        } else {
            // fall-back to default routing if router is not configured
            $next_page = $this->pageRouter();
        }

        return $next_page;
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
        $request = $this->getRequest();

        if ($secure != $request->isSSL() && $request->getRequestMethod() == request::METHOD_GET) {
            header('Location: ' . ($secure ? $request->getSSLUrl() : $request->getNonSSLUrl()));
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

        $next_page = $this->routing();
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
