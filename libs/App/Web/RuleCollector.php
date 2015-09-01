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
 * Collector for routing and rewriting rules.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class RuleCollector extends \FastRoute\RouteCollector
{
    /**
     * Adds a rewrite rule to the collection.
     *
     * @param   string|string[]   $httpMethod         Method(s) of route.
     * @param   string            $route              Route.
     */
    public function addRewrite($httpMethod, $route)
    {
        $this->addRoute($httpMethod, $route, null);
    }

    /**
     * Adds a route to the collection.
     *
     * @param   string|string[]   $httpMethod         Method(s) of route.
     * @param   string            $route              Route.
     * @param   string|callable   $handler            Either a page name or a callable.
     */
    public function addRoute($httpMethod, $route, $handler) {
        parent::addRoute($httpMethod, $route, $handler);
    }
}
