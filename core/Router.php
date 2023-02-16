<?php

namespace App\core;

use App\core\Request;

class Router
{
	public array $routes = [];
	private Request $request;

	public function __construct()
	{
		$this->request = new Request();
	}

	public function get(string $path, array $callback)
	{
		$this->routes['get'][$path] = $callback;
	}

	public function post(string $path, array $callback)
	{
		$this->routes['post'][$path] = $callback;
	}

	public function put(string $path, array $callback)
	{
		$this->routes['put'][$path] = $callback;
	}

	public function delete(string $path, array $callback)
	{
		$this->routes['delete'][$path] = $callback;
	}

	public function resolve()
	{
		$path = $this->request->getPath();
		$method = strtolower($_SERVER['REQUEST_METHOD']);

		$callback = $this->routes[$method][$path] ?? false;

		if (!$callback) {
            http_response_code(404);
            die();
        }

        $controller = new $callback[0]();
    	$controller->action = $callback[1];
    	$callback[0] = $controller;

    	return call_user_func($callback, $this->request);
	}
}