<?php 

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class AuthTest extends TestCase
{
	private $client;
	private $conn;

	public function setUp(): void
	{
		$this->client = new Client(['base_uri' => 'http://api', 'http_errors' => false]);
	}

	public function testRegisterValidationFailedWithEmptyValues()
	{
		$response = $this->client->post('/register');

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
		$response = $this->client->post(
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
		$response = $this->client->post(
            '/register',
            [
            	'form_params' => [
            		'email' => 'email@mail.commaas',
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
		$response = $this->client->post(
            '/register',
            [
            	'form_params' => [
            		'email' => 'email@mail.comm',
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
}