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
 * Service base class.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class Service
{
    /**
     * Application instance.
     *
     * @type    \Octris\Core\App\Web
     */
    protected $app;

    /**
     * Validation schema, to be configured by subclass.
     *
     * @type    array
     */
    protected $schema = array(
        'type'       => \Octris\Core\Validate::T_OBJECT,
        'properties' => array()
    );

    /**
     * Allowed request method.
     *
     * @type    string
     */
    protected $method = \Octris\Core\App\Web\Request::METHOD_GET;

    /**
     * Constructor.
     *
     * @param   \Octris\Core\App\Web        $app        Application instance.
     */
    public function __construct(\Octris\Core\App\Web $app)
    {
        $this->app = $app;
    }

    /**
     * Validate.
     *
     * @return  array                                   Returns am array (is_valid, errors).
     */
    public function validate()
    {
        $errors = array();

        // check if request method is valid for service
        if (!($is_valid = ($this->method == $this->app->getRequest()->getRequestMethod()))) {
            $errors[] = 'Invalid request method';
        }

        if ($is_valid) {
            // check arguments
            $provider = \Octris\Core\Provider::access($this->method);

            list($is_valid, , $errors, $validator) = $provider->doValidate($this->schema);
        }

        return array($is_valid, $errors);
    }

    /**
     * Run service.
     */
    abstract public function run();
}
