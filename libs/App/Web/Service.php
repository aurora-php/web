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
     * Stored errors.
     *
     * @param   array
     */
    protected $errors = array();

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
     * Add an error for service.
     *
     * @param   string                      $msg        Error message to add.
     */
    protected function addError($msg)
    {
        $this->errors[] = $msg;
    }

    /**
     * Add multiple errors for service.
     *
     * @param   array           $err                        Array of error messages.
     */
    protected function addErrors(array $err)
    {
        $this->errors = array_merge($this->errors, $err);
    }

    /**
     * Return error messages stored for service.
     *
     * @return  array                                   Stored error messages.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Validate.
     *
     * @return  bool                                    Returns true if validation suceeded, otherwise false.
     */
    public function validate()
    {
        // check if request method is valid for service
        if (!($is_valid = ($this->method == $this->app->getRequest()->getRequestMethod()))) {
            $this->addError('Invalid request method');
        }

        if ($is_valid) {
            // check arguments
            $provider = \Octris\Core\Provider::access($this->method);

            list($is_valid, , $errors, ) = $provider->doValidate($this->schema);

            if (!$is_valid) {
                $this->addErrors($errors);
            }
        }

        return $is_valid;
    }

    /**
     * Run service.
     */
    abstract public function run();
}
