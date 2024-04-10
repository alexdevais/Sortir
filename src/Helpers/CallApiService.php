<?php

namespace App\Helpers;

use App\Entity\Location;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallApiService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getFranceDataFromStreet($street)
    {
        try {
            $response = $this->client->request(
                'GET',
                'https://api-adresse.data.gouv.fr/search/?q='.$street
            );
        }catch (\Exception $e){
            $response = [];
            dump($e);
        }

        return $response->toArray();
    }
}