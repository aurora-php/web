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
 * Headers storage class.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Headers implements \IteratorAggregate, \Countable {
    /**
     * Header storage.
     *
     * @type    array
     */
    protected $headers = array();

    /**
     * Constructor.
     *
     * @param   array               $headers                Optional headers to store.
     */
    public function __construct(array $headers = array())
    {
        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }
    }

    /**
     * Set a header.
     *
     * @param   string              $name                   Name of header to set.
     * @param   string|string[]     $values                 A single value or multiple values.
     * @param   bool                $replace                Optional wether to replace are append to existing headers.
     */
    public function setHeader($name, $values, $replace = true)
    {
        $name = strtolower($name);
        $values = array_values((array)$values);

        if ($replace || !isset($this->headers[$name])) {
            $this->headers[$name] = $values;
        } else {
            $this->headers[$name] = array_merge($this->headers[$name], $values);
        }
    }

    /**
     * Return a header value. Returns null, if header does not exist.
     *
     * @param   string                  $name                   Name of header to return.
     * @param   bool                    $first                  Optional whether to return first occurence only.
     * @return  string|string[]|null                            Header value(s).
     */
    public function getHeader($name, $first = true)
    {
        $name = strtolower($name);

        return (isset($this->headers[$name])
                ? ($first
                    ? $this->headers[$name][0]
                    : $this->headers[$name])
                : null);
    }

    /**
     * Test if a header is available.
     *
     * @param   string                  $name                   Name of header to test.
     * @return  bool                                            Returns true, if a header is available.
     */
    public function hasHeader($name)
    {
        $name = strtolower($name);

        return (isset($this->headers[$name]));
    }

    /**
     * Returns instance of ArrayIterator for headers.
     *
     * @return  \ArrayIterator                                  Instance of ArrayIterator.
     */
    public function getIterator()
    {
        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                yield $name => $value;
            }
        }
    }

    /**
     * Returns number of stored headers.
     *
     * @return  int                                             Number of stored headers.
     */
    public function count()
    {
        return count($this->headers);
    }
}
