<?php

namespace App\models;

use App\core\DatabaseConnection;

abstract class Model
{
	public array $errors = [];

	protected $conn;

	public function __construct()
	{
		$this->conn = DatabaseConnection::getConnection();
	}

	public function validate(array $validationRules): bool
	{
		foreach ($validationRules as $attribute => $rules) {
			$value = $this->{$attribute};
			foreach ($rules as $rule) {
				$ruleName = $rule;
				if (!is_string($ruleName)) {
					$ruleName = $rule[0];
				}

				if ($ruleName === 'required' && !$value) {
					$this->addErrorForRule($attribute, 'required');
					break;
				}
				if ($ruleName === 'max' && strlen($value) > $rule[1]) {
					$this->addErrorForRule($attribute, 'max', $rule);
				}
				if ($ruleName === 'min' && strlen($value) < $rule[1]) {
					$this->addErrorForRule($attribute, 'min', $rule);
				}
				if ($ruleName === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
					$this->addErrorForRule($attribute, 'email', $rule);
					break;
				}
				if ($ruleName === 'boolean') {
					$booleanValues = [true, false, 1, 0, '1', '0'];

					if (!in_array($value, $booleanValues, true)) {
						$this->addErrorForRule($attribute, 'boolean', $rule);
					}
				}
				if ($ruleName === 'unique') {
					$table = $rule[1];
					$attr = $rule[2];
					$stmt = $this->conn->prepare("SELECT * FROM $table WHERE $attr = :attr");
					$stmt->execute([':attr' => $value]);
					$result = $stmt->fetchObject();

					if ($result) {
						$this->addErrorForRule($attribute, 'unique', $rule);
					}
				}
				if ($ruleName === 'exists') {
					$table = $rule[1];
					$attr = $rule[2];
					$stmt = $this->conn->prepare("SELECT * FROM $table WHERE $attr = :attr");				
					$stmt->execute([':attr' => $value]);
					$result = $stmt->fetchObject();

					if (!$result) {
						$this->addErrorForRule($attribute, 'exists', $rule);
					}
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
			'email' => 'Email field must be valid email address',
			'unique' => 'This field is already exists',
			'exists' => 'Record with this value does not exists in {exists} table',
			'boolean' => 'This field must be type of boolean',
            'min' => 'Min length of this field must be {min}',
            'max' => 'Max length of this field must be {max}',
		];
	}
}