<?php

namespace App\Console\Commands;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class KongRegisterCommand extends Command
{
    protected $signature = 'kong:register {urlKong : The URL Kong} {urlService : The URL Service}';

    protected $description = "Indexa todos os pedidos para elasticsearch";

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
                'name' => 'UserAPI',
                'url' => $localHost.'/api/users'
            )
        ));
        if ($resKong->getStatusCode() == 201) {
            $service = json_decode($resKong->getBody(), true);
            print_r("Register - ".$service['name'] . " - " . $service['id'] . "\n");


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
                        'name' => 'FrontUserAPI_GET',
                        'methods' => "GET",
                        'paths' => '/user-api'
                    )
                ));
            $resKongRoutesPost = $client->request('POST', $kongPath . "/services/" . $service['id'] . "/routes", array(
                'form_params' => array(
                    'name' => 'FrontUserAPI_POST',
                    'methods' => "POST", 'paths' => '/user-api'
                )
            ));
            $resKongRoutesPut = $client->request('POST', $kongPath . "/services/" . $service['id'] . "/routes", array(
                'form_params' => array(
                    'name' => 'FrontUserAPI_PUT',
                    'methods' => "PUT", 'paths' => '/user-api'
                )
            ));
            $resKongRoutesDelete = $client->request('POST', $kongPath . "/services/" . $service['id'] . "/routes", array(
                'form_params' => array(
                    'name' => 'FrontUserAPI_DELETE',
                    'methods' => "DELETE", 'paths' => '/user-api'
                )
            ));
            $resKongRoutesOption = $client->request('POST', $kongPath . "/services/" . $service['id'] . "/routes", array(
                'form_params' => array(
                    'name' => 'FrontUserAPI_OPTION',
                    'methods' => "OPTION", 'paths' => '/user-api'
                )
            ));
        }else {
            $service = json_decode($resKong->getBody(), true);
            dd($service);
        }

        $this->info("\n\nFeito !!!!!!!! - " . $kongPath);
    }
}

