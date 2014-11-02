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
 * Class provides functionality for handling CSRF (cross-site request forgery) tokens.
 *
 * @copyright   copyright (c) 2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Csrf
{
    /**
     * Instance of a random bytes generator.
     *
     * @type    null|\Octris\Core\Security\IRandom
     */
    protected static $random = null;

    /**
     * Server-side storage for CSRF tokens.
     *
     * @type    null|\Octris\Core\App\Web\Csrf\IStorage
     */
    protected static $storage = null;

    /**
     * Entropy for generating random bytes for CSRF token.
     *
     * @type    int
     */
    protected $entropy;

    /**
     * Constructor.
     *
     * @param   int             $entryp             Entropy for generating random bytes for CSRF token.
     */
    public function __construct($entropy = 256)
    {
        $this->entropy = $entropy;
    }

    /**
     * Set random number generator / provider.
     *
     * @param   \Octris\Core\Security\IRandom     $random             Instance of random number generator.
     */
    public static function setRandomProvider(\Octris\Core\Security\IRandom $random)
    {
        self::$random = $random;
    }

    /**
     * Set server-side storage for generated CSRF token.
     *
     * @param   \Octris\Core\App\Web\Csrf\IStorage    $storage        Instance of CSRF token storage.
     */
    public static function setStorage(\Octris\Core\App\Web\Csrf\IStorage $storage)
    {
        self::$storage = $storage;
    }

    /**
     * Create a CSRF token and put it into CSRF token storage.
     *
     * @param   string                      $scope                          Optional parameter to limit the scope of the token.
     * @return  string                                                      Created CSRF token.
     */
    public function createToken($scope = '')
    {
        $token = self::$random->getRandom($this->entropy / 8);

        self::$storage->addToken($token, $scope);

        return $token;
    }

    /**
     * Check if a token exists in storage and remove it from storage.
     *
     * @param   string                      $token                          CSRF token to verify.
     * @param   string                      $scope                          Optional scope of the token, this parameter must be provided, if the token was added using a scope.
     * @return  bool                                                        Return true if verification was successful.
     */
    public function verifyToken($token, $scope = '')
    {
        if (($is_valid = self::$storage->hasToken($token, $scope))) {
            self::$storage->removeToken($token, $scope);
        }

        return $is_valid;
    }
}
