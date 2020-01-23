<?php

/*
 * This file is part of the 'octris/web' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Web\Router;

/**
 * Interface for implementing routing callback handlers.
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
interface CallbackHandlerInterface
{
    /**
     * Recreate state of class instance.
     *
     * @param   array                               $properties      Properties values.
     */
    public static function __set_state(array $properties);

    /**
     * Make class instance a callable.
     *
     * @param   \Octris\Web                $app            Instance of application.
     * @return  \Octris\Web\Page|null                      Allowed return values.
     */
    public function __invoke(\Octris\Web $app);
}
