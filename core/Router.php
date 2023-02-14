<?php

namespace App\core;

class Router
{
	public array $routes = [];

	public function get($path, $callback)
	{
		$this->routes['get'][$path] = $callback;
	}

	public function post($path, $callback)
	{
		$this->routes['post'][$path] = $callback;
	}

	public function put($path, $callback)
	{
		$this->routes['put'][$path] = $callback;
	}

	public function delete($path, $callback)
	{
		$this->routes['delete'][$path] = $callback;
	}

	public function resolve()
	{
		$path = $this->getPath();
		$method = strtolower($_SERVER['REQUEST_METHOD']);

		$callback = $this->routes[$method][$path] ?? false;

		if (!$callback) {
            http_response_code(404);
            die();
        }

        $controller = new $callback[0]();
    	$controller->action = $callback[1];
    	$callback[0] = $controller;

    	return call_user_func($callback);
	}

	public function getPath()
	{
		$path = $_SERVER['REQUEST_URI'] ?? '/';
		$position = strpos($path, '?');
		if ($position === false) {
			return $path;
		}
		return substr($path, 0, $position);
	}
}