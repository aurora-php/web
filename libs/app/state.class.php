<?php

namespace org\octris\core\app {
    /**
     * The state class is used to transfer page/action specific data between two or more requests. The state is essencial for
     * transfering for example the last visited page to determine the next valid page. It can also be used to transfer additional
     * abitrary data for example search query parameters, parameters that should not be visible and or not be modified by a user
     * between two requests. The state helps to bring stateful requests to a web application, too.
     *
     * @octdoc      c:app/state
     * @copyright   copyright (c) 2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    class state extends \org\octris\core\type\collection
    /**/
    {
        /**
         * Hash algorithm to use to generate the checksum of the state.
         * 
         * @octdoc  d:state/hash_algo
         */
        const hash_algo = 'sha256';
        /**/

        /**
         * Magic setter.
         *
         * @octdoc  m:state/__set
         * @param   string          $name               Name of state variable to set value for.
         * @param   mixed           $value              Value for state variable.
         */
        public function __set($name, $value)
        /**/
        {
            parent::offsetSet($name, $value);
        }
        
        /**
         * Magic getter.
         *
         * @octdoc  m:state/__get
         * @param   string          $name               Name of state variable to return value of.
         * @return  mixed                               Value stored in state variable.
         */
        public function __get($name)
        /**/
        {
            return parent::offsetGet($name);
        }
        
        /**
         * Return value of a stored state variable and remove the variable from the state.
         *
         * @octdoc  m:state/pop
         * @param   string          $name               Name of state variable to return value of and remove.
         * @return  mixed                               Value stored in state variable.
         */
        public function pop($name)
        /**/
        {
            $return = parent::offsetGet($name);
            
            parent::offsetUnset($name);
            
            return $return;
        }

        /**
         * Freeze state object.
         *
         * @octdoc  m:state/freeze
         * @param   string          $secret             Secret to use for generating hash and prevent the state from manipulation.
         * @return  string                              Serialized and base64 encoded object secured by a hash.
         */
        public function freeze($secret = '')
        /**/
        {
            $frozen = gzcompress(serialize($this)); 
            $sum    = hash(self::hash_algo, $frozen . $secret);
            $return = base64_encode($sum . '|' . $frozen);
        
            return $return;
        }

        /**
         * Thaw frozen state object.
         *
         * @octdoc  m:state/thaw
         * @param   string          $state              State to thaw.
         * @param   string          $secret             Optional secret to use for generating hash to test if state is valid.
         * @return  \org\octris\core\app\state          Instance of state object.
         */
        public static function thaw($state, $secret = '')
        /**/
        {
            $decoded = base64_decode($state);
            $sum     = '';
            $frozen  = '';

            if (($pos = strpos($decoded, '|')) !== false) {
                $sum    = substr($decoded, 0, $pos);
                $frozen = substr($decoded, $pos + 1);
                
                unset($decoded);
            }

            if (($test = hash(self::hash_algo, $frozen . $secret)) != $sum) {
                // hash did not match
                throw new \Exception(sprintf('[%s !=  %s | %s]', $test, $sum, $state));
            } else {
                return new static(unserialize(gzuncompress($frozen)));
            }
        }
        
    }
}
