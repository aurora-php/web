<?php

namespace Octris\Web;

/*
 * This file is part of the 'octris/web' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Core web application class.
 *
 * @copyright   copyright (c) 2010-present by Harald Lapp
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
