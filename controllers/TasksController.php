<?php 

namespace App\controllers;

use App\models\Task;

class TasksController extends Controller
{
	public function create()
	{
		$task = new Task();

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
	}

	public function read()
	{
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
	}

	public function show()
	{
		$task = new Task($db);

		$id = isset($_GET['id']) ? $_GET['id']: die();

		$task = $task->findById($id);

		if($task['image']) {
			$task['image'] = dirname(dirname(__DIR__)) . '\uploads\\' . $task['image'];
		}

		echo json_encode($task);
	}

	public function update()
	{
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
	}

	public function delete()
	{
		$task = new Task($db);

		$task->id = isset($_GET['id']) ? $_GET['id']: die();

		if ($task->delete()) {
			http_response_code(204);
		} else {
			echo json_encode([
				'message' => 'Task not deleted'
			]);
		}
	}
}