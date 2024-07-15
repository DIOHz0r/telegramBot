<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Services\TgBotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookController extends AbstractController
{

    #[Route('/webhook', name: 'app_webhook')]
    public function index(Request $request, TgBotService $botService, EntityManagerInterface $entityManager)
    {
        $data = $request->getPayload()->getIterator();
        if (!$data->valid()) {
            return new JsonResponse(null, 400);
        }
        if (isset($data['message'])) {
            // private messages.
            return $this->privateMsgResponse($data['message'], $botService, $entityManager);
        } elseif (isset($data['my_chat_member'])) {
            // chat member event.
            return $this->chatMemberResponse($data['my_chat_member'], $botService, $entityManager);
        } elseif (isset($data['channel_post'])) {
            // channel messages.
        }

        return new JsonResponse(null, 200);
    }

    /**
     * @param $message
     * @param TgBotService $botService
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function privateMsgResponse(
        $message,
        TgBotService $botService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if ($this->getParameter('app.tg_owner_id') != $message['from']['id']) {
            return new JsonResponse(null, 200);
        }

        $chatId = $message['chat']['id'];
        $parts = explode(' ', $message['text']);
        switch ($parts[0]) {
            case '/start':
            case '/ping':
                $botService->sendMessage([
                    'chat_id' => $chatId,
                    'parse_mode' => 'MarkdownV2',
                    'text' => 'Hola *'.$message['from']['first_name'].'*',
                ]);
                break;
            case '/list_channels':
                $channels = $entityManager->getRepository(Channel::class)->findAll();
                $text = $channels ? implode("\n", array_map('strval', $channels)) : 'No hay canales disponibles';
                $botService->sendMessage([
                    'chat_id' => $chatId,
                    'parse_mode' => 'Markdown',
                    'text' => $text,
                ]);
                break;
            case '/edit_channel_data':
                $serviceName = $parts[1] ?? null;
                $channelId = $parts[2] ?? null;
                if (!$channelId || !$serviceName) {
                    $botService->sendMessage([
                        'chat_id' => $chatId,
                        'parse_mode' => 'Markdown',
                        'text' => 'Faltan los argumentos requeridos',
                    ]);
                    break;
                }
                $channel = $entityManager->getRepository(Channel::class)->find($channelId);
                if (!$channel) {
                    $botService->sendMessage([
                        'chat_id' => $chatId,
                        'parse_mode' => 'Markdown',
                        'text' => 'Id de canal no encontrado',
                    ]);
                    break;
                }
                $channel->setService($serviceName);
                $channel->setEnvironment($parts[3] ?? null);
                $entityManager->persist($channel);
                $entityManager->flush();
                $botService->sendMessage([
                    'chat_id' => $chatId,
                    'parse_mode' => 'Markdown',
                    'text' => 'Canal y servicio actualizado',
                ]);
                break;
        }

        return new JsonResponse(null, 200);
    }

    public function chatMemberResponse(
        $message,
        TgBotService $botService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $newChatMember = $message['new_chat_member'];
        if ($newChatMember['user']['id'] != $this->getParameter('app.tg_bot_id')) {
            return new JsonResponse(null, 200);
        }
        $channelId = $message['chat']['id'];
        $channelName = $message['chat']['title'];
        $entity = $entityManager->getRepository(Channel::class)->find($channelId);
        switch ($newChatMember['status']) {
            case 'left':
            case 'kicked':
                if ($entity) {
                    $entityManager->remove($entity);
                    $entityManager->flush();
                }
                $botService->sendMessage([
                    'chat_id' => $this->getParameter('app.tg_owner_id'),
                    'text' => 'El bot abandono el canal: '.$channelName,
                ]);
                break;
            case 'administrator':
            case 'restricted':
                if (!$entity) {
                    $entity = new Channel();
                }
                $entity->setId($channelId);
                $entity->setName($channelName);
                $entityManager->persist($entity);
                $entityManager->flush();
                $botService->sendMessage([
                    'chat_id' => $this->getParameter('app.tg_owner_id'),
                    'text' => 'Bot agregado al canal '.$channelName.' ('.$channelId.'), falta configurar el servicio.',
                ]);
                break;
        }

        return new JsonResponse(null, 200);
    }
}
