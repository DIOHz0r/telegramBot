<?php

namespace App;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Contracts\HttpClient\ResponseInterface;

trait HttpClientHandler
{
    public function checkErrors(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        $level = (int)floor($statusCode / 100);
        if ($level != 2) {
            if ($level === 4) {
                $label = 'Client error';
                $className = ClientException::class;
            } elseif ($level === 5) {
                $label = 'Server error';
                $className = ServerException::class;
            } else {
                throw new BadRequestException('Error sending request');
            }
            throw new $className($response, $statusCode);
        }
    }
}