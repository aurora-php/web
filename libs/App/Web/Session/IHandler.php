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
 * Interface for implementing session handlers.
 *
 * @copyright   copyright (c) 2011-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
interface IHandler
{
    /**
     * Open session.
     *
     * @param   string          $path               Session starage path.
     * @param   string          $name               Session name.
     */
    public function open($path, $name);

    /**
     * Close session.
     */
    public function close();

    /**
     * Read session.
     *
     * @param   string      $id                     Id of session to read.
     */
    public function read($id);

    /**
     * Write session.
     *
     * @param   string      $id                     Id of session to write.
     * @param   array       $data                   Session data to write.
     */
    public function write($id, array $data);

    /**
     * Destroy session.
     *
     * @param   string      $id                     Id of session to destroy.
     */
    public function destroy($id);

    /**
     * Garbage collect a session.
     *
     * @param   int         $lifetime               Maximum lifetime of session.
     */
    public function gc($lifetime);
    /**/
}
