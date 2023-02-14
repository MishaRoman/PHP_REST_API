<?php

namespace App\controllers;

abstract class Controller
{
	public string $action = '';

	public function __construct()
	{
		header("Access-Control-Allow-Orgin: *");
        header("Content-Type: application/json");
	}
}