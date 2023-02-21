<?php

namespace App\models;

use App\core\Auth;
use App\core\Storage;
use PDO;

class Task extends Model
{
	private string $table = 'tasks';

	public $id;
	public $title;
	public $body;
	public $user_id;
	public $category_id;
	public $category_name;
	public $is_urgent;
	public $is_active;
	public $image;
	public $created_at;

	public function __construct()
	{
		parent::__construct();

		if (!Auth::check()) {
			http_response_code(403);
			echo json_encode(['error' => 'unauthorized']);
			die();
		};
	}

	public function read(array $params): array
	{
		$query = "SELECT
			t.id,
			c.name as category,
			t.title,
			t.body,
			t.user_id,
			t.category_id,
			t.is_active,
			t.is_urgent,
			t.image,
			t.created_at
		  FROM " . $this->table . " t
		  LEFT JOIN
		  	categories c ON t.category_id = c.id
		  WHERE t.user_id = ? ";

		if ($params) {
			foreach($params as $param => $value) {
				if ($param === 'active') {
					if ($value === "0" || $value === "1") {
						$query .= "AND t.is_active = $value ";
					}
				}
				if ($param === 'urgent') {
					if ($value === "0" || $value === "1") {
						$query .= "AND t.is_urgent = $value ";
					}
				}
			}
		}

		$orderby = $params['orderby'];
		
		if ($orderby) {
			if ($orderby === 'asc' || $orderby === 'desc') {
				$query .= "ORDER BY t.created_at $orderby";
			}
		} else {
			$query .= "ORDER BY t.created_at DESC ";
		}

		$limit = intval($params['limit']);

		if (isset($limit) && $limit > 0) {
			$query .= "LIMIT $limit";
		}

		$user_id = Auth::getAuthUserId();

		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(1, $user_id);
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function findById(int $id): array
	{
		$query = "SELECT
			t.id,
			c.name as category,
			t.title,
			t.body,
			t.user_id,
			t.category_id,
			t.is_active,
			t.is_urgent,
			t.image,
			t.created_at
		  FROM " . $this->table . " t
		  LEFT JOIN
		  	categories c ON t.category_id = c.id
		  WHERE
		    t.id = ?
		  LIMIT 0,1";

		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(1, $id);

		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if (!$row) {
			http_response_code(404);
			die();
		}

		$user_id = Auth::getAuthUserId();

		if ($user_id !== $row['user_id']) {
			http_response_code(403);
			echo json_encode(['error' => 'unauthorized']);
			die();
		}

		return $row;
	}

	public function create(): bool
	{
		if (!$this->validate($this->validationRules())) {
			return false;
		}

		$query = "INSERT INTO " . $this->table . "
		  SET
		  	title = :title,
		  	body = :body,
		  	user_id = :user_id,
		  	is_urgent = :is_urgent,
		  	image = :image,
		  	category_id = :category_id";

		$stmt = $this->conn->prepare($query);

		$this->user_id = Auth::getAuthUserId();

		if (isset($_FILES['image'])) {
			try {
				$image = Storage::saveImage($_FILES['image'], '../../uploads');
			} catch (Exception $e) {
				echo $e->getMessage();
				die();
			}
		}

		$params = [
			':title' => $this->title,
			':body' => $this->body,
			':user_id' => $this->user_id,
			':is_urgent' => $this->is_urgent,
			':image' => $this->image,
			':category_id' => $this->category_id,
		];

		if ($stmt->execute($params)) {
			return true;
		}

		printf("Error: %s.\n", $stmt->error);

		return false;
	}

	public function update()
	{
		$task = $this->getById($this->id);

		if (!$task) {
			http_response_code(404);
			die();
		}

		if ($task['user_id'] !== Auth::getAuthUserId()) {
			http_response_code(403);
			echo json_encode(['error' => 'unauthorized']);
			die();
		}

		$query = "UPDATE " . $this->table . "
		  SET
		  	title = IF('$this->title' = '', title, :title),
		  	body = IF('$this->body' = '', body, :body),
		  	is_active = IF('$this->is_active' = '', is_active, :is_active),
		  	is_urgent = IF('$this->is_urgent' = '', is_urgent, :is_urgent),
		  	category_id = IF('$this->category_id' = '', category_id, :category_id)
		  WHERE
		    id = :id";

		$stmt = $this->conn->prepare($query);

		$params = [
			':title' => $this->title,
			':body' => $this->body,
			':is_urgent' => $this->is_urgent,
			':is_active' => $this->is_active,
			':category_id' => $this->category_id,
			':id' => $task['id'],
		];

		if ($stmt->execute($params)) {
			$task = $this->getById($task['id']);
			return $task;	
		}

		printf("Error: %s.\n", $stmt->error);
		return false;
	}

	public function delete(): bool
	{
		$task = $this->getById($this->id);
		
		if (!$task) {
			http_response_code(404);
			die();
		}

		if ($task['user_id'] !== Auth::getAuthUserId()) {
			http_response_code(403);
			echo json_encode(['error' => 'unauthorized']);
			die();
		}

		$query = "DELETE FROM " . $this->table . " WHERE id = :id";

		$stmt = $this->conn->prepare($query);

		if ($stmt->execute([':id' => $task['id']])) {
			return true;
		}

		printf("Error: %s.\n", $stmt->error);

		return false;
	}

	public function validationRules(): array
	{
		return [
			'title' => ['required', ['max', 255]],
			'category_id' => ['required', ['exists', 'categories', 'id']],
		];
	}

	private function getById(int $id)
	{
		$query = "SELECT
			t.id,
			c.name as category,
			t.title,
			t.body,
			t.user_id,
			t.category_id,
			t.is_active,
			t.is_urgent,
			t.image,
			t.created_at
		  FROM " . $this->table . " t
		  LEFT JOIN
		  	categories c ON t.category_id = c.id
		  WHERE
		    t.id = ?
		  LIMIT 0,1";

		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(1, $id);

		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return $row;
	}

}