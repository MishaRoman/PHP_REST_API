<?php 

namespace App\controllers;

use App\core\Database;
use App\models\User;

class AuthController extends Controller
{
	public function register()
	{
		$user = new User();

		$user->email = trim($_POST['email']);
		$user->password = trim($_POST['password']);

		if ($user->register()) {
			echo json_encode([
				'message' => 'User created',
				'token' => $user->token
			]);
		} else {
			echo json_encode([
				'errors' => $user->errors
			]);
		}
	}

	public function login()
	{
		$user = new User();

		$user->email = trim($_POST['email']);
		$user->password = trim($_POST['password']);

		if ($user->login()) {
			echo json_encode([
				'message' => 'Logged in successfully',
				'token' => $user->token,
			]);
		} else {
			echo json_encode([
				'errors' => $user->errors
			]);
		}
	}

	
}