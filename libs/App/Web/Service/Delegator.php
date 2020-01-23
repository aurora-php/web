<?php

/*
 * This file is part of the 'octris/readline' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Web\App\Web\Service;

/**
 * Service delegator.
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Delegator implements \Octris\Web\App\Web\Router\ICallbackHandler
{
    /**
     * Service registry.
     *
     * @type    array
     */
    protected $services = array();

    /**
     * Whether to enable CSRF protection.
     *
     * @type    bool
     */
    protected $csrf_protection = true;

    /**
     * Accepted mime type.
     *
     * @type    string
     */
    protected $mime_type = 'application/json';

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Recreate state of class instance.
     *
     * @param   array                      $properties      Properties values.
     */
    public static function __set_state(array $properties)
    {
        $instance = new static();

        foreach ($properties as $name => $value) {
            $instance->{$name} = $value;
        }

        return $instance;
    }

    /**
     * Invoke service delegator.
     *
     * @param   \Octris\Web\App\Web        $app            Instance of application.
     */
    public function __invoke(\Octris\Web\App\Web $app)
    {
        $result = array(
            'error' => array(),
            'data' => null
        );

        $get = \Octris\Web\Provider::access('get');

        do {
            list($is_valid, $errors) = $this->validate($app);

            if (!$is_valid) {
                $result['error'] = $errors;
                break;
            }

            if (!($get->isExist('SERVICE') &&
                    $get->isValid('SERVICE', \Octris\Web\Validate::T_PATTERN, ['pattern' => '/^[a-zA-Z_][a-zA-Z0-9_]*$/']))) {
                $result['error'][] = 'Invalid service name or service name not provided!';
                break;
            }

            $service = $get->getValue('SERVICE');

            if (!isset($this->services[$service])) {
                $result['error'][] = sprintf('Unknown service "%s"!', $service);
                break;
            }

            $class = $this->services[$service];

            if (!(class_exists($class) && is_subclass_of($class, '\Octris\Web\App\Web\Service'))) {
                $result['error'][] = sprintf('Service implementation missing "%s"!', $class);
                break;
            }

            $instance = new $class($app);

            if ($instance->validate()) {
                $result['data'] = $instance->run();
            }

            $result['error'] = array_merge($result['error'], $instance->getErrors());
        } while (false);

        // return result to client
        $response = $app->getResponse();

        $response->headers->setHeader('Content-Type', $this->mime_type);
        $response->setContent($this->prepareData($result));
        $response->send();

        exit();
    }

    /**
     * Prepare result for output.
     *
     * @param   array                       $data           Data to prepare.
     * @return  string                                      Prepared data.
     */
    public function prepareData(array $data)
    {
        return json_encode($data);
    }

    /**
     * Generic validation of request to service class.
     *
     * @param   \Octris\Web\App\Web        $app            Instance of application.
     * @return  array                                       Array of (is_valid, errors).
     */
    protected function validate(\Octris\Web\App\Web $app)
    {
        $errors = array();
        $is_valid = true;

        do {
            // validate accept header
            $headers = $app->getRequest()->headers;
            $negotiator = new \Negotiation\Negotiator();

            if (!($is_valid = ($headers->hasHeader('accept') && !is_null($negotiator->getBest($headers->getHeader('accept'), array($this->mime_type)))))) {
                $errors[] = __('Invalid accept header!');
                break;
            }

            if ($this->csrf_protection) {
                // CSRF validation
                $state = $app->getState();

                if (!($is_valid = isset($state['__csrf_token']))) {
                    // CSRF token is not in state
                    $errors[] = __('CSRF token is not provided in application state!');
                    break;
                }

                $csrf = new \Octris\Web\App\Web\Csrf();

                if (!($is_valid = $csrf->verifyToken($state->pop('__csrf_token')))) {
                    $errors[] = __('Provided CSRF token is invalid!');
                    break;
                }
            }
        } while (false);

        return array($is_valid, $errors);
    }
}
