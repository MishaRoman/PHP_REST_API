<?php 

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-type,Authorization,Access-Control-Allow-Methods,X-Requested-With');

require_once '../../config/Database.php';
require_once '../../models/User.php';

$database = new Database();
$db = $database->connect();

$user = new User($db);

$user->email = trim($_POST['email']);
$user->password = trim($_POST['password']);


if ($user->create()) {
	echo json_encode([
		'message' => 'User created',
		'token' => $user->token
	]);
} else {
	echo json_encode([
		'error' => $user->validation_errors
	]);
}