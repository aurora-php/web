<?php

/*
 * This file is part of the 'org.octris.core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\octris\core\app\web {
    use \org\octris\core\provider as provider;
    use \org\octris\core\validate as validate;

    /**
     * Page controller for web applications.
     *
     * @octdoc      c:web/page
     * @copyright   copyright (c) 2010-2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    abstract class page extends \org\octris\core\app\page
    /**/
    {
        /**
         * Template instance.
         *
         * @octdoc  p:page/$template
         * @var     \org\octris\core\tpl
         */
        private $template = null;
        /**/

        /**
         * Whether the page should be delivered only through HTTPS.
         *
         * @octdoc  p:page/$secure
         * @var     bool
         */
        protected $secure = false;
        /**/

        /**
         * Breadcrumb for current page.
         *
         * @octdoc  p:page/$breadcrumb
         * @var     array
         */
        protected $breadcrumb = array();
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
         * Return instance of template for current page.
         *
         * @octdoc  m:page/getTemplate
         * @return  \org\octris\core\tpl                Instance of template engine.
         */
        public function getTemplate()
        /**/
        {
            if (is_null($this->template)) {
                $this->template = \org\octris\core\app::getInstance()->getTemplate();

                // TODO: PHP5.4
                $breadcrumb =& $this->breadcrumb;

                $this->template->registerMethod('getBreadcrumb', function() use (&$breadcrumb) {
                    return $breadcrumb;
                }, array('max' => 0));
                
                // values
                $this->template->setValues($this->values);
                $this->template->setValue('errors',   $this->errors);
                $this->template->setValue('messages', $this->messages);
            }

            return $this->template;
        }
    }
}
