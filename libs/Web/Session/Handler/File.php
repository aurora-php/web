<?php

/*
 * This file is part of the 'octris/web' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Web\Session\Handler;

/**
 * Session handler for storing sesion data in files.
 *
 * @copyright   copyright (c) 2011-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class File implements \Octris\Web\Session\HandlerInterface
{
    /**
     * Stores the path the session files are stored in.
     *
     * @type    string
     */
    protected $session_path;

    /**
     * Constructor.
     *
     * @param   array           $options            Options for session handler.
     */
    public function __construct(array $options = array())
    {
        $this->session_path = rtrim((isset($options['session_path'])
                                    ? $options['session_path']
                                    : session_save_path()), '/');

        if (!is_dir($this->session_path) || !is_writable($this->session_path)) {
            throw new \Exception(sprintf('Session path "%s/" is not writeable', $this->session_path));
        }
    }

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
     * @param   string      $id                     Id of session to write.
     * @param   array       $data                   Session data to write.
     */
    public function write($id, array $data)
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
     * @param   string      $id                     Id of session to destroy.
     */
    public function destroy($id)
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
     * @param   int         $lifetime               Maximum lifetime of session.
     */
    public function gc($lifetime)
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
