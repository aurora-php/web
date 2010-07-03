<?php

namespace org\octris\core\app {
    use \org\octris\core\config as config;
    
    /****c* app/state
     * NAME
     *      state
     * FUNCTION
     *      Methods to freeze and thaw objects. The state class is used to transfer page/action specific data between two 
     *      or more request, which can't be stored in a session because:
     *
     *      * they are not generalized, eg.: transfering search parameters between
     *        requests - you need to be sure that you can handle multiple searches
     *        without overwriting the parameters which will be the case, if the 
     *        parameters are stored in the session
     *
     *      * the browser-back button is required to work and the data is (slightly)
     *        changed between each request.
     * COPYRIGHT
     *      copyright (c) 2005-2010 by Harald Lapp
     * AUTHOR
     *      Harald Lapp <harald@octris.org>
     ****
     */
     
    class state extends \org\octris\core\type\collection {
        /****d* state/hash_algo
         * SYNOPSIS
         */
        const hash_algo = 'sha256';
        /*
         * FUNCTION
         *      hash algorithm to use to generate checksum.
         ****
         */
    
        /****m* state/push
         * SYNOPSIS
         */
        function push($name, $value)
        /*
         * FUNCTION
         *      push value into state
         * INPUTS
         *      * $name (string) -- name of state variable to set value for
         *      * $value (mixed) -- value for state variable
         ****
         */
        {
            parent::offsetSet($name, $value);
        }

        /****m* state/pop
         * SYNOPSIS
         */
        function pop($name) 
        /*
         * FUNCTION
         *      returns and delete value from state
         * INPUTS
         *      * $name (string) -- name of variable to fetch from state
         * OUTPUTS
         *      (mixed) -- data object to fetch
         ****
         */
        {
            $return = parent::offsetGet($name);
            parent::offsetUnset($name);
            
            return $return;
        }
    
        /****m* state/freeze
         * SYNOPSIS
         */
        function freeze($secret = null) 
        /*
         * FUNCTION
         *      freeze object
         * INPUTS
         *      * $secret (string) -- (optional) secret to use for state freezing. if not supplied, use secret from config file.
         * OUTPUTS
         *      (string) -- serialized and encoded object + base64 obfuscated md5 checksum ;-)
         ****
         */
        {
            $frozen = gzcompress(serialize($this)); 
            $secret = (!is_null($secret) ? $secret : config::get('common.state.secret'));
            $sum    = hash(self::hash_algo, $frozen . $secret);
            $return = base64_encode($sum . '|' . $frozen);
        
            return $return;
        }

        /****m* state/thaw
         * SYNOPSIS
         */
        static function thaw($state, $secret = null)
        /*
         * FUNCTION
         *      thaw object
         * INPUTS
         *      * $state (string) -- serialized object with included md5 checksum
         *      * $secret (string) -- (optional) secret to use for state thawing. if not supplied, use secret from config file.
         * OUTPUTS
         *      (object) -- on success returns unserialized object instance
         ****
         */
        {
            $debug = $state;
            $state = base64_decode($state);

            $pos    = (int)strpos($state, '|');
            $sum    = substr($state, 0, $pos);
            $frozen = substr($state, $pos + 1);
            $secret = (!is_null($secret) ? $secret : config::get('common.state.secret'));

            if (hash(self::hash_algo, $frozen . $secret) != $sum) {
                // error
                if (config::get('common.application.development')) {
                    // debug output only on development server
                    print "$sum != " . hash(self::hash_algo, $frozen . $secret) . "<br />";
                    print $debug . "<br />";
                    print_r(unserialize(gzuncompress($frozen)));
                }

                throw new \Exception('hack attempt - checksum does not match!');
            } else {
                return new static(unserialize(gzuncompress($frozen)));
            }
        }
    }
}
