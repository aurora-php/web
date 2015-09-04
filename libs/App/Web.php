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
     * Application state.
     *
     * @type    \Octris\Core\App\State
     */
    protected $state = null;

    /**
     * Entry page to use if no other page is loaded. To be overwritten by applications' main class.
     *
     * @type    string
     */
    protected $entry_page = '';

    /**
     * Constructor.
     *
     * @param   \Octris\Core\App\Web\IRouter    $router     Instance of router to use.
     */
    public function __construct(\Octris\Core\App\Web\IRouter $router)
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
     * Return application state.
     *
     * @return  \Octris\Core\App\State          State of application.
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Try to determine the last visited page supplied by the application state. If
     * last visited page can't be determined (eg.: when entering the application),
     * a new instance of the applications' entry page is created.
     *
     * @return  \Octris\Core\App\Page           Returns instance of determined last visited page or instance of entry page.
     */
    protected function getLastPage()
    {
        $class = (isset($this->state['__last_page'])
                  ? $this->state['__last_page']
                  : $this->entry_page);

        $page = new $class($this);

        return $page;
    }

    /**
     * Make a page the last visited page. This method is called internally by the 'process' method
     * before aquiring an other application page.
     *
     * @param   \Octris\Core\App\Page       $page           Page object to set as last visited page.
     */
    protected function setLastPage(\Octris\Core\App\Page $page)
    {
        $class = get_class($page);

        $this->state['__last_page'] = $class;
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

        $content = $this->router->route();

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
