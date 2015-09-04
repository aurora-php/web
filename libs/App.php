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
         * Constructor.
         */
        public function __construct()
        {
        }

        /**
         * Initialize must be implemented by any subclass.
         *
         * @abstract
         */
        abstract protected function initialize();

        /**
         * Run must be implemented by any subclass.
         *
         * @abstract
         */
        abstract public function run();
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
