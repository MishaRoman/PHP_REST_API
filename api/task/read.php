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

$params = $_GET;

$result = $task->read($params);

for($i = 0; $i < count($result); $i++) {
	if($result[$i]['image']) {
		$result[$i]['image'] = dirname(dirname(__DIR__)) . '\uploads\\' . $result[$i]['image'];
	}
}

if($result) {
	echo json_encode($result);
} else {
	echo json_encode([]);
}