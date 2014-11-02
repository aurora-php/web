<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\App\Web\Csrf\Storage;

/**
 * Storage handler for storing CSRF tokens into session.
 *
 * @copyright   copyright (c) 2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Session implements \Octris\Core\App\Web\Csrf\IStorage
{
    /**
     * Instance of session class.
     *
     * @type    \octris\core\app\web\session
     */
    protected $session;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->session = \Octris\Core\App\Web\Session::getInstance();
    }

    /**
     * Add a CSRF token to session storage.
     *
     * @param   string                      $token              CSRF token to add.
     * @param   string                      $scope              Scope of the token.
     */
    public function addToken($token, $scope)
    {
        $this->session->setValue($token . ':' . $scope, microtime(true), __CLASS__);
    }

    /**
     * Test whether a CSRF token exists in session storage.
     *
     * @param   string                      $token              CSRF token to test.
     * @param   string                      $scope              Scope of the token.
     * @return  bool                                            Returns true if token exists or false if it does not exist.
     */
    public function hasToken($token, $scope)
    {
        return $this->session->isExist($token . ':' . $scope, __CLASS__);
    }

    /**
     * Remove a token from session storage.
     *
     * @param   string                      $token              CSRF token to remove.
     * @param   string                      $scope              Scope of the token.
     */
    public function removeToken($token, $scope)
    {
        $this->session->unsetValue($token . ':' . $scope, __CLASS__);
    }
}
