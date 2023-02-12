<?php 

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

require_once '../../config/Database.php';
require_once '../../models/Category.php';

$database = new Database();
$db = $database->connect();

$category = new Category($db);

$categories = $category->read();

if($categories) {
	echo json_encode($categories)
} else {
	echo json_encode([]);
}