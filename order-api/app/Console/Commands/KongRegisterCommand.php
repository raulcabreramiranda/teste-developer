<?php

namespace App\Console\Commands;

use App\Order;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class KongRegisterCommand extends Command
{
    protected $signature = 'kong:register {urlKong : The URL Kong} {urlService : The URL Service}';

    protected $description = "Registrando serviço de Pedidos em Kong";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $arguments = $this->arguments();
        $kongPath = $arguments['urlKong'];
        $localHost = $arguments['urlService'];

        $client = new Client(['http_errors' => false]);

        $resKong = $client->request('POST', $kongPath . "/services", array(
            'form_params' => array(
                'name' => 'OrderAPI',
                'url' => $localHost.'/api/orders'
            )
        ));
        if ($resKong->getStatusCode() == 201) {
            $service = json_decode($resKong->getBody(), true);


            $resConsumer = $client->request('POST', $kongPath . "/consumers", array(
                'form_params' => array(
                    'username' => 'ConsumerAuthAPI',
                    'custom_id' => uniqid(true),
                )
            ));
            $consumer = json_decode($resConsumer->getBody(), true);
            $resConsumerKey = $client->request('POST', $kongPath . "/consumers/ConsumerAuthAPI/key-auth", array(
                'form_params' => array(
                    'key' => 'secret_consumer_auth_api',
                )
            ));
            if($resConsumerKey->getStatusCode() == 200 || $resConsumerKey->getStatusCode() == 201){
                print_r("Register Customer - ConsumerAuthAPI"."\n");
            }

            $resKongPlugin = $client->request('POST',
                $kongPath . "/services/" . $service['id'] . "/plugins", array(
                    'form_params' => array(
                        'name' => 'key-auth'
                    )
                ));


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
        }else {
            $service = json_decode($resKong->getBody(), true);
            dd($service);
        }

        $this->info("\n\nFeito !!!!!!!! - " . $kongPath. '/order-api?apikey=secret_consumer_auth_api');
    }
}

