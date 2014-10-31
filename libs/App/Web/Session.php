<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\App\Web;

/**
 * Session base class.
 *
 * @copyright   copyright (c) 2011-2013 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Session
{
    /**
     * Instance of session class.
     *
     * @type    \octris\core\app\web\session
     */
    private static $instance = null;
    
    /**
     * Instance of session handler.
     *
     * @type    \octris\core\app\web\session\handler
     */
    private static $handler = null;
    
    /**
     * Options configured through 'setHandler'.
     *
     * @type    array
     */
    private static $options = array();
    
    /**
     * Session data.
     *
     * @type    array
     */
    private static $data = array();
    
    /**
     * Session lifetime. See php.ini: session.gc_maxlifetime.
     *
     * @type    int
     */
    protected $lifetime = 0;
    
    /**
     * The domain, the session is valid for.
     *
     * @type    string
     */
    protected $domain = '';
    
    /**
     * Session name. See php.ini: session.name.
     *
     * @type    string
     */
    protected $name = '';
    
    /**
     * Stores Id of current session.
     *
     * @type    string
     */
    protected $id = '';
    
    /**
     * Constructor.
     *
     */
    protected function __construct()
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

        if (isset(self::$options['save_path'])) {
            session_save_path(self::$options['save_path']);
        }
    }

    /*
     * prevent cloning
     */
    protected function __clone() {}

    /**
     * Destructor.
     *
     */
    public function __destruct()
    {
        session_write_close();
    }

    /**
     * Set session handler.
     *
     * @param   \Octris\Core\App\Web\Session\Handler_if     $handler        Instance of session handler.
     * @param   array                                           $options        Optional options overwrite settings from php.ini.
     */
    public static function setHandler(\Octris\Core\App\Web\Session\Handler_if $handler, array $options = array())
    {
        session_set_save_handler(
            array($handler, 'open'),
            array($handler, 'close'),
            function ($id) use ($handler) {
                self::$data = $handler->read($id);
            },
            function ($id, $_data) use ($handler) {
                $handler->write($id, self::$data);
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
     * @return  \octris\core\app\web\session\handler_if                 Session handler of session class instance.
     */
    public static function getHandler()
    {
        return self::$handler;
    }

    /**
     * Return instance of session handler backend.
     *
     * @return  \octris\core\app\web\session                            Session class instance.
     */
    public static function getInstance()
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
     * @param   string          $name               Name of property to set.
     * @param   mixed           $value              Value to store in session.
     * @param   string          $namespace          Optional namespace.
     */
    public function setValue($name, $value, $namespace = 'default')
    {
        if (!isset(self::$data[$namespace])) self::$data[$namespace] = array();

        self::$data[$namespace][$name] = $value;
    }

    /**
     * Return a value stored in session.
     *
     * @param   string          $name               Name of property to return value of.
     * @param   string          $namespace          Optional namespace.
     */
    public function getValue($name, $namespace = 'default')
    {
        return self::$data[$namespace][$name];
    }

    /**
     * Unset a value stored in session.
     *
     * @param   string          $name               Name of property to unset.
     * @param   string          $namespace          Optional namespace.
     */
    public function unsetValue($name, $namespace = 'default')
    {
        unset(self::$data[$namespace][$name]);
    }

    /**
     * Test if a stored property exists.
     *
     * @param   string          $name               Name of property to test.
     * @param   string          $namespace          Optional namespace.
     */
    public function isExist($name, $namespace = 'default')
    {
        return (isset(self::$data[$namespace]) && array_key_exists($name, self::$data[$namespace]));
    }

    /**
     * Return current session Id.
     *
     * @return  string                              Current session Id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return domain the session is valid for.
     *
     * @return  string                              Domain name the session is valid for.
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Return name of session, which is either configured by 'php.ini' or by the options property
     * specified at the method 'setHandler'.
     *
     * @return  string                              Name of the session.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return session lifetime, which is either configured by 'php.ini' or by the options property
     * specified at the method 'setHandler'.
     *
     * @return  int                                 Session lifetime.
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Start or continue a session.
     *
     */
    public function start()
    {
        session_name($this->name);

        $cookie    = \Octris\Core\Provider::access('cookie');
        $cookie_id = ($cookie->isExist($this->name) && $cookie->isValid($this->name, \Octris\Core\Validate::T_PRINTABLE)
                        ? $cookie->getValue($this->name)
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
     */
    public function regenerate()
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
session::setHandler(new \Octris\Core\App\Web\Session\Handler\Request());
