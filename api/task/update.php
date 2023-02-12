<?php 

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-type,Authorization,Access-Control-Allow-Methods,X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] != 'PUT') {
	http_response_code(405);
	echo json_encode(['error' => 'Method not allowed']);
	die();
}

require_once '../../config/Database.php';
require_once '../../models/Task.php';

$database = new Database();
$db = $database->connect();

$data = json_decode(file_get_contents("php://input"));

$task = new Task($db);

$task->id = isset($_GET['id']) ? $_GET['id']: die();
$task->title = $data->title;
$task->body = $data->body;
$task->is_active = $data->is_active;
$task->is_urgent = $data->is_urgent;
$task->category_id = $data->category_id;

$result = $task->update();

if ($result) {
	echo json_encode($result);
} else {
	echo json_encode([
		'message' => 'Task not updated'
	]);
}