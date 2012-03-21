<?php

/*
 * This file is part of the 'org.octris.core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\octris\core\app\web {
    /**
     * Session base class.
     *
     * @octdoc      c:web/session
     * @copyright   copyright (c) 2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    class session
    /**/
    {
        /**
         * Instance of session class.
         *
         * @octdoc  p:session/$instance
         * @var     \org\octris\core\app\web\session
         */
        private static $instance = null;
        /**/

        /**
         * Instance of session handler.
         *
         * @octdoc  p:session/$handler
         * @var     \org\octris\core\app\web\session\handler
         */
        private static $handler = null;
        /**/

        /**
         * Options configured through 'setHandler'.
         *
         * @octdoc  p:session/$options
         * @var     array
         */
        private static $options = array();
        /**/

        /**
         * Session data.
         *
         * @octdoc  p:session/$data
         * @var     array
         */
        private static $data = array();
        /**/

        /**
         * Session lifetime. See php.ini: session.gc_maxlifetime.
         *
         * @octdoc  p:session/$lifetime
         * @var     int
         */
        protected $lifetime = 0;
        /**/

        /**
         * The domain, the session is valid for.
         *
         * @octdoc  p:session/$domain
         * @var     string
         */
        protected $domain = '';
        /**/

        /**
         * Session name. See php.ini: session.name.
         *
         * @octdoc  p:session/$name
         * @var     string
         */
        protected $name = '';
        /**/

        /**
         * Stores Id of current session.
         *
         * @octdoc  p:session/$id
         * @var     string
         */
        protected $id = '';
        /**/

        /**
         * Constructor.
         *
         * @octdoc  m:session/__construct
         */
        protected function __construct()
        /**/
        {
            $this->name     = (isset(self::$options['name'])
                                ? self::$options['name']
                                : ini_get('session.name'));


            $this->domain   = (isset(self::$options['domain'])
                                ? self::$options['domain']
                                : null);

            $this->lifetime = (int)(array_key_exists('lifetime', self::$options)
                                ? self::$options['lifetime']
                                : ini_get('session.gc_maxlifetime'));
        }

        /*
         * prevent cloning
         */
        protected function __clone() {}

        /**
         * Destructor.
         *
         * @octdoc  m:session/__destruct
         */
        public function __destruct()
        /**/
        {
            session_write_close();
        }

        /**
         * Set session handler.
         *
         * @octdoc  m:session/setHandler
         * @param   \org\octris\core\app\web\session\handler_if     $handler        Instance of session handler.
         * @param   array                                           $options        Optional options overwrite settings from php.ini.
         */
        public static function setHandler(\org\octris\core\app\web\session\handler_if $handler, array $options = array())
        /**/
        {
            $data =& self::$data;

            session_set_save_handler(
                array($handler, 'open'),
                array($handler, 'close'),
                function($id) use ($handler, &$data) {
                    $data = $handler->read($id);
                },
                function($id, $_data) use ($handler, &$data) {
                    $handler->write($id, $data);
                },
                array($handler, 'destroy'),
                array($handler, 'gc')
            );

            self::$handler = $handler;
            self::$options = $options;
        }

        /**
         * Return session handler instance.
         *
         * @octdoc  m:session/getHandler
         * @return  \org\octris\core\app\web\session\handler
         */
        public static function getHandler()
        /**/
        {
            return self::$handler;
        }

        /**
         * Return instance of session handler backend.
         *
         * @octdoc  m:session/getInstance
         */
        public static function getInstance()
        /**/
        {
            if (is_null(self::$instance)) {
                self::$instance = new static();
                self::$instance->start();
            }

            return self::$instance;
        }

        /**
         * Store a value in session.
         *
         * @octdoc  m:session/setValue
         * @param   string          $name               Name of property to set.
         * @param   mixed           $value              Value to store in session.
         * @param   string          $namespace          Optional namespace.
         */
        public function setValue($name, $value, $namespace = 'default')
        /**/
        {
            if (!isset(self::$data[$namespace])) self::$data[$namespace] = array();

            self::$data[$namespace][$name] = $value;
        }

        /**
         * Return a value stored in session.
         *
         * @octdoc  m:session/getValue
         * @param   string          $name               Name of property to return value of.
         * @param   string          $namespace          Optional namespace.
         */
        public function getValue($name, $namespace = 'default')
        /**/
        {
            return self::$data[$namespace][$name];
        }

        /**
         * Unset a value stored in session.
         *
         * @octdoc  m:session/unsetValue
         * @param   string          $name               Name of property to unset.
         * @param   string          $namespace          Optional namespace.
         */
        public function unsetValue($name, $namespace = 'default')
        /**/
        {
            unset(self::$data[$namespace][$name]);
        }

        /**
         * Test if a stored property exists.
         *
         * @octdoc  m:session/isExist
         * @param   string          $name               Name of property to test.
         * @param   string          $namespace          Optional namespace.
         */
        public function isExist($name, $namespace = 'default')
        /**/
        {
            return (isset(self::$data[$namespace]) && array_key_exists($name, self::$data[$namespace]));
        }

        /**
         * Return current session Id.
         *
         * @octdoc  m:session/getId
         * @return  string
         */
        public function getId()
        /**/
        {
            return $this->id;
        }

        /**
         * Return domain the session is valid for.
         *
         * @octdoc  m:session/getDomain
         */
        public function getDomain()
        /**/
        {
            return $this->domain;
        }

        /**
         * Return name of session, which is either configured by 'php.ini' or by the options property
         * specified at the method 'setHandler'.
         *
         * @octdoc  m:session/getName
         */
        public function getName()
        /**/
        {
            return $this->name;
        }

        /**
         * Return session lifetime, which is either configured by 'php.ini' or by the options property
         * specified at the method 'setHandler'.
         *
         * @octdoc  m:session/getLifetime
         */
        public function getLifetime()
        /**/
        {
            return $this->lifetime;
        }

        /**
         * Start or continue a session.
         *
         * @octdoc  m:session/start
         */
        public function start()
        /**/
        {
            session_name($this->name);

            $cookie    = \org\octris\core\provider::access('cookie');
            $cookie_id = ($cookie->isExist($this->name)
                            ? $cookie->getValue($this->name, \org\octris\core\validate::T_PRINTABLE)
                            : false);

            if ($cookie_id !== false) {
                session_id($cookie_id);
            }

            session_set_cookie_params(
                $this->lifetime,
                '/',
                $this->domain,
                false,
                true
            );

            session_start();

            $this->id = session_id();

            unset($_SESSION);   // the octris-framework does _not_ use super-globals!
        }

        /**
         * Regenerate the session. This method should be called after each login and logout
         * and should prevent session fixation.
         *
         * @octdoc  m:session/regenerate
         */
        public function regenerate()
        /**/
        {
            session_name($this->name);

            session_set_cookie_params(
                $this->lifetime,
                '/',
                $this->domain,
                false,
                true
            );

            session_regenerate_id(true);

            $this->id = session_id();
        }
    }

    // set default session handler
    session::setHandler(new \org\octris\core\app\web\session\handler\request());
}
