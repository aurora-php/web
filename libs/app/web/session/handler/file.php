<?php

/*
 * This file is part of the 'org.octris.core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\octris\core\app\web\session\handler {
    /**
     * Session handler for storing sesion data in files.
     *
     * @octdoc      c:handler/file
     * @copyright   copyright (c) 2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    class file implements \org\octris\core\app\web\session\handler_if
    /**/
    {
        /**
         * Stores the path the session files are stored in.
         *
         * @octdoc  p:file/$session_path
         * @type    string
         */
        protected $session_path;
        /**/

        /**
         * Open session.
         *
         * @octdoc  m:file/open
         * @param   string          $path               Session starage path.
         * @param   string          $name               Session name.
         */
        public function open($path, $name)
        /**/
        {
            $this->session_path = rtrim($path, '/');

            if (!is_dir($this->session_path) || !is_writable($this->session_path)) {
                throw new \Exception(sprintf('Session path "%s/" is not writeable', $this->session_path));
            }

            return true;
        }

        /**
         * Close session.
         *
         * @octdoc  m:file/close
         */
        public function close()
        /**/
        {
            return true;
        }

        /**
         * Read session.
         *
         * @octdoc  m:file/read
         * @param   string      $id                     Id of session to read.
         */
        public function read($id)
        /**/
        {
            $file   = $this->session_path . '/sess_' . $id;
            $return = array();

            if (is_file($file) && is_readable($file)) {
                if (($tmp = unserialize(file_get_contents($file))) !== false) {
                    $return = $tmp;
                }
            }

            return $return;
        }

        /**
         * Write session.
         *
         * @octdoc  m:file/write
         * @param   string      $id                     Id of session to write.
         * @param   array       $data                   Session data to write.
         */
        public function write($id, array $data)
        /**/
        {
            $return = false;

            if (is_dir($this->session_path) && is_writable($this->session_path)) {
                $file = $this->session_path . '/sess_' . $id;

                if (!file_exists($file) || is_writable($file)) {
                    file_put_contents($file, serialize($data));

                    $return = true;
                }
            }

            return $return;
        }

        /**
         * Destroy session.
         *
         * @octdoc  m:file/destroy
         * @param   string      $id                     Id of session to destroy.
         */
        public function destroy($id)
        /**/
        {
            $file   = $this->session_path . '/sess_' . $id;
            $return = false;

            if (is_file($file) && is_writable($file)) {
                $return = unlink($file);
            }

            return $return;
        }

        /**
         * Garbage collect a session.
         *
         * @octdoc  m:file/gc
         * @param   int         $lifetime               Maximum lifetime of session.
         */
        public function gc($lifetime)
        /**/
        {
            if (is_dir($this->session_path)) {
                $file = $this->session_path . '/sess_*';
                $time = time();

                foreach (glob($file) as $filename) {
                    if (filemtime($filename) + $lifetime < $time) {
                        unlink($filename);
                    }
                }
            }

            return true;
        }
    }
}
