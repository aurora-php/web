<?php

namespace org\octris\core\app\web {
    use \org\octris\core\app\web as app;
    use \org\octris\core\validate as validate;
    
    /**
     * Page controller for web applications.
     *
     * @octdoc      c:web/page
     * @copyright   copyright (c) 2010-2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    abstract class page extends \org\octris\core\app\page
    /**/
    {
        /**
         * Whether the page should be delivered only through HTTPS.
         *
         * @octdoc  v:page/$secure
         * @var     bool
         */
        protected $secure = false;
        /**/

        /**
         * Returns whether page should be only delivered secured.
         *
         * @octdoc  m:page/isSecure
         * @return  bool                                    Secured flag.
         */
        public final function isSecure()
        /**/
        {
            return $this->secure;
        }

        /****m* page/validate
         * SYNOPSIS
         */
        public function validate($action)
        /*
         * FUNCTION
         *      apply a validation ruleset
         * INPUTS
         *      * $action (string) -- action
         * OUTPUTS
         *      (bool) -- returns false, if validation failed, otherwise true
         ****
         */
        {
            return validate::getInstance()->validate($this, $action);
        }

        /****m* page/getNextPage
         * SYNOPSIS
         */
        public function getNextPage(\org\octris\core\app $app)
        /*
         * FUNCTION
         *      get's next page from action and next_pages array of last page
         * INPUTS
         *      * $app (object) -- application object
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
                    $next  = new $class($this->app);
                } else {
                    // lookup next page from entry page's next_page array
                    $entry = new $entry_page($this->app);

                    if (is_array($entry->next_pages) && isset($entry->next_pages[$action])) {
                        $class = $entry->next_pages[$action];
                        $next  = new $class($this->app);
                    }
                }
            }

            return $next;
        }

        /****m* page/getAction
         * SYNOPSIS
         */
        public function getAction()
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

            $method = app::getRequestMethod();

            if ($method == 'POST' || $method == 'GET') {
                // try to determine action from a request parameter named ACTION
                $method = ($method == 'POST' ? $_POST : $_GET);
            
                if ($method->validate('ACTION', validate::T_ALPHANUM)) {
                    $action = $method['ACTION']->value;
                }

                if ($action == '') {
                    // try to determine action from a request parameter named ACTION_...
                    foreach ($method as $k => $v) {
                        if (preg_match('/^ACTION_([a-zA-Z]+)$/', $k, $match)) {
                            $action = $match[1];

                            return $action;
                        }
                    }
                }
            }

            if ($action == '') {
                $action = 'default';
            }

            return $action;
        }

        /****m* page/getValidationRuleset
         * SYNOPSIS
         */
        public function getValidationRuleset($action)
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
        public function addError($err)
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
        public function addMessage($msg)
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
        public function countErrors()
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
        public function countMessages()
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
        public function getErrors()
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
        public function getMessages()
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
        public function addErrors(array $errors)
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
        public function addMessages(array $messages)
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
        public function getValidateRulesets()
        /*
         * FUNCTION
         *      return validate rulesets
         ****
         */
        {
        }

        /****m* page/prepareMessages
         * SYNOPSIS
         */
        public function prepareMessages()
        /*
         * FUNCTION
         *      prepare messages for output page (eg error- or status messages)
         ****
         */
        {
            if (count($this->errors) > 0) {
                $this->app->setErrors($this->errors);
            }

            if (count($this->messages) > 0) {
                $this->app->setMessages($this->messages);
            }
        }
    }
}
