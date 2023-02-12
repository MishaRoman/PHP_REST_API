<?php 

require_once '../../config/Auth.php';

class User
{
	private $conn;
	private string $table = 'users';
	
	public string $email;
	public string $password;
	public array $validation_errors = [];

	public function __construct($db)
	{
		$this->conn = $db;
		session_start();
	}

	public function create(): bool
	{
		// Validation
		if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
		  	$this->validation_errors['email'][] = 'Invalid email address';
		}
		if ($this->getUserByEmail($this->email)) {
			$this->validation_errors['email'][] = 'This email is already taken';
		}
		if (strlen($this->password) < 6) {
			$this->validation_errors['password'][] = 'Password length must be more than 6';
		}
		if ($this->validation_errors) {
			return false;
		}
		// Create
		$this->password = password_hash($this->password, PASSWORD_DEFAULT);

		$query = "INSERT INTO " . $this->table . "
		  SET
		  	email = :email,
		  	password = :password";

		$stmt = $this->conn->prepare($query);

		$stmt->bindParam(':email', $this->email);
		$stmt->bindParam(':password', $this->password);

		if($stmt->execute()) {
			$user = $this->getUserByEmail($this->email);
			$_SESSION['user_id'] = $user['id'];

			$jwt = Auth::generate_jwt($this->email);

            $this->token = $jwt;

			return true;
		}

		printf("Error: %s.\n", $stmt->error);

		return false;
	}

	public function login(): bool
	{
		$user = $this->getUserByEmail($this->email);		

	    if (!$user) {
	    	$this->validation_errors['email'][] = 'User with this email address not exists';
	    	return false;
	    }

	    if (!password_verify($this->password, $user['password'])){
			$this->validation_errors['password'][] = 'Password is incorrect';
            return false;
        }

        $_SESSION['user_id'] = $user['id'];

		$jwt = Auth::generate_jwt($user['email']);

        $this->token = $jwt;

		return true;
	}

	private function getUserByEmail(string $email)
	{
		$query = "SELECT * FROM " . $this->table . " WHERE email = '$email'";
		$stmt = $this->conn->query($query);
	    $result = $stmt->fetch(PDO::FETCH_ASSOC);

	    return $result;
	}
}