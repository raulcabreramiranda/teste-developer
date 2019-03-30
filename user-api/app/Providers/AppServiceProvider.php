<?php

namespace App\Providers;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use App\Repositories\UsersRepository;
use App\Repositories\EloquentUsersRepository;
use App\Repositories\ElasticsearchUsersRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UsersRepository::class, function ($app) {
            if (!config('services.search.enabled')) {
                return new EloquentUsersRepository();
            }
            return new ElasticsearchUsersRepository(
                $app->make(Client::class)
            );

        });
        $this->bindSearchClient();
    }

    private function bindSearchClient()
    {
        $this->app->bind(Client::class, function ($app) {
            return ClientBuilder::create()
                ->setHosts(config('services.search.hosts'))
                ->build();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
