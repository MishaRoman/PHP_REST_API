<?php 

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use App\core\DatabaseConnection;

class AuthTest extends TestCase
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

		self::$client = new Client(['base_uri' => 'http://api', 'http_errors' => false]);
	}

	public function testRegisterValidationFailedWithEmptyValues()
	{
		$response = self::$client->post('/register');

        $expectedJson = [
        	'errors' => [
        		'email' => [
        			'This field is required',
        		],
        		"password" => [
		            "This field is required",
		        ]
        	]
        ];
        $actualJson = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($expectedJson, $actualJson);
		$this->assertEquals(422, $response->getStatusCode());
	}

	public function testRegisterValidationFailedWithInvalidData()
	{
		$response = self::$client->post(
            '/register',
            [
            	'form_params' => [
            		'email' => 'invalidemail.com',
            		'password' => 'short'
            	]
            ]
        );

        $expectedJson = [
        	'errors' => [
        		'email' => [
        			'Email field must be valid email address',
        		],
        		"password" => [
		            "Min length of this field must be 6",
		        ]
        	]
        ];
        $actualJson = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($expectedJson, $actualJson);
		$this->assertEquals(422, $response->getStatusCode());
	}

	public function testRegisterIsSuccessfull()
	{
		$response = self::$client->post(
            '/register',
            [
            	'form_params' => [
            		'email' => 'email@mail.com',
            		'password' => 'password'
            	]
            ]
        );

        $result = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('token', $result);
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testRegisterFailedWithSameEmail()
	{
		$response = self::$client->post(
            '/register',
            [
            	'form_params' => [
            		'email' => 'email@mail.com',
            		'password' => 'password'
            	]
            ]
        );

        $expectedJson = [
        	'errors' => [
        		'email' => [
        			'This field is already exists',
        		]
        	]
        ];

        $actualJson = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($expectedJson, $actualJson);
		$this->assertEquals(422, $response->getStatusCode());
	}

	public function testLoginValidationFailedWithEmptyValues()
	{
		$response = self::$client->post('/login');

        $expectedJson = [
        	'errors' => [
        		'email' => [
        			'This field is required',
        		],
        		"password" => [
		            "This field is required",
		        ]
        	]
        ];
        $actualJson = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($expectedJson, $actualJson);
		$this->assertEquals(422, $response->getStatusCode());
	}

	public function testLoginValidationFailedWithInvalidData()
	{
		$response = self::$client->post(
            '/login',
            [
            	'form_params' => [
            		'email' => 'invalidemail.com',
            		'password' => 'short'
            	]
            ]
        );

        $expectedJson = [
        	'errors' => [
        		'email' => [
        			'Email field must be valid email address',
        		],
        		"password" => [
		            "Min length of this field must be 6",
		        ]
        	]
        ];
        $actualJson = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($expectedJson, $actualJson);
		$this->assertEquals(422, $response->getStatusCode());
	}

	public function testLoginValidationFailedWithUnexistingEmail()
	{
		$response = self::$client->post(
            '/login',
            [
            	'form_params' => [
            		'email' => 'unexisting@email.com',
            		'password' => 'password'
            	]
            ]
        );

        $expectedJson = [
        	'errors' => [
        		'email' => [
        			'Record with this value does not exists in users table',
        		]
        	]
        ];
        $actualJson = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($expectedJson, $actualJson);
		$this->assertEquals(422, $response->getStatusCode());
	}

	public function testLoginValidationFailedWithIncorrectPassword()
	{
		$response = self::$client->post(
            '/login',
            [
            	'form_params' => [
            		'email' => 'email@mail.com',
            		'password' => 'blablabla'
            	]
            ]
        );

        $expectedJson = [
        	'errors' => [
        		'password' => [
        			'Password is incorrect',
        		]
        	]
        ];
        $actualJson = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($expectedJson, $actualJson);
		$this->assertEquals(422, $response->getStatusCode());
	}

	public function testLoginIsSuccessfull()
	{
		$response = self::$client->post(
            '/login',
            [
            	'form_params' => [
            		'email' => 'email@mail.com',
            		'password' => 'password'
            	]
            ]
        );

        $result = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('token', $result);
		$this->assertEquals(200, $response->getStatusCode());
	}

	public static function tearDownAfterClass(): void
	{
		$stmt = self::$conn->prepare("TRUNCATE TABLE users");
		$stmt->execute();
	}
}