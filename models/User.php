<?php 

namespace App\models;

use PDO;
use App\core\Auth;

class User extends Model
{
	private string $table = 'users';
	
	public $email;
	public $password;

	public function register(): bool
	{
		if (!$this->validate($this->registerValidationRules())) {
			return false;
		}

		$this->password = password_hash($this->password, PASSWORD_DEFAULT);

		$query = "INSERT INTO " . $this->table . "
		  SET
		  	email = :email,
		  	password = :password";

		$stmt = $this->conn->prepare($query);

		$params = [
			':email' => $this->email,
			':password' => $this->password,
		];

		if ($stmt->execute($params)) {
			$user = $this->findBy('email', $this->email);

			$jwt = Auth::generateJwt($user['id']);

            $this->token = $jwt;

			return true;
		}

		printf("Error: %s.\n", $stmt->error);

		return false;
	}

	public function login(): bool
	{
		if (!$this->validate($this->loginValidationRules())) {
			return false;
		}
		$user = $this->findBy('email', $this->email);

	    if (!password_verify($this->password, $user['password'])){
			$this->errors['password'][] = 'Password is incorrect';
            return false;
        }

		$jwt = Auth::generateJwt($user['id']);

        $this->token = $jwt;

		return true;
	}

	public function getAuthUserInfo(): array
	{
		$id = Auth::getUserIdFromToken();
		$user = $this->findBy('id', $id);
		
		return [
			'id' => $user['id'],
			'email' => $user['email'],
		];
	}

	public function registerValidationRules(): array
	{
		return [
			'email' => ['required', 'email', ['unique', 'users', 'email']],
			'password' => ['required', ['min', 6]],
		];
	}

	public function loginValidationRules(): array
	{
		return [
			'email' => ['required', 'email', ['exists', 'users', 'email']],
			'password' => ['required', ['min', 6]],
		];
	}

	private function findBy(string $field, string $value)
	{
		$query = "SELECT * FROM " . $this->table . " WHERE $field = '$value'";
		$stmt = $this->conn->query($query);
	    $result = $stmt->fetch(PDO::FETCH_ASSOC);
	    return $result;
	}
}