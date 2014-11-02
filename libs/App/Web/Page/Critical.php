<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\App\Web\Page;

/**
 * Special page for handling critical errors.
 *
 * @copyright   copyright (c) 2011-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class Critical extends \Octris\Core\App\Web\Page
{
    /**
     * Template filename of page for rendering critical error information.
     *
     * @type    string
     */
    protected $template = 'critical.html';

    /**
     * Instance of a logger.
     *
     * @type    \octris\core\logger
     */
    private $logger = null;

    /**
     * Identifier to print on the webpage. The identifier may be send by a
     * user to the support. On the one hand it helps communicating between
     * user and support, on the other hand the identifier helps to locate
     * the error in the logging backend.
     *
     * @type    string
     */
    private $identifier = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Configure a logger instance to log critical exception to.
     *
     * @param   \Octris\Core\Logger     $logger         Logger instance.
     */
    public function setLogger(\Octris\Core\Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set exception to handle.
     *
     * @param   \Exception                  $exception      Exception to handle.
     * @param   array                       $data           Additional data to include in error report.
     */
    public function setException(\Exception $exception, array $data = array())
    {
        $this->identifier = base64_encode(uniqid(gethostname() . '.', true));

        throw $exception;

        if (!is_null($this->logger)) {
            try {
                $this->logger->log(
                    \Octris\Core\Logger::T_CRITICAL,
                    $exception,
                    array(
                        '_identifier' => $this->identifier
                    )
                );
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Implements abstract prepare methof of parent class.
     *
     * @param   \Octris\Core\App\Page       $last_page      Instance of last called page.
     * @param   string                          $action         Action that led to current page.
     * @return  mixed                                           Returns either page to redirect to or null.
     */
    public function prepare(\Octris\Core\App\Page $last_page, $action)
    {
    }

    /**
     * Renders critical error page.
     */
    public function render()
    {
        $tpl = $this->getTemplate();
        $tpl->setValue('identifier', $this->identifier);
        $tpl->render($this->template);
    }
}
