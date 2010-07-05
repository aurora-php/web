<?php

namespace org\octris\core\app {
    /****c* app/page
     * NAME
     *      page
     * FUNCTION
     *      page controller of the MVC framework
     * COPYRight
     *      copyright (c) 2006-2010 by Harald Lapp
     * AUTHOR
     *      Harald Lapp <harald@octris.org>
     ****
     */

    abstract class page {
        /****v* page/$next_pages
         * SYNOPSIS
         */
        protected $next_pages = array(
        );
        /*
         * FUNCTION
         *      next valid actions -> pages for current page
         ****
         */

        /****v* page/$errors
         * SYNOPSIS
         */
        protected $errors = array();
        /*
         * FUNCTION
         *      stored error messages occured during execution of the current page
         ****
         */

        /****v* page/$messages
         * SYNOPSIS
         */
        protected $messages = array();
        /*
         * FUNCTION
         *      stored messages collected during execution of the current page
         ****
         */

        /****v* page/$name
         * SYNOPSIS
         */
        protected $name = '';
        /*
         * FUNCTION
         *      stores name of page
         ****
         */

        /****m* page/$secure
         * SYNOPSIS
         */
        protected $secure = false;
        /*
         * FUNCTION
         *      whether the page should be delivered through https or not
         ****
         */

        /****m* page/__construct
         * SYNOPSIS
         */
        function __construct()
        /*
         * FUNCTION
         *      constructor
         * INPUTS
         *      * $app (object) -- application object
         ****
         */
        {
        }

        /****m* page/__toString
         * SYNOPSIS
         */
        function __toString()
        /*
         * FUNCTION
         *      magic method __toString returns name of page class
         ****
         */
        {
            return get_class($this);
        }

        /****m* page/isSecure
         * SYNOPSIS
         */
        final function isSecure()
        /*
         * FUNCTION
         *      return, whether page has to be secured
         ****
         */
        {
            return $this->secure;
        }

        /****m* page/render, prepareRender
         * SYNOPSIS
         */
        abstract function render(lima_app $app);
        abstract function prepareRender(lima_app $app, lima_page $last_page, $action);
        /*
         * FUNCTION
         *      abstract methods must be defined in the application page classes
         ****
         */

        /****m* page/validate
         * SYNOPSIS
         */
        function validate(lima_app $app, $action)
        /*
         * FUNCTION
         *      apply a validation ruleset
         * INPUTS
         *      * $app (object) -- application object
         *      * $action (string) -- action
         * OUTPUTS
         *      (bool) -- returns false, if validation failed, otherwise true
         ****
         */
        {
            return \org\octris\core\validate::getInstance()->validate($this, $action);
        }

        /****m* page/getAction
         * SYNOPSIS
         */
        function getAction()
        /*
         * FUNCTION
         *      determine the action the page called with
         * OUTPUTS
         *      (string) -- name of action
         ****
         */
        {
            static $action = '';

            if ($action != '') {
                return $action;
            }

            $method = strtoupper($_SERVER->import('REQUEST_METHOD', new lima_validate_alpha())->value);

            if ($method == 'POST' || $method == 'GET') {
                $method = ($method == 'POST' ? $_POST : $_GET);
                $tmp    = $method->import('ACTION', new lima_validate_alphanum());
                $action = ($tmp->isValid ? $tmp->value : $action);
            }

            if ($action == '') {
                $arr = $_REQUEST->getIterator();

                while ($arr->valid()) {
                    if (preg_match('/^ACTION_([a-zA-Z]+)$/', $arr->key(), $m)) {
                        $action = $m[1];

                        return $action;
                    }

                    $arr->next();
                }

                $path_info = $_SERVER->import('PATH_INFO', new lima_validate_print());

                if ($path_info->isValid) {
                    $path = explode('/', substr($path_info->value, 1));

                    if ($action = trim(array_shift($path))) {
                        return $action;
                    }
                }
            }

            if ($action == '') {
                $action = 'default';
            }

            return $action;
        }

        /****m* page/getNextPage
         * SYNOPSIS
         */
        function getNextPage(lima_app $app, $entry_page)
        /*
         * FUNCTION
         *      get's next page from action and next_pages array of last page
         * INPUTS
         *      * $app (object) -- application object
         *      * $entry_page (string) -- name of the entry page for possible fallback
         * OUTPUTS
         *      (object) -- instance of next page
         ****
         */
        {
            $next = $this;

            if (count($this->errors) <= 0) {
                $action = $this->getAction();

                if (is_array($this->next_pages) && isset($this->next_pages[$action])) {
                    // lookup next page from current page's next_page array
                    $class = $this->next_pages[$action];
                    $next  = new $class($app);
                } else {
                    // lookup next page from entry page's next_page array
                    $entry = new $entry_page($app);

                    if (is_array($entry->next_pages) && isset($entry->next_pages[$action])) {
                        $class = $entry->next_pages[$action];
                        $next  = new $class($app);
                    }
                }
            }

            return $next;
        }

        /****m* page/getValidationRuleset
         * SYNOPSIS
         */
        function getValidationRuleset($action)
        /*
         * FUNCTION
         *      returns a validation ruleset for specified action
         * INPUTS
         *      * $action (string) -- name of action to return ruleset for
         * OUTPUTS
         *      (mixed) -- array of rules for specified action, returns false, if no ruleset is specified for action
         ****
         */
        {
            $return = false;

            if (isset($this->validate[$action])) {
                $return = $this->validate[$action];
            }

            return $return;
        }

        /****m* page/addError
         * SYNOPSIS
         */
        function addError($err)
        /*
         * FUNCTION
         *      add error message for current page
         * INPUTS
         *      * $err (string) -- error message
         ****
         */
        {
            $this->errors[] = $err;
        }

        /****m* page/addMessage
         * SYNOPSIS
         */
        function addMessage($msg)
        /*
         * FUNCTION
         *      add message for current page
         * INPUTS
         *      * $msg (string) -- message
         ****
         */
        {
            $this->messages[] = $msg;
        }

        /****m* page/countErrors
         * SYNOPSIS
         */
        function countErrors()
        /*
         * FUNCTION
         *      return number of errors for current page
         ****
         */
        {
            return count($this->errors);
        }

        /****m* page/countMessages
         * SYNOPSIS
         */
        function countMessages()
        /*
         * FUNCTION
         *      return number of messages for current page
         ****
         */
        {
            return count($this->messages);
        }

        /****m* page/getErrors
         * SYNOPSIS
         */
        function getErrors()
        /*
         * FUNCTION
         *      return all errors
         ****
         */
        {
            return $this->errors;
        }

        /****m* page/getMessages
         * SYNOPSIS
         */
        function getMessages()
        /*
         * FUNCTION
         *      return all messages
         ****
         */
        {
            return $this->messages;
        }

        /****m* page/addErrors
         * SYNOPSIS
         */
        function addErrors(array $errors)
        /*
         * FUNCTION
         *      method to add multiple errors for page
         * INPUTS
         *      * $errors (array) -- array of error messages
         ****
         */
        {
            $this->errors = array_merge($this->errors, $errors);
        }

        /****m* page/addMessages
         * SYNOPSIS
         */
        function addMessages(array $messages)
        /*
         * FUNCTION
         *      method to add multiple messages for page
         * INPUTS
         *      * $messages (array) -- array of messages 
         ****
         */
        {
            $this->messages = array_merge($this->messages, $messages);
        }

        /****m* page/getValidateRulesets
         * SYNOPSIS
         */
        function getValidateRulesets()
        /*
         * FUNCTION
         *      return validate rulesets
         ****
         */
        {
            return lima_validate::getInstance()->export($this);
        }

        /****m* page/prepareMessages
         * SYNOPSIS
         */
        function prepareMessages(lima_app $app)
        /*
         * FUNCTION
         *      prepare messages for output page (eg error- or status messages)
         * INPUTS
         *      * $app (object) -- application object
         ****
         */
        {
            if (count($this->errors) > 0) {
                $app->setErrors($this->errors);
            }

            if (count($this->messages) > 0) {
                $app->setMessages($this->messages);
            }
        }
    }
}
