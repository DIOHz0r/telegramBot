<?php

namespace App\EventListener;

use App\Entity\Channel;
use App\Event\SocialEvent;
use App\Services\TgBotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'app.social.send_post', method: 'onAppSocialSendPost')]
#[AsEventListener(event: 'app.social.send_photo', method: 'onAppSocialSendPhoto')]
final class SendPostTelegramListener
{
    private TgBotService $tgBotService;
    private EntityManagerInterface $entityManager;

    public function __construct(TgBotService $tgBotService, EntityManagerInterface $entityManager)
    {
        $this->tgBotService = $tgBotService;
        $this->entityManager = $entityManager;
    }

    public function onAppSocialSendPost(SocialEvent $event): void
    {
        $post = $event->getData();
        if (!$post) {
            return;
        }
        $channels = $this->entityManager->getRepository(Channel::class)->findBy(
            ['service' => 'DolarArgScrapService']
        );
        foreach ($channels as $channel) {
            $this->tgBotService->sendMessage([
                'chat_id' => $channel->getId(),
                'text' => $post,
                'parse_mode' => 'Markdown',
            ]);
        }

    }

    public function onAppSocialSendPhoto(SocialEvent $event)
    {
        $data = $event->getData();
        if(!$data) {
            return;
        }
        $channels = $this->entityManager->getRepository(Channel::class)->findBy(
            ['service' => 'DolarArgScrapService']
        );
        foreach ($channels as $channel) {
            $this->tgBotService->sendPhoto([
                'chat_id' => $channel->getId(),
                'parse_mode' => 'Markdown',
                'type' => 'photo',
            ] + $data);
        }
    }
}
