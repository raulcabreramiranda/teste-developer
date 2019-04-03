<?php

namespace App\Console\Commands;

use App\User;
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
                'name' => 'UserAPI',
                'url' => 'http://'.$localHost.':'.$localPort.'/api/users'
            )
        ));
        if ($resKong->getStatusCode() == 201) {
            $service = json_decode($resKong->getBody(), true);
            print_r("Register - ".$service['name'] . " - " . $service['id'] . "\n");
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
        }

        $this->info("\n\nFeito !!!!!!!! - " . $kongPath);
    }
}

