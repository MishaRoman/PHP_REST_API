<?php 

namespace App\controllers;

use App\core\Database;
use App\core\Request;
use App\models\User;

class AuthController extends Controller
{
	public function register(Request $request)
	{
		$user = new User();
		$data = $request->all();

		$user->email = $data['email'];
		$user->password = $data['password'];

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

	public function login(Request $request)
	{
		$user = new User();
		$data = $request->all();

		$user->email = $data['email'];
		$user->password = $data['password'];

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