<?php 

namespace App\controllers;

use App\core\Request;
use App\core\Auth;
use App\models\Task;

class TasksController extends Controller
{
	public function __construct()
	{
		parent::__construct();

		if (!Auth::check()) {
			http_response_code(401);
			echo json_encode(['error' => 'unauthorized']);
			die();
		};
	}
	public function create(Request $request)
	{
		$data = $request->all();

		$task = new Task();

		$task->title = $data['title'];
		$task->body = $data['body'];
		$task->is_urgent = $data['is_urgent'] ?? 0;
		$task->category_id = $data['category_id'];

		if ($task->create()) {
			http_response_code(201);
			echo json_encode([
				'message' => 'Task created'
			]);
		} else {
			http_response_code(422);
			echo json_encode([
				'errors' => $task->errors
			]);
		}
	}

	public function read(Request $request)
	{
		$task = new Task();
		
		$params = $request->get();

		$result = $task->read($params);

		for ($i = 0; $i < count($result); $i++) {
			if ($result[$i]['image']) {
				$result[$i]['image'] = ROOT . '\uploads\\' . $result[$i]['image'];
			}
		}

		if ($result) {
			echo json_encode($result);
		} else {
			echo json_encode([]);
		}
	}

	public function show(Request $request)
	{
		$task = new Task();

		$id = $request->get('id');

		$task = $task->findBy('id', $id);

		$user_id = Auth::getUserIdFromToken();

		if ($user_id !== (int) $task['user_id']) {
			http_response_code(403);
			echo json_encode(['error' => 'unauthorized']);
			die();
		}

		if ($task['image']) {
			$task['image'] = ROOT . '\uploads\\' . $task['image'];
		}

		echo json_encode($task);
	}

	public function update(Request $request)
	{
		$data = $request->all();

		$currentTask = (new Task())->findBy('id', $data['id']);

		$user_id = Auth::getUserIdFromToken();

		if ($user_id != $currentTask['user_id']) {
			http_response_code(403);
			echo json_encode(['error' => 'unauthorized']);
			die();
		}

		$task = new Task();

		$task->id = $data['id'];
		$task->title = $data['title'];
		$task->body = $data['body'];
		$task->is_active = $data['is_active'];
		$task->is_urgent = $data['is_urgent'];
		$task->category_id = $data['category_id'];

		$result = $task->update();

		if ($result) {
			echo json_encode($result);
		} else {
			echo json_encode([
				'message' => 'Task not updated'
			]);
		}
	}

	public function delete(Request $request)
	{
		$task = (new Task())->findBy('id', $request->get('id'));

		$user_id = Auth::getUserIdFromToken();

		if ($user_id !== (int) $task['user_id']) {
			http_response_code(403);
			echo json_encode(['error' => 'unauthorized']);
			die();
		}

		if ((new Task())->delete($task['id'])) {
			http_response_code(204);
		} else {
			echo json_encode([
				'message' => 'Task not deleted'
			]);
		}
	}
}