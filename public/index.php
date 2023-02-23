<?php

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

use App\core\Router;
use App\controllers\AuthController;
use App\controllers\CategoriesController;
use App\controllers\TasksController;
use App\core\DatabaseConnection;

define('ROOT', dirname(__DIR__));

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

if ($_ENV['APP_ENV'] === 'test') {
	$db_name = $_ENV['DB_TEST_DATABASE'];
} else {
	$db_name = $_ENV['DB_DATABASE'];
}

DatabaseConnection::connect($host, $db_name, $username, $password);

$router = new Router();

$router->post('/register', [AuthController::class, 'register']);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/tasks', [TasksController::class, 'read']);
$router->get('/tasks/show', [TasksController::class, 'show']);
$router->post('/tasks/create', [TasksController::class, 'create']);
$router->put('/tasks/update', [TasksController::class, 'update']);
$router->delete('/tasks/delete', [TasksController::class, 'delete']);

$router->get('/categories', [CategoriesController::class, 'read']);


$router->resolve();
