<?php 

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-type,Authorization,Access-Control-Allow-Methods,X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	http_response_code(405);
	echo json_encode(['error' => 'Method not allowed']);
	die();
}

require_once '../../config/Database.php';
require_once '../../models/Task.php';

$database = new Database();
$db = $database->connect();

$task = new Task($db);

$task->title = $_POST['title'];
$task->body = $_POST['body'];
$task->is_urgent = $_POST['is_urgent'] ?? 0;
$task->category_id = $_POST['category_id'];

if ($task->create()) {
	http_response_code(201);
	echo json_encode([
		'message' => 'Task created'
	]);
} else {
	echo json_encode([
		'message' => $task->errors
	]);
}