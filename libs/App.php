<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core;

use \Octris\Core\Registry as registry;

/**
 * Core application class.
 *
 * @copyright   copyright (c) 2010-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class App
{
    /**
     * Used in combination with app/getPath to determine path.
     *
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
    
    /**
     * Used to abstract application context types.
     *
     */
    const T_CONTEXT_UNDEFINED = 0;
    const T_CONTEXT_CLI       = 1;
    const T_CONTEXT_WEB       = 2;
    const T_CONTEXT_TEST      = 3;
    
    /**
     * Application instance.
     *
     * @type    \octris\core\app
     */
    private static $instance = null;
    
    /**
     * Application state.
     *
     * @type    \octris\core\app\state
     */
    protected $state = null;
    
    /**
     * Entry page to use if no other page is loaded. To be overwritten by applications' main class.
     *
     * @type    string
     */
    protected $entry_page = '';
    
    /**
     * Constructor is protected to force creation of instance using 'getInstance' method.
     *
     */
    protected function __construct()
    {
    }

    /**
     * Abstract method definition. Initialize must be implemented by any subclass.
     *
     * @abstract
     */
    protected function initialize()
    {
    }

    /**
     * Abstract method definition. Process must be implemented by any subclass.
     *
     * @abstract
     */
    abstract public function process();
    
    /**
     * Invoke the page of an application without using the process workflow.
     *
     * @param   \Octris\Core\App\Page       $next_page          Application page to invoke.
     * @param   string                          $action             Optional action to invoke page with.
     */
    public function invoke(\Octris\Core\App\Page $next_page, $action = '')
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
     * @return  \octris\core\app\state          State of application.
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
     * @return  \octris\core\app\page           Returns instance of determined last visit page or instance of entry page.
     */
    protected function getLastPage()
    {
        $class = (isset($this->state['__last_page'])
                  ? $this->state['__last_page']
                  : $this->entry_page);

        $page = new $class();

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
     * Returns path for specified path type for current application instance.
     *
     * @param   string          $type               The type of the path to return.
     * @param   string          $module             Optional name of module to return path for. Default is: current application name.
     * @param   string          $rel_path           Optional additional relative path to add.
     * @return  string                              Existing path or false, if path does not exist.
     */
    public static function getPath($type, $module = '', $rel_path = '')
    {
        $reg = registry::getInstance();

        if ($type == self::T_PATH_HOME_ETC) {
            $info = posix_getpwuid(posix_getuid());
            $base = $info['dir'];
        } else {
            $base = $reg->OCTRIS_BASE;
        }

        $return = sprintf(
            $type,
            $base,
            ($module
                ? $module
                : $reg->OCTRIS_APP)
        ) . ($rel_path
                ? '/' . $rel_path
                : '');

        return realpath($return);
    }

    /**
     * Return application name.
     *
     * @param   string          $module             Optional module name to extract application name from.
     * @return  string                              Determined application name.
     */
    public static function getAppName($module = '')
    {
        if ($module == '') {
            $module = registry::getInstance()->OCTRIS_APP;
        }

        return substr($module, strrpos($module, '.') + 1);
    }

    /**
     * Return instance of main application class.
     *
     * @return  \octris\core\app                Instance of main application class.
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}

}

namespace {

require_once(__DIR__ . '/debug.php');
require_once(__DIR__ . '/error.php');

/**
 * Global translate function.
 *
 * @param   string      $msg            Message to translate.
 * @param   array       $args           Optional additional arguments.
 * @param   string      $domain         Optional text domain.
 * @return  string                      Localized text.
 */
function __($msg, array $args = array(), $domain = null)
{
    return \Octris\Core\L10n::getInstance()->translate($msg, $args, $domain);
}

}
