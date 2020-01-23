<?php

/*
 * This file is part of the 'octris/web' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Web;

/**
 * The state class is used to transfer page/action specific data between two
 * or more requests. The state is essencial for transfering for example the
 * last visited page to determine the next valid page. It can also be used
 * to transfer additional abitrary data for example search query parameters,
 * parameters that should not be visible and or not be modified by a user
 * between two requests. The state helps to bring stateful requests to a web
 * application, too.
 *
 * @copyright   copyright (c) 2011-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class State extends \Octris\Web\Type\Collection
{
    /**
     * Hash algorithm to use to generate the checksum of the state.
     */
    const HASH_ALGO = 'sha256';

    /**
     * Secret to use for generating hash and prevent the state from manipulation.
     *
     * @type    string
     */
    protected static $secret = '';

    /**
     * Set global state secret.
     *
     * @param   string          $secret             Secret for securing state.
     */
    public static function setSecret($secret)
    {
        self::$secret = $secret;
    }

    /**
     * Magic setter.
     *
     * @param   string          $name               Name of state variable to set value for.
     * @param   mixed           $value              Value for state variable.
     */
    public function __set($name, $value)
    {
        parent::offsetSet($name, $value);
    }

    /**
     * Magic getter.
     *
     * @param   string          $name               Name of state variable to return value of.
     * @return  mixed                               Value stored in state variable.
     */
    public function __get($name)
    {
        return parent::offsetGet($name);
    }

    /**
     * Return value of a stored state variable and remove the variable from the state.
     *
     * @param   string          $name               Name of state variable to return value of and remove.
     * @return  mixed                               Value stored in state variable.
     */
    public function pop($name)
    {
        $return = parent::offsetGet($name);

        parent::offsetUnset($name);

        return $return;
    }

    /**
     * Freeze state object.
     *
     * @param   array           $data               Optional data to inject into state before freezing. Note that
     *                                              the original
     *                                              state will not be modified, only the frozen state contains
     *                                              the specified data.
     * @return  string                              Serialized and base64 for URLs encoded object secured by a hash.
     */
    public function freeze(array $data = array())
    {
        $tmp = array_merge($this->getArrayCopy(), $data);

        $frozen = gzcompress(serialize($tmp));
        $sum    = hash(self::HASH_ALGO, $frozen . self::$secret);
        $return = \Octris\Web\Request::base64UrlEncode($sum . '|' . $frozen);

        return $return;
    }

    /**
     * Validate frozen state object.
     *
     * @param   string          $state              Frozen state to validate.
     * @param   string          $decoded            Returns array with checksum and compressed state, ready to thaw.
     * @return  bool                                Returns true if state is valid, otherwise returns false.
     */
    public static function validate($state, array &$decoded = null)
    {
        $tmp    = \Octris\Web\Request::base64UrlDecode($state);
        $sum    = '';
        $frozen = '';

        if (($pos = strpos($tmp, '|')) !== false) {
            $sum    = substr($tmp, 0, $pos);
            $frozen = substr($tmp, $pos + 1);

            unset($tmp);

            $decoded = array(
                'checksum'  => $sum,
                'state'     => $frozen
            );
        }

        return (($test = hash(self::HASH_ALGO, $frozen . self::$secret)) != $sum);
    }

    /**
     * Thaw frozen state object.
     *
     * @param   string          $state              State to thaw.
     * @return  \Octris\Web\State          Instance of state object.
     */
    public static function thaw($state)
    {
        $frozen = array();

        if (self::validate($state, $frozen)) {
            // hash did not match
            throw new \Exception(sprintf('[%s !=  %s | %s]', $test, $frozen['checksum'], $frozen['state']));
        } else {
            return new static(unserialize(gzuncompress($frozen['state'])));
        }
    }
}
