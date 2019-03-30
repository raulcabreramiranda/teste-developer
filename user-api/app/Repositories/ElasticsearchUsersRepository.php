<?php

namespace App\Repositories;

use App\User;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Collection;

class ElasticsearchUsersRepository implements UsersRepository
{
    private $search;

    public function __construct(Client $client) {
        $this->search = $client;
    }

    public function search(string $query = ""): Collection
    {
        $items = $this->searchOnElasticsearch($query);

        return $this->buildCollection($items);
    }

    private function searchOnElasticsearch(string $query): array
    {
        $instance = new User;

        $items = $this->search->search([
            'index' => $instance->getSearchIndex(),
            'type' => $instance->getSearchType(),
            'body' => [
                'query' => [
                    'multi_match' => [
                        'fields' => ['cpf', 'email', 'name'],
                        'query' => $query,
                    ],
                ],
            ],
        ]);

        return $items;
    }

    private function buildCollection(array $items): Collection
    {
        $hits = array_pluck($items['hits']['hits'], '_source') ?: [];

        $sources = array_map(function ($source) {
            return $source;
        }, $hits);

        // Convertendo o array de resultados em Eloquent Models.
        return User::hydrate($sources);
    }
}
