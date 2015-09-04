<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\App\Web\Router;

/**
 * Url-based routing using FastRoute
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class UrlBased extends PageBased
{
    /**
     * Instance of fastroute dispatcher.
     *
     * @type    \FastRoute\Dispatcher|null
     */
    protected $router_dispatcher = null;

    /**
     * Constructor.
     *
     * @param   string          $entry_page     Name of class of entry page.
     * @param   callable        $setup          Callback that defines routes to setup.
     * @param   string|null     $file           Optional cache file for routes.
     */
    public function __construct($entry_page, callable $setup, $file = null)
    {
        if (is_null($file)) {
            $this->router_dispatcher = \FastRoute\simpleDispatcher(
                $setup,
                [
                    'routeCollector' => '\Octris\Core\App\Web\Router\RuleCollector'
                ]
            );
        } else {
            $this->router_dispatcher = \FastRoute\cachedDispatcher(
                $setup,
                [
                    'cacheFile' => $file,
                    'routeCollector' => '\Octris\Core\App\Web\Router\RuleCollector'
                ]
            );
        }

        parent::__construct($entry_page);
    }

    /**
     * Routing.
     *
     * @param   \Octris\Core\App\Web        $app            Instance of application.
     * @param   \Octris\Core\App\Page       $last_page      Last page.
     * @return  \Octris\Core\App\Page                       Returns instance of next page to render.
     */
    protected function routing(\Octris\Core\App\Web $app, \Octris\Core\App\Page $last_page)
    {
        do {
            $request = $app->getRequest();
            $response = $app->getResponse();

            $result = $this->router_dispatcher->dispatch($request->getRequestMethod(), $request->getUri());

            switch ($result[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    $response->setStatusCode(404);

                    $next_page = new \Octris\Core\App\Web\Page\Error($app);
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $response->setStatusCode(405);

                    $next_page = new \Octris\Core\App\Web\Page\Error($app);
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $result[1];
                    $vars = $result[2];

                    $provider = provider::access('get');

                    foreach ($vars as $name => $value) {
                        $provider->setValue($name, $value);
                    }

                    if (is_null($handler[1])) {
                        // no handler provided use default routing
                        $next_page = parent::routing($app, $last_page);
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

        return $next_page;
    }
}
