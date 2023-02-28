<?php 

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use App\core\DatabaseConnection;
use App\models\Task;

class TasksTest extends TestCase
{
	private static $client;
	private static $client2;
	private static $taskId;
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

	public function testGetTasks()
	{
		$response = self::$client->get('/tasks');

		$tasks = json_decode($response->getBody()->getContents(), true);

		self::$taskId = $tasks[0]['id'];

		$this->assertNotEmpty($tasks);
		$this->assertEquals($response->getStatusCode(), 200);
	}

	public function testGetSingleTask()
	{
		$response = self::$client->get('/tasks/show?id=' . self::$taskId);

		$task = $response->getBody()->getContents();

		$this->assertNotEmpty($task);
		$this->assertEquals($response->getStatusCode(), 200);
	}

	public function testAnotherUserCannotAccessData()
	{
		$client = self::getSecondClient();

		$response = $client->get('/tasks/show?id=' . self::$taskId);

		$this->assertEquals($response->getStatusCode(), 403);
	}

	public function testUpdateTask()
	{
		$params = [
			'title' => 'new title',
			'body' => 'new body',
			'is_urgent' => 0,
			'is_active' => 0,
			'category_id' => 2
		];
		$response = self::$client->put('/tasks/update?id=' . self::$taskId, ['json' => $params]);

		$task = json_decode($response->getBody()->getContents(), true);

		$updatedValues = [
			'title' => $task['title'],
			'body' => $task['body'],
			'is_urgent' => (int) $task['is_urgent'],
			'is_active' => (int) $task['is_active'],
			'category_id' => (int) $task['category_id'],
		];
		$this->assertEquals($updatedValues, $params);
		$this->assertEquals($response->getStatusCode(), 200);
	}

	public function testUpdateTaskWithEmptyValues()
	{
		$response = self::$client->get('/tasks/show?id=' . self::$taskId);
		$task = json_decode($response->getBody()->getContents(), true);

		$updateResponse = self::$client->put('/tasks/update?id=' . self::$taskId);
		$updatedTask = json_decode($updateResponse->getBody()->getContents(), true);

		$this->assertEquals($task, $updatedTask);
		$this->assertEquals($response->getStatusCode(), 200);
	}

	public function testAnotherUserCannotUpdateTask()
	{
		$client = self::getSecondClient();

		$response = $client->put('/tasks/update?id=' . self::$taskId);

		$this->assertEquals($response->getStatusCode(), 403);
	}

	public function testAnotherUserCannotDeleteTask()
	{
		$client = self::getSecondClient();

		$response = $client->delete('/tasks/delete?id=' . self::$taskId);

		$this->assertEquals($response->getStatusCode(), 403);
	}

	public function testDeleteTask()
	{
		$response = self::$client->delete('/tasks/delete?id=' . self::$taskId);

		$tasks = self::$client->get('/tasks')->getBody()->getContents();

		$this->assertEmpty(json_decode($tasks));
		$this->assertEquals($response->getStatusCode(), 204);
	}

	protected function getSecondClient()
	{
		if (!is_null(self::$client2)) {
			return self::$client2;
		}

		$client = new Client(['http_errors' => false]);

		$response = $client->post('http://api/register', 
			[
				'form_params' => [
            		'email' => 'email@mail.com2',
            		'password' => 'password'
            	]
			]
		);
		$data = json_decode($response->getBody()->getContents(), true);

		self::$client2 = new Client([
			'base_uri' => 'http://api',
			'http_errors' => false,
			'headers' => [
				'Authorization' => 'Bearer ' . $data['token']
			]
		]);

		return self::$client2;
	}

	public static function tearDownAfterClass(): void
	{
		$stmt = self::$conn->prepare("TRUNCATE TABLE users");
		$stmt->execute();

		$stmt = self::$conn->prepare("TRUNCATE TABLE tasks");
		$stmt->execute();
	}

}