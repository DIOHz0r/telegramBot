<?php

namespace App\Command;

use App\Entity\Channel;
use App\Services\DolarArgScrapService;
use App\Services\ScrapInterface;
use App\Services\TgBotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:dolar-arg',
    description: 'Add a short description for your command',
)]
class DolarArgCommand extends Command
{
    private TgBotService $tgBotService;
    private EntityManagerInterface $entityManager;
    private DolarArgScrapService $scrapService;

    public function __construct(
        TgBotService $tgBotService,
        EntityManagerInterface $entityManager,
        ScrapInterface $scrapService
    ) {
        parent::__construct();
        $this->tgBotService = $tgBotService;
        $this->entityManager = $entityManager;
        $this->scrapService = $scrapService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $channels = $this->entityManager->getRepository(Channel::class)->findBy(
            ['service' => 'DolarArgScrapService']
        );
        foreach ($channels as $channel) {
            $scrap = $this->scrapService->scrapSources();
            $scrap->saveData();
            $post = $scrap->preparePost();
            if ($post) {
                $this->tgBotService->sendMessage([
                    'chat_id' => $channel->getId(),
                    'text' => $post,
                    'parse_mode' => 'Markdown',
                ]);
            }
        }
        $io->success('Comando completado sin problemas');

        return Command::SUCCESS;
    }
}
