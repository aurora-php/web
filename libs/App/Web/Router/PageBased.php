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
 * Default -- page based -- router.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class PageBased implements \Octris\Core\App\Web\IRouter
{
    /**
     * Entry page to use if no other page is loaded.
     *
     * @type    string
     */
    protected $entry_page = '';

    /**
     * Constructor.
     *
     * @param   string          $entry_page     Name of class of entry page.
     */
    public function __construct($entry_page)
    {
        $this->entry_page = $entry_page;
    }

    /**
     * Application initial routing.
     *
     * @param   \Octris\Core\App\Web        $app            Instance of application.
     * @param   \Octris\Core\App\Page       $last_page      Last page.
     * @return  \Octris\Core\App\Page                       Returns instance of next page to render.
     */
    protected function routing(\Octris\Core\App\Web $app, \Octris\Core\App\Page $last_page)
    {
        $action = $last_page->getAction();

        $last_page->validate($action);

        $next_page = $last_page->getNextPage($action, $this->entry_page);

        return $next_page;
    }

    /**
     * Application rerouting.
     *
     * @param   \Octris\Core\App\Web        $app            Instance of application.
     * @param   \Octris\Core\App\Page       $last_page      Last page.
     * @param   \Octris\Core\App\Page       $next_page      Expected page to render.
     * @return  \Octris\Core\App\Page                       Actual page to render.
     */
    protected function rerouting(\Octris\Core\App\Web $app, \Octris\Core\App\Page $last_page, \Octris\Core\App\Page $next_page)
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
        $request = $app->getRequest();

        if ($secure != $request->isSSL() && $request->getRequestMethod() == request::METHOD_GET) {
            header('Location: ' . ($secure ? $request->getSSLUrl() : $request->getNonSSLUrl()));
            exit;
        }

        return $next_page;
    }

    /**
     * Initiate routing.
     *
     * @param   \Octris\Core\App\Web        $app            Instance of application.
     * @return  string                                      Content to render.
     */
    public function route(\Octris\Core\App\Web $app)
    {
        // determine last page
        $state = $app->getState();
        $class = (isset($state['__last_page'])
                  ? $state['__last_page']
                  : $this->entry_page);

        $last_page = new $class($app);

        // routing
        $next_page = $this->routing($app, $last_page);
        $next_page = $this->rerouting($app, $last_page, $next_page);

        $state['__last_page'] = get_class($next_page);

        // render content and return
        $content = $next_page->render();

        return $content;
    }
}
