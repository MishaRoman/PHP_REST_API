<?php

namespace App\models;

use App\core\Auth;
use App\core\Storage;
use PDO;

class Task extends Model
{
	private $table = 'tasks';

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

		if($params) {
			foreach($params as $param => $value) {
				if($param === 'active') {
					if($value === "0" || $value === "1") {
						$query .= "AND t.is_active = $value ";
					}
				}
				if($param === 'urgent') {
					if($value === "0" || $value === "1") {
						$query .= "AND t.is_urgent = $value ";
					}
				}
			}
		}

		$orderby = $params['orderby'];
		
		if($orderby) {
			if($orderby === 'asc' || $orderby === 'desc') {
				$query .= "ORDER BY t.created_at $orderby";
			}
		} else {
			$query .= "ORDER BY t.created_at DESC ";
		}

		$limit = intval($params['limit']);

		if(isset($limit) && $limit > 0) {
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
		
		if(!$row) {
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

		if(isset($_FILES['image'])) {
			try {
				$image = Storage::saveImage($_FILES['image'], '../../uploads');
			} catch (Exception $e) {
				echo $e->getMessage();
				die();
			}
		}

		$stmt->bindParam(':title', $this->title);
		$stmt->bindParam(':body', $this->body);
		$stmt->bindParam(':user_id', $this->user_id);
		$stmt->bindParam(':category_id', $this->category_id);
		$stmt->bindParam(':is_urgent', $this->is_urgent);
		$stmt->bindParam(':image', $image);

		if($stmt->execute()) {
			return true;
		}

		printf("Error: %s.\n", $stmt->error);

		return false;
	}

	public function update()
	{
		$task = $this->getById($this->id);

		if(!$task) {
			http_response_code(404);
			die();
		}

		if($task['user_id'] !== Auth::getAuthUserId()) {
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
		
		$this->title = htmlspecialchars(strip_tags($this->title));
		$this->body = htmlspecialchars(strip_tags($this->body));
		$this->is_active = htmlspecialchars(strip_tags($this->is_active));
		$this->is_urgent = htmlspecialchars(strip_tags($this->is_urgent));
		$this->category_id = htmlspecialchars(strip_tags($this->category_id));

		$stmt->bindParam(':title', $this->title);
		$stmt->bindParam(':body', $this->body);
		$stmt->bindParam(':is_urgent', $this->is_urgent);
		$stmt->bindParam(':is_active', $this->is_active);
		$stmt->bindParam(':category_id', $this->category_id);
		$stmt->bindParam(':id', $task['id']);

		if($stmt->execute()) {
			$task = $this->getById($task['id']);
			return $task;	
		}

		printf("Error: %s.\n", $stmt->error);
		return false;
	}

	public function delete(): bool
	{
		$task = $this->getById($this->id);
		
		if(!$task) {
			http_response_code(404);
			die();
		}

		if($task['user_id'] !== Auth::getAuthUserId()) {
			http_response_code(403);
			echo json_encode(['error' => 'unauthorized']);
			die();
		}

		$query = "DELETE FROM " . $this->table . " WHERE id = :id";

		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(':id', $task['id']);

		if($stmt->execute()) {
			return true;
		}

		printf("Error: %s.\n", $stmt->error);

		return false;
	}

	public function validationRules(): array
	{
		return [
			'title' => ['required', ['max', 255]],
			'category_id' => ['required', ['exists', 'categories']],
		];
	}

	private function getById($id)
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