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
         * @octdoc  v:session/$instance
         * @var     \org\octris\core\app\web\session
         */
        private static $instance = null;
        /**/

        /**
         * Instance of session handler.
         *
         * @octdoc  v:session/$handler
         * @var     \org\octris\core\app\web\session\handler
         */
        private static $handler = null;
        /**/

        /**
         * Options configured through 'setHandler'.
         *
         * @octdoc  v:session/$options
         * @var     array
         */
        private static $options = array();
        /**/

        /**
         * Session lifetime. See php.ini: session.gc_maxlifetime.
         *
         * @octdoc  v:session/$lifetime
         * @var     int
         */
        protected $lifetime = 0;
        /**/

        /**
         * The domain, the session is valid for.
         *
         * @octdoc  v:session/$domain
         * @var     string
         */
        protected $domain = '';
        /**/

        /**
         * Session name. See php.ini: session.name.
         *
         * @octdoc  v:session/$name
         * @var     string
         */
        protected $name = '';
        /**/

        /**
         * Stores Id of current session.
         *
         * @octdoc  v:session/$id
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

            $this->lifetime = (int)(array_key_exists(self::$options['lifetime'])
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
         * Set session handler. Note, that this method can only be called as long as the session was not
         * been instantiated using 'getInstance' method.
         *
         * @octdoc  m:session/setHandler
         * @param   \org\octris\core\app\web\session\handler    $handler        Instance of session handler.
         * @param   array                                       $options        Optional options overwrite settings from php.ini.
         */
        public static function setHandler(\org\octris\core\app\web\session\handler $handler, array $options = array())
        /**/
        {
            if (!is_null(self::$instance)) {
                throw new \Exception('Unable to reconfigure session handler after session was instantiated');
            }

            session_set_save_handler(
                array($handler, 'open'),
                array($handler, 'close'),
                array($handler, 'readData'),
                array($handler, 'writeData'),
                array($handler, 'destroy'),
                array($handler, 'gc')
            );

            $handler->initialize();

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
         * Return instance of session handler backend. Note, that this function will throw an exception,
         * if the method 'setHandler' never has been called before.
         *
         * @octdoc  m:session/getInstance
         */
        public static function getInstance()
        /**/
        {
            if (is_null(self::$handler)) {
                throw new Exception('There is no session handler configured');
            } elseif (is_null(self::$instance)) {
                self::$instance = new static();
            }

            return self::$instance;
        }

        /**
         * Store a value in session.
         *
         * @octdoc  m:session/setValue
         * @param   string          $name               Name of property to set.
         * @param   mixed           $value              Value to store in session.
         */
        public function setValue($name, $value)
        /**/
        {
            self::$handler->setValue($name, $value);
        }

        /**
         * Return a value stored in session.
         *
         * @octdoc  m:session/getValue
         * @param   string          $name               Name of property to return value of.
         */
        public function getValue($name)
        /**/
        {
            return self::$handler->getValue($name);
        }

        /**
         * Test if a stored property exists.
         *
         * @octdoc  m:session/isExist
         * @param   string          $name               Name of property to test.
         */
        public function isExist($name)
        /**/
        {
            return self::$handler->isExist($name);
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

            $cookie    = provider::access('cookie');
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
}
