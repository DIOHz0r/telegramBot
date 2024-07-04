<?php

namespace App\Controller;

use App\TgBotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/webhook', name: 'app_webhook')]
    public function index(Request $request, TgBotService $botService)
    {
        $data = $request->getPayload()->getIterator();
        if (!$data->valid()) {
            return new Response(null, 400);
        }
        $message = $data['message'];
        if ($this->getParameter('app.tg_owner_id') != $message['from']['id']) {
            return new Response(null, 403);
        }

        $content = null;
        switch ($message['text']) {
            case '/start':
                $chatId = $message['chat']['id'];
                $this->sendMessage($chatId, 'Hello '.$message['from']['first_name'].', you said: start');
            case '/stop':
                $chatId = $message['chat']['id'];
                $this->sendMessage($chatId, 'Bye '.$message['from']['first_name']);
                break;
            default:
                break;
        }

        return new Response();
    }

    private function sendMessage($chatId, $text)
    {
        $url = $this->getParameter('app.tg_api_url').'/bot'.$this->getParameter('app.tg_bot_token').'/sendMessage';
        $this->httpClient->request('POST', $url, [
            'json' => [
                'chat_id' => $chatId,
                'text' => $text,
            ],
        ]);
    }
}
