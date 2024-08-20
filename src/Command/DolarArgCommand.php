<?php

namespace App\Command;

use App\Event\SocialEvent;
use App\Services\DolarArgScrapService;
use App\Services\ScrapInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:dolar-arg',
    description: 'Add a short description for your command',
)]
class DolarArgCommand extends Command
{
    private DolarArgScrapService $scrapService;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        ScrapInterface $scrapService,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct();
        $this->scrapService = $scrapService;
        $this->dispatcher = $dispatcher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $scrap = $this->scrapService->scrapSources();
        $scrap->saveData();
        $post = $scrap->preparePost();
        $io->info('Enviando data a redes disponibles.');
        $event = new SocialEvent($post);
        $this->dispatcher->dispatch($event, SocialEvent::SEND_POST);
        $io->success('Comando completado.');

        return Command::SUCCESS;
    }
}
