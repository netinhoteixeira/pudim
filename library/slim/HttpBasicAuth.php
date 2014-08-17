<?php

namespace Slim\Middleware\Extras;

class HttpBasicAuth extends \Slim\Middleware {

    /**
     * @var string
     */
    protected $realm;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * Constructor
     *
     * @param   string  $username   The HTTP Authentication username
     * @param   string  $password   The HTTP Authentication password
     * @param   string  $realm      The HTTP Authentication realm
     */
    public function __construct($username, $password, $realm = 'Protected Area') {
        $this->username = $username;
        $this->password = $password;
        $this->realm = $realm;
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
        $res = $this->app->response();
        $authUser = $req->headers('PHP_AUTH_USER');
        $authPass = $req->headers('PHP_AUTH_PW');
        if ($authUser && $authPass && $authUser === $this->username && $authPass === $this->password) {
            $this->next->call();
        } else {
            $res->status(401);
            // DONE: https://groups.google.com/forum/#!topic/angular/A2RktQ83RW8
            // ANSWER: I think this is not the 401 status making the browser native login
            // dialog appear, but the:
            // WWW-Authenticate: Basic realm="--realm-name-here--"
            // instead.
            // Can you double check the HTTP headers?
            //$res->header('WWW-Authenticate', sprintf('Basic realm="%s"', $this->realm));
        }
    }

}
