<?php

/*
 * This file is part of the 'octris/web' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Web\App\Web;

/**
 * Interface for implementing routers.
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
interface RouterInterface
{
    /**
     * Initiate routing.
     *
     * @param   \Octris\Web\App\Web        $app            Instance of application.
     */
    public function route(\Octris\Web\App\Web $app);
}
