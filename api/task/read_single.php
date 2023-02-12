<?php 

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
header('Access-Control-Allow-Methods: GET');

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
	http_response_code(405);
	echo json_encode(['error' => 'Method not allowed']);
	die();
}

require_once '../../config/Database.php';
require_once '../../models/Task.php';

$database = new Database();
$db = $database->connect();

$task = new Task($db);

$id = isset($_GET['id']) ? $_GET['id']: die();

$task = $task->read_single($id);

if($task['image']) {
	$task['image'] = dirname(dirname(__DIR__)) . '\uploads\\' . $task['image'];
}

echo json_encode($task);