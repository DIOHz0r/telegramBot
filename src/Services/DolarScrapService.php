<?php

namespace App\Services;

use App\Entity\ScrapedData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DolarScrapService implements ScrapInterface
{

    const SOURCES = [
        'dolar_blue' => 'https://mercados.ambito.com/dolar/informal/variacion',
        'dolar_banco_nacion' => 'https://mercados.ambito.com/dolarnacion/variacion',
        'euro' => 'https://mercados.ambito.com/euro/variacion',
        'dolar_turista' => 'https://mercados.ambito.com/dolarturista/variacion',
        'dolar_cripto' => 'https://mercados.ambito.com/dolarcripto/variacion',
        'dolar_ccl' => 'https://mercados.ambito.com/dolarrava/cl/variacion',
        'dolar_oficial' => 'https://mercados.ambito.com/dolar/oficial/variacion',
        'dolar_mep' => 'https://mercados.ambito.com/dolarrava/mep/variacion',
        'euro_blue' => 'https://mercados.ambito.com/euro/informal/variacion',
        'dolar_mayorista' => 'https://mercados.ambito.com/dolar/mayorista/variacion',
        'dolar_futuro' => 'https://mercados.ambito.com/dolarfuturo/variacion',
    ];

    protected HttpClientInterface $httpClient;

    protected mixed $data = null;
    protected EntityManagerInterface $entityManager;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
    }

    public function scrapSources()
    {
        $scraped = [];
        foreach (self::SOURCES as $type => $url) {
            $response = $this->httpClient->request('GET', $url);
            if ($response->getStatusCode() != 200) {
                continue;
            }
            $data = json_decode($response->getContent(), true);
            if (!$data) {
                continue;
            }
            $scraped[] = $data;
        }
        $this->data = $scraped;
    }

    public function saveData()
    {
        if ($this->data === null) {
            return;
        }
        $scrapedData = new ScrapedData();
        $scrapedData->setSourceType(__CLASS__);
        $scrapedData->setData($this->data);
        $em = $this->entityManager;
        $em->persist($scrapedData);
        $em->flush();
    }

    public function getData()
    {
        return $this->data;
    }

    public function preparePost()
    {
        $post = '';
        $data = $this->data;
        foreach ($data as $type => $info) {
            switch ($info['class-variacion']) {
                case 'up':
                    $icon = '🔺';
                    break;
                case 'down':
                    $icon = '🔻';
                    break;
                case 'equal':
                    $icon = '🔹';
                    break;
                default:
                    $icon = '';
                    break;
            }
            $post .= "*".ucwords(str_replace('_', ' ', $type)).":*\n";
            $post .= "*compra:* ".$info['compra']." - *venta:* ".$info['venta']."\n";
            $post .= "*variacion:* ".$info['variacion']." ".$icon."\n\n";
        }
        return $post;
    }
}