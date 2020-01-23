<?php

/*
 * This file is part of the 'octris/web' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Web\App\Web\Page;

/**
 * Custom error pages.
 *
 * @copyright   copyright (c) 2015-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Error extends \Octris\Web\App\Web\Page
{
    /**
     * Status code.
     *
     * @type    int
     */
    protected $status_code;

    /**
     * Status text.
     *
     * @type    string
     */
    protected $status_text;

    /**
     * Instance of a logger.
     *
     * @type    \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * Constructor.
     *
     * @param   \Octris\Web\App                        Application instance.
     */
    public function __construct(\Octris\Web\App\Web $app)
    {
        parent::__construct($app);
    }

    /**
     * Configure a logger instance to log critical exception to.
     *
     * @param   \Psr\Log\LoggerInterface        $logger         Logger instance.
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Implements abstract prepare methof of parent class.
     *
     * @param   \Octris\Web\App\Web\Page       $last_page      Instance of last called page.
     * @param   string                          $action         Action that led to current page.
     * @return  mixed                                           Returns either page to redirect to or null.
     */
    public function prepare(\Octris\Web\App\Web\Page $last_page, $action)
    {
        $response = $this->app->getResponse();

        $this->status_code = $response->getStatusCode();
        $this->status_text = $response->getStatusText();
    }

    /**
     * Renders critical error page.
     */
    public function render()
    {
        $filename = 'error/' . $this->status_code . '.html';

        $tpl = $this->getTemplate();

        if ($tpl->templateExists($filename)) {
            $return = $tpl->fetch('error/' . $this->status . '.html');
        } else {
            $return = sprintf('<h1>%d -- %s</h1>', $this->status_code, $this->status_text);
        }

        return $return;
    }
}
