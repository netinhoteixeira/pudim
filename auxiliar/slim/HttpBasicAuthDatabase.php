<?php

namespace Slim\Middleware\Extras;

class HttpBasicAuthDatabase extends \Slim\Middleware {

    /**
     * @var string
     */
    protected $realm;

    /**
     * Constructor
     *
     * @param   string  $realm      The HTTP Authentication realm
     */
    public function __construct($realm = 'Protected Area') {
        $this->realm = $realm;
    }

    /**
     * Deny Access
     *
     */
    public function deny_access() {
        $res = $this->app->response();
        $res->status(401);
        // DONE: https://groups.google.com/forum/#!topic/angular/A2RktQ83RW8
        // ANSWER: I think this is not the 401 status making the browser native login
        // dialog appear, but the:
        // WWW-Authenticate: Basic realm="--realm-name-here--"
        // instead.
        // Can you double check the HTTP headers?
        //$res->header('WWW-Authenticate', sprintf('Basic realm="%s"', $this->realm));
    }

    /**
     * Authenticate 
     *
     * @param   string  $username   The HTTP Authentication username
     * @param   string  $password   The HTTP Authentication password     
     *
     */
    public function authenticate($username, $password) {
        if (!ctype_alnum($username)) {
            return FALSE;
        }

        if (isset($username) && isset($password)) {
            $password = crypt($password);
            // Check database here with $username and $password
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Call
     *
     * This method will check the HTTP request headers for previous authentication. If
     * the request has already authenticated, the next middleware is called. Otherwise,
     * a 401 Authentication Required response is returned to the client.
     */
    public function call() {
        $req = $this->app->request();
        //$res = $this->app->response();
        $authUser = $req->headers('PHP_AUTH_USER');
        $authPass = $req->headers('PHP_AUTH_PW');

        if ($this->authenticate($authUser, $authPass)) {
            $this->next->call();
        } else {
            $this->deny_access();
        }
    }

}

/*
require 'Slim/Slim.php';
require 'Slim/Middleware.php';
require 'Slim/Middleware/HttpBasicAuth.php';
 
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->add(new \HttpBasicAuth());
*/