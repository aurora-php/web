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
         * Whether the page should be delivered only through HTTPS.
         *
         * @octdoc  v:page/$secure
         * @var     bool
         */
        protected $secure = false;
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
            
            if ($method == request::T_POST || $method == request::T_GET) {
                $method = ($method == request::T_POST
                            ? 'post'
                            : 'get');
                
                $request = provider::access($method);
            }
            
            if (($tmp = $request->getValue('ACTION', validate::T_ALPHANUM)) !== false) {
                $action = $tmp;
            } else {
                // try to determine action from a request parameter named ACTION_...
                foreach ($request->getPrefixed('ACTION_', validate::T_ALPHANUM) as $k => $v) {
                    $action = substr($k, 7);
                    break;
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
    }
}
