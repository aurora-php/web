<?php

/*
 * This file is part of the 'octris/web' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Web\App\Web;

/**
 * Service base class.
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class Service
{
    /**
     * Application instance.
     *
     * @type    \Octris\Web\App\Web
     */
    protected $app;

    /**
     * Validation schema, to be configured by subclass.
     *
     * @type    array
     */
    protected $schema = array(
        'type'       => \Octris\Web\Validate::T_OBJECT,
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
    protected $method = \Octris\Web\App\Web\Request::METHOD_GET;

    /**
     * Constructor.
     *
     * @param   \Octris\Web\App\Web        $app        Application instance.
     */
    public function __construct(\Octris\Web\App\Web $app)
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
            $provider = \Octris\Web\Provider::access($this->method);

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
