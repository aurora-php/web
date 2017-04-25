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
abstract class Page
{
    /**
     * Template instance.
     *
     * @type    \Octris\Core\Tpl
     */
    private $template = null;

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
     * Next valid actions and their view pages.
     *
     * @type    array
     */
    protected $next_pages = array();

    /**
     * Stored error Messages occured during execution of the current page.
     *
     * @type    array
     */
    protected $errors = array();

    /**
     * Stored notification messages collected during execution of the current page.
     *
     * @type    array
     */
    protected $messages = array();

    /**
     * Defined validator(s), only available after 'validate' method was called.
     *
     * @type    array
     */
    private $validators = array();

    /**
     * Application instance.
     *
     * @type    \Octris\Core\App\Web
     */
    protected $app;

    /**
     * Constructor.
     *
     * @param   \Octris\Core\App\Web        $app        Application instance.
     */
    public function __construct(\Octris\Core\App\Web $app)
    {
        $this->app = $app;
    }

    /**
     * Added magic getter to provide readonly access to protected properties.
     *
     * @param   string          $name                   Name of property to return.
     * @return  mixed                                   Value of property.
     */
    public function __get($name)
    {
        return (isset($this->{$name}) ? $this->{$name} : null);
    }

    /**
     * Returns name of page class if page instance is casted to a string.
     *
     * @param   string                                  Returns name of class.
     */
    final public function __toString()
    {
        return get_called_class();
    }

    /**
     * Add a validator for the page.
     *
     * @param   string                          $type           Name of data to access through data provider.
     * @param   string|array                    $action         Action(s) that trigger(s) the validator.
     * @param   array                           $schema         Validation schema.
     * @param   int                             $mode           Validation mode.
     */
    protected function addValidator($type, $action, array $schema, $mode = \Octris\Core\Validate\Schema::T_IGNORE)
    {
        $actions = (array)$action;

        foreach ($actions as $action) {
            provider::access($type)->addValidator((string)$this . ':' . $action, $schema);
        }
    }

    /**
     * Apply a configured validator.
     *
     * @param   string                          $type           Name of data to access through data provider.
     * @param   string                          $action         Action to apply validator for.
     * @return  mixed                           Returns true, if valid otherwise an array with error messages.
     */
    protected function applyValidator($type, $action)
    {
        $provider = provider::access($type);
        $key      = (string)$this . ':' . $action;

        return ($provider->hasValidator($key)
                ? $provider->applyValidator($key)
                : array(true, null, array(), null));
    }

    /**
     * Return an array of data sent by a request of the specified action. Note, that request data can only be
     * available as soon as the method 'validate' was called. Otherwise the method returns an empty array.
     * This method is probably only of use for child classes to access request data in case of a
     * validation error to pre-fill form fields when re-building the form template to re-enter the data.
     *
     * The second argument takes an array of key=>value pairs: the key specified the data fields that will
     * be returned. The value acts as default data, if no request data of the specified key exists.
     *
     * @param   string                          $action         Action to return data for.
     * @param   array                           $input_data     Input data, see method description for details.
     * @return  array                                           Data.
     */
    protected function getRequestData($action, array $input_data)
    {
        $data = (isset($this->validators[$action])
                    ? $this->validators[$action]->getData()
                    : []);

        return ($data + $input_data);
    }

    /**
     * Gets next page from action and next_pages array of last page
     *
     * @param   string                          $action         Action to get next page for.
     * @param   string                          $entry_page     Name of the entry page for possible fallback.
     * @return  \Octris\Core\App\Web\Page                       Next page.
     */
    public function getNextPage($action, $entry_page)
    {
        $next = $this;

        if (count($this->errors) == 0) {
            if (isset($this->next_pages[$action])) {
                // lookup next page from current page's next_page array
                $class = $this->next_pages[$action];
                $next  = new $class($this->app);
            } else {
                // lookup next page from entry page's next_page array
                $entry = new $entry_page($this->app);

                if (isset($entry->next_pages[$action])) {
                    $class = $entry->next_pages[$action];
                    $next  = new $class($this->app);
                }
            }
        }

        return $next;
    }

    /**
     * Add error message for current page.
     *
     * @param   string          $err                        Error message to add.
     */
    public function addError($err)
    {
        $this->errors[] = $err;
    }

    /**
     * Add multiple errors for current page.
     *
     * @param   array           $err                        Array of error messages.
     */
    public function addErrors(array $err)
    {
        $this->errors = array_merge($this->errors, $err);
    }

    /**
     * Add message for current page.
     *
     * @param   string          $msg                        Message to add.
     */
    public function addMessage($msg)
    {
        $this->messages[] = $msg;
    }

    /**
     * Add multiple messages for current page.
     *
     * @param   array           $msg                        Array of messages.
     */
    public function addMessages(array $msg)
    {
        $this->messages = array_merge($this->messages, $msg);
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

        if (!is_null($action)) {
            return $action;
        }

        $method  = $this->app->getRequest()->getRequestMethod();
        $request = null;

        if ($method == request::METHOD_POST || $method == request::METHOD_GET) {
            $method = ($method == request::METHOD_POST
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
        $state = $this->app->getState();

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
        $is_valid = true;

        if ($action != '') {
            $method = $this->app->getRequest()->getRequestMethod();

            list($is_valid, , $errors, $validator) = $this->applyValidator($method, $action);

            if (!$is_valid) {
                $this->validators[$action] = $validator;

                $this->addErrors($errors);
            }
        }

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
            $tpl = \Octris\Core\Registry::getInstance()->createTemplate;

            // register common template methods
            $tpl->registerMethod('getState', function (array $data = array()) {
                return $this->app->getState()->freeze($data);
            }, array('min' => 0, 'max' => 1));
            $tpl->registerMethod('isAuthenticated', function () {
                return \Octris\Core\Auth::getInstance()->isAuthenticated();
            }, array('min' => 0, 'max' => 0));
            $tpl->registerMethod('getBreadcrumb', function () {
                return $this->breadcrumb;
            }, array('max' => 0));
            $tpl->registerMethod('getCsrfToken', function ($scope = '') {
                $csrf = new \Octris\Core\App\Web\Csrf();

                return $csrf->createToken($scope);
            }, array('max' => 1));

            // values
            $tpl->setValue('errors', $this->errors);
            $tpl->setValue('messages', $this->messages);

            $this->template = $tpl;
        }

        return $this->template;
    }

    /**
     * Abstract method definition.
     *
     * @param   \Octris\Core\App\Web\Page       $last_page      Instance of last called page.
     * @param   string                          $action         Action that led to current page.
     * @return  mixed                                           Returns either page to redirect to or null.
     * @abstract
     */
    abstract public function prepare(\Octris\Core\App\Web\Page $last_page, $action);

    /**
     * Abstract method definition.
     *
     * @abstract
     */
    abstract public function render();
    /**/
}
