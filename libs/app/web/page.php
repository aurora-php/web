<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace octris\core\app\web {
    use \octris\core\provider as provider;
    use \octris\core\validate as validate;

    /**
     * Page controller for web applications.
     *
     * @octdoc      c:web/page
     * @copyright   copyright (c) 2010-2014 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    abstract class page extends \octris\core\app\page
    /**/
    {
        /**
         * Template instance.
         *
         * @octdoc  p:page/$template
         * @type    \octris\core\tpl
         */
        private $template = null;
        /**/

        /**
         * Whether the page should be delivered only through HTTPS.
         *
         * @octdoc  p:page/$secure
         * @type    bool
         */
        protected $secure = false;
        /**/

        /**
         * Breadcrumb for current page.
         *
         * @octdoc  p:page/$breadcrumb
         * @type    array
         */
        protected $breadcrumb = array();
        /**/

        /**
         * Enabled CSRF protection.
         *
         * @octdoc  p:page/$csrf_protection
         * @type    array
         */
        protected $csrf_protection = array();
        /**/

        /**
         * Constructor.
         *
         * @octdoc  m:page/__construct
         */
        public function __construct()
        /**/
        {
            parent::__construct();
        }

        /**
         * Returns whether page should be only delivered secured.
         *
         * @octdoc  m:page/isSecure
         * @return  bool                                    Secured flag.
         */
        public final function isSecure()
        /**/
        {
            return $this->secure;
        }
        
        /**
         * Enable CSRF protection for the specified action with an optional specified
         * scope.
         *
         * @octdoc  m:page/enableCsrfProtection
         * @param   string          $action                 Action the CSRF protection should be enabled for.
         * @param   string          $scope                  Optional scope of the CSRF token.
         */
        public function enableCsrfProtection($action, $scope = '')
        /**/
        {
            $this->csrf_protection[$action] = $scope;
        }

        /**
         * Add an item to the breadcrumb
         *
         * @octdoc  m:page/addBreadcrumbItem
         * @param   string          $name                   Name of item.
         * @param   string          $url                    URL for item.
         */
        public function addBreadcrumbItem($name, $url)
        /**/
        {
            $this->breadcrumb[] = array(
                'name'  => $name,
                'url'   => $url
            );
        }

        /**
         * Determine the action of the request.
         *
         * @octdoc  m:page/getAction
         * @return  string                                      Name of action
         */
        public function getAction()
        /**/
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

            if ($request instanceof provider) {
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
         * Determine requested module with specified action. If a module was determined but the action is not
         * valid, this method will return default application module. The module must be reachable from inside
         * the application.
         *
         * @octdoc  m:page/getModule
         * @return  string                                      Name of module
         */
        public function getModule()
        /**/
        {
            static $module = '';

            if ($module != '') {
                return $module;
            }

            $method  = request::getRequestMethod();

            if ($method == request::T_POST || $method == request::T_GET) {
                $method = ($method == request::T_POST
                            ? 'post'
                            : 'get');

                $request = provider::access($method);
            }

            if (($tmp = $request->getValue('MODULE', validate::T_ALPHANUM)) !== false) {
                $module = $tmp;
            } else {
                // try to determine module from a request parameter named MODULE_...
                foreach ($request->getPrefixed('MODULE_', validate::T_ALPHANUM) as $k => $v) {
                    $module = substr($k, 7);
                    break;
                }
            }

            if (!$module) {
                $module = 'default';
            }

            return $module;
        }

        /**
         * Fetch a CSRF token from the state and verify it.
         *
         * @octdoc  m:page/verifyCsrfToken
         * @param   string                  $scope                  Scope of the CSRF token to verify.
         * @return  bool                                            Returns true if CSRF token is valid, otherwiese false.
         */
        protected function verifyCsrfToken($scope)
        /**/
        {            
            $state = \octris\core\app::getInstance()->getState();
            
            if (!($is_valid = isset($state['__csrf_token']))) {
                // CSRF token is not in state
                $this->addError(__('CSRF token is not provided in application state!'));
            } else {                    
                $csrf = new \octris\core\app\web\csrf();
                
                if (!($is_valid = $csrf->verifyToken($state->pop('__csrf_token'), $scope))) {
                    $this->addError(__('Provided CSRF token is invalid!'));
                }
            }
            
            return $is_valid;
        }

        /**
         * Validate configured CSRF protection.
         *
         * @octdoc  m:page/validate
         * @param   string                          $action         Action to select ruleset for.
         * @return  bool                                            Returns true if validation suceeded, otherwise false.
         */
        public function validate($action)
        /**/
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
         * @octdoc  m:page/getTemplate
         * @return  \octris\core\tpl                Instance of template engine.
         */
        public function getTemplate()
        /**/
        {
            if (is_null($this->template)) {
                $this->template = \octris\core\app::getInstance()->getTemplate();

                $this->template->registerMethod('getBreadcrumb', function() {
                    return $this->breadcrumb;
                }, array('max' => 0));
                $this->template->registerMethod('getCsrfToken', function($scope = '') {
                    $csrf = new \octris\core\app\web\csrf();
                    
                    return $csrf->createToken($scope);
                }, array('max' => 1));
                
                // values
                $this->template->setValue('errors',   $this->errors);
                $this->template->setValue('messages', $this->messages);
            }

            return $this->template;
        }
    }
}
