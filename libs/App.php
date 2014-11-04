<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core {

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
         * Used to abstract application context types.
         */
        const T_CONTEXT_UNDEFINED = 0;
        const T_CONTEXT_CLI       = 1;
        const T_CONTEXT_WEB       = 2;
        const T_CONTEXT_TEST      = 3;

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
         */
        public function __construct()
        {
        }

        /**
         * Abstract method definition. Initialize must be implemented by any subclass.
         *
         * @abstract
         */
        abstract protected function initialize();

        /**
         * Abstract method definition. Process must be implemented by any subclass.
         *
         * @abstract
         */
        abstract public function process();

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
         * @return  \Octris\Core\App\Page           Returns instance of determined last visit page or instance of entry page.
         */
        protected function getLastPage()
        {
            $class = (isset($this->state['__last_page'])
                      ? $this->state['__last_page']
                      : $this->entry_page);

            $page = new $class($this->app);

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
    }

}

namespace {

    require_once(__DIR__ . '/Debug.php');
    require_once(__DIR__ . '/Error.php');

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
