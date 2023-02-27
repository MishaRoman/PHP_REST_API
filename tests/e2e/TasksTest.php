<?php 

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use App\core\DatabaseConnection;
use App\models\Task;

class TasksTest extends TestCase
{
	private static $client;
	private static $conn;

	public static function setUpBeforeClass(): void
	{
		$dotenv = \Dotenv\Dotenv::createImmutable(dirname(dirname(__DIR__)));
		$dotenv->load();

		$host = $_ENV['DB_HOST'];
		$dbName = $_ENV['DB_TEST_DATABASE'];
		$user = $_ENV['DB_USER'];
		$pass = $_ENV['DB_PASSWORD'];

		DatabaseConnection::connect($host, $dbName, $user, $pass);
		self::$conn = DatabaseConnection::getConnection();

		$client = new Client(['http_errors' => false]);

		$response = $client->post('http://api/register', 
			[
				'form_params' => [
            		'email' => 'email@mail.com',
            		'password' => 'password'
            	]
			]
		);
		$data = json_decode($response->getBody()->getContents(), true);

		self::$client = new Client([
			'base_uri' => 'http://api',
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $data['token']
			]
		]);
	}

	public function testGetTasksRequestReturnsErrorWithoutAuthorization()
	{
		$client = new Client(['http_errors' => false]);
		$response = $client->get('http://api/tasks');

		$this->assertEquals($response->getStatusCode(), 401);
	}

	public function testGetTasksRequestReturnsEmptyArray()
	{
		$response = self::$client->get('/tasks');

		$result = json_decode($response->getBody()->getContents(), true);

		$this->assertEmpty($result);
		$this->assertEquals($response->getStatusCode(), 200);
	}

	public function testCreateTaskValidationFailedWithEmptyValues()
	{
		$response = self::$client->post('/tasks/create');

        $expectedJson = [
        	'errors' => [
        		'title' => [
        			'This field is required',
        		],
        		"category_id" => [
		            "This field is required",
		        ]
        	]
        ];
        $actualJson = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($expectedJson, $actualJson);
		$this->assertEquals($response->getStatusCode(), 422);
	}

	public function testCreateTaskIsSuccessful()
	{
		$params = [
			'title' => 'title',
			'body' => 'some body once told me',
			'category_id' => 1,
			'is_urgent' => 1
		];
		$response = self::$client->post('/tasks/create', ['form_params' => $params]);

		$this->assertEquals($response->getStatusCode(), 201);
	}

	public static function tearDownAfterClass(): void
	{
		$stmt = self::$conn->prepare("TRUNCATE TABLE users");
		$stmt->execute();

		$stmt = self::$conn->prepare("TRUNCATE TABLE tasks");
		$stmt->execute();
	}

}