<?php 

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] != 'DELETE') {
	http_response_code(405);
	echo json_encode(['error' => 'Method not allowed']);
	die();
}

require_once '../../config/Database.php';
require_once '../../models/Task.php';

$database = new Database();
$db = $database->connect();

$task = new Task($db);

$task->id = isset($_GET['id']) ? $_GET['id']: die();

if ($task->delete()) {
	http_response_code(204);
} else {
	echo json_encode([
		'message' => 'Task not deleted'
	]);
}