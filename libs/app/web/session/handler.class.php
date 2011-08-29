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
     * Session handler base class.
     *
     * @octdoc      c:session/handler
     * @copyright   copyright (c) 2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    abstract class handler
    /**/
    {
        /**
         * Session data.
         *
         * @octdoc  v:handler/$data
         * @var     array
         */
        protected $data = array();
        /**/

        /**
         * Constructor.
         *
         * @octdoc  m:handler/__construct
         */
        public function __construct()
        /**/
        {
        }

        /*
         * prevent cloning
         */
        private function __clone() {}

        /**
         * Store a value in session.
         *
         * @octdoc  m:handler/setValue
         * @param   string          $name               Name of property to set.
         * @param   mixed           $value              Value to store in session.
         */
        public function setValue($name, $value)
        /**/
        {
            $this->data[$name] = $value;
        }

        /**
         * Return a value stored in session.
         *
         * @octdoc  m:handler/getValue
         * @param   string          $name               Name of property to return value of.
         */
        public function getValue($name)
        /**/
        {
            return $this->data[$name];
        }

        /**
         * Test if a stored property exists.
         *
         * @octdoc  m:handler/isExist
         * @param   string          $name               Name of property to test.
         */
        public function isExist($name)
        /**/
        {
            return (array_key_exists($this->data[$name]));
        }

        /**
         * Handle reading session data and storing it into internal data array, so that the
         * '$_SESSION' superglobal can be ommited.
         *
         * @octdoc  m:handler/readData
         * @param   string      $id                     Id of session to read.
         */
        public final function readData($id)
        /**/
        {
            $this->data = $this->read($id);
        }

        /**
         * Handle writing session data from internal data array, so that the '$_SESSION' superglobal
         * can be ommited.
         *
         * @octdoc  m:handler/writeData
         * @param   string      $id                     Id of session to write.
         * @param   array       $data                   Session data to write.
         */
        public final function writeData($id, array $data)
        /**/
        {
            $this->write($id, $this->data);
        }

        /**
         * Open session.
         *
         * @octdoc  m:handler/open
         * @param   string          $path               Session starage path.
         * @param   string          $name               Session name.
         * @abstract
         */
        public abstract function open($path, $name);
        /**/

        /**
         * Close session.
         *
         * @octdoc  m:handler/close
         * @abstract
         */
        public abstract function close();
        /**/

        /**
         * Read session.
         *
         * @octdoc  m:handler/read
         * @param   string      $id                     Id of session to read.
         * @abstract
         */
        public abstract function read($id);
        /**/

        /**
         * Write session.
         *
         * @octdoc  m:handler/write
         * @param   string      $id                     Id of session to write.
         * @param   array       $data                   Session data to write.
         * @abstract
         */
        public abstract function write($id, array $data);
        /**/

        /**
         * Destroy session.
         *
         * @octdoc  m:handler/destroy
         * @param   string      $id                     Id of session to destroy.
         * @abstract
         */
        public abstract function destroy($id);
        /**/

        /**
         * Garbage collect a session.
         *
         * @octdoc  m:handler/gc
         * @param   int         $lifetime               Maximum lifetime of session.
         * @abstract
         */
        public abstract function gc($lifetime);
        /**/
    }
}
