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


    public function getFranceDataLoc(Location $location): array
    {
        $queryParams = '?q='.str_replace(' ','+',$location->getStreet()).'&postcode='.$location->getPostcode();

        try {
            $response = $this->client->request(
                'GET',
                'https://api-adresse.data.gouv.fr/search/'.$queryParams
            );
        }catch (\Exception $e){
            $response = [];
            dump($e);
        }

        return $response->toArray();
    }

}