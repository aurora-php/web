<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\App\Web\Session;

/**
 * Interface for implementing routers.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
interface IRouter
{
    /**
     * Initiate routing.
     *
     * @param   \Octris\Core\App\Web        $app            Instance of application.
     */
    public function route(\Octris\Core\App\Web $app);
}
