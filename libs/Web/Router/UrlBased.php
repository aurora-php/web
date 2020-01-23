<?php

/*
 * This file is part of the 'octris/web' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Web\Router;

/**
 * Url-based routing using FastRoute
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
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
                    'routeCollector' => '\Octris\Web\Router\RuleCollector'
                ]
            );
        } else {
            $this->router_dispatcher = \FastRoute\cachedDispatcher(
                $setup,
                [
                    'cacheFile' => $file,
                    'routeCollector' => '\Octris\Web\Router\RuleCollector'
                ]
            );
        }

        parent::__construct($entry_page);
    }

    /**
     * Routing.
     *
     * @param   \Octris\Web        $app            Instance of application.
     * @param   \Octris\Web\Page   $last_page      Last page.
     * @return  \Octris\Web\Page                   Returns instance of next page to render.
     */
    protected function routing(\Octris\Web $app, \Octris\Web\Page $last_page)
    {
        do {
            $request = $app->getRequest();
            $response = $app->getResponse();

            $result = $this->router_dispatcher->dispatch(
                $request->getRequestMethod(),
                parse_url(  // https://github.com/nikic/FastRoute/issues/19
                    $request->getUri(),
                    PHP_URL_PATH
                )
            );

            switch ($result[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    $response->setStatusCode(404);

                    $next_page = new \Octris\Web\Page\Error($app);
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $response->setStatusCode(405);

                    $next_page = new \Octris\Web\Page\Error($app);
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $result[1];
                    $vars = $result[2];

                    $provider = \Octris\Web\Provider::access('get');

                    foreach ($vars as $name => $value) {
                        $provider->setValue($name, $value);
                    }

                    if ($handler === '') {
                        // no handler provided use default routing
                        $next_page = parent::routing($app, $last_page);
                    } elseif (is_callable($handler) && $handler instanceof \Octris\Web\Router\CallbackHandlerInterface) {
                        // an instance of a calback handler
                        $next_page = $handler($app);

                        if (!(is_object($handler) && $handler instanceof \Octris\Web\Page)) {
                            // callback did not return an instance of a page class, exit application
                            exit();
                        }
                    } elseif (is_object($handler) && $handler instanceof \Octris\Web\Page) {
                        // handler is the instance of a page class
                        $next_page = $handler;
                    } elseif (is_string($handler) && class_exists($handler) && is_subclass_of($handler, '\Octris\Web\Page')) {
                        // handler is the name of a page class
                        $next_page = new $handler($app);
                    } else {
                        throw new \InvalidArgumentException('Either an instance of a route handler or a name or instance of a page class is required as route handler');
                    }

                    break;
            }
        } while (false);

        return $next_page;
    }
}
