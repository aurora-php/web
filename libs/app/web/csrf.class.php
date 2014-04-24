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
     * Class provides functionality for handling CSRF (cross-site request forgery) tokens.
     *
     * @octdoc      c:web/csrf
     * @copyright   copyright (c) 2014 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    class csrf
    /**/
    {
        /**
         * Instance of a random bytes generator.
         *
         * @octdoc  p:csrf/$random
         * @type    null|\org\octris\core\security\random_if
         */
        protected static $random = null;
        /**/
        
        /**
         * Server-side storage for CSRF tokens.
         *
         * @octdoc  p:csrf/$storage
         * @type    null|\org\octris\core\app\web\csrf\storage_if
         */
        protected static $storage = null;
        /**/
        
        /**
         * Entropy for generating random bytes for CSRF token.
         *
         * @octdoc  p:csrf/$entropy
         * @type    int
         */
        protected $entropy;
        /**/
        
        /**
         * Constructor.
         *
         * @octdoc  m:csrf/__construct
         * @param   int             $entryp             Entropy for generating random bytes for CSRF token.
         */
        public function __construct($entropy = 256)
        /**/
        {
            $this->entropy = $entropy;
        }
        
        /**
         * Set random number generator / provider.
         *
         * @octdoc  m:csrf/setRandomProvider
         * @param   \org\octris\core\security\random_if     $random             Instance of random number generator.
         */
        public static function setRandomProvider(\org\octris\core\security\random_if $random)
        /**/
        {
            self::$random = $random;
        }
        
        /**
         * Set server-side storage for generated CSRF token.
         *
         * @octdoc  m:csrf/setStorage
         * @param   \org\octris\core\app\web\csrf\storage_if    $storage        Instance of CSRF token storage.
         */
        public static function setStorage(\org\octris\core\app\web\csrf\storage_if $storage)
        /**/
        {
            self::$storage = $storage;
        }
        
        /**
         * Create a CSRF token and put it into CSRF token storage.
         *
         * @octdoc  m:csrf/createToken
         * @param   string                      $scope                          Optional parameter to limit the scope of the token.
         * @return  string                                                      Created CSRF token.
         */
        public function createToken($scope = '')
        /**/
        {
            $token = self::$random->getRandom($this->entropy / 8);
            
            self::$storage->addToken($token, $scope);
            
            return $token;
        }
        
        /**
         * Check if a token exists in storage and remove it from storage.
         *
         * @octdoc  m:csrf/verifyToken
         * @param   string                      $token                          CSRF token to verify.
         * @param   string                      $scope                          Optional scope of the token, this parameter must be provided, if the token was added using a scope.
         * @return  bool                                                        Return true if verification was successful.
         */
        public function verifyToken($token, $scope = '')
        /**/
        {
            if (($is_valid = self::$storage->hasToken($token, $scope))) {
                self::$storage->removeToken($token, $scope);
            }
            
            return $is_valid;
        }
    }
}
