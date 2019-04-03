<?php

namespace App\Console\Commands;

use App\Order;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class KongRegisterCommand extends Command
{
    protected $signature = 'kong:register';

    protected $description = "Indexa todos os pedidos para elasticsearch";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $kongPath = env('KONG_PATH');
        $localHost = env('LOCAL_HOST');
        $localPort = env('LOCAL_PORT');

        $client = new Client(['http_errors' => false]);

        $resKong = $client->request('POST', $kongPath . "/services", array(
            'form_params' => array(
                'name' => 'OrderAPI',
                'url' => 'http://'.$localHost.':'.$localPort.'/api/orders'
            )
        ));
        if ($resKong->getStatusCode() == 201) {
            $service = json_decode($resKong->getBody(), true);
            $resKongRoutesGet = $client->request('POST',
                $kongPath . "/services/" . $service['id'] . "/routes", array(
                    'form_params' => array(
                        'name' => 'FrontOrderAPI_GET',
                        'methods' => "GET",
                        'paths' => '/order-api'
                    )
                ));
            $resKongRoutesPost = $client->request('POST', $kongPath . "/services/" . $service['id'] . "/routes", array(
                'form_params' => array(
                    'name' => 'FrontOrderAPI_POST',
                    'methods' => "POST", 'paths' => '/order-api'
                )
            ));
            $resKongRoutesPut = $client->request('POST', $kongPath . "/services/" . $service['id'] . "/routes", array(
                'form_params' => array(
                    'name' => 'FrontOrderAPI_PUT',
                    'methods' => "PUT", 'paths' => '/order-api'
                )
            ));
            $resKongRoutesDelete = $client->request('POST', $kongPath . "/services/" . $service['id'] . "/routes", array(
                'form_params' => array(
                    'name' => 'FrontOrderAPI_DELETE',
                    'methods' => "DELETE", 'paths' => '/order-api'
                )
            ));
            $resKongRoutesOption = $client->request('POST', $kongPath . "/services/" . $service['id'] . "/routes", array(
                'form_params' => array(
                    'name' => 'FrontOrderAPI_OPTION',
                    'methods' => "OPTION", 'paths' => '/order-api'
                )
            ));
        }

        $this->info("\n\nFeito !!!!!!!! - " . $kongPath);
    }
}

