<?php 

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class CategoriesTest extends TestCase
{
	private $client;

	public function setUp(): void
	{
		$this->client = new Client(['base_uri' => 'http://api', 'http_errors' => false]);
	}

	public function testGetCategories()
	{
		$response = $this->client->get('/categories');

        $expectedJson = [
        	[
        		'id' => 1,
        		'name' => 'Personal'
        	],
        	[
        		'id' => 2,
        		'name' => 'Working'
        	]
        ];
        $actualJson = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($expectedJson, $actualJson);
		$this->assertEquals(200, $response->getStatusCode());
	}
}