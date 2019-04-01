<?php

namespace App\Repositories;

use App\Order;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Collection;

class ElasticsearchOrdersRepository implements OrdersRepository
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


    public function searchByUserId(array $ids = array()): Collection
    {
        $items = $this->searchTermsOnElasticsearch("user_id", $ids);

        return $this->buildCollection($items);
    }

    private function searchOnElasticsearch(string $query): array
    {
        $instance = new Order;

        $items = $this->search->search([
            'index' => $instance->getSearchIndex(),
            'type' => $instance->getSearchType(),
            'body' => [
                'query' => [
                    'multi_match' => [
                        'fields' => ['item_description'],
                        'query' => $query,
                    ],
                ],
            ],
        ]);
        return $items;
    }

    private function searchTermsOnElasticsearch(string $field, array $terms): array
    {
        $instance = new Order;

        $items = $this->search->search([
            'index' => $instance->getSearchIndex(),
            'type' => $instance->getSearchType(),
            'body' => [
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'terms' => [
                                $field => $terms,
                            ],
                        ],
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
        return Order::hydrate($sources);
    }
}
