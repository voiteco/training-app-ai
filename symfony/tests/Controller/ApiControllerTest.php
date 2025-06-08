<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testApiEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/test');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('timestamp', $responseData);
    }

    public function testCorsHeaders(): void
    {
        $client = static::createClient();
        $client->request(
            'OPTIONS', 
            '/api/test',
            [],
            [],
            [
                'HTTP_ORIGIN' => 'http://localhost:5173',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET'
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Access-Control-Allow-Origin', 'http://localhost:5173');
        $this->assertResponseHeaderSame('Access-Control-Allow-Methods', 'GET, POST, DELETE, OPTIONS');
    }
}
