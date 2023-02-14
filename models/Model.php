<?php

namespace App\models;

use App\core\Database;

abstract class Model
{
	public array $errors = [];

	protected $conn;

	public function __construct()
	{
		$database = new Database();
		$db = $database->connect();
		$this->conn = $db;
	}

	abstract public function validationRules(): array;

	public function validate(): bool
	{
		foreach ($this->validationRules() as $attribute => $rules) {
			$value = $this->{$attribute};
			foreach ($rules as $rule) {
				$ruleName = $rule;
				if(!is_string($ruleName)) {
					$ruleName = $rule[0];
				}

				if ($ruleName === 'required' && !$value) {
					$this->addErrorForRule($attribute, 'required');
				}
				if ($ruleName === 'max' && strlen($value) > $rule[1]) {
					$this->addErrorForRule($attribute, 'max', $rule);
				}
				if ($ruleName === 'min' && strlen($value) < $rule[1]) {
					$this->addErrorForRule($attribute, 'min', $rule);
				}
			}
		}
		
		return empty($this->errors);
	}

	public function addErrorForRule(string $attribute, string $rule, $params = [])
	{
		$message = $this->errorMessages()[$rule] ?? '';
		if ($params) {
			$message = str_replace("{{$params[0]}}", $params[1], $message);
		}
		$this->errors[$attribute][] = $message;
	}

	public function errorMessages(): array
	{
		return [
			'required' => 'This field is required',
            'min' => 'Min length of this field must be {min}',
            'max' => 'Max length of this field must be {max}',
		];
	}
}