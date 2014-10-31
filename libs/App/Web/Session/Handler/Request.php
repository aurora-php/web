<?php

/*
 * This request is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * request that was distributed with this source code.
 */

namespace Octris\Core\App\Web\Session\Handler;

/**
 * The request session handler is the default session handler, which is set initially,
 * when session library is included. This session handler stores session data only
 * during the current request. That means, that session data is not persistent, instead
 * every request starts with an empty session data storage.
 *
 * @copyright   copyright (c) 2011 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Request implements \Octris\Core\App\Web\Session\Handler_if
{
    /**
     * Open session.
     *
     * @param   string          $path               Session starage path.
     * @param   string          $name               Session name.
     */
    public function open($path, $name)
    {
        return true;
    }

    /**
     * Close session.
     *
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session.
     *
     * @param   string      $id                     Id of session to read.
     */
    public function read($id)
    {
        return array();
    }

    /**
     * Write session.
     *
     * @param   string      $id                     Id of session to write.
     * @param   array       $data                   Session data to write.
     */
    public function write($id, array $data)
    {
        return true;
    }

    /**
     * Destroy session.
     *
     * @param   string      $id                     Id of session to destroy.
     */
    public function destroy($id)
    {
        return true;
    }

    /**
     * Garbage collect a session.
     *
     * @param   int         $lifetime               Maximum lifetime of session.
     */
    public function gc($lifetime)
    {
        return true;
    }
}
