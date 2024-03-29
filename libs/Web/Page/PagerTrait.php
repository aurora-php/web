<?php

/*
 * This file is part of the 'octris/web' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Web\Page;

/**
 * Implements functionality to generate pagers.
 *
 * @copyright   copyright (c) 2011-present by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
trait PagerTrait
{
    /**
     * Current page number.
     *
     * @type    int
     */
    protected $page = 1;

    /**
     * Total number of "items" the pager should create pages for.
     *
     * @type    int
     */
    protected $total_items = 0;

    /**
     * Number of "items" the application should display per page.
     *
     * @type    int
     */
    protected $items_per_page = 20;

    /**
     * Total number of pages.
     *
     * @type    int
     */
    protected $total_pages = 1;

    /**
     * Number of "positions" the the pager contains.
     *
     * @type    int
     */
    protected $pager_positions = 9;

    /**
     * Where to insert a filler, if the number of pages are more than the pager has
     * positions to show buttons for them.
     *
     * @type    int
     */
    protected $filler_position = 2;

    /**
     * Character to use as filler.
     *
     * @type    string
     */
    protected $filler_char = '&#8230;';

    /**
     * Set the total items of the page.
     *
     * @param   int         $total              Total items of the page.
     */
    public function setTotalItems($total)
    {
        $this->total_items = $total;
    }

    /**
     * Return the number of the current page.
     *
     * @return  int                             Number of current page.
     */
    public function getPage()
    {
        static $page = null;

        if (is_null($page)) {
            // either import or use default on first call
            $method  = $this->app->getRequest()->getRequestMethod();

            $request = \Octris\Web\Provider::access(strtolower($method));

            if ($request->isExist('page')) {
                $page = $request->getValue('page', validate::T_DIGIT);
                $this->page = $page;
            } else {
                $page = $this->page;
            }
        }

        return $page;
    }

    /**
     * Return number of items to show per page.
     *
     * @return  int                             Number of items per page.
     */
    public function getItemsPerPage()
    {
        static $ipp = null;

        if (is_null($ipp)) {
            // either import or use default on first call
            $method  = $this->app->getRequest()->getRequestMethod();

            $request = \Octris\Web\Provider::access(strtolower($method));

            if ($request->isExist('ipp')) {
                $ipp = $request->getValue('ipp', validate::T_DIGIT);
                $this->items_per_page;
            } else {
                $ipp = $this->items_per_page;
            }
        }

        return $ipp;
    }

    /**
     * Create and return all necessary information for rendering a navigation pager.
     *
     * @return  array                           Array of pager data.
     */
    public function gePager()
    {
        $page = $this->getPage();
        $ipp  = $this->getItemsPerPage();

        $this->total_pages = ceil($this->total_items / $ipp);

        $pages = array();

        if ($this->total_pages <= $this->pager_positions) {
            for ($i = 1; $i <= $this->total_pages; ++$i) {
                $pages[$i - 1] = $i;
            }
        } else {
            for ($i = 1; $i <= $this->pager_positions; ++$i) {
                if ($page < ceil($this->pager_positions / 2)) {
                    if ($i == $this->pager_positions - 1) {
                        $pages[$i - 1] = $this->filler_char;
                    } elseif ($i == $this->pager_positions) {
                        $pages[$i - 1] = $this->total_pages;
                    } else {
                        $pages[$i - 1] = $i;
                    }
                } elseif ($page > ($this->total_pages - ($this->pager_positions / 2)) + 2) {
                    if ($i == 1) {
                        $pages[$i - 1] = '1';
                    } elseif ($i == $this->filler_position) {
                        $pages[$i - 1] = $this->filler_char;
                    } else {
                        $pages[$i - 1] = ($this->total_pages - $this->pager_positions + $i);
                    }
                } else {
                    if ($i == 1) {
                        $pages[$i - 1] = '1';
                    } elseif ($i == $this->pager_positions) {
                        $pages[$i - 1] = $this->total_pages;
                    } elseif ($i == $this->filler_position || $i == $this->pager_positions - 1) {
                        $pages[$i - 1] = $this->filler_char;
                    } else {
                        $pages[$i - 1] = ceil($page - $this->pager_positions / 2 + $i - 1);
                    }
                }
            }
        }

        $offset = (($page - 1) * $ipp) + 1;

        return array(
            'pages'         => $pages,
            'page'          => $page,
            'total_pages'   => $this->total_pages,
            'total_items'   => $this->total_items,
            'items'         => $ipp,
            'is_first_page' => ($page == 1),
            'is_last_page'  => ($page >= $this->total_pages),
            'prev_page'     => $page - 1,
            'next_page'     => $page + 1,
            'offset'        => $offset,
            'offset_end'    => $offset + ($ipp - 1)
        );
    }
}
