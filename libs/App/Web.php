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
     * @type    \Octris\Core\App\Web\Request|null
     */
    protected $request = null;

    /**
     * Instance of response object.
     *
     * @type    \Octris\Core\App\Web\Response|null
     */
    protected $response = null;

    /**
     * Application state.
     *
     * @type    \Octris\Core\App\State|null
     */
    protected $state = null;

    /**
     * Instance of router.
     *
     * @type    \Octris\Core\App\Web\IRouter
     */
    protected $router;

    /**
     * Constructor.
     *
     * @param   \Octris\Core\App\Web\IRouter    $router     Instance of router to use.
     */
    public function __construct(\Octris\Core\App\Web\IRouter $router)
    {
        $this->router = $router;

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
     * Return application state.
     *
     * @return  \Octris\Core\App\State          State of application.
     */
    public function getState()
    {
        if (is_null($this->state)) {
            // state needs to be created first
            $request = provider::access('request');

            if ($request->isExist('state') && $request->isValid('state', validate::T_BASE64)) {
                $this->state = state::thaw($request->getValue('state', validate::T_BASE64));
            }

            if (!is_object($this->state)) {
                $this->state = new state();
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
