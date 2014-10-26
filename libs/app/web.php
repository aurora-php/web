<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace octris\core\app;

use \octris\core\app\web\request as request;
use \octris\core\validate as validate;
use \octris\core\provider as provider;

/**
 * Core class for Web applications.
 *
 * @octdoc      c:app/web
 * @copyright   copyright (c) 2011-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
abstract class web extends \octris\core\app
{
    /**
     * Initialization of web application.
     *
     * @octdoc  m:web/initialize
     */
    protected function initialize()
    {
        $request = provider::access('request');

        if ($request->isExist('state') && $request->isValid('state', validate::T_BASE64)) {
            $this->state = state::thaw($request->getValue('state', validate::T_BASE64));
        }

        if (!is_object($this->state)) {
            $this->state = new state();
        }
    }

    /**
     * Main application processor. This is the only method that needs to be called to
     * invoke an application. Internally this method determines the last visited page
     * and handles everything required to determine the next page to display.
     *
     * The following example shows how to invoke an application, assuming that 'test'
     * implements an application based on \octris\core\app.
     *
     * <code>
     * $app = test::getInstance();
     * $app->process();
     * </code>
     *
     * @octdoc  m:web/process
     */
    public function process()
    {
        ob_start();

        // perform initialization
        $this->initialize();

        // page flow control
        $last_page = $this->getLastPage();
        $action    = $last_page->getAction();
        // $module = self::getModule();

        $last_page->validate($action);

        $next_page = $last_page->getNextPage($action, $this->entry_page);

        $max = 3;

        do {
            $redirect_page = $next_page->prepare($last_page, $action);

            if (is_object($redirect_page) && $next_page != $redirect_page) {
                $next_page = $redirect_page;
            } else {
                break;
            }
        } while (--$max);

        // fix security context
        $secure = $next_page->isSecure();

        if ($secure != request::isSSL() && request::getRequestMethod() == 'GET') {
            $this->redirectHttp(($secure ? request::getSSLUrl() : request::getNonSSLUrl()));
            exit;
        }

        // process with page
        $this->setLastPage($next_page);

        // $next_page->sendHeaders($this->headers);
        $next_page->render();

        header('Content-Type: text/html; charset="UTF-8"');

        ob_end_flush();
    }

    /**
     * Adds header to output when rendering web site.
     *
     * @octdoc  m:web/addHeader
     * @param   string          $name               Name of header to add.
     * @param   string          $value              Value to set for header.
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Create new instance of template engine and setup common stuff needed for templates of a web application.
     *
     * @octdoc  m:web/getTemplate
     * @return  \octris\core\tpl                Instance of template class.
     */
    public function getTemplate()
    {
        $path_cache = \octris\core\app::getPath(\octris\core\app::T_PATH_CACHE);
        $path_host  = \octris\core\app::getPath(\octris\core\app::T_PATH_HOST);
        $path_work  = \octris\core\app::getPath(\octris\core\app::T_PATH_WORK);

        $tpl = new \octris\core\tpl();

        // setup template engine environment
        $tpl->setL10n(\octris\core\l10n::getInstance());
        $tpl->setOutputPath('tpl', $path_cache . '/templates_c/');
        $tpl->setOutputPath('css', $path_host . '/styles/');
        $tpl->setOutputPath('js',  $path_host . '/libsjs/');
        $tpl->setResourcePath('css', $path_work);
        $tpl->setResourcePath('js',  $path_work);
        $tpl->addSearchPath(\octris\core\app::getPath(\octris\core\app::T_PATH_WORK_TPL));

        // register common template methods
        $tpl->registerMethod('getState', function (array $data = array()) {
            return $this->getState()->freeze($data);
        }, array('min' => 0, 'max' => 1));
        $tpl->registerMethod('isAuthenticated', function () {
            return \octris\core\auth::getInstance()->isAuthenticated();
        }, array('min' => 0, 'max' => 0));

        return $tpl;
    }
}

provider::set('server',  $_SERVER,  provider::T_READONLY);
provider::set('env',     $_ENV,     provider::T_READONLY);
provider::set('request', $_REQUEST, provider::T_READONLY);
provider::set('post',    $_POST,    provider::T_READONLY);
provider::set('get',     $_GET,     provider::T_READONLY);
provider::set('cookie',  $_COOKIE,  provider::T_READONLY);
provider::set('files',   $_FILES,   provider::T_READONLY);
