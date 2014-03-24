<?php

/*
 * This file is part of the 'org.octris.core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\octris\core {
    require_once('org.octris.core/app/autoloader.class.php');

    use \org\octris\core\validate as validate;
    use \org\octris\core\provider as provider;

    /**
     * Core application class.
     *
     * @octdoc      c:core/app
     * @copyright   copyright (c) 2010-2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    abstract class app
    /**/
    {
        /**
         * Used in combination with app/getPath to determine path.
         *
         * @octdoc  d:app/T_PATH_BASE, T_PATH_CACHE, T_PATH_DATA, T_PATH_ETC, T_PATH_HOME_ETC, T_PATH_HOST, T_PATH_LIBS, T_PATH_LIBSJS, T_PATH_LOCALE, T_PATH_RESOURCES, T_PATH_STYLES, T_PATH_LOG, T_PATH_WORK, T_PATH_WORK_LIBSJS, T_PATH_WORK_RESOURCES, T_PATH_WORK_STYLES, T_PATH_WORK_TPL
         */
        const T_PATH_BASE           = '%s';
        const T_PATH_CACHE          = '%s/cache/%s';
        const T_PATH_CACHE_DATA     = '%s/cache/%s/data';
        const T_PATH_CACHE_TPL      = '%s/cache/%s/templates_c';
        const T_PATH_DATA           = '%s/data/%s';
        const T_PATH_ETC            = '%s/etc/%s';
        const T_PATH_HOME_ETC       = '%s/.octris/%s';
        const T_PATH_HOST           = '%s/host/%s';
        const T_PATH_LIBS           = '%s/libs/%s';
        const T_PATH_LIBSJS         = '%s/host/%s/libsjs';
        const T_PATH_LOCALE         = '%s/locale/%s';
        const T_PATH_LOG            = '%s/log/%s';
        const T_PATH_RESOURCES      = '%s/host/%s/resources';
        const T_PATH_STYLES         = '%s/host/%s/styles';
        const T_PATH_TOOLS          = '%s/tools/%s';
        const T_PATH_WORK           = '%s/work/%s';
        const T_PATH_WORK_LIBS      = '%s/work/%s/libs';
        const T_PATH_WORK_LIBSJS    = '%s/work/%s/libsjs';
        const T_PATH_WORK_RESOURCES = '%s/work/%s/resources';
        const T_PATH_WORK_STYLES    = '%s/work/%s/styles';
        const T_PATH_WORK_TPL       = '%s/work/%s/templates';
        /**/

        /**
         * Used to abstract application context types.
         *
         * @octdoc  d:app/T_CONTEXT_UNDEFINED, T_CONTEXT_CLI, T_CONTEXT_WEB, T_CONTEXT_TEST
         */
        const T_CONTEXT_UNDEFINED = 0;
        const T_CONTEXT_CLI       = 1;
        const T_CONTEXT_WEB       = 2;
        const T_CONTEXT_TEST      = 3;
        /**/

        /**
         * Application name.
         *
         * @octdoc  p:app/$octris_app
         * @type    string|null
         */
        private $octris_app = null;
        /**/

        /**
         * Application root directory.
         *
         * @octdoc  p:app/$octris_base
         * @type    string|null
         */
        private $octris_base = null;
        /**/

        /**
         * Application instance.
         *
         * @octdoc  p:app/$instance
         * @type    \org\octris\core\app
         */
        private static $instance = null;
        /**/

        /**
         * Context of the application.
         *
         * @octdoc  p:app/$context
         * @type    int
         */
        protected $context = self::T_CONTEXT_UNDEFINED;
        /**/

        /**
         * Application state.
         *
         * @octdoc  p:app/$state
         * @type    \org\octris\core\app\state
         */
        protected $state = null;
        /**/

        /**
         * Entry page to use if no other page is loaded. To be overwritten by applications' main class.
         *
         * @octdoc  p:app/$entry_page
         * @type    string
         */
        protected $entry_page = '';
        /**/

        /**
         * Constructor is protected to force creation of instance using 'getInstance' method.
         *
         * @octdoc  m:app/__construct
         */
        protected function __construct()
        /**/
        {
        }

        /**
         * Abstract method definition. Initialize must be implemented by any subclass.
         *
         * @octdoc  m:app/initialize
         * @abstract
         */
        protected function initialize()
        /**/
        {
        }

        /**
         * Abstract method definition. Process must be implemented by any subclass.
         *
         * @octdoc  m:app/process
         * @abstract
         */
        abstract public function process();
        /**/

        /**
         * Invoke the page of an application without using the process workflow.
         *
         * @octdoc  m:app/invoke
         * @param   \org\octris\core\app\page       $next_page          Application page to invoke.
         * @param   string                          $action             Optional action to invoke page with.
         */
        public function invoke(\org\octris\core\app\page $next_page, $action = '')
        /**/
        {
            $this->initialize();

            $max = 3;

            $last_page = $next_page;

            do {
                $redirect_page = $next_page->prepare($last_page, $action);

                if (is_object($redirect_page) && $next_page != $redirect_page) {
                    $next_page = $redirect_page;
                } else {
                    break;
                }
            } while (--$max);

            $next_page->render();
        }

        /**
         * Return application state.
         *
         * @octdoc  m:app/getState
         * @return  \org\octris\core\app\state          State of application.
         */
        public function getState()
        /**/
        {
            return $this->state;
        }

        /**
         * Try to determine the last visited page supplied by the application state. If
         * last visited page can't be determined (eg.: when entering the application),
         * a new instance of the applications' entry page is created.
         *
         * @octdoc  m:app/getLastPage
         * @return  \org\octris\core\app\page           Returns instance of determined last visit page or instance of entry page.
         */
        protected function getLastPage()
        /**/
        {
            $class = (isset($this->state['last_page'])
                      ? $this->state['last_page']
                      : $this->entry_page);

            $page = new $class();

            return $page;
        }

        /**
         * Make a page the last visited page. This method is called internally by the 'process' method
         * before aquiring an other application page.
         *
         * @octdoc  m:app/setLastPage
         * @param   \org\octris\core\app\page       $page           Page object to set as last visited page.
         */
        protected function setLastPage(\org\octris\core\app\page $page)
        /**/
        {
            $class = get_class($page);

            $this->state['last_page'] = $class;
        }

        /**
         * Return context the application is running in.
         *
         * @octdoc  m:app/getContext
         * @return  int                                 Application context.
         */
        public static final function getContext()
        /**/
        {
            return static::$context;
        }

        /**
         * Returns path for specified path type for current application instance.
         *
         * @octdoc  m:app/getPath
         * @param   string          $type               The type of the path to return.
         * @param   string          $module             Optional name of module to return path for. Default is: current application name.
         * @param   string          $rel_path           Optional additional relative path to add.
         * @return  string                              Existing path or false, if path does not exist.
         */
        public static function getPath($type, $module = '', $rel_path = '')
        /**/
        {
            $env = provider::access('env');

            if ($type == self::T_PATH_HOME_ETC) {
                $info = posix_getpwuid(posix_getuid());
                $base = $info['dir'];
            } else {
                $base = $this->octris_base;
            }

            $return = sprintf(
                $type,
                $base,
                ($module
                    ? $module
                    : $this->octris_app
            ) . ($rel_path
                    ? '/' . $rel_path
                    : '');

            return realpath($return);
        }

        /**
         * Return application name.
         *
         * @octdoc  m:app/getAppName
         * @param   string          $module             Optional module name to extract application name from.
         * @return  string                              Determined application name.
         */
        public static function getAppName($module = '')
        /**/
        {
            if ($module == '') {
                $module = $this->octris_app;
            }

            return substr($module, strrpos($module, '.') + 1);
        }

        /**
         * Return instance of main application class.
         *
         * @octdoc  m:app/getInstance
         * @return  \org\octris\core\app                Instance of main application class.
         */
        public static function getInstance()
        /**/
        {
            if (is_null(self::$instance)) {
                self::$instance = new static();
            }

            return self::$instance;
        }
    }

    // register error handler for 'normal' php errors
    set_error_handler(function($code, $msg, $file, $line) {
        throw new \ErrorException($msg, $code, 0, $file, $line);
    }, E_ALL);
}

/*
 * Put translate function and other stuff into global namespace for convenience reasons.
 */
namespace {
    require_once('debug.class.php');

    /**
     * Global translate function.
     *
     * @octdoc  m:l10n/__
     * @param   string      $msg            Message to translate.
     * @param   array       $args           Optional additional arguments.
     * @param   string      $domain         Optional text domain.
     * @return  string                      Localized text.
     */
    function __($msg, array $args = array(), $domain = null)
    /**/
    {
        return \org\octris\core\l10n::getInstance()->translate($msg, $args, $domain);
    }
}
