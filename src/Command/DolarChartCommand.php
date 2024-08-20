<?php

namespace App\Command;

use App\Entity\ScrapedData;
use App\Event\SocialEvent;
use Doctrine\ORM\EntityManagerInterface;
use mitoteam\jpgraph\MtJpGraph;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:dolar-chart',
    description: 'Add a short description for your command',
)]
class DolarChartCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected KernelInterface $kernel,
        protected EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Fetch and fix data history.
        $data = $this->entityManager->getRepository(ScrapedData::class)->findLastMonthData();
        $fixed_data = [];
        foreach ($data as $item) {
            $real_date = $item['timestamp']->format('d');
            foreach ($item['data'] as $index => $sub_data) {
                $title = ucwords(str_replace('_', ' ', $index));
                $sub_data['compra'] = str_replace(',', '.', $sub_data['compra']);
                $sub_data['venta'] = str_replace(',', '.', $sub_data['venta']);
                $fixed_data[$title][$real_date][] = (($sub_data['compra'] + $sub_data['venta']) / 2);
            }
        }
        foreach ($fixed_data as $index => $list) {
            foreach ($list as $key => $values) {
                $fixed_data[$index][$key] = round(array_sum($values) / count($values), 2);
            }

        }

        // Create graph
        MtJpGraph::load('line', true);
        $graph = new \Graph(1280, 720);
        $graph->SetScale("textlin");
        $graph->SetMargin(60, 40, 40, 100);
        $graph->title->Set($data[0]['timestamp']->format('m Y'));
        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false, false);
        $graph->xgrid->Show();
        $graph->xgrid->SetLineStyle("solid");
        $graph->xaxis->SetTickLabels(array_keys($fixed_data['Dolar Blue']));
        $colors = [
            'Dolar Oficial' => 'cyan',
            'Dolar Banco Nacion' => 'darkcyan',
            'Dolar Turista' => 'deepskyblue3',
            'Dolar Blue' => 'blue',
            'Dolar Mep' => 'green',
            'Dolar Cripto' => 'orange',
            'Dolar Ccl' => 'darkgreen',
            'Dolar Mayorista' => 'red',
            'Euro' => 'violet',
            'Euro Blue' => 'darkviolet',
        ];
        foreach ($fixed_data as $index => $sub_data) {
            $line = new \LinePlot(array_values($sub_data));
            $graph->Add($line);
            $line->SetLegend($index);
            $line->SetColor($colors[$index]);
            $line->SetWeight(2);
            $line->mark->SetType(MARK_FILLEDCIRCLE);
            $line->mark->SetFillColor($colors[$index]);
            $line->mark->SetColor($colors[$index]);
        }
        $graph->legend->SetColumns(count($fixed_data) / 2);
        $graph->legend->SetFrameWeight(1);
        $graph->legend->SetPos(0.5, 0.98, 'center', 'bottom');

        $graph->Stroke(_IMG_HANDLER);
        $fileName = $this->kernel->getProjectDir()."/public/dolar_graph.png";
        $graph->img->Stream($fileName);

        // Trigger publish event.
        $event = new SocialEvent(
            [
                'attach' => $fileName,
                'caption' => 'Variación de precios del mes anterior para los distintos tipos de cambio.',
            ]
        );
        $this->dispatcher->dispatch($event, SocialEvent::SEND_PHOTO);

        // Output.
        $io = new SymfonyStyle($input, $output);
        $io->success('Comando completado.');

        return Command::SUCCESS;
    }
}
