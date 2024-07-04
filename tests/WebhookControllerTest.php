<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class WebhookControllerTest extends WebTestCase
{
    public function fakeMessage($command = ''): string
    {
        $user_template = [
            'id' => 1,
            'first_name' => 'first',
            'last_name' => 'last',
            'username' => 'user',
        ];

        $chat_template = [
            'id' => 1,
            'first_name' => 'first',
            'last_name' => 'last',
            'username' => 'name',
            'type' => 'private',
            'all_members_are_administrators' => false,
        ];

        $data = [
            'update_id' => mt_rand(),
            'message' => [
                'message_id' => mt_rand(),
                'from' => $user_template,
                'chat' => $chat_template,
                'date' => time(),
                'text' => $command,
            ],
        ];

        return json_encode($data);
    }

    public function testInvalidJson(): void
    {
        $client = static::createClient();
        $client->request('POST', '/webhook');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testForbidden(): void
    {
        $client = static::createClient();
        $msg = json_decode($this->fakeMessage(), true);
        $msg['message']['from']['id'] = 100;
        $client->request('POST', '/webhook', [], [], [], json_encode($msg));

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testStart(): void
    {
        $httpClient = new MockHttpClient(
            new MockResponse(
                $mockResponse,
                ['http_code' => 200, 'response_headers' => ['Content-Type: application/json']]
            )
        );
        $stub = $this->getMockBuilder($httpClient)->onlyMethods(['sendMessage'])->getMock();

        $client = static::createClient();
        self::$container->set();
        $client->request('POST', '/webhook', [], [], [], $this->fakeMessage('/start'));

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(['user_exists' => true], $response);
    }
}
