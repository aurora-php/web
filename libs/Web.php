<?php

/*
 * This file is part of the 'octris/web' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Web\App;

use \Octris\Web\Request as request;
use \Octris\Web\Validate as validate;
use \Octris\Web\Provider as provider;

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
 * @copyright   copyright (c) 2011-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class Web
{
    /**
     * Instance of request object.
     *
     * @type    \Octris\Web\Request|null
     */
    protected $request = null;

    /**
     * Instance of response object.
     *
     * @type    \Octris\Web\Response|null
     */
    protected $response = null;

    /**
     * Application state.
     *
     * @type    \Octris\Web\App\State|null
     */
    protected $state = null;

    /**
     * Instance of router.
     *
     * @type    \Octris\Web\RouterInterface
     */
    protected $router;

    /**
     * Constructor.
     *
     * @param   \Octris\Web\RouterInterface    $router     Instance of router to use.
     */
    public function __construct(\Octris\Web\RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Returns instance of request object.
     *
     * @return  \Octris\Web\Request                Instance of request object.
     */
    public function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = new \Octris\Web\Request();
        }

        return $this->request;
    }

    /**
     * Returns instance of response object.
     *
     * @return  \Octris\Web\Response               Instance of response object.
     */
    public function getResponse()
    {
        if (is_null($this->response)) {
            $this->response = new \Octris\Web\Response();
        }

        return $this->response;
    }

    /**
     * Return application state.
     *
     * @return  \Octris\Web\App\State          State of application.
     */
    public function getState()
    {
        if (is_null($this->state)) {
            // state needs to be created first
            $request = provider::access('request');

            if ($request->isExist('state') && $request->isValid('state', validate::T_BASE64)) {
                $this->state = Web\State::thaw($request->getValue('state', validate::T_BASE64));
            }

            if (!is_object($this->state)) {
                $this->state = new Web\State();
            }
        }

        return $this->state;
    }

    /**
     * Misc application initialization.
     */
    protected function initialize()
    {
    }

    /**
     * Run the application. Example:
     *
     * <code>
     * $app = new App\Test();
     * $app->run();
     * </code>
     */
    public function run()
    {
        $response = $this->getResponse();

        $response->headers->setHeader(
            'Content-Type',
            'text/html; charset="UTF-8"'
        );

        $this->initialize();

        $content = $this->router->route($this);

        $response->setContent($content);

        $response->send();
    }
}
