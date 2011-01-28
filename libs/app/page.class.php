<?php

namespace org\octris\core\app {
    use \org\octris\core\app as app;
    
    /**
     * Core page controller class.
     *
     * @octdoc      c:app/page
     * @copyright   copyright (c) 2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    abstract class page
    /**/
    {
        /**
         * Next valid actions and their view pages.
         *
         * @octdoc  v:page/$next_pages
         * @var     array
         */
        protected $next_pages = array();
        /**/

        /**
         * Stored error Messages occured during execution of the current page.
         *
         * @octdoc  v:page/$errors
         * @var     array
         */
        protected $errors = array();
        /**/

        /**
         * Stored notification messages collected during execution of the current page.
         *
         * @octdoc  v:page/$messages
         * @var     array
         */
        protected $messages = array();
        /**/
        
        /**
         * Constructor.
         *
         * @octdoc  m:page/__construct
         */
        public function __construct()
        /**/
        {
        }

        /**
         * Added magic getter to provide readonly access to protected properties.
         *
         * @octdoc  m:page/__get
         * @param   string          $name                   Name of property to return.
         * @return  mixed                                   Value of property.
         */
        public function __get($name)
        /**/
        {
            return (isset($this->{$name}) ? $this->{$name} : null);
        }

        /**
         * Returns name of page class if page instance is casted to a string.
         *
         * @octdoc  m:page/__toString
         * @param   string                                  Returns name of class.
         */
        public final function __toString()
        /**/
        {
            return get_called_class($this);
        }

        /**
         * Abstract method definition.
         *
         * @octdoc  m:page/prepare
         * @param   \org\octris\core\app\page       $last_page      Instance of last called page.
         * @param   string                          $action         Action that led to current page.
         * @return  mixed                                           Returns either page to redirect to or null.
         * @abstract   
         */
        abstract public function prepare(\org\octris\core\app\page $last_page, $action);
        /**/

        /**
         * Abstract method definition
         *
         * @octdoc  m:page/render
         * @abstract   
         */
        abstract public function render();
        /**/

        /**
         * Apply validation ruleset.
         *
         * @octdoc  m:page/validate
         * @param   string                          $action         Action to select ruleset for.
         * @return  bool                                            Returns true if validation suceeded, otherwise false.
         */
        public function validate($action)
        /**/
        {
            // return validate::getInstance()->validate($this, $action);
        }

        /**
         * Gets next page from action and next_pages array of last page
         *
         * @octdoc  m:page/getNextPage
         * @param   string                          $action         Action to get next page for.
         * @param   string                          $entry_page     Name of the entry page for possible fallback.
         * @return  \org\octris\core\app\page                       Next page.
         */
        public function getNextPage($action, $entry_page)
        /**/
        {
            $next = $this;

            if (count($this->errors) == 0) {
                if (isset($this->next_pages[$action])) {
                    // lookup next page from current page's next_page array
                    $class = $this->next_pages[$action];
                    $next  = new $class();
                } else {
                    // lookup next page from entry page's next_page array
                    $entry = new $entry_page();

                    if (isset($entry->next_pages[$action])) {
                        $class = $entry->next_pages[$action];
                        $next  = new $class();
                    }
                }
            }

            return $next;
        }

        /**
         * Add error message for current page.
         *
         * @octdoc  m:page/addError
         * @param   string          $err                        Error message to add.
         */
        public function addError($err)
        /**/
        {
            $this->errors[] = $err;
        }

        /**
         * Add message for current page.
         *
         * @octdoc  m:page/addMessage
         * @param   string          $msg                        Message to add.
         */
        public function addMessage($msg)
        /**/
        {
            $this->messages[] = $msg;
        }
    }
}
