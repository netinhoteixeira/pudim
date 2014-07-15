<?php

namespace Slim\Middleware\Extras;

class HttpBasicAuthRouteDatabase extends \Slim\Middleware\Extras\HttpBasicAuthDatabase {

    protected $route;

    public function __construct($realm = 'Protected Area', $route = '') {
        $this->route = $route;
        parent::__construct($realm);
    }

    public function call() {
        if ($this->route === '*') {
            parent::call();
        } else {
            if (strpos($this->app->request()->getPathInfo(), $this->route) !== FALSE) {
                parent::call();
                return;
            }

            $this->next->call();
        }
    }

}
