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

use \Octris\Core\Provider as provider;
use \Octris\Core\Validate as validate;

/**
 * Page controller for web applications.
 *
 * @copyright   copyright (c) 2010-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class Page extends \Octris\Core\App\Page
{
    /**
     * Template instance.
     *
     * @type    \Octris\Core\Tpl
     */
    private $template = null;

    /**
     * Whether the page should be delivered only through HTTPS.
     *
     * @type    bool
     */
    protected $secure = false;

    /**
     * Breadcrumb for current page.
     *
     * @type    array
     */
    protected $breadcrumb = array();

    /**
     * Enabled CSRF protection.
     *
     * @type    array
     */
    protected $csrf_protection = array();

    /**
     * Constructor.
     *
     * @param   \Octris\Core\App\Web                    Application instance.
     */
    public function __construct(\Octris\Core\App\Web $app)
    {
        parent::__construct($app);
    }

    /**
     * Returns whether page should be only delivered secured.
     *
     * @return  bool                                    Secured flag.
     */
    final public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Enable CSRF protection for the specified action with an optional specified
     * scope.
     *
     * @param   string          $action                 Action the CSRF protection should be enabled for.
     * @param   string          $scope                  Optional scope of the CSRF token.
     */
    public function enableCsrfProtection($action, $scope = '')
    {
        $this->csrf_protection[$action] = $scope;
    }

    /**
     * Add an item to the breadcrumb
     *
     * @param   string          $name                   Name of item.
     * @param   string          $url                    URL for item.
     */
    public function addBreadcrumbItem($name, $url)
    {
        $this->breadcrumb[] = array(
            'name'  => $name,
            'url'   => $url
        );
    }

    /**
     * Determine the action of the request.
     *
     * @return  string                                      Name of action
     */
    public function getAction()
    {
        static $action = null;

        if (!is_null($action) != '') {
            return $action;
        }

        $method  = request::getRequestMethod();
        $request = null;

        if ($method == request::T_POST || $method == request::T_GET) {
            $method = ($method == request::T_POST
                        ? 'post'
                        : 'get');

            $request = provider::access($method);
        }

        if ($request instanceof Provider) {
            if ($request->isExist('ACTION')) {
                if ($request->isValid('ACTION', validate::T_ALPHANUM)) {
                    $action = $request->getValue('ACTION');
                }
            } else {
                // try to determine action from a request parameter named ACTION_...
                foreach ($request->filter('ACTION_') as $k) {
                    if ($request->isValid($k, validate::T_PRINTABLE)) {
                        $action = substr($k, 7);
                        break;
                    }
                }
            }
        }

        if (is_null($action)) {
            $action = '';
        }

        return $action;
    }

    /**
     * Fetch a CSRF token from the state and verify it.
     *
     * @param   string                  $scope                  Scope of the CSRF token to verify.
     * @return  bool                                            Returns true if CSRF token is valid, otherwiese false.
     */
    protected function verifyCsrfToken($scope)
    {
        $state = \Octris\Core\App::getInstance()->getState();

        if (!($is_valid = isset($state['__csrf_token']))) {
            // CSRF token is not in state
            $this->addError(__('CSRF token is not provided in application state!'));
        } else {
            $csrf = new \Octris\Core\App\Web\Csrf();

            if (!($is_valid = $csrf->verifyToken($state->pop('__csrf_token'), $scope))) {
                $this->addError(__('Provided CSRF token is invalid!'));
            }
        }

        return $is_valid;
    }

    /**
     * Validate configured CSRF protection.
     *
     * @param   string                          $action         Action to select ruleset for.
     * @return  bool                                            Returns true if validation suceeded, otherwise false.
     */
    public function validate($action)
    {
        $is_valid = parent::validate($action);

        if (array_key_exists($action, $this->csrf_protection)) {
            $is_valid = ($this->verifyCsrfToken($this->csrf_protection[$action]) && $is_valid);
        }

        return $is_valid;
    }

    /**
     * Return instance of template for current page.
     *
     * @return  \Octris\Core\Tpl                Instance of template engine.
     */
    public function getTemplate()
    {
        if (is_null($this->template)) {
            $this->template = \Octris\Core\App::getInstance()->getTemplate();

            $this->template->registerMethod('getBreadcrumb', function () {
                return $this->breadcrumb;
            }, array('max' => 0));
            $this->template->registerMethod('getCsrfToken', function ($scope = '') {
                $csrf = new \Octris\Core\App\Web\Csrf();

                return $csrf->createToken($scope);
            }, array('max' => 1));

            // values
            $this->template->setValue('errors', $this->errors);
            $this->template->setValue('messages', $this->messages);
        }

        return $this->template;
    }
}
