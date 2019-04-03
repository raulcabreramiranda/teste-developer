<?php

namespace App\Console\Commands;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class KongUnregisterCommand extends Command
{
    protected $signature = 'kong:unregister';

    protected $description = "Indexa todos os pedidos para elasticsearch";


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $kongPath = env('KONG_PATH');

        $client = new Client(['http_errors' => false]);

        // Obtendo os dados do usuario
        $resKong = $client->request('GET', $kongPath . "/services");
        if ($resKong->getStatusCode() === 200) {
            $services = json_decode($resKong->getBody(), true);

            foreach ($services['data'] as $service) {
                print_r("Unregister - " . $service['name'] . " - " . $service['id'] . "\n");
                if ($service['name'] == "UserAPI") {
                    $resKongRoutes = $client->request('GET', $kongPath . "/services/" . $service['id'] . "/routes");
                    $routes = json_decode($resKongRoutes->getBody(), true);

                    foreach ($routes['data'] as $route) {
                        $resKongRoutes = $client->request('DELETE', $kongPath . "/routes/" . $route['id']);
                        print_r("--> " . $route['name'] . " - " . $route['id'] . "\n");
                    }
                    $resKongRoutes = $client->request('DELETE', $kongPath . "/services/" . $service['id']);
                }
            }
        }

        $this->info("\n\nFeito !!!!!!!! - " . $kongPath);
    }
}
