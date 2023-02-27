<?php

namespace App\core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
	private static string $serverName = 'http://api';

	public static function generateJwt(int $user_id): string
	{
		$key = $_ENV['SECRET_KEY'];
		$issuer = self::$serverName;
		$issuedAt = time();
		$expire = ($issuedAt + 60 * 60 * 24);

		$payload = [
			'iat' => $issuedAt,
			'iss' => $issuer,
			'nbf' => $issuedAt,
			'exp' => $expire,
			'user_id' => $user_id
		];

		$token = JWT::encode($payload, $key, 'HS256');

		return $token;
	}

	public static function check(): bool
	{
		$bearer_token = self::getBearerToken();
		if (is_null($bearer_token)) {
			return false;
		}
		return self::isJwtValid($bearer_token);	
	}

	public static function getUserIdFromToken(): int
	{
		$bearer_token = self::getBearerToken();

		if (!$bearer_token) {
			http_response_code(401);
			echo json_encode([
				'error' => 'Api token is empty'
			]);
			exit;
		}

		$secret = $_ENV['SECRET_KEY'];

		try {
			$token = JWT::decode($bearer_token, new Key($secret, 'HS256'));
		} catch (\Exception $e) {
			http_response_code(401);
			echo json_encode([
				'errors' => $e->getMessage()
			]);
			exit;
		}

		return $token->user_id;	
	}

	private static function isJwtValid(string $jwt): bool
	{
		$secret = $_ENV['SECRET_KEY'];
		try {
			$token = JWT::decode($jwt, new Key($secret, 'HS256'));
		} catch (\Exception $e) {
			http_response_code(401);
			echo json_encode([
				'error' => $e->getMessage()
			]);
			exit;
		}

		return true;
	}

	private static function getAuthorizationHeader()
	{
		$headers = null;
		
		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER["Authorization"]);
		} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} else if (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		
		return $headers;
	}

	private static function getBearerToken()
	{
	    $headers = self::getAuthorizationHeader();
		
	    // HEADER: Get the access token from the header
	    if (!empty($headers)) {
	        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
	            return $matches[1];
	        }
	    }
	    return null;
	}
}