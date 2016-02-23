<?php

namespace Recca0120\HMVC;

use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\Registrar as RoutingRegistrarContract;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request
{
    protected $router;

    protected $uri;

    protected $method = 'GET';

    protected $filter = null;

    public function __construct(Container $app, RoutingRegistrarContract $router)
    {
        $this->app = $app;
        $this->router = $router;
    }

    public function uri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    public function method($method = 'GET')
    {
        $this->method = strtoupper($method);
    }

    public function filter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    public function execute($parameters = [], $cookies = null, $server = null)
    {
        $request = $this->app->request;
        if (is_null($cookies) === true) {
            $cookies = $request->cookie();
        }
        if (is_null($server) === true) {
            $server = $request->server();
        }
        $newRequest = IlluminateRequest::createFromBase(SymfonyRequest::create($this->uri, $this->method, $parameters, $cookies, [], $server));
        $route = $this->router->getRoutes()->match($newRequest);
        $user = $request->user();
        $session = $request->session();
        $newRequest->setRouteResolver(function () use ($route) {
            return $route;
        });
        $newRequest->setUserResolver(function () use ($user) {
            return $user;
        });
        $newRequest->setSession($session);
        $this->app->request = $newRequest;
        $response = $route->run($newRequest);
        $this->app->request = $request;
        $content = $response->getContent();
        if (is_null($this->filter) === false) {
            $crawler = new Crawler($content);
            $content = $crawler->filter($this->filter)->html();
        }

        return $content;
    }
}
