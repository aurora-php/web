<?php

/*
 * This file is part of the 'org.octris.core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\octris\core\app\web\csrf {
    /**
     * Interface for CSRF token storages classes.
     *
     * @octdoc      c:csrf/storage_if
     * @copyright   copyright (c) 2014 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    interface storage_if
    /**/
    {
        /**
         * Add a CSRF token to session storage.
         *
         * @octdoc  m:storage_if/addToken
         * @param   string                      $token              CSRF token to add.
         * @param   string                      $scope              Scope of the token.
         */
        public function addToken($token, $scope);
        /**/

        /**
         * Test whether a CSRF token exists in session storage.
         *
         * @octdoc  m:storage_if/hasToken
         * @param   string                      $token              CSRF token to test.
         * @param   string                      $scope              Scope of the token.
         * @return  bool                                            Returns true if token exists or false if it does not exist.
         */
        public function hasToken($token, $scope);
        /**/

        /**
         * Remove a token from session storage.
         *
         * @octdoc  m:storage_if/removeToken
         * @param   string                      $token              CSRF token to remove.    
         * @param   string                      $scope              Scope of the token.
         */
        public function removeToken($token, $scope);
        /**/
    }
}
