<?php

namespace Slim\Middleware\Extras;

class HttpBasicAuthRoute extends \Slim\Middleware\Extras\HttpBasicAuth {

    protected $route;

    public function __construct($username, $password, $realm = 'Protected Area', $route = '') {
        $this->route = $route;
        parent::__construct($username, $password, $realm);
    }

    public function call() {
        if (strpos($this->app->request()->getPathInfo(), $this->route) !== FALSE) {
            parent::call();
            return;
        }
        $this->next->call();
    }

}

/*
$app->add(new HttpBasicAuthCustom('username', 'password', 'Some Realm Name', 'someroute'));

$app->get('/someroute', function () use ($app) {
    echo "Welcome!";
})->name('someroute');
*/