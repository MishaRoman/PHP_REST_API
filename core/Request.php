<?php 

namespace App\core;

class Request
{
	public array $data = [
		'get' => [],
		'post' => [],
		'input' => [],
	];

	public function __construct()
	{
		$this->getData();
	}

	public function getData()
	{
		foreach ($_GET as $key => $value) {
			$this->data['get'][$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
		}
		foreach ($_POST as $key => $value) {
			$this->data['post'][$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
		}
		$input = json_decode(file_get_contents("php://input"), true);

		if (!empty($input)) {
			$input = filter_var_array($input, FILTER_SANITIZE_SPECIAL_CHARS);
			foreach ($input as $key => $value) {
				$this->data['input'][$key] = $value;
			}
		}
		
	}

	public function getPath(): string
	{
		$path = $_SERVER['REQUEST_URI'] ?? '/';
		$position = strpos($path, '?');
		if ($position === false) {
			return $path;
		}
		return substr($path, 0, $position);
	}

	public function get(string $key = null)
	{
		if ($key) {
			return $this->data['get'][$key];
		}
		return $this->data['get'];
	}

	public function post(string $key = null)
	{
		if ($key) {
			return $this->data['post'][$key];
		}
		return $this->data['post'];
	}

	public function input(string $key = null)
	{
		if ($key) {
			return $this->data['input'][$key];
		}
		return $this->data['input'];
	}

	public function all(): array
	{
		return array_merge($this->data['get'], $this->data['post'], $this->data['input']);
	}
}