<?php

/*
 * This file is part of the 'org.octris.core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\octris\core\app\web\session {
    /**
     * Interface for implementing session handlers.
     *
     * @octdoc      i:session/handler_if
     * @copyright   copyright (c) 2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    interface handler_if
    /**/
    {
        /**
         * Open session.
         *
         * @octdoc  m:handler_if/open
         * @param   string          $path               Session starage path.
         * @param   string          $name               Session name.
         */
        public function open($path, $name);
        /**/

        /**
         * Close session.
         *
         * @octdoc  m:handler_if/close
         */
        public function close();
        /**/

        /**
         * Read session.
         *
         * @octdoc  m:handler_if/read
         * @param   string      $id                     Id of session to read.
         */
        public function read($id);
        /**/

        /**
         * Write session.
         *
         * @octdoc  m:handler_if/write
         * @param   string      $id                     Id of session to write.
         * @param   array       $data                   Session data to write.
         */
        public function write($id, array $data);
        /**/

        /**
         * Destroy session.
         *
         * @octdoc  m:handler_if/destroy
         * @param   string      $id                     Id of session to destroy.
         */
        public function destroy($id);
        /**/

        /**
         * Garbage collect a session.
         *
         * @octdoc  m:handler_if/gc
         * @param   int         $lifetime               Maximum lifetime of session.
         */
        public function gc($lifetime);
        /**/
    }
}
